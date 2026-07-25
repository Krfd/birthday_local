<?php

if (isset($_GET['inv'])) {
    $inv = $_GET['inv'];
    $branch = $_GET['branch'];

    include("../../config/connection.php");

    try {

        $checkEntries = $conn->prepare("SELECT * FROM raffle_entries WHERE Invoice_Number = :inv AND Branch = :branch");
        $checkEntries->bindParam(":inv", $inv);
        $checkEntries->bindParam(":branch", $branch);
        $checkEntries->execute();

        if ($checkEntries->fetchAll()) {
            $reupdate = $conn->prepare("UPDATE mismatch SET Updated = 0 WHERE Invoice_Number = :inv AND Branch = :branch");
            $reupdate->bindParam(":inv", $inv);
            $reupdate->bindParam(":branch", $branch);
            $reupdate->execute();

            $exclude = $conn->prepare("UPDATE raffle_entries SET Excluded = 1 WHERE Invoice_Number = :inv AND Branch = :branch");
            $exclude->bindParam(":inv", $inv);
            $exclude->bindParam(":branch", $branch);
            $exclude->execute();
        } else {
            $entries = $conn->prepare("UPDATE mismatch SET Updated = 1 WHERE Invoice_Number = :inv AND Branch = :branch");
            $entries->bindParam(":inv", $inv);
            $entries->bindParam(":branch", $branch);
            $entries->execute();
        }

        echo "proceed";
        exit();
    } catch (PDOException $e) {
        errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    }
}
