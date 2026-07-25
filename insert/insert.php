<?php

// include("./config/connection.php");
$servername = "192.168.101.68";
$db = "ANNIVERSAYA";
$username = "sa";
$password = "SB1Admin";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $password = password_hash("Password", PASSWORD_DEFAULT);

    $insert = $conn->prepare("INSERT INTO users (username, password, dept, region, disabled, isAdmin, created) VALUES ('Karlalooo', :password, '', '', 0, 1, SYSDATETIME())");
    $insert->bindParam(":password", $password);

    // $insert = $conn->prepare("UPDATE users SET password = :password WHERE id = 10");
    // $insert->bindParam(":password", $password);

    if ($insert->execute()) {
        echo "admin created";
        // echo "user updated";
        exit();
    }
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
