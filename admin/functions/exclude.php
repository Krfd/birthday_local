<?php

if (isset($_GET['inv'])) {
    $inv = $_GET['inv'];
    $branch = $_GET['branch'];

    include("../../config/connection.php");

    try {
        $included = $conn->prepare("UPDATE raffle_entries SET Excluded = 1 WHERE Invoice_Number = :inv AND Branch = :branch");
        $included->bindParam(":inv", $inv);
        $included->bindParam(":branch", $branch);
        $included->execute();

        echo "excluded";
        exit();
    } catch (PDOException $e) {
        errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    }
}
