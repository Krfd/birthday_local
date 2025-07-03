<?php

include("../config/config.php");

// EMPLOYEE DETAILS
$user_id = trim(htmlspecialchars($_POST['user_id']));
$user_dept = trim(htmlspecialchars($_POST['user_dept']));
$user_name = trim(htmlspecialchars($_POST['username']));

// CUSTOMER DETAILS

$first = trim(htmlspecialchars($_POST['first']));
$last = trim(htmlspecialchars($_POST['last']));
$fname = trim(htmlspecialchars($_POST['fname']));
$lname = trim(htmlspecialchars($_POST['lname']));
$branch = trim(htmlspecialchars($_POST['branch']));
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
            $update = $conn->prepare("UPDATE responses SET First_Name = :firstname, Last_Name = :lastname WHERE First_Name = :first AND Last_Name = :last AND branch = :branch");
            $update->bindParam(":firstname", $fname);
            $update->bindParam(":lastname", $lname);
            $update->bindParam(":first", $first);
            $update->bindParam(":last", $last);
            $update->bindParam(":branch", $branch);
            $update->execute();
        }
    }

    $customerName = $first . ' ' . $last;
    $newCustomerName = $fname . ' ' . $lname;

    // INSERT USER LOGS
    $log = $conn->prepare("INSERT INTO logs (user_id, customer_name, new_customer_name, customer_inv, username, dept, modified)
    VALUES (:user_id, :customer_name, :new_customer_name, :customer_inv, :username, :dept, SYSDATETIME())");
    $log->bindParam(":user_id", $user_id);
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
