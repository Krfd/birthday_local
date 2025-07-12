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

    // Fetch data from responses table instead of CSV

    // Get columns from responses table dynamically
    $columnsStmt = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name'");
    $db_columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$db_columns || count($db_columns) < 5) {
        errorHandler(E_USER_WARNING, "Invalid or missing columns in `$table_name` table.", __FILE__, __LINE__);
        die("❌ `$table_name` table columns are missing or invalid.");
    }

    // Normalize columns like in CSV section
    function normalize_col($col)
    {
        $col = trim($col);
        $col = str_ireplace([' ', '-', '?'], '_', $col);
        $col = strtolower($col);
        if ($col === 'company name') return 'company_name';
        return $col;
    }
    $normalized_columns = array_map('normalize_col', $db_columns);

    // Create raffle_entries table if not exists (keep existing code)
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

    // Create mismatch table if not exists
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
        [Email] VARCHAR(255),
        [Findings] VARCHAR(255)
    )')
");
    echo "✅ Table `$mismatch_table` ready.<br>";

    // Create invalid_invoices table if not exists
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

    // Required columns - check presence
    $required_columns = [
        'branch_purchased',
        'invoice_number',
        'first_name',
        'last_name',
        'mobile_number',
        'email',
    ];

    $missing = [];
    foreach ($required_columns as $col) {
        if (!isset($column_map[$col])) {
            $missing[] = $col;
        }
    }

    if (!empty($missing)) {
        $msg = "❌ Required column(s) missing in `$table_name`: " . implode(', ', $missing);
        errorHandler(E_USER_WARNING, $msg, __FILE__, __LINE__);
        echo $msg . "<br><pre>" . print_r($normalized_columns, true) . "</pre>";
        exit;
    }

    // Prepare statements for raffle and checks
    $branchIndex    = $column_map['branch_purchased'];
    $invoiceIndex   = $column_map['invoice_number'];
    $firstNameIndex = $column_map['first_name'];
    $lastNameIndex  = $column_map['last_name'];
    $mobileIndex    = $column_map['mobile_number'];
    $emailIndex     = $column_map['email'];
    $companyIndex   = $column_map['company_name'] ?? null;

    // Fetch all rows from responses table
    $stmt = $conn->query("SELECT * FROM [$table_name]");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare other necessary statements
    $customer_stmt = $conn->prepare("SELECT inv_amount, branch, cust_name FROM customers WHERE LOWER(LTRIM(RTRIM(inv))) = LOWER(?)");

    $mismatch_check_stmt = $conn->prepare("SELECT 1 FROM [$mismatch_table] WHERE [docId] = ?");

    $raffle_stmt = $conn->prepare("INSERT INTO [$raffle_table] 
    ([Branch], [First_Name], [Last_Name], [Company], [Invoice_Number], [Mobile_Number], [Email])
    VALUES (?, ?, ?, ?, ?, ?, ?)");

    $raffle_check_stmt = $conn->prepare("SELECT COUNT(*) FROM [$raffle_table]
    WHERE [Branch] = ? AND [Invoice_Number] = ? AND [First_Name] = ? AND [Last_Name] = ?");

    $inserted = 0;  // responses already in DB, so can skip this count or just track processed count
    $ticketed = 0;

    $invalid_stmt_check = $conn->prepare("SELECT 1 FROM [$invalid_invoice_table] WHERE [Invoice_Number] = ?");

    foreach ($rows as $rowIndex => $row) {
        // Normalize names & columns as needed
        foreach ($row as $colName => $value) {
            $row[$colName] = trim($value);
        }

        // Normalize timestamp column if exists
        if (isset($row['timestamp'])) {
            $timestamp = strtotime($row['timestamp']);
            $row['timestamp'] = $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
        }

        $inv_val = $row[$db_columns[$invoiceIndex]];
        $branch_val = $row[$db_columns[$branchIndex]];
        $first_name = $row[$db_columns[$firstNameIndex]];
        $last_name  = $row[$db_columns[$lastNameIndex]];
        $email_val = isset($row[$db_columns[$emailIndex]]) ? strtolower(trim($row[$db_columns[$emailIndex]])) : '';
        $mobile_val = $row[$db_columns[$mobileIndex]] ?? '';
        $company_val = ($companyIndex !== null && isset($row[$db_columns[$companyIndex]])) ? $row[$db_columns[$companyIndex]] : '';

        // Normalize inputs
        $normalized_inv = normalize_name($inv_val);
        $normalized_branch = normalize_name($branch_val);
        $normalized_firstname = normalize_name($first_name);

        // Query customer by invoice only
        $customer_stmt->execute([$normalized_inv]);
        $customer = $customer_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {

            $invalid_stmt_check->execute([$inv_val]);
            $existing_invalid = $invalid_stmt_check->fetchColumn();

            // If the invoice already exists, skip insertion
            if ($existing_invalid) {
                echo "Skipping record with Invoice Number: $inv_val (Already exists in invalid_invoices)<br>";
                continue;
            }

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

        // Existing normalization:
        $expected_branch = strtolower(trim($customer['branch']));
        $cust_name = normalize_name($customer['cust_name'] ?? '');

        $has_company = !empty(trim($company_val));

        if ($has_company) {
            // Normalize company names for comparison
            $company_normalized = normalize_name($company_val);
            $company_match = $company_normalized === $cust_name;

            // Branch and company name must match for a valid entry
            $is_valid = ($expected_branch === $normalized_branch) && $company_match;
        } else {
            // Normalize first and last names for comparison
            $cust_name_words = preg_split('/\s+/', preg_replace('/[^a-z\s]/i', '', $cust_name));

            $normalized_firstname = normalize_name($first_name);
            $first_name_words = preg_split('/\s+/', preg_replace('/[^a-z\s]/i', '', $normalized_firstname));
            $last_name_normalized = normalize_name($last_name);

            $first_match = in_array($first_name_words[0], $cust_name_words);
            $last_match = in_array($last_name_normalized, $cust_name_words);
            $extra_first_names = array_filter($first_name_words, fn($word) => !in_array($word, $cust_name_words));

            // Branch and full name validation (original)
            $is_valid = (
                $expected_branch === $normalized_branch &&
                $first_match &&
                $last_match &&
                empty($extra_first_names)
            );
        }

        if ($is_valid) {
            // Valid entry logic (raffle ticket generation)
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
            // Mismatch insertion logic (adjust findings accordingly)
            $findings = [];

            if ($expected_branch !== $normalized_branch) {
                $findings[] = "Branch mismatch (expected: {$customer['branch']})";
            }

            if ($has_company) {
                // Company name mismatch
                $company_normalized = normalize_name($company_val);
                if ($company_normalized !== $cust_name) {
                    $findings[] = "Company name mismatch (expected: {$customer['cust_name']})";
                }
            } else {
                // First name mismatch
                if (!$first_match) {
                    $findings[] = "First name mismatch (expected match in: {$customer['cust_name']})";
                }
                // Last name mismatch
                if (!$last_match) {
                    $findings[] = "Last name mismatch (expected match in: {$customer['cust_name']})";
                }
                // Extra names mismatch
                if (!empty($extra_first_names)) {
                    $findings[] = "Extra/lack name(s) in first name: " . implode(', ', $extra_first_names);
                }
            }

            $finding_text = implode("; ", $findings);

            $docId = $row['id'] ?? null;

            if ($docId) {
                $mismatch_check_stmt->execute([$docId]);

                if (!$mismatch_check_stmt->fetch()) {
                    $mismatch_stmt = $conn->prepare("INSERT INTO [$mismatch_table]
            ([docId], [Branch], [First_Name], [Last_Name], [Company], [Invoice_Number], [Mobile_Number], [Email], [Findings])
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $mismatch_stmt->execute([
                        $docId,
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
    }

    echo "<br>✅ Processed " . count($rows) . " response(s).<br>";
    echo "🎟️ $ticketed ticket(s) generated.<br>";
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    die("Connection failed: " . $e->getMessage());
}
