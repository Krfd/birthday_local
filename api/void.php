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

    $table_name = 'toVoid';
    $raffle_table = 'raffle_entries';

    $stmt = $conn->query("SELECT * FROM [$table_name]");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $ticketed = 0;

    foreach ($rows as $row) {

        $sap_docnum = trim($row['DocNum']);

        $count_stmt = $conn->prepare("SELECT COUNT(*) FROM [$raffle_table] WHERE DocNum = :DocNum");
        $count_stmt->execute([':DocNum' => $sap_docnum]);
        $ticket_count = (int) $count_stmt->fetchColumn();

        try {
            $ticketed += $ticket_count;
            $conn->prepare("UPDATE [$raffle_table] SET Excluded = 1 WHERE DocNum = :DocNum")->execute([':DocNum' => $sap_docnum]);
        } catch (PDOException $e) {
            errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
        }
    }

    echo "<br>✅ Processed " . count($rows) . " docnum(s).<br>";
    echo "🎟️ $ticketed ticket(s) has been voided.<br>";
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    die("Connection failed: " . $e->getMessage());
}
