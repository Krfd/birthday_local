<?php
include("functions/conn.php");

$table = 'users';

try {
    if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['clear_table'])) {
        $conn->exec("TRUNCATE TABLE [$table]");

        $pass = password_hash("Password", PASSWORD_DEFAULT);
        $insert = $conn->prepare("INSERT INTO users (username, password, dept, disabled, isAdmin, created)
        VALUES (:username, :password, :dept, :disabled, :isAdmin, SYSDATETIME())");

        $uname = "Karlalooo";
        $dept = "";
        $disabled = 0;
        $isAdmin = 1;

        $insert->bindParam(":username", $uname);
        $insert->bindParam(":password", $pass);
        $insert->bindParam(":dept", $dept);
        $insert->bindParam(":disabled", $disabled);
        $insert->bindParam(":isAdmin", $isAdmin);
        $insert->execute();

        header("Location: dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
