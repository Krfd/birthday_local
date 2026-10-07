<?php

file_put_contents("log.txt", date("Y-m-d H:i:s") . " - Script started\n", FILE_APPEND);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function errorHandler($errno, $errstr, $errfile, $errline)
{
    date_default_timezone_set('Asia/Manila');
    $logMessage = "[" . date("Y-m-d H:i:s") . "] Error: [$errno] $errstr in $errfile on line $errline" . PHP_EOL;
    file_put_contents(__DIR__ . '/error_log.txt', $logMessage, FILE_APPEND);
}
set_error_handler("errorHandler");

function generate_name_variants($str)
{
    $variants = [$str];
    $n_to_enye = str_ireplace('n', 'ñ', $str);
    $enye_to_n = str_ireplace('ñ', 'n', $str);

    if (!in_array($n_to_enye, $variants, true)) {
        $variants[] = $n_to_enye;
    }
    if (!in_array($enye_to_n, $variants, true)) {
        $variants[] = $enye_to_n;
    }
    return array_unique($variants);
}

$servername = "192.168.101.68";
$db = "BIRTHDAY_RAFFLE";
$username = "sa";
$password = "SB1Admin";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $table_name = 'responses';
    $raffle_table = 'raffle_entries';

    $stmt = $conn->query("SELECT * FROM [$table_name]");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $raffle_stmt = $conn->prepare("INSERT INTO [$raffle_table] 
        ([Counter], [cust_Id], [Branch], [First_Name], [Last_Name], [Suffix], [Mobile_Number], [Excluded], [Cancel], [Exported], [hasWon])
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // ORIGINAL
    // $existing_check = $conn->prepare("SELECT COUNT(*) FROM [$raffle_table] WHERE [Branch] = ? AND (([First_Name] = ? AND [Last_Name] = ? AND [Suffix] = ?) OR ([First_Name] = ? AND [Last_Name] = ? AND [Suffix] = ?))");
    $existing_check = $conn->prepare("SELECT COUNT(*) FROM [$raffle_table] WHERE [Branch] = ? AND (([First_Name] = ? AND [Last_Name] = ? AND [Suffix] = ?) OR ([First_Name] = ? AND [Last_Name] = ? AND [Suffix] = ?))");

    // VALIDATION
    $validation = $conn->prepare("EXEC ValidResponses");

    // $ticketed = 0;

    foreach ($rows as $row) {
        $docId = $row['id'];
        $first_name = trim($row['First_Name'] ?? '');
        $last_name = trim($row['Last_Name'] ?? '');
        $suffix = trim($row['Suffix'] ?? '');

        $validated = strtoupper(trim($row['Validated'] ?? 'N'));
        $invalid = strtoupper(trim($row['InValid'] ?? 'N'));
        $cancel = strtoupper(trim($row['Cancel'] ?? 'N'));

        $validated = ($validated === 'Y') ? 'Y' : 'N';
        $invalid = ($invalid === 'Y') ? 'Y' : 'N';
        $cancel = ($cancel === 'Y') ? 'Y' : 'N';

        $validation->execute();

        $results = [];

        $results = $validation->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $validation_result) {

            $Ticket_Number = trim($validation_result['Ticket_Number'] ?? null);
            $firstName = trim($validation_result['First_Name'] ?? null);
            $lastName = trim($validation_result['Last_Name'] ?? null);
            $ticketCount = $validation_result['TicketCount'];

            $id = $validation_result['id'] ?? null;
            $customerId = $validation_result['cust_Id'] ?? null;
            $mobile = $validation_result['Mobile_Number'] ?? null;
            $branchPurchased = $validation_result['Branch'] ?? null;

            if (!$existing_check->fetchColumn()) {

                $exists_doc = $conn->prepare("SELECT COUNT(*) FROM [$raffle_table] WHERE [Ticket_Number] = ?");
                $exists_doc->execute([$Ticket_Number]);

                if (!$exists_doc->fetchColumn()) {
                    for ($i = 0; $i < $ticketCount; $i++) {
                        $raffle_stmt->execute(
                            [
                                $ticketCount,
                                $customerId,
                                $branchPurchased,
                                $firstName,
                                $lastName,
                                $suffix,
                                $mobile,
                                0,
                                0,
                                0,
                                0
                            ]
                        );
                    }
                    // $ticketed += $ticketCount;
                    $conn->prepare("UPDATE [$table_name] SET Validated = 'Y' WHERE id = ?")->execute([$id]);
                }
            }
        }
    }

    echo "<br>✅ Processed " . count($rows) . " response(s).<br>";
    // echo "🎟️ $ticketed ticket(s) generated.<br>";
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    die("Connection failed: " . $e->getMessage());
}
