<?php

header('Content-Type: application/json');
// UPDATED
$stateFile = 'raffle_state.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = file_get_contents('php://input');
    $decoded = json_decode($data, true);

    $existing = file_exists($stateFile) ? json_decode(file_get_contents($stateFile), true) : [];

    // Merge new data with existing
    $merged = array_merge($existing, $decoded);

    file_put_contents($stateFile, json_encode($merged));
    echo 'OK';
    exit;
}

if (file_exists($stateFile)) {
    echo file_get_contents($stateFile);
} else {
    echo json_encode(["status" => "idle", "excludedTicketNumbers" => []]);
}
