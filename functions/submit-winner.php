<?php

// $authorized_users = ['karlalooo' => 'iap', 'merruel' => 'iap', 'jummie' => 'iap'];
// $user_ = $_SERVER['PHP_AUTH_USER'] ?? '';
// $pass_ = $_SERVER['PHP_AUTH_PW'] ?? '';

// if (!isset($authorized_users[$user_]) || $authorized_users[$user_] !== $pass_) {
//     // header('WWW-Authenticate: Basic realm="Developer Toggle"');
//     $realm = 'Developer Toggle ' . time(); // Use time or session to make it dynamic
//     header('WWW-Authenticate: Basic realm="' . $realm . '"');
//     header('HTTP/1.0 401 Unauthorized');
//     echo 'ASA 😝';
//     exit;
// }

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("config/connection.php");

$data = json_decode(file_get_contents("php://input"), true);

try {
    // Check if the data is received
    if ($data && isset($data['winnerName']) && isset($data['winnerTicket'])) {
        $winnerName = $data['winnerName'];
        $winnerTicket = $data['winnerTicket'];
        $winnerArea = $data['region'];
        $winnerCategory = $data['category'];
        $winnerPrize = $data['prize'];

        $winner = $conn->prepare("INSERT INTO winners (winner, winningTicket, area, category, prize, dateWon) VALUES (:winner, :winningTicket, :area, :category, :prize, SYSDATETIME())");
        $winner->bindParam(":winner", $winnerName);
        $winner->bindParam(":winningTicket", $winnerTicket);
        $winner->bindParam(":area", $winnerArea);
        $winner->bindParam(":category", $winnerCategory);
        $winner->bindParam(":prize", $winnerPrize);
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
