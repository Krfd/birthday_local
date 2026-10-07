<?php
// include("../config/connection.php");
session_start();

function errorHandler($errno, $errstr, $errfile, $errline)
{
    date_default_timezone_set('Asia/Manila');
    $logMessage = "[" . date("Y-m-d H:i:s") . "] Error: [$errno] $errstr in $errfile on line $errline" . PHP_EOL;
    file_put_contents('error_log.txt', $logMessage, FILE_APPEND);
}

set_error_handler("errorHandler");

$servername = "SOFTDEV-KARL\SQLEXPRESS";
$db = "BIRTHDAY_RAFFLE";
$username = "";
$password = "";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    die("Connection failed: " . $e->getMessage());
}

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header("Location: ./login.html");
    exit();
}

$name = htmlspecialchars($_POST['username']);
$pass = htmlspecialchars($_POST['password']);

try {

    $query = $conn->prepare("EXEC Login ?");
    $query->execute([$name]);

    $rows = $query->fetchAll(PDO::FETCH_OBJ);

    if ($rows) {
        foreach ($rows as $row) {
            if (password_verify($pass, $row->password)) {
                if ($row->disabled == 1) {
                    echo "banned";
                    exit();
                } else {
                    if ($row->username == $name && $row->isAdmin == 1) {
                        $_SESSION['id'] = $row->id;
                        echo "admin";
                        exit();
                    } else {
                        if ($name === "controller1" && $row->isAdmin == 0) {
                            $_SESSION['id'] = $row->id;
                            echo 'controller';
                            exit();
                        } else if ($name === "game1" && $row->isAdmin == 0) {
                            $_SESSION['id'] = $row->id;
                            echo "game";
                            exit();
                        } else if ($row->username === $name && $row->dept === "AR") {
                            $_SESSION['id'] = $row->id;
                            echo "ar";
                            exit();
                        } else if ($row->username === $name) {
                            $_SESSION['id'] = $row->id;
                            echo "user";
                            exit();
                        } else {
                            echo "invalid";
                            exit();
                        }
                    }
                }
            } else {
                echo "invalid";
                exit();
            }
        }
    } else {
        echo "unknown";
        exit();
    }
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    echo $e->getMessage();
    exit();
}
