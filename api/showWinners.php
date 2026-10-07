<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("../config/connection.php");

header('Content-Type: application/json');

try {

    $winners = $conn->prepare("EXEC ShowWinners");
    $winners->execute();

    $getWinners = $winners->fetchAll(PDO::FETCH_OBJ);

    echo json_encode([
        'status' => 'success',
        'winners' => $getWinners
    ]);
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
