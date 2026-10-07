<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
include("../config/connection.php");

$data = json_decode(file_get_contents("php://input"), true);

try {
    if ($data && isset($data['winnerName']) && isset($data['winnerTicket'])) {
        $winnerName = $data['winnerName'];
        $winnerTicket = $data['winnerTicket'];
        $cust_id = $data['cust_id'];
        $winnerArea = $data['region'];
        $winnerCategory = $data['category'];
        $winnerPrize = $data['prize'];
        $winnerBranch = $data['branch'];

        $updateCustId = $conn->prepare("EXEC UpdateCustId ?");
        $updateCustId->execute([$cust_id]);

        $ins_winner = $conn->prepare("EXEC insertWinner ?,?,?,?,?,?");
        $ins_winner->execute([$winnerName, $winnerTicket, $winnerBranch, $winnerArea, $winnerCategory, $winnerPrize]);

        echo json_encode(["status" => "success", "message" => "Winner details submitted"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid data"]);
    }
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
