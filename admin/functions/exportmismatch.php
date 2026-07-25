<?php

include("../../config/connection.php");

try {
    $stmt = $conn->prepare("SELECT 
            docId,
            cust_Id,
            DocNum,
            Branch,
            Invoice_Number,
            First_Name,
            Last_Name,
            Suffix,
            InvAmt,
            InvName,
            InvDate
        FROM mismatch
        WHERE Exported IS NULL OR Exported = 0
        ORDER BY id ASC");
    $stmt->execute();
    $entries = $stmt->fetchAll(PDO::FETCH_OBJ);

    if (empty($entries)) {
        die("No mismatch records available for export.");
    }

    // Set CSV headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="mismatch.csv"');

    // Open output stream
    $output = fopen('php://output', 'w');

    fwrite($output, "\xEF\xBB\xBF");

    // Write CSV headers
    // fwrite($output, "Branch,Invoice,First Name,Last Name,Suffix\n");
    fputcsv($output, [
        'DocId',
        'CustomerId',
        'DocNum',
        'Branch',
        'Invoice',
        'FirstName',
        'LastName',
        'Suffix',
        'InvoiceAmt',
        'InvoiceName',
        'InvoiceDate'
    ]);

    $entryIdsToUpdate = [];

    foreach ($entries as $entry) {
        fputcsv($output, [
            $entry->docId,
            $entry->cust_Id,
            $entry->DocNum,
            $entry->Branch,
            $entry->Invoice_Number,
            $entry->First_Name,
            $entry->Last_Name,
            $entry->Suffix,
            $entry->InvAmt,
            $entry->InvName,
            $entry->InvDate
        ]);

        $entryIdsToUpdate[] = $entry->Invoice_Number;
    }
    fclose($output);

    if (!empty($entryIdsToUpdate)) {
        $placeholders = implode(
            ',',
            array_fill(0, count($entryIdsToUpdate), '?')
        );

        $updateStmt = $conn->prepare("UPDATE mismatch
            SET Exported = 1
            WHERE Invoice_Number IN ($placeholders)
        ");

        $updateStmt->execute($entryIdsToUpdate);
    }

    exit();
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    echo 'Database error: ' . $e->getMessage();
    exit;
}
