<?php

if (isset($_GET['id'])) {
    $access_id = $_GET['id'];

    include("../../config/connection.php");

    try {
        $enable = $conn->prepare("UPDATE users SET disabled = 0 WHERE id = :id");
        $enable->bindParam(":id", $access_id);
        $enable->execute();


        echo "enabled";
        exit();
    } catch (PDOException $e) {
        errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    }
}
