<?php

include("../../config/connection.php");

try {
    $stmt = $conn->prepare("SELECT 
            docId,
            cust_Id,
            TicketCount,
            DocNum,
            Branch,
            First_Name,
            Last_Name,
            Invoice_Number,
            Suffix,
            InvAmt,
            InvName,
            -- InvDate,
            Mobile_Number,
            Email,
            Findings
        FROM mismatch
        WHERE Exported IS NULL OR Exported = 0
        ORDER BY id ASC");
    $stmt->execute();
    $entries = $stmt->fetchAll(PDO::FETCH_OBJ);

    if (empty($entries)) {
        die("No mismatch records available for export.");
    }

    $stmtBatch = $conn->query("SELECT COALESCE(MAX(Batch), 100000) + 1 AS NextBatch
        FROM mismatch
        WHERE Batch IS NOT NULL AND Exported = 0
    ");

    $batchNo = $stmtBatch->fetch(PDO::FETCH_ASSOC)['NextBatch'];

    // Set CSV headers
    header('Content-Type: text/csv; charset=utf-8');
    // header('Content-Disposition: attachment; filename="batch.csv"');
    header("Content-Disposition: attachment; filename=\"batch_{$batchNo}.csv\"");

    // Open output stream
    $output = fopen('php://output', 'w');

    fwrite($output, "\xEF\xBB\xBF");

    // Write CSV headers
    // fwrite($output, "Branch,Invoice,First Name,Last Name,Suffix\n");
    fputcsv($output, [
        'Batch',
        'docId',
        'counter',
        'cust_Id',
        'DocNum',
        'Branch',
        'First_Name',
        'Last_Name',
        'Suffix',
        'Invoice_Number',
        'InvAmt',
        'InvoiceName',
        // 'InvoiceDate',
        'Mobile_Number',
        'Email',
        'Remarks',
        'Validated',
        'Invalid'
    ]);

    $entryIdsToUpdate = [];

    foreach ($entries as $entry) {
        fputcsv($output, [
            $batchNo,
            $entry->docId,
            $entry->TicketCount,
            $entry->cust_Id,
            $entry->DocNum,
            $entry->Branch,
            $entry->First_Name,
            $entry->Last_Name,
            $entry->Suffix,
            $entry->Invoice_Number,
            $entry->InvAmt,
            $entry->InvName,
            // $entry->InvDate,
            $entry->Mobile_Number,
            $entry->Email,
            $entry->Findings,
            0,
            0
        ]);

        $entryIdsToUpdate[] = $entry->Invoice_Number;
    }
    fclose($output);

    // if (!empty($entryIdsToUpdate)) {
    //     $placeholders = implode(
    //         ',',
    //         array_fill(0, count($entryIdsToUpdate), '?')
    //     );

    //     $updateStmt = $conn->prepare("UPDATE mismatch
    //         SET Exported = 1,
    //         WHERE Invoice_Number IN ($placeholders)
    //     ");
    //     $updateStmt->execute($entryIdsToUpdate);
    // }

    if (!empty($entryIdsToUpdate)) {

        $placeholders = implode(',', array_fill(0, count($entryIdsToUpdate), '?'));

        $sql = "UPDATE mismatch
        SET Exported = 1,
            Batch = ?
        WHERE Invoice_Number IN ($placeholders)";

        $updateStmt = $conn->prepare($sql);

        $params = array_merge([$batchNo], $entryIdsToUpdate);

        $updateStmt->execute($params);
    }

    exit();
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    echo 'Database error: ' . $e->getMessage();
    exit;
}
