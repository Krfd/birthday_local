<?php

$servername = "192.168.101.68";
$db = "RAFFLE";
$username = "sa";
$password = "SB1Admin";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $password = password_hash("9Password", PASSWORD_DEFAULT);

    $insert = $conn->prepare("INSERT INTO users (username, password, dept, disabled, isAdmin, created) 
    VALUES ('controller1', :password, '', 0, 0, SYSDATETIME())");
    $insert->bindParam(":password", $password);

    if ($insert->execute()) {
        echo "Control access created";
        exit();
    }
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
