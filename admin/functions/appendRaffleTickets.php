<?php

include("../../config/connection.php");

try {
    $recycleValidation = $conn->prepare("SELECT * FROM recycle WHERE Validated = 0");
    $recycleValidation->execute();

    $recycle = $recycleValidation->fetchAll(PDO::FETCH_OBJ);

    $forTickets = $conn->prepare("EXEC AppendRaffleTickets ?,?,?,?,?,?,?,?,?,?,?,?");

    foreach ($recycle as $tick) {
        $BatchNo = $tick->Batch;
        $documentId = $tick->docId;
        $customerId = $tick->cust_Id;

        $validation = $conn->prepare("SELECT TOP 1 1 FROM mismatch WHERE docId = ? 
        AND cust_Id = ? 
        AND Validated <> 1 
        AND First_Name IS NOT NULL
        AND LTRIM(RTRIM(First_Name)) <> ''
        AND Last_Name IS NOT NULL
        AND LTRIM(RTRIM(Last_Name)) <> ''");
        $validation->execute([$documentId, $customerId]);

        $valids = $validation->fetchAll(PDO::FETCH_OBJ);

        if ($valids) {
            foreach ($valids as $valid) {
                $docId = $tick->docId;
                $counter = $tick->counter;
                $cust_Id = $tick->cust_Id;
                $docNum = $tick->DocNum;
                $branch = $tick->Branch;
                $firstName = $tick->First_Name;
                $lastName = $tick->Last_Name;
                $suffix = $tick->Suffix;
                $inv = $tick->Invoice_Number;
                $amt = $tick->InvAmt;
                $mobile = $tick->Mobile_Number;
                $email = $tick->Email;

                $forTickets->execute(
                    [
                        $BatchNo,
                        $counter,
                        $cust_Id,
                        $docNum,
                        $branch,
                        $firstName,
                        $lastName,
                        $suffix,
                        $inv,
                        $amt,
                        $mobile,
                        $email
                    ]
                );
            }
        } else {
            $invalid = $conn->prepare("UPDATE recycle SET Invalid = 1 WHERE docId = ?");
            $invalid->execute([$documentId]);
        }
    }

    echo json_encode([
        'isSuccess' => 'success',
        'message' => 'Recycle migration completed successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'isSuccess' => 'error',
        'message' => $e->getMessage()
    ]);
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
