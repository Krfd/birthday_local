<?php
function errorHandler($errno, $errstr, $errfile, $errline)
{
    date_default_timezone_set('Asia/Manila');
    $logMessage = "[" . date("Y-m-d H:i:s") . "] Error: [$errno] $errstr in $errfile on line $errline" . PHP_EOL;
    file_put_contents('error_log.txt', $logMessage, FILE_APPEND);
}

set_error_handler("errorHandler");

$servername = "192.168.101.68";
$db = "RAFFLE";
$username = "sa";
$password = "SB1Admin";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    die("Connection failed: " . $e->getMessage());
}


$branches = ["North", "South", "East", "West", "Central"];
$firstNames = ["John", "Jane", "Alex", "Chris", "Pat", "Taylor", "Jordan", "Morgan"];
$lastNames = ["Smith", "Johnson", "Brown", "Davis", "Miller", "Wilson", "Moore", "Taylor"];

function generateInvoiceNumber($index)
{
    return str_pad($index + 1000, 4, "0", STR_PAD_LEFT); // Start from 1001
}

try {
    $stmt = $conn->prepare("INSERT INTO customers (inv, inv_amount, branch, cust_name) VALUES (?, ?, ?, ?)");

    for ($i = 1; $i <= 10; $i++) {
        $inv = generateInvoiceNumber($i);

        // Generate amount between 1000 and 40000
        $inv_amount = rand(100000, 4000000) / 100.0;

        $branch = $branches[array_rand($branches)];
        $cust_name = $firstNames[array_rand($firstNames)] . " " . $lastNames[array_rand($lastNames)];

        $stmt->execute([$inv, $inv_amount, $branch, $cust_name]);
    }

    echo "✅ 10 dummy customers inserted successfully.";
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    echo "❌ Error inserting data: " . $e->getMessage();
}
