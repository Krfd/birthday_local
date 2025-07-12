<?php

include("conn.php");

if (isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['dept']) && !empty($_POST['dept'])) {
    $user = trim(htmlspecialchars($_POST['username']));
    $dept = trim(htmlspecialchars($_POST['dept']));
} else {
    echo "Username and department are required.";
    exit();
}

try {

    $exists = $conn->prepare("SELECT * FROM users WHERE username = :username AND dept = :dept");
    $exists->bindParam(":username", $user);
    $exists->bindParam(":dept", $dept);
    $exists->execute();

    $exist = $exists->fetchAll(PDO::FETCH_OBJ);

    if (!$exist) {
        $readable_pass = 'iap' . $dept;
        $password = password_hash('iap' . $dept, PASSWORD_DEFAULT);

        $insert = $conn->prepare("INSERT INTO users (username, password, dept, disabled, isAdmin, created) 
        VALUES (:username, :password, :dept, 0, 0, SYSDATETIME())");
        $insert->bindParam(":username", $user);
        $insert->bindParam(":password", $password);
        $insert->bindParam(":dept", $dept);
        $insert->execute();

        echo "added";
        exit();
    } else {
        echo "exists";
        exit();
    }
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
