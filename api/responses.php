<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function errorHandler($errno, $errstr, $errfile, $errline)
{
    date_default_timezone_set('Asia/Manila');
    $logMessage = "[" . date("Y-m-d H:i:s") . "] Error: [$errno] $errstr in $errfile on line $errline" . PHP_EOL;
    file_put_contents('error_log.txt', $logMessage, FILE_APPEND);
}

set_error_handler("errorHandler");

date_default_timezone_set('Asia/Manila');

$servername = "192.168.101.68";
$db = "ANNIVERSAYA";
$username = "sa";
$password = "SB1Admin";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch CSV
    $csv_url = 'https://docs.google.com/spreadsheets/d/1qflQZPkC22AnoB9EtC4PurYV2R4-6mzfwdQVBeEhtro/export?format=csv&gid=1195666039';
    $csv_content = file_get_contents($csv_url);

    // DEFINE ENCRYPTION KEY AND INITIALIZATION VECTOR
    $encryption_key = 'ENCRYPTING CUSTOMERS DATA';
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($iv_length);

    // ENCRYPT CSV DATA
    $encrypted_data = openssl_encrypt($csv_content, 'aes-256-cbc', $encryption_key, 0, $iv);
    $encrypted_data_with_iv = base64_encode($iv . $encrypted_data);


    // DECRYPT CSV DATA
    $decoded_data = base64_decode($encrypted_data_with_iv);

    // EXTRACT IV FROM THE DECODED DATA
    $iv_from_data = substr($decoded_data, 0, $iv_length);
    $encrypted_data_from_storage = substr($decoded_data, $iv_length);

    $decrypted_data = openssl_decrypt($encrypted_data_from_storage, 'aes-256-cbc', $encryption_key, 0, $iv_from_data);

    if ($decrypted_data === false) die("❌ Error fetching decrypted data.");

    $rows = str_getcsv($decrypted_data, "\n");

    array_shift($rows);

    $branch_map = [
        'BACOLOD - ARANETA' => 'BACOLOD',
        'BACOLOD - MANDALAGAN' => 'BACOLOD DOS',
        'BUTUAN - M. CALO' => 'BUTUAN',
        'BUTUAN - JC AQUINO' => 'BUTUAN DOS',
        'KALIBO - REGALADO' => 'KALIBO',
        'KALIBO - ANDAGAO' => 'KALIBO DOS',
        'LAGAO GENERAL SANTOS' => 'LAGAO',
        'LUCENA - BAYAN' => 'LUCENA',
        'LUCENA - IBABANG DUPAY' => 'LUCENA DOS',
        'MEGA SHOWROOM' => 'SHOWROOM',
        'OZAMIZ' => 'OZAMIS',
        'PLAZA - DELGADO' => 'PLAZA',
        'ROXAS - ROXAS AVENUE' => 'ROXAS',
        'ROXAS - LAWA-AN' => 'ROXAS DOS',
        'ROXAS MINDORO' => 'ORMIN ROXAS',
        'SAN PEDRO - LAGUNA' => 'SAN PEDRO',
        'SAN PEDRO - PALAWAN' => 'SNPEDRO PALAWAN',
        'TAGUM - QUEZON' => 'TAGUM',
        'TAGUM - BONIFACIO' => 'TAGUM DOS',
        'BAYBAY LEYTE' => 'BAYBAY',
        'SILANG CAVITE' => 'SILANG',
        'ZAMBOANGA - PUTIK' => 'ZAMBOANGA',
        'ZAMBOANGA - VETERANS' => 'ZAMBOANGA DOS'
    ];

    $stmt = $conn->prepare("EXEC InsertResponses ?,?,?,?,?,?,?,?,?,?,?");

    $count = 0;

    // Get the current highest Cust_Id from the database
    $maxCustStmt = $conn->query("SELECT ISNULL(MAX(cust_Id), 0) FROM responses");

    $nextCustId = (int) $maxCustStmt->fetchColumn() + 1;

    // Store mobile number => Cust_Id mappings
    $customerMap = [];

    $latestTimestampStmt = $conn->query("SELECT MAX(Timestamp) FROM responses");
    $latestTimestamp = $latestTimestampStmt->fetchColumn();
    if ($latestTimestamp) {
        $latestTimestamp = new DateTime($latestTimestamp);
    }

    $today = (new DateTime())->format('Y-m-d');

    foreach ($rows as $row) {
        $columns = str_getcsv($row);

        if (count($columns) < 13) continue;

        $csvTimestamp = DateTime::createFromFormat('n/j/Y H:i:s', trim($columns[0]));

        if (!$csvTimestamp) {
            echo "Skipping invalid timestamp: {$columns[0]}<br>";
            continue;
        }

        if ($csvTimestamp->format('Y-m-d') === $today) {
            echo "Skipping today's record: {$columns[4]} ({$columns[0]})<br>";
            continue;
        }

        if ($latestTimestamp && $csvTimestamp <= $latestTimestamp) {
            continue;
        }

        $mobileNumber = trim($columns[5]);

        if (isset($customerMap[$mobileNumber])) {

            // Same mobile number encountered in this import
            $cust_Id = $customerMap[$mobileNumber];
        } else {

            // Check if this mobile number already exists in the database
            $custStmt = $conn->prepare("SELECT TOP 1 cust_Id FROM responses WHERE Mobile_Number = ?");

            $custStmt->execute([$mobileNumber]);

            $existingCustId = $custStmt->fetchColumn();

            if ($existingCustId !== false) {

                // Existing mobile number gets its existing Cust_Id
                $cust_Id = (int) $existingCustId;
            } else {

                // New mobile number gets a new incremental Cust_Id
                $cust_Id = $nextCustId++;
            }

            // Remember this mobile number for the rest of this import
            $customerMap[$mobileNumber] = $cust_Id;
        }

        $consent1 = strtolower(trim($columns[9]));
        $consent2 = strtolower(trim($columns[10]));
        $consent3 = strtolower(trim($columns[11]));
        $consent4 = strtolower(trim($columns[12]));

        // Skip if any consent is not "yes"
        if ($consent1 !== 'yes' || $consent2 !== 'yes' || $consent3 !== 'yes' || $consent4 !== 'yes') {
            echo "❌ Skipping record due to missing consent (Invoice: {$columns[4]})\n";
            continue;
        }

        // Normalize branch name based on mapping
        $original_branch = trim($columns[1]);
        // $original_branch = trim($branch);
        $normalized_branch = $branch_map[$original_branch] ?? $original_branch;

        $email = strtolower(trim($columns[6]));

        $original_ref = "$original_branch | $columns[4] | {$columns[5]} | " . mb_strtoupper("{$columns[2]} {$columns[3]}", 'UTF-8');


        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM responses WHERE Ref = :ref");
        $checkStmt->execute([':ref' => $original_ref]);

        $existingCount = $checkStmt->fetchColumn();

        if ($existingCount > 0) {
            echo "Skipping duplicate: $original_ref\n";
            continue;
        }

        $stmt->execute([
            $cust_Id,
            $columns[0],
            $normalized_branch,
            mb_strtoupper($columns[2], 'UTF-8'),
            mb_strtoupper($columns[3], 'UTF-8'),
            isset($columns[14]) ? mb_strtoupper(trim($columns[14]), 'UTF-8') : '',
            $columns[4],
            $columns[5],
            strtolower(trim($columns[6])),
            mb_strtoupper($columns[7], 'UTF-8'),
            $original_ref
        ]);
        $count++;

        echo "About to insert: {$columns[4]} | {$columns[5]}<br>";
    }

    $log = date("Y-m-d H:i:s") . " Imported: $count rows\n";

    file_put_contents(
        "import.log",
        $log,
        FILE_APPEND
    );

    echo "✅ All new data inserted successfully into the 'responses' table.\n";
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    file_put_contents(
        "error.log",
        $e->getMessage(),
        FILE_APPEND
    );
    die("Connection failed: " . $e->getMessage());
}
