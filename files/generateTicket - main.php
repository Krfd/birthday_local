<?php

file_put_contents("log.txt", date("Y-m-d H:i:s") . " - Script started\n", FILE_APPEND);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function errorHandler($errno, $errstr, $errfile, $errline)
{
    date_default_timezone_set('Asia/Manila');
    $logMessage = "[" . date("Y-m-d H:i:s") . "] Error: [$errno] $errstr in $errfile on line $errline" . PHP_EOL;
    file_put_contents(__DIR__ . '/error_log.txt', $logMessage, FILE_APPEND);
}
set_error_handler("errorHandler");

function normalize_name($name)
{
    return strtolower(trim($name));
}

// DB connection
$servername = "192.168.101.68";
$db = "RAFFLE";
$username = "sa";
$password = "SB1Admin";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    $table_name = 'responses';
    $raffle_table = 'raffle_entries';
    $mismatch_table = 'mismatch';
    $invalid_invoice_table = 'invalid_invoices';

    // Fetch CSV

    // COMPANY GMAIL
    $csv_url = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQhn0T0QXcsdnrllDEsSo4sQxAiFJ5jlbvYGiDkON_NHzE6hB7d10XSlaM-2qvWinKld7qFRXErkN3h/pub?gid=1957502855&single=true&output=csv';

    $csv_content = file_get_contents($csv_url);

    // DEFINE ENCRYPTION KEY AND INITIALIZATION VECTOR
    $encryption_key = 'ENCRYPTING CUSTOMERS DATA';
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($iv_length);

    // ENCRYPT CSV DATA
    $encrypted_data = openssl_encrypt($csv_content, 'aes-256-cbc', $encryption_key, 0, $iv);

    $encrypted_data_with_iv = base64_encode($iv . $encrypted_data);

    echo 'Encrypted data: ' . $encrypted_data_with_iv . "\n";

    // DECRYPT CSV DATA
    $decoded_data = base64_decode($encrypted_data_with_iv);

    // EXTRACT IV FROM THE DECODED DATA
    $iv_from_data = substr($decoded_data, 0, $iv_length);
    $encrypted_data_from_storage = substr($decoded_data, $iv_length);

    $decrypted_data = openssl_decrypt($encrypted_data_from_storage, 'aes-256-cbc', $encryption_key, 0, $iv_from_data);

    echo "Decrypted Data: " . $decrypted_data . "\n";

    if ($decrypted_data === false) die("❌ Error fetching decrypted data.");
    echo "✅ Decrypted data fetched successfully!<br>";

    $rows = array_map('str_getcsv', explode("\n", $decrypted_data));
    $rows = array_filter($rows, fn($r) => count(array_filter($r)) > 0);
    $header = array_shift($rows);
    $header = array_map('trim', $header);

    if (!$header || count($header) < 5) {
        errorHandler(E_USER_WARNING, "Invalid or missing header: " . json_encode($header), __FILE__, __LINE__);
        die("❌ CSV header is missing or invalid.");
    }

    // Normalize column names
    function normalize_col($col)
    {
        $col = trim($col);
        $col = str_ireplace([' ', '-', '?'], '_', $col);
        $col = strtolower($col);
        if ($col === 'company name') return 'company_name';
        return $col;
    }

    $normalized_columns = array_map('normalize_col', $header);
    $db_columns = array_map(fn($col) => preg_replace('/[^a-zA-Z0-9_]/', '_', trim($col)), $header);
    $db_column_map = array_combine($normalized_columns, $db_columns);

    // Drop + Create responses table
    try {
        $conn->exec("IF OBJECT_ID('$table_name', 'U') IS NOT NULL DROP TABLE [$table_name]");
        echo "🧹 Dropped existing `$table_name`.<br>";
    } catch (PDOException $e) {
        errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
        echo "❌ Failed to drop `$table_name`: " . $e->getMessage() . "<br>";
    }

    $columns_sql = ['[id] INT IDENTITY (1,1) PRIMARY KEY'];
    foreach ($normalized_columns as $i => $col) {
        $db_col = $db_columns[$i];
        $columns_sql[] = ($col === 'timestamp') ? "[$db_col] DATETIME" : "[$db_col] VARCHAR(255)";
    }
    $create_sql = "CREATE TABLE [$table_name] (" . implode(', ', $columns_sql) . ")";
    $conn->exec($create_sql);
    echo "✅ Table `$table_name` created.<br>";

    // Create raffle_entries table if not exists
    $conn->exec("
IF OBJECT_ID('$raffle_table', 'U') IS NULL
    EXEC('CREATE TABLE [$raffle_table] (
        [Ticket_Number] INT IDENTITY(1000001,1) PRIMARY KEY,
        [Branch] VARCHAR(255),
        [First_Name] VARCHAR(255),
        [Last_Name] VARCHAR(255),
        [Company] VARCHAR(255),
        [Invoice_Number] VARCHAR(255),
        [Mobile_Number] VARCHAR(255),
        [Email] VARCHAR(255)
    )')
");
    echo "✅ Table `$raffle_table` ready.<br>";

    // CREATE MISMATCH TABLE
    $conn->exec("
IF OBJECT_ID('$mismatch_table', 'U') IS NULL
    EXEC('CREATE TABLE [$mismatch_table] (
        [id] INT IDENTITY PRIMARY KEY,
        [docId] INT,
        [Branch] VARCHAR(255),
        [First_Name] VARCHAR(255),
        [Last_Name] VARCHAR(255),
        [Company] VARCHAR(255),
        [Invoice_Number] VARCHAR(255),
        [Mobile_Number] VARCHAR(255),
        [Email] VARCHAR(255)
    )')
");
    echo "✅ Table `$mismatch_table` ready.<br>";

    // Ensure 'doc_id' and 'Findings' columns exist in mismatch table
    $conn->exec("
IF COL_LENGTH('$mismatch_table', 'docId') IS NULL
    ALTER TABLE [$mismatch_table] ADD [docId] INT
");

    // Ensure 'Findings' column exists in mismatch table
    $conn->exec("
IF COL_LENGTH('$mismatch_table', 'Findings') IS NULL
    ALTER TABLE [$mismatch_table] ADD [Findings] VARCHAR(255)
");

    // CREATE INVALID INVOICES TABLE
    $conn->exec("IF OBJECT_ID('$invalid_invoice_table', 'U') IS NULL
    EXEC('CREATE TABLE [$invalid_invoice_table] (
        [id] INT IDENTITY PRIMARY KEY,
        [Branch] VARCHAR(255),
        [First_Name] VARCHAR(255),
        [Last_Name] VARCHAR(255),
        [Company] VARCHAR(255),
        [Invoice_Number] VARCHAR(255),
        [Mobile_Number] VARCHAR(255),
        [Email] VARCHAR(255),
        [Findings] VARCHAR(255)
    )')
");
    echo "✅ Table `$invalid_invoice_table` ready.<br>";

    $column_map = array_flip($normalized_columns);
    $branchIndex    = $column_map['branch_purchased'] ?? null;
    $invoiceIndex   = $column_map['invoice_number'] ?? null;
    $firstNameIndex = $column_map['first_name'] ?? null;
    $lastNameIndex  = $column_map['last_name'] ?? null;
    $mobileIndex    = $column_map['mobile_number'] ?? null;
    $emailIndex     = $column_map['email'] ?? null;
    $companyIndex   = $column_map['company_name'] ?? null;

    $missing = [];
    if ($branchIndex === null) $missing[] = 'branch_purchased';
    if ($invoiceIndex === null) $missing[] = 'invoice_number';
    if ($firstNameIndex === null) $missing[] = 'first_name';
    if ($lastNameIndex === null) $missing[] = 'last_name';
    if ($mobileIndex === null) $missing[] = 'mobile_number';
    if ($emailIndex === null) $missing[] = 'email';

    if (!empty($missing)) {
        $msg = "❌ Required column(s) missing: " . implode(', ', $missing);
        errorHandler(E_USER_WARNING, $msg, __FILE__, __LINE__);
        echo $msg . "<br><pre>" . print_r($normalized_columns, true) . "</pre>";
        exit;
    }

    $placeholders = rtrim(str_repeat('?,', count($db_columns)), ',');
    $columns_sql_str = implode(',', array_map(fn($col) => "[$col]", $db_columns));
    $insert_stmt = $conn->prepare("INSERT INTO [$table_name] ($columns_sql_str) VALUES ($placeholders)");

    $check_stmt = $conn->prepare("SELECT 1 FROM [$table_name]
    WHERE [{$db_column_map['branch_purchased']}] = ? AND [{$db_column_map['invoice_number']}] = ? 
          AND [{$db_column_map['first_name']}] = ? AND [{$db_column_map['last_name']}] = ?");

    $customer_stmt = $conn->prepare("SELECT inv_amount, branch, cust_name FROM customers WHERE LOWER(LTRIM(RTRIM(inv))) = LOWER(?)");

    $mismatch_check_stmt = $conn->prepare("SELECT 1 FROM [$mismatch_table] WHERE [docId] = ?");

    $raffle_stmt = $conn->prepare("INSERT INTO [$raffle_table] 
    ([Branch], [First_Name], [Last_Name], [Company], [Invoice_Number], [Mobile_Number], [Email])
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

    $raffle_check_stmt = $conn->prepare("SELECT COUNT(*) FROM [$raffle_table]
    WHERE [Branch] = ? AND [Invoice_Number] = ? AND [First_Name] = ? AND [Last_Name] = ?
");

    $inserted = 0;
    $ticketed = 0;

    foreach ($rows as $rowIndex => $row) {
        if (count($row) !== count($header)) continue;

        $row = array_map('trim', $row);
        foreach ($normalized_columns as $i => $col_name) {
            if ($col_name === 'timestamp') {
                $timestamp = strtotime($row[$i]);
                $row[$i] = $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
            }
        }

        $check_stmt->execute([
            $row[$branchIndex],
            $row[$invoiceIndex],
            $row[$firstNameIndex],
            $row[$lastNameIndex]
        ]);
        if ($check_stmt->fetch()) continue;

        $insert_stmt->execute($row);
        $response_id = $conn->lastInsertId();
        $inserted++;

        $inv_val = $row[$invoiceIndex];
        $branch_val = $row[$branchIndex];
        $first_name = $row[$firstNameIndex];
        $last_name  = $row[$lastNameIndex];
        $email_val = isset($row[$emailIndex]) ? strtolower(trim($row[$emailIndex])) : '';
        $mobile_val = $row[$mobileIndex] ?? '';
        $company_val = ($companyIndex !== null && isset($row[$companyIndex])) ? $row[$companyIndex] : '';

        // Normalize inputs
        $normalized_inv = normalize_name($inv_val);
        $normalized_branch = normalize_name($branch_val);
        $normalized_firstname = normalize_name($first_name);

        // Query customer by invoice only
        $customer_stmt->execute([$normalized_inv]);
        $customer = $customer_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            // Invoice not found → invalid
            $invalid_stmt = $conn->prepare("INSERT INTO [$invalid_invoice_table]
        ([Branch], [First_Name], [Last_Name], [Company], [Invoice_Number], [Mobile_Number], [Email], [Findings])
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $invalid_stmt->execute([
                $branch_val,
                $first_name,
                $last_name,
                $company_val,
                $inv_val,
                $mobile_val,
                $email_val,
                "Invoice number not found in customers"
            ]);
            continue;
        }

        $expected_branch = strtolower(trim($customer['branch']));
        $cust_name = normalize_name($customer['cust_name'] ?? '');
        $cust_name_words = preg_split('/\s+/', preg_replace('/[^a-z\s]/i', '', $cust_name));

        $first_name_words = preg_split('/\s+/', preg_replace('/[^a-z\s]/i', '', $normalized_firstname));
        $last_name_normalized = normalize_name($last_name);

        $first_match = in_array($first_name_words[0], $cust_name_words);
        $last_match = in_array($last_name_normalized, $cust_name_words);

        $extra_first_names = array_filter($first_name_words, fn($word) => !in_array($word, $cust_name_words));

        if ($expected_branch === $normalized_branch && $first_match && $last_match && empty($extra_first_names)) {
            // ✅ Valid match → insert raffle ticket(s)
            $inv_amount = floatval($customer['inv_amount']);
            $num_tickets = floor($inv_amount / 5000);
            if ($num_tickets < 1) continue;

            $raffle_check_stmt->execute([$branch_val, $inv_val, $first_name, $last_name]);
            $existing = $raffle_check_stmt->fetchColumn();

            $tickets_to_insert = $num_tickets - $existing;
            if ($tickets_to_insert <= 0) continue;

            for ($i = 0; $i < $tickets_to_insert; $i++) {
                $raffle_stmt->execute([
                    $branch_val,
                    $first_name,
                    $last_name,
                    $company_val,
                    $inv_val,
                    $mobile_val,
                    $email_val
                ]);
                $ticketed++;
            }
        } else {
            // ❌ Mismatch → Insert into mismatch table
            $findings = [];

            $findings = [];
            if ($expected_branch !== $normalized_branch) {
                $findings[] = "Branch mismatch (expected: {$customer['branch']})";
            }
            if (!$first_match) {
                $findings[] = "First name mismatch (expected match in: {$customer['cust_name']})";
            }
            if (!$last_match) {
                $findings[] = "Last name mismatch (expected match in: {$customer['cust_name']})";
            }
            if (!empty($extra_first_names)) {
                $findings[] = "Extra name(s) in first name: " . implode(', ', $extra_first_names);
            }

            $finding_text = implode("; ", $findings);

            $mismatch_check_stmt->execute([$response_id]);

            if (!$mismatch_check_stmt->fetch()) {
                $mismatch_stmt = $conn->prepare("INSERT INTO [$mismatch_table]
            ([docId], [Branch], [First_Name], [Last_Name], [Company], [Invoice_Number], [Mobile_Number], [Email], [Findings])
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $mismatch_stmt->execute([
                    $response_id,
                    $branch_val,
                    $first_name,
                    $last_name,
                    $company_val,
                    $inv_val,
                    $mobile_val,
                    $email_val,
                    $finding_text
                ]);
            }
        }
    }

    echo "<br>✅ $inserted response(s) inserted.<br>";
    echo "🎟️ $ticketed ticket(s) generated.<br>";
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    die("Connection failed: " . $e->getMessage());
}
