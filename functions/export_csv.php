<?php

include("../config/config.php");

try {
    // Fetch all non-excluded entries ordered by ticket number
    $stmt = $conn->prepare("SELECT * FROM raffle_entries WHERE Excluded = 0 AND Exported = 0 ORDER BY Ticket_Number ASC");
    $stmt->execute();
    $entries = $stmt->fetchAll(PDO::FETCH_OBJ);

    // Set CSV headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="anniversaya_entries.csv"');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Write CSV headers
    fwrite($output, "MobileNo,ETicket,Total\n");

    // Group by Invoice Number + Branch
    $groupedEntries = [];
    $entryIdsToUpdate = [];

    foreach ($entries as $entry) {
        $key = $entry->Invoice_Number . '|' . $entry->Branch;

        if (!isset($groupedEntries[$key])) {
            $groupedEntries[$key] = [
                'tickets' => [],
                'mobiles' => []
            ];
        }

        $groupedEntries[$key]['tickets'][] = $entry->Ticket_Number;

        // Normalize mobile number to Philippines format starting with "63"
        $mobile = preg_replace('/\D/', '', $entry->Mobile_Number); // Remove non-digit characters

        if (strpos($mobile, '63') === 0) {
            // Already starts with 63, keep as is
        } elseif (strpos($mobile, '0') === 0) {
            // Starts with 0, replace leading 0 with 63
            $mobile = '63' . substr($mobile, 1);
        } elseif (strpos($mobile, '9') === 0) {
            // Starts with 9 (no leading 0), prepend 63
            $mobile = '63' . $mobile;
        } else {
            // Fallback for anything else, just prepend 63
            $mobile = '63' . $mobile;
        }

        // Add if not already included
        if (!in_array($mobile, $groupedEntries[$key]['mobiles'])) {
            $groupedEntries[$key]['mobiles'][] = $mobile;
        }
    }

    foreach ($groupedEntries as $data) {
        $tickets = $data['tickets'];
        sort($tickets);

        $ticketDisplay = count($tickets) === 1
            ? $tickets[0]
            : min($tickets) . ' - ' . max($tickets);

        $totalTickets = count($tickets);

        $mobileNumbers = implode(', ', $data['mobiles']);

        fwrite($output, "{$mobileNumbers},{$ticketDisplay},{$totalTickets}\n");

        foreach ($data['tickets'] as $ticketNumber) {
            $entryIdsToUpdate[] = $ticketNumber;
        }
    }

    fclose($output);
    // After exporting the entries, update the "Exported" column for the selected ticket numbers
    if (!empty($entryIdsToUpdate)) {
        $placeholders = implode(',', array_fill(0, count($entryIdsToUpdate), '?'));
        $updateStmt = $conn->prepare("UPDATE raffle_entries SET Exported = 1 WHERE Ticket_Number IN ($placeholders)");
        $updateStmt->execute($entryIdsToUpdate);
    }

    // Collect unique Invoice + Branch pairs
    $responsePairs = [];

    foreach ($groupedEntries as $key => $data) {
        [$invoice, $branch] = explode('|', $key);
        $responsePairs["$invoice|$branch"] = [$invoice, $branch];
    }

    // SHOULD USE DOCNUM UPON UPDATING RESPONSES
    $updateResponse = $conn->prepare("EXEC UpdateExportedResponses ?,?,?");

    $batchStmt = $conn->query("SELECT ISNULL(MAX(Batch), 0) + 1
    FROM raffle_entries");

    $batch = $batchStmt->fetchColumn();

    foreach ($responsePairs as [$invoice, $branch]) {
        $updateResponse->execute([$invoice, $branch, $batch]);
    }
    exit();
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    echo 'Database error: ' . $e->getMessage();
    exit;
}
