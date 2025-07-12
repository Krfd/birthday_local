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
$fname = trim(htmlspecialchars($_POST['fname']));
$lname = trim(htmlspecialchars($_POST['lname']));
$branch = trim(htmlspecialchars($_POST['branch']));
$new_branch = trim(htmlspecialchars($_POST['new_branch']));
$inv = trim(htmlspecialchars($_POST['inv']));

try {
    $checkCustomer = $conn->prepare("SELECT * FROM mismatch WHERE First_Name = :First_Name AND Last_Name = :Last_Name AND branch = :branch");
    $checkCustomer->bindParam(":First_Name", $first);
    $checkCustomer->bindParam(":Last_Name", $last);
    $checkCustomer->bindParam(":branch", $branch);
    $checkCustomer->execute();

    $getCustomer = $checkCustomer->fetchAll(PDO::FETCH_OBJ);

    if ($getCustomer) {
        foreach ($getCustomer as $customer) {

            try {
                $update = $conn->prepare("UPDATE responses SET Branch_Purchased = :branch, First_Name = :firstname, Last_Name = :lastname WHERE id = :id");
                $update->bindParam(":branch", $new_branch);
                $update->bindParam(":firstname", $fname);
                $update->bindParam(":lastname", $lname);
                $update->bindParam(":id", $docId);
                $update->execute();
            } catch (PDOException $e) {
                errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
            }

            $updateFindings = $conn->prepare("UPDATE mismatch SET Branch = :branch, First_Name = :firstname, Last_Name = :lastname WHERE docId = :docId");
            $updateFindings->bindParam(":branch", $new_branch);
            $updateFindings->bindParam(":firstname", $fname);
            $updateFindings->bindParam(":lastname", $lname);
            $updateFindings->bindParam(":docId", $docId);
            $updateFindings->execute();
        }
    }

    $customerName = $first . ' ' . $last;
    $newCustomerName = $fname . ' ' . $lname;

    // INSERT USER LOGS
    $log = $conn->prepare("INSERT INTO logs (user_id, customer_branch, customer_name, new_customer_name, customer_inv, username, dept, modified)
    VALUES (:user_id, :customer_branch, :customer_name, :new_customer_name, :customer_inv, :username, :dept, SYSDATETIME())");
    $log->bindParam(":user_id", $user_id);
    $log->bindParam(":customer_branch", $new_branch);
    $log->bindParam(":customer_name", $customerName);
    if ($company && !empty($company)) {
        $log->bindParam(":new_customer_name", $company);
    } else {
        $log->bindParam(":new_customer_name", $newCustomerName);
    }
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
