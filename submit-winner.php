<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("config/connection.php");

// Get the raw POST data
$data = json_decode(file_get_contents("php://input"), true);

try {
    // Check if the data is received
    if ($data && isset($data['winnerName']) && isset($data['winnerTicket'])) {
        $winnerName = $data['winnerName'];
        $winnerTicket = $data['winnerTicket'];

        // INSERT WINNER
        $winner = $conn->prepare("INSERT INTO winners (winner, winningTicket, dateWon) VALUES (:winner, :winningTicket, SYSDATETIME())");
        $winner->bindParam(":winner", $winnerName);
        $winner->bindParam(":winningTicket", $winnerTicket);
        $winner->execute();

        // Send a success response
        echo json_encode(["status" => "success", "message" => "Winner details submitted"]);
    } else {
        // Send an error response if data is not valid
        echo json_encode(["status" => "error", "message" => "Invalid data"]);
    }
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
