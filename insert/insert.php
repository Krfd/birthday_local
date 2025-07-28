<?php

$servername = "192.168.101.68";
$db = "RAFFLE";
$username = "sa";
$password = "SB1Admin";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $password = password_hash("Password", PASSWORD_DEFAULT);

    $insert = $conn->prepare("INSERT INTO users (username, password, dept, region, disabled, isAdmin, created) VALUES ('Karlalooo', :password, '', '', 0, 1, SYSDATETIME())");
    $insert->bindParam(":password", $password);

    if ($insert->execute()) {
        echo "admin created";
        exit();
    }
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
