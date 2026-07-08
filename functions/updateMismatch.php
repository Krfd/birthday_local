<?php

include("../config/config.php");

// EMPLOYEE DETAILS
$user_id = trim(htmlspecialchars($_POST['user_id']));
$user_dept = trim(htmlspecialchars($_POST['user_dept']));
$user_name = trim(htmlspecialchars($_POST['username']));

// CUSTOMER DETAILS

$docId = trim(htmlspecialchars($_POST['docId']));
$first = trim(htmlspecialchars($_POST['first']));
$last = trim(htmlspecialchars($_POST['last']));
$suffix =  trim(htmlspecialchars($_POST['suffix']));
$fname = isset($_POST['fname']) ? trim(htmlspecialchars($_POST['fname'])) : $first;
$lname = isset($_POST['lname']) ? trim(htmlspecialchars($_POST['lname'])) : $last;
$branch = trim(htmlspecialchars($_POST['branch']));
$inv = trim(htmlspecialchars($_POST['inv']));


try {
    // $checkCustomer = $conn->prepare("SELECT * FROM mismatch WHERE docId = :docId");
    $checkCustomer = $conn->prepare("EXEC SelectMismatch ?");
    // $checkCustomer->bindParam(":docId", $docId);
    $checkCustomer->execute([$docId]);

    $getCustomer = $checkCustomer->fetchAll(PDO::FETCH_OBJ);

    if ($getCustomer) {
        foreach ($getCustomer as $customer) {
            try {

                if ($fname !== $first || $lname !== $last) {
                    // Update names only
                    // $stmt1 = $conn->prepare("UPDATE responses SET First_Name = :firstname, Last_Name = :lastname, Suffix = :suffix WHERE id = :id");
                    $stmt1 = $conn->prepare("EXEC UpdateResponse ?,?,?,?");
                    // $stmt2 = $conn->prepare("UPDATE mismatch SET First_Name = :firstname, Last_Name = :lastname, Suffix = :suffix, Updated = 1 WHERE docId = :docId");
                    $stmt2 = $conn->prepare("EXEC UpdateMismatch ?,?,?,?");

                    // $stmt1->bindParam(":firstname", $fname);
                    // $stmt1->bindParam(":lastname", $lname);
                    // $stmt1->bindParam(":suffix", $suffix);
                    // $stmt1->bindParam(":id", $docId);

                    // $stmt2->bindParam(":firstname", $fname);
                    // $stmt2->bindParam(":lastname", $lname);
                    // $stmt2->bindParam(":suffix", $suffix);
                    // $stmt2->bindParam(":docId", $docId);

                    $stmt1->execute([$fname, $lname, $suffix, $docId]);
                    $stmt2->execute([$fname, $lname, $suffix, $docId]);
                }
            } catch (PDOException $e) {
                errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
            }
        }
    }

    $customerName = $first . ' ' . $last;
    $newCustomerName = $fname . ' ' . $lname;

    // INSERT USER LOGS
    $log = $conn->prepare("INSERT INTO logs (user_id, customer_branch, customer_name, new_customer_name, customer_inv, username, dept, modified)
    VALUES (:user_id, :customer_branch, :customer_name, :new_customer_name, :customer_inv, :username, :dept, SYSDATETIME())");
    $log->bindParam(":user_id", $user_id);
    $log->bindParam(":customer_branch", $branch);
    $log->bindParam(":customer_name", $customerName);
    $log->bindParam(":new_customer_name", $newCustomerName);
    $log->bindParam(":customer_inv", $inv);
    $log->bindParam(":username", $user_name);
    $log->bindParam(":dept", $user_dept);
    $log->execute();

    echo "updated";
    exit();
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    echo $e->getMessage();
    exit();
}
