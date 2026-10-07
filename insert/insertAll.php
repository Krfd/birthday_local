<?php

$servername = "192.168.101.68";
$db = "BIRTHDAY_RAFFLE";
$username = "sa";
$password = "SB1Admin";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $users = [
        ['username' => 'Karlalooo',    'raw_password' => 'Password', 'isAdmin' => 1],
        ['username' => 'game1',        'raw_password' => '9Password', 'isAdmin' => 0],
        ['username' => 'controller1',  'raw_password' => '9Password', 'isAdmin' => 0],
    ];

    $check = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username");

    $insert = $conn->prepare("
        INSERT INTO users (username, password, isAdmin, created)
        VALUES (:username, :password, :isAdmin, SYSDATETIME())
    ");

    foreach ($users as $user) {
        $check->execute([':username' => $user['username']]);
        if ($check->fetchColumn() > 0) {
            echo "{$user['username']} already exists<br>";
            continue;
        }

        $hashed_password = password_hash($user['raw_password'], PASSWORD_DEFAULT);
        $insert->execute([
            ':username' => $user['username'],
            ':password' => $hashed_password,
            ':isAdmin'  => $user['isAdmin'],
        ]);
        echo "{$user['username']} created<br>";
    }
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
