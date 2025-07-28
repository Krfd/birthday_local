<?php

include("conn.php");

$user_id = htmlspecialchars($_POST['user_id']);
$old_password = htmlspecialchars($_POST['old']);
$new_password = htmlspecialchars($_POST['new']);
$confirm_password = htmlspecialchars($_POST['confirm']);

try {

    $query = $conn->prepare("SELECT password, username, dept, disabled FROM users WHERE id = :id");
    $query->bindParam(":id", $user_id);
    $query->execute();

    $rows = $query->fetchAll(PDO::FETCH_OBJ);

    if ($rows) {
        foreach ($rows as $row) {
            if ($new_password !== $confirm_password) {
                echo 'mismatch';
                exit();
            } else if (password_verify($old_password, $row->password)) {
                if ($row->disabled == 1) {
                    echo 'disabled';
                    exit();
                } else {
                    $newly_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update = $conn->prepare("UPDATE users SET password = :password, updated = 1, prev_pass = :old, new_pass = :new WHERE id = :id");
                    $update->bindParam(":password", $newly_hashed_password);
                    $update->bindParam(":old", $old_password);
                    $update->bindParam(":new", $new_password);
                    $update->bindParam(":id", $user_id);
                    $update->execute();

                    echo "updated";
                    exit();
                }
            } else {
                echo "invalid";
                exit();
            }
        }
    } else {
        echo "invalid";
        exit();
    }
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
