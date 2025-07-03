<?php

include("../config/connection.php");

try {
    // Fetch data from the database
    $stmt = $conn->prepare("SELECT First_Name, Last_Name, Ticket_Number FROM raffle_entries");
    $stmt->execute(); // Ensure the statement is executed
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare the users array
    $users = [];

    foreach ($rows as $row) {
        $name = trim($row['First_Name'] . ' ' . $row['Last_Name']);
        $ticket = (int)$row['Ticket_Number'];

        if (!isset($users[$name])) {
            $users[$name] = [];
        }
        $users[$name][] = $ticket;
    }

    $result = [];
    foreach ($users as $name => $tickets) {
        $result[] = [
            'customerName' => $name,
            'ticketNumbers' => $tickets
        ];
    }

    // Return JSON response
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    // Optionally log the error for debugging
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
