<?php

file_put_contents("log.txt", date("Y-m-d H:i:s") . " - Script started\n", FILE_APPEND);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
mb_internal_encoding("UTF-8");
error_reporting(E_ALL);

function errorHandler($errno, $errstr, $errfile, $errline)
{
    date_default_timezone_set('Asia/Manila');
    $logMessage = "[" . date("Y-m-d H:i:s") . "] Error: [$errno] $errstr in $errfile on line $errline" . PHP_EOL;
    file_put_contents(__DIR__ . '/error_log.txt', $logMessage, FILE_APPEND);
}
set_error_handler("errorHandler");

$servername = "192.168.101.68";
$db = "ANNIVERSAYA";
$username = "sa";
$password = "SB1Admin";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $table_name = 'validDocNums';
    $raffle_table = 'raffle_entries';

    $stmt = $conn->query("SELECT * FROM [$table_name]");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $raffle_stmt = $conn->prepare("INSERT INTO [$raffle_table] 
        ([Counter], [DocNum], [Branch], [First_Name], [Last_Name], [Suffix], [Invoice_Number], [InvAmt], [Mobile_Number], [Email], [Postal_Address], [Excluded], [Cancel], [Exported], [Batch], [hasWon])
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $ticketed = 0;

    foreach ($rows as $row) {

        $custId = $row['id'];
        $branch_val = trim($row['Branch'] ?? '');
        $sap_docnum = trim($row['DocNum']);
        $first_name = trim($row['First_Name'] ?? '');
        $last_name = trim($row['Last_Name'] ?? '');
        $suffix = trim($row['Suffix'] ?? '');
        $mobile_val = trim($row['Mobile_Number'] ?? '');
        $inv_val = trim($row['Invoice'] ?? '');
        $email_val = trim(strtolower($row['Email'] ?? ''));
        $postal_address = trim($row['Postal_Address'] ?? '');

        $validation_proc = $conn->prepare("EXEC CustomerValidation2 @mDocNum_ = ?");

        $results = [];
        $matched = false;

        $validation_proc->execute([$sap_docnum]);
        $results = $validation_proc->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $validation_result) {
            $total_amt = (float) ($validation_result['TotalAmt'] ?? null);

            $ticket_count = floor($total_amt / 5000);
            if ($ticket_count > 0) {

                try {
                    $exists_doc = $conn->prepare("SELECT COUNT(*) FROM [$raffle_table] WHERE [DocNum] = ?");
                    $exists_doc->execute([$sap_docnum]);

                    if (!$exists_doc->fetchColumn()) {
                        for ($i = 0; $i < $ticket_count; $i++) {
                            $raffle_stmt->execute([
                                $ticket_count,
                                $sap_docnum,
                                $branch_val,
                                $first_name,
                                $last_name,
                                $suffix,
                                $inv_val,
                                $validation_result['TotalAmt'],
                                $mobile_val,
                                $email_val,
                                $postal_address,
                                0,
                                0,
                                0,
                                0,
                                0
                            ]);
                        }

                        $ticketed += $ticket_count;
                        $conn->prepare("UPDATE validDocNums SET Ticketed = 1 WHERE id = :id")->execute([':id' => $custId]);
                    }
                } catch (PDOException $e) {
                    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
                }
            }
        }
    }

    echo "<br>✅ Processed " . count($rows) . " docnum(s).<br>";
    echo "🎟️ $ticketed ticket(s) generated.<br>";
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    die("Connection failed: " . $e->getMessage());
}
