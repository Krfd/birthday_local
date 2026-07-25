<?php

include("../config/connection.php");

try {
    // $stmt = $conn->prepare("SELECT TOP (2) First_Name, Last_Name, Ticket_Number
    // $stmt = $conn->prepare("SELECT First_Name, Last_Name, Ticket_Number
    // FROM current_entries 
    //     ce
    //     WHERE ce.Excluded = 0
    //     AND NOT EXISTS (
    //         SELECT 1
    //         FROM winners w
    //         WHERE w.winner = ce.First_Name + ' ' + ce.Last_Name
    //     )
    // ");

    // $stmt = $conn->prepare("SELECT cust_Id, First_Name, Last_Name, Ticket_Number
    // FROM current_entries WHERE hasWon <> 1");

    $stmt = $conn->prepare("SELECT 
        ce.cust_Id,
        ce.First_Name,
        ce.Last_Name,
        ce.Ticket_Number
        FROM current_entries ce
        INNER JOIN raffle_entries re
            ON ce.cust_Id = re.cust_Id
        WHERE re.hasWon <> 1 OR ce.Excluded = 0
    ");

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $users = [];

    foreach ($rows as $row) {
        $name = trim($row['First_Name'] . ' ' . $row['Last_Name']);
        $ticket = (int)$row['Ticket_Number'];
        $cust_Id = $row['cust_Id'];

        if (!isset($users[$name])) {
            $users[$name] = [];
        }
        $users[$name][] = $ticket;
    }

    $result = [];
    foreach ($users as $name => $ticket) {
        $result[] = [
            'customerName' => $name,
            'ticketNumbers' => $ticket,
            'cust_id' => $cust_Id
        ];
    }

    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
