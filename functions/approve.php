<?php

if (isset($_GET['inv'])) {
    $inv = $_GET['inv'];
    $branch = $_GET['branch'];
    $id = $_GET['id'];

    include("../config/connection.php");

    try {
        $included = $conn->prepare("UPDATE mismatch SET Updated = 1 WHERE Invoice_Number = :inv AND Branch = :branch AND id = :id");
        $included->bindParam(":inv", $inv);
        $included->bindParam(":branch", $branch);
        $included->bindParam(":id", $id);
        $included->execute();

        $responses = $conn->prepare("UPDATE responses SET InValid = 'N' WHERE Branch = :branch AND id = :id");
        $responses->bindParam(":branch", $branch);
        $responses->bindParam(":id", $id);
        $responses->execute();

        echo "approved";
        exit();
    } catch (PDOException $e) {
        errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    }
}
