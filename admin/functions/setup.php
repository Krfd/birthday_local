<?php
include("conn.php");

// Validate inputs
if (
    isset($_POST['from'], $_POST['to']) &&
    $_POST['from'] !== '' && $_POST['to'] !== ''
) {
    $from  = trim(htmlspecialchars($_POST['from']));
    $to = trim(htmlspecialchars($_POST['to']));

    $fromDate = new DateTime($from);
    $toDate = new DateTime($to);

    if ($fromDate >= $toDate) {
        echo 'invalid';
        exit();
    }

    $conn->exec("TRUNCATE TABLE customers");
    $conn->exec("TRUNCATE TABLE mismatch");
    $conn->exec("TRUNCATE TABLE raffle_entries");
    $conn->exec("TRUNCATE TABLE invalid_invoices");
} else {
    echo "Missing/invalid inputs.";
    exit();
}

try {
    // Begin transaction
    $conn->beginTransaction();

    // Call stored procedure
    $sql = "EXEC dbo.CUSTOMER_DATA @StartDate = :from, @EndDate = :to";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':from', $from);
    $stmt->bindParam(':to', $to);
    $stmt->execute();

    $results = [];
    do {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $row;
        }
    } while ($stmt->nextRowset());

    $totalFetched = count($results);
    error_log("Fetched $totalFetched rows");

    if ($totalFetched === 0) {
        echo "No data to insert.";
        exit();
    }

    // Prepare insert
    $insert = $conn->prepare("
        INSERT INTO dbo.customers (inv, inv_amount, branch, cust_name, mobile, areaCode)
        VALUES (:inv, :inv_amount, :branch, :cust_name, :mobile, :areaCode)
    ");

    $inserted = 0;

    foreach ($results as $idx => $row) {
        $rawAddress = $row['Address'] ?? '';
        $mobile = '';

        if ($rawAddress && preg_match(
            '/(09\d{9}|\d{3}[- ]?\d{4}|\d{4}[- ]?\d{3}[- ]?\d{4})/',
            $rawAddress,
            $m
        )) {
            $mobile = $m[0];
        }

        $success = $insert->execute([
            ':inv'        => $row['U_MDN']     ?? null,
            ':inv_amount' => $row['DocTotal']  ?? null,
            ':branch'     => $row['Branch']    ?? null,
            ':cust_name'  => $row['CardName']  ?? null,
            ':mobile'     => $mobile,
            ':areaCode' => $row['PoNo']
        ]);

        if (!$success) {
            $err = $insert->errorInfo();
            error_log("Insert failed at row #$idx: " . print_r($err, true) . " Data=" . json_encode($row));
        } else {
            $inserted++;
            error_log("Inserted row #$idx successfully");
        }
    }

    // Commit if all inserted, else rollback
    if ($inserted === $totalFetched) {
        $conn->commit();
        echo "updated";
        exit();
    } else {
        $conn->rollBack();
        echo "Partial insert: $inserted of $totalFetched rows. Rolling back.";
    }
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    error_log("PDO Exception: " . $e->getMessage());
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error during processing: " . htmlspecialchars($e->getMessage());
}
