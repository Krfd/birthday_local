<?php

ini_set('display_errors', 0);
error_reporting(0);

include("conn.php");

if (isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['dept']) && !empty($_POST['dept'])) {
    $user = trim(htmlspecialchars($_POST['username']));
    $dept = trim(htmlspecialchars($_POST['dept']));

    if (isset($_POST['region']) && is_array($_POST['region'])) {
        $regionArray = array_map('trim', $_POST['region']);
        // $regionArray = array_map('htmlspecialchars', $regionArray);
        $region = implode(', ', $regionArray);
    } else {
        $region = null;
    }
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

        $insert = $conn->prepare("INSERT INTO users (username, password, dept, region, disabled, isAdmin, updated, prev_pass, new_pass, created, modified) 
        VALUES (:username, :password, :dept, :region, 0, 0, 0, null, null, SYSDATETIME(), SYSDATETIME())");
        $insert->bindParam(":username", $user);
        $insert->bindParam(":password", $password);
        $insert->bindParam(":dept", $dept);
        $insert->bindParam(":region", $region);
        $insert->execute();

        echo "added";
        exit();
    } else {
        echo "exists";
        exit();
    }
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    exit();
}
