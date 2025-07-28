<?php

$authorized_users = ['karlalooo' => 'iap', 'merruel' => 'iap', 'jummie' => 'iap'];
$user_ = $_SERVER['PHP_AUTH_USER'] ?? '';
$pass_ = $_SERVER['PHP_AUTH_PW'] ?? '';

if (!isset($authorized_users[$user_]) || $authorized_users[$user_] !== $pass_) {
    // header('WWW-Authenticate: Basic realm="Developer Toggle"');
    $realm = 'Developer Toggle ' . time(); // Use time or session to make it dynamic
    header('WWW-Authenticate: Basic realm="' . $realm . '"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'ASA 😝';
    exit;
}

$servername = "192.168.101.68";
$db = "RAFFLE";
$username = "sa";
$password = "SB1Admin";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $users = [
        ['username' => 'Karlalooo',    'raw_password' => 'Password',   'dept' => null, 'region' => null,   'isAdmin' => 1, 'updated' => 0, 'prev_pass' => null, 'new_pass' => null],
        ['username' => 'Merruel',    'raw_password' => '9Password',   'dept' => null, 'region' => null,   'isAdmin' => 1, 'updated' => 0, 'prev_pass' => null, 'new_pass' => null],
        ['username' => 'Jummie',    'raw_password' => '9Password',   'dept' => null, 'region' => null,   'isAdmin' => 1, 'updated' => 0, 'prev_pass' => null, 'new_pass' => null],
        ['username' => 'game1',        'raw_password' => '9Password',  'dept' => null, 'region' => null, 'isAdmin' => 0, 'updated' => 0, 'prev_pass' => null, 'new_pass' => null],
        ['username' => 'controller1',  'raw_password' => '9Password',  'dept' => null, 'region' => null, 'isAdmin' => 0, 'updated' => 0, 'prev_pass' => null, 'new_pass' => null],
    ];

    $check = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username");

    $insert = $conn->prepare("
        INSERT INTO users (username, password, dept, region, disabled, isAdmin, updated, prev_pass, new_pass, created, modified)
        VALUES (:username, :password, :dept, :region, 0, :isAdmin, :updated, :prev_pass, :new_pass, SYSDATETIME(), SYSDATETIME())
    ");

    foreach ($users as $user) {
        // Check if user already exists
        $check->execute([':username' => $user['username']]);
        if ($check->fetchColumn() > 0) {
            echo "{$user['username']} already exists<br>";
            continue;
        }

        // Hash password and insert new user
        $hashed_password = password_hash($user['raw_password'], PASSWORD_DEFAULT);
        $insert->execute([
            ':username' => $user['username'],
            ':password' => $hashed_password,
            ':dept'     => $user['dept'],
            ':region'   => $user['region'],
            ':isAdmin'  => $user['isAdmin'],
            ':updated'  => $user['updated'],
            ':prev_pass'  => $user['prev_pass'],
            ':new_pass'  => $user['new_pass']

        ]);
        echo "{$user['username']} created<br>";
    }
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
