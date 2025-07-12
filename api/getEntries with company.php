<?php

include("../config/connection.php");

try {
    $stmt = $conn->prepare("SELECT First_Name, Last_Name, Ticket_Number, Company FROM raffle_entries");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $users = [];

    foreach ($rows as $row) {
        $name = trim($row['First_Name'] . ' ' . $row['Last_Name']);
        $ticket = (int)$row['Ticket_Number'];
        $company = $row['Company'] ?? '';

        if (!isset($users[$name])) {
            $users[$name] = [
                'tickets' => [],
                'company' => $company
            ];
        }

        $users[$name]['tickets'][] = $ticket;
        if (empty($users[$name]['company']) && !empty($company)) {
            $users[$name]['company'] = $company;
        }
    }

    $result = [];
    foreach ($users as $name => $data) {
        $result[] = [
            'customerName' => $name,
            'ticketNumbers' => $data['tickets'],
            'company' => $data['company'],
        ];
    }


    // Return JSON response
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
