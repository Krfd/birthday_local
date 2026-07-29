<?php

header('Content-Type: application/json');

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

$servername = "192.168.101.68";
$db = "ANNIVERSAYA";
$username = "sa";
$password = "SB1Admin";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->beginTransaction();

    $location = $_GET['location'] ?? '';
    $category = $_GET['category'] ?? '';

    if ($location && $category) {
        // $stmt = $conn->prepare("SELECT product, brand, model FROM prizes WHERE location = :location AND category = :category");
        $stmt = $conn->prepare("EXEC SelectPrize ?,?");
        // $stmt->execute([':location' => $location, ':category' => $category]);
        $stmt->execute([$location, $category]);
        $prize = $stmt->fetch(PDO::FETCH_ASSOC);

        $reset_current_entries = $conn->prepare("TRUNCATE TABLE current_entries");
        $reset_current_entries->execute();

        $location = strtoupper($location);

        // $branch_categories = $conn->prepare("SELECT branch FROM branch_category WHERE area = ?");
        $branch_categories = $conn->prepare("EXEC SelectBranch ?");
        $branch_categories->execute([$location]);

        $branches = $branch_categories->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($branches)) {

            try {
                $placeholders = implode(',', array_fill(0, count($branches), '?'));

                $insertSql = "INSERT INTO current_entries 
                (Ticket_Number, Counter, cust_Id, DocNum, Branch, First_Name, Last_Name, Invoice_Number, Mobile_Number, Email, Excluded, Exported, hasWon)
                SELECT Ticket_Number, Counter, cust_Id, DocNum, Branch, First_Name, Last_Name, Invoice_Number, Mobile_Number, Email, Excluded, Exported, hasWon
                FROM raffle_entries
                WHERE Branch IN ($placeholders)";

                $insertStmt = $conn->prepare($insertSql);
                $insertStmt->execute($branches);

                $conn->commit();
            } catch (PDOException $e) {
                $conn->rollBack();
                errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
            }
        }

        // $file = 'branches.txt';
        // file_put_contents($file, implode(PHP_EOL, $branches));

        echo json_encode($prize ?: []);
    } else {
        echo json_encode([]);
    }
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
