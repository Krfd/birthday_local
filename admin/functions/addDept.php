<?php

include("conn.php");

$dept = trim(htmlspecialchars($_POST['department']));

try {

    $exists = $conn->prepare("SELECT department FROM departments WHERE department = :department");
    $exists->bindParam(":department", $dept);
    $exists->execute();

    $exist = $exists->fetchAll(PDO::FETCH_OBJ);

    if (!$exist) {
        $insert = $conn->prepare("INSERT INTO departments (department) VALUES (:department)");
        $insert->bindParam(":department", $dept);
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
