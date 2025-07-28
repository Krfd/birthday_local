<?php

header('Content-Type: application/json');
// UPDATED
$stateFile = 'raffle_state.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = file_get_contents('php://input');
    $decoded = json_decode($data, true);

    $existing = file_exists($stateFile)
        ? json_decode(file_get_contents($stateFile), true)
        : [
            "status" => "idle",
            "finalInvoice" => "",
            "excludedTicketNumbers" => [],
            "excludedCustomerNames" => [],
        ];

    if (isset($decoded['region'])) {
        $existing['region'] = $decoded['region'];
    }
    if (isset($decoded['category'])) {
        $existing['category'] = $decoded['category'];
    }
    if (isset($decoded['prize'])) {
        $existing['prize'] = $decoded['prize'];
    }

    $allowedKeys = ["status", "finalInvoice", "excludedTicketNumbers", "excludedCustomerNames"];

    foreach ($allowedKeys as $key) {
        if (isset($decoded[$key])) {
            $existing[$key] = $decoded[$key];
        }
    }
    // Save back updated state
    file_put_contents($stateFile, json_encode($existing));

    $response = $existing;

    echo json_encode($response);
    exit;
}

if (file_exists($stateFile)) {
    session_start(); // ⬅️ Add this at the top of the GET block
    $state = json_decode(file_get_contents($stateFile), true);
    echo json_encode($state);
} else {
    echo json_encode([
        "status" => "idle",
        "excludedTicketNumbers" => [],
        "excludedCustomerNames" => [],
        "region" => "",
        "category" => "",
        "prize" => ""
    ]);
}
