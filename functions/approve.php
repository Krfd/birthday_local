<?php

include("../config/connection.php");

if (isset($_GET['inv'])) {
    $inv = $_GET['inv'];
    $branch = $_GET['branch'];
    $id = $_GET['id'];

    try {
        $included = $conn->prepare("EXEC ApproveInvoice ?,?,?");
        $included->execute([$inv, $branch, $id]);

        echo "approved";
        exit();
    } catch (PDOException $e) {
        errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    }
}
