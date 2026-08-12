<?php

include("../config/connection.php");

try {

    $stmt = $conn->prepare("SELECT
            ce.cust_Id,
            ce.First_Name,
            ce.Last_Name,
            ce.Ticket_Number,
            ce.Branch,
            ce.hasWon
        FROM current_entries ce
        JOIN raffle_entries re
            ON ce.cust_Id = re.cust_Id
        WHERE ISNULL(re.hasWon, 0) = 0
        AND ISNULL(ce.hasWon, 0) = 0
        AND ISNULL(ce.Excluded, 0) = 0
    ");

    // OFFICIAL
    // re . hasWon = 0
    //     and ce . hasWon = 0
    //     and ce . Excluded = 0

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $users = [];

    foreach ($rows as $row) {
        $name = trim(
            $row['First_Name'] . ' ' . $row['Last_Name']
        );

        $ticket = (int) $row['Ticket_Number'];
        $cust_Id = $row['cust_Id'];
        $branch = $row['Branch'];

        if (!isset($users[$name])) {
            $users[$name] = [
                'cust_id' => $cust_Id,
                'branch' => $branch,
                'ticketNumbers' => []
            ];
        }

        $users[$name]['ticketNumbers'][] = $ticket;
    }

    $result = [];

    foreach ($users as $name => $user) {
        $result[] = [
            'customerName' => $name,
            'ticketNumbers' => $user['ticketNumbers'],
            'cust_id' => $user['cust_id'],
            'branch' => $user['branch'],
        ];
    }
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
