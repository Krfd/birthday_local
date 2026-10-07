<?php

session_start();

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
$db = "BIRTHDAY_RAFFLE";
$username = "sa";
$password = "SB1Admin";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    die("Connection failed: " . $e->getMessage());
}


if (!isset($_SESSION['id'])) {
    header("Location: ../index.html");
}

$id = $_SESSION['id'];

try {
    $data = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $data->bindParam(":id", $id);
    $data->execute();

    $getData = $data->fetchAll(PDO::FETCH_OBJ);

    if ($getData) {
        foreach ($getData as $person) {
            $userId = $person->id;
            $user_name = $person->username;
            $pass = $person->password;
        }
    }
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../index.html");
    exit();
}
