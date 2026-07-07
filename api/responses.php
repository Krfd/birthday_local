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

// DB connection
$servername = "192.168.101.68";
$db = "ANNIVERSAYA";
$username = "sa";
$password = "SB1Admin";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch CSV
    $csv_url = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQhn0T0QXcsdnrllDEsSo4sQxAiFJ5jlbvYGiDkON_NHzE6hB7d10XSlaM-2qvWinKld7qFRXErkN3h/pub?gid=1957502855&single=true&output=csv';
    $csv_content = mb_convert_encoding(file_get_contents($csv_url), 'UTF-8', 'auto');

    // DEFINE ENCRYPTION KEY AND INITIALIZATION VECTOR
    $encryption_key = 'ENCRYPTING CUSTOMERS DATA';
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($iv_length);

    // ENCRYPT CSV DATA
    $encrypted_data = openssl_encrypt($csv_content, 'aes-256-cbc', $encryption_key, 0, $iv);
    $encrypted_data_with_iv = base64_encode($iv . $encrypted_data);

    echo 'Encrypted data: ' . $encrypted_data_with_iv . "\n";

    // DECRYPT CSV DATA
    $decoded_data = base64_decode($encrypted_data_with_iv);

    // EXTRACT IV FROM THE DECODED DATA
    $iv_from_data = substr($decoded_data, 0, $iv_length);
    $encrypted_data_from_storage = substr($decoded_data, $iv_length);

    $decrypted_data = openssl_decrypt($encrypted_data_from_storage, 'aes-256-cbc', $encryption_key, 0, $iv_from_data);

    echo "\nDecrypted Data: " . $decrypted_data . "\n";

    if ($decrypted_data === false) die("❌ Error fetching decrypted data.");

    echo "✅ Decrypted data fetched successfully!\n";

    // Parse CSV data
    $rows = str_getcsv($decrypted_data, "\n"); // Split data by new line to get rows

    // Skip the first row (header row)
    array_shift($rows);

    // Branch normalization mapping
    $branch_map = [
        'BACOLOD - ARANETA' => 'BACOLOD',
        'BACOLOD - MANDALAGAN' => 'BACOLOD DOS',
        'BUTUAN - M. CALO' => 'BUTUAN',
        'BUTUAN - JC AQUINO' => 'BUTUAN DOS',
        'GENSAN' => 'GENERAL SANTOS',
        'LUCENA - BAYAN' => 'LUCENA',
        'LUCENA - IBABANG DUPAY' => 'LUCENA DOS',
        'MEGA SHOWROOM' => 'SHOWROOM',
        'PLAZA - DELGADO' => 'PLAZA',
        'ROXAS - ROXAS AVENUE' => 'ROXAS',
        'ROXAS - LAWA-AN' => 'ROXAS DOS',
        'ROXAS MINDORO' => 'ORMIN ROXAS',
        'SAN PEDRO PALAWAN' => 'SNPEDRO PALAWAN',
        'TAGUM - QUEZON' => 'TAGUM',
        'TAGUM - BONIFACIO' => 'TAGUM DOS',
        'ZAMBOANGA - TETUAN' => 'ZAMBOANGA',
        'ZAMBOANGA - PUTIK' => 'ZAMBOANGA DOS',
        'BAYBAY LEYTE' => 'BAYBAY',
        'SILANG CAVITE' => 'SILANG'
    ];

    $stmt = $conn->prepare("INSERT INTO responses (
    Timestamp, Branch_Purchased, First_Name, Last_Name, Suffix,
    Invoice_Number, Mobile_Number, Email, Postal_Address, Ref,
    Validated, InValid, Cancel, SMS
) VALUES (
    :timestamp, :branch, :first_name, :last_name, :suffix,
    :invoice_number, :mobile_number, :email, :postal_address, :ref,
    :validated, :invalid, :cancel, :sms
)");

    // Loop through each row and insert if not exists
    foreach ($rows as $row) {
        $columns = str_getcsv($row); // Split row by commas to get columns

        if (count($columns) < 13) continue; // Skip incomplete rows

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
        $normalized_branch = $branch_map[$original_branch] ?? $original_branch;

        $email = strtolower(trim($columns[6]));

        $original_ref = "$original_branch | {$columns[5]} | " . mb_strtoupper("{$columns[2]} {$columns[3]}", 'UTF-8');


        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM responses WHERE Ref = :ref");
        $checkStmt->execute([':ref' => $original_ref]);

        $existingCount = $checkStmt->fetchColumn();

        if ($existingCount > 0) {
            echo "Skipping duplicate: $original_ref\n";
            continue;
        }

        $stmt->execute([
            ':timestamp' => $columns[0],
            ':branch' => $normalized_branch,
            ':first_name' => mb_strtoupper($columns[2], 'UTF-8'),
            ':last_name' => mb_strtoupper($columns[3], 'UTF-8'),
            ':suffix' => isset($columns[14]) ? mb_strtoupper(trim($columns[14]), 'UTF-8') : '',
            ':invoice_number' => $columns[4],
            ':mobile_number' => $columns[5],
            ':email' => strtolower(trim($columns[6])),
            ':postal_address' => mb_strtoupper($columns[7], 'UTF-8'),
            ':ref' => $original_ref,
            ':validated' => 'N',
            ':invalid' => 'N',
            ':cancel' => 'N',
            ':sms' => 'N'
        ]);

        echo "✅ Data inserted for Invoice Number: {$columns[4]} (Branch: {$normalized_branch})\n";

        echo "✅ Data inserted for Invoice Number: " . $columns[4] . "(Branch: " . $columns[1] . ")\n";
    }

    echo "✅ All new data inserted successfully into the 'responses' table.\n";
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    die("Connection failed: " . $e->getMessage());
}
