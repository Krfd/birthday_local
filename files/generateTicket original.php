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
    $name = trim($name);
    $name = mb_strtolower($name, 'UTF-8');
    $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name); // ñ → n
    return preg_replace('/[^a-z0-9\s]/i', '', $name);
}
// DB connection
$servername = "192.168.101.68";
$db = "RAFFLE";
$username = "sa";
$password = "SB1Admin";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Function to check if record exists and if findings have changed
    function check_existing_overall_findings($conn, $table, $data)
    {
        $stmt = $conn->prepare("SELECT id, findings FROM [$table] WHERE [branch] = ? AND [first_name] = ? AND [last_name] = ? AND [inv] = ?");
        $stmt->execute([
            $data['Branch'] ?? '',
            $data['First_Name'] ?? '',
            $data['Last_Name'] ?? '',
            $data['Invoice_Number'] ?? ''
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // INSERT INTO OVERALL FINDINGS
    function insert_overall_findings($conn, $table, $data, $finding_text)
    {
        // Check if the record already exists and if findings are the same
        $existingRecord = check_existing_overall_findings($conn, $table, $data);

        // If record exists and findings are the same, skip insertion
        if ($existingRecord) {
            if ($existingRecord['findings'] === $finding_text) {
                echo "Skipping record for Invoice: {$data['Invoice_Number']} (Already exists with same findings).<br>";
                return; // Skip insertion
            }
        }

        $stmt = $conn->prepare("INSERT INTO [$table] (branch, first_name, last_name, inv, mobile, email, postal, findings)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $data['Branch'] ?? '',
            $data['First_Name'] ?? '',
            $data['Last_Name'] ?? '',
            $data['Invoice_Number'] ?? '',
            $data['Mobile_Number'] ?? '',
            $data['Email'] ?? '',
            $data['Postal_Address'] ?? '',
            $finding_text
        ]);
    }

    $table_name = 'responses';
    $raffle_table = 'raffle_entries';
    $mismatch_table = 'mismatch';
    $invalid_invoice_table = 'invalid_invoices';
    $overall_findings_table = 'overall_findings';

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
        return $col;
    }
    $normalized_columns = array_map('normalize_col', $db_columns);

    // Create raffle_entries table if not exists (keep existing code)
    $conn->exec("
    IF OBJECT_ID('$raffle_table', 'U') IS NULL
        EXEC('CREATE TABLE [$raffle_table] (
            [Ticket_Number] INT IDENTITY(100001,1) PRIMARY KEY,
            [Counter] INT,
            [Branch] VARCHAR(255),
            [First_Name] VARCHAR(255),
            [Last_Name] VARCHAR(255),
            [Invoice_Number] VARCHAR(255),
            [Mobile_Number] VARCHAR(255),
            [Email] VARCHAR(255),
            [Postal_Address] VARCHAR(255),
            [Area_Code] INT,
            [Excluded] INT,
            [Exported] INT
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
        [Invoice_Number] VARCHAR(255),
        [Mobile_Number] VARCHAR(255),
        [Email] VARCHAR(255),
        [Findings] VARCHAR(255),
        [Area_Code] INT,
        [Updated]
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
        [Invoice_Number] VARCHAR(255),
        [Mobile_Number] VARCHAR(255),
        [Email] VARCHAR(255),
        [Findings] VARCHAR(255),
        [Area_Code] INT,
        [Updated] INT
        )')");
    echo "✅ Table `$invalid_invoice_table` ready.<br>";

    // CREATE OVERALL FINDINGS TABLE IF NOT EXISTS
    $conn->exec("IF OBJECT_ID('$overall_findings_table', 'U') IS NULL EXEC('CREATE TABLE [$overall_findings_table] (
        [id] INT IDENTITY PRIMARY KEY,
        [branch] VARCHAR(255),
        [first_name] VARCHAR(255),
        [last_name] VARCHAR(255),
        [inv] VARCHAR(255),
        [mobile] VARCHAR(255),
        [email] VARCHAR(255),
        [postal] VARCHAR(255),
        [findings] VARCHAR(255)
        )')
    ");

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

    // Fetch all rows from responses table
    $stmt = $conn->query("SELECT * FROM [$table_name]");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ORIGINAL
    $customer_stmt = $conn->prepare("SELECT inv_amount, branch, cust_name, areaCode FROM customers WHERE LOWER(LTRIM(RTRIM(inv))) = LOWER(?)");

    $mismatch_check_stmt = $conn->prepare("SELECT 1 FROM [$mismatch_table] WHERE [docId] = ?");

    $raffle_stmt = $conn->prepare("INSERT INTO [$raffle_table] 
    ([Counter], [Branch], [First_Name], [Last_Name], [Invoice_Number], [Mobile_Number], [Email], [Postal_Address], [Area_Code], [Excluded], [Exported])
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $raffle_check_stmt = $conn->prepare("SELECT 1 FROM [$raffle_table] WHERE [Branch] = ? AND [Invoice_Number] = ?");

    $inserted = 0;
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

        $postal_address = isset($row['Postal_Address']) ? $row['Postal_Address'] : '';

        $inv_val = $row[$db_columns[$invoiceIndex]];
        $branch_val = $row[$db_columns[$branchIndex]];
        $first_name = $row[$db_columns[$firstNameIndex]];
        $last_name  = $row[$db_columns[$lastNameIndex]];
        $email_val = isset($row[$db_columns[$emailIndex]]) ? strtolower(trim($row[$db_columns[$emailIndex]])) : '';
        $mobile_val = isset($row[$db_columns[$mobileIndex]]) ? $row[$db_columns[$mobileIndex]] : '';

        // Normalize inputs
        $normalized_inv = normalize_name($inv_val);
        // ORIGINAL
        $normalized_branch = strtolower($branch_val);
        $normalized_firstname = normalize_name($first_name);

        // Query customer by invoice only
        $customer_stmt->execute([$normalized_inv, $normalized_branch]);
        $customer = $customer_stmt->fetch(PDO::FETCH_ASSOC);

        $docId = $row['id'] ?? null;

        $areaCode = $customer['areaCode'] ?? null;
        $customerBranch = is_array($customer) ? $customer['branch'] : '';

        if (!$customer) {

            $mismatch_check = $conn->prepare("SELECT 1 FROM customers WHERE LOWER(Branch) = ? AND LOWER(cust_name) LIKE ?");
            $like_first_name = "%$normalized_firstname%";
            $mismatch_check->execute([$normalized_branch, $like_first_name]);
            $is_mismatch = $mismatch_check->fetchColumn();

            if ($is_mismatch) {
                $mismatch_check_stmt = $conn->prepare("SELECT 1 FROM [$mismatch_table] WHERE [docId] = ?");
                $mismatch_check_stmt->execute([$docId]);
                $already_exists = $mismatch_check_stmt->fetchColumn();

                if ($already_exists) {
                    continue;
                }

                // Insert into mismatch table
                $mismatch_stmt = $conn->prepare("INSERT INTO [$mismatch_table]
                ([docId], [Branch], [First_Name], [Last_Name], [Invoice_Number], [Mobile_Number], [Email], [Findings], [Postal_Address], [Area_Code], [Updated])
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,?)");
                $mismatch_stmt->execute([
                    $docId,
                    $branch_val,
                    $first_name,
                    $last_name,
                    $inv_val,
                    $mobile_val,
                    $email_val,
                    "invoice",
                    $postal_address,
                    $areaCode,
                    0
                ]);
            } else {
                if (!empty($customerBranch)) {
                    if ($normalized_branch !== $customerBranch) {

                        $invalid_stmt_check->execute([$inv_val]);
                        $existing_invalid = $invalid_stmt_check->fetchColumn();

                        // If the invoice already exists, skip insertion
                        if ($existing_invalid) {
                            echo "Skipping record with Invoice Number: $inv_val (Already exists in invalid_invoices)<br>";
                            continue;
                        }

                        // Invoice not found → invalid
                        $invalid_stmt = $conn->prepare("INSERT INTO [$invalid_invoice_table]
                    ([Branch], [First_Name], [Last_Name], [Invoice_Number], [Mobile_Number], [Email], [Findings], [Postal_Address])
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $invalid_stmt->execute([
                            $branch_val,
                            $first_name,
                            $last_name,
                            $inv_val,
                            $mobile_val,
                            $email_val,
                            "invoice",
                            $postal_address
                        ]);
                    }
                } else {
                    $invalid_stmt_check->execute([$inv_val]);
                    $existing_invalid = $invalid_stmt_check->fetchColumn();

                    // If the invoice already exists, skip insertion
                    if ($existing_invalid) {
                        echo "Skipping record with Invoice Number: $inv_val (Already exists in invalid_invoices)<br>";
                        continue;
                    }

                    // Invoice not found → invalid
                    $invalid_stmt = $conn->prepare("INSERT INTO [$invalid_invoice_table]
                    ([Branch], [First_Name], [Last_Name], [Invoice_Number], [Mobile_Number], [Email], [Findings], [Postal_Address])
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $invalid_stmt->execute([
                        $branch_val,
                        $first_name,
                        $last_name,
                        $inv_val,
                        $mobile_val,
                        $email_val,
                        "invoice",
                        $postal_address
                    ]);
                }
            }

            insert_overall_findings($conn, $overall_findings_table, [
                'Branch' => $branch_val,
                'First_Name' => $first_name,
                'Last_Name' => $last_name,
                'Invoice_Number' => $inv_val,
                'Mobile_Number' => $mobile_val,
                'Email' => $email_val,
                'Postal_Address' => $postal_address
            ], "Invoice number not found in customers");
            continue;
        }

        if ($normalized_inv !== $inv_val) {
            $docId = $row['id'] ?? null;

            if ($docId) {
                $mismatch_check_stmt->execute([$docId]);
                if (!$mismatch_check_stmt->fetch()) {
                    $mismatch_stmt = $conn->prepare("INSERT INTO [$mismatch_table]
                ([docId], [Branch], [First_Name], [Last_Name], [Invoice_Number], [Mobile_Number], [Email], [Findings], [Postal_Address], [Area_Code], [Updated])
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $mismatch_stmt->execute([
                        $docId,
                        $branch_val,
                        $first_name,
                        $last_name,
                        $inv_val,
                        $mobile_val,
                        $email_val,
                        'invoice',
                        $postal_address,
                        isset($customer['areaCode']) ? (int)$customer['areaCode'] : null,
                        0
                    ]);
                }
            }

            // Skip further processing for this record
            continue;
        }

        // ORIGINAL
        if ($customer) {
            // Existing normalization:
            $expected_branch = strtolower(trim($customer['branch']));
            // Check for branch mismatch (but all other fields should match)
            if ($normalized_branch !== $expected_branch) {
                $cust_name = normalize_name($customer['cust_name'] ?? '');
                $cust_name_words = preg_split('/\s+/', preg_replace('/[^a-z\s]/i', '', $cust_name));

                $normalized_firstname = normalize_name($first_name);
                $first_name_words = preg_split('/\s+/', preg_replace('/[^a-z\s]/i', '', $normalized_firstname));
                $last_name_normalized = normalize_name($last_name);

                $first_match = !empty(array_intersect($first_name_words, $cust_name_words));
                // $last_match = in_array($last_name_normalized, $cust_name_words);

                $last_name_words = preg_split('/\s+/', preg_replace('/[^a-z\s]/i', '', $last_name_normalized));

                $last_match = true;
                foreach ($last_name_words as $word) {
                    if (!in_array($word, $cust_name_words)) {
                        $last_match = false;
                        break;
                    }
                }

                $all_response_names_exist_in_customer = true;
                foreach ($first_name_words as $word) {
                    if (!in_array($word, $cust_name_words)) {
                        $all_response_names_exist_in_customer = false;
                        break;
                    }
                }

                $is_name_matching = $first_match && $last_match && $all_response_names_exist_in_customer;

                if ($is_name_matching) {
                    $docId = $row['id'] ?? null;

                    if ($docId) {
                        $mismatch_check_stmt->execute([$docId]);
                        if (!$mismatch_check_stmt->fetch()) {
                            $mismatch_stmt = $conn->prepare("INSERT INTO [$mismatch_table]
                                ([docId], [Branch], [First_Name], [Last_Name], [Invoice_Number], [Mobile_Number], [Email], [Findings], [Postal_Address], [Area_Code], [Updated])
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $mismatch_stmt->execute([
                                $docId,
                                $branch_val,
                                $first_name,
                                $last_name,
                                $inv_val,
                                $mobile_val,
                                $email_val,
                                'branch',
                                $postal_address,
                                isset($customer['areaCode']) ? (int)$customer['areaCode'] : null,
                                0
                            ]);
                        }
                    }

                    insert_overall_findings($conn, $overall_findings_table, [
                        'Branch' => $branch_val,
                        'First_Name' => $first_name,
                        'Last_Name' => $last_name,
                        'Invoice_Number' => $inv_val,
                        'Mobile_Number' => $mobile_val,
                        'Email' => $email_val,
                        'Postal_Address' => $postal_address
                    ], "Branch mismatch");

                    continue; // Skip further processing
                }
            }
        }

        $cust_name = normalize_name($customer['cust_name'] ?? '');

        // Normalize first and last names for comparison
        $cust_name_words = preg_split('/\s+/', preg_replace('/[^a-z\s]/i', '', $cust_name));

        $normalized_firstname = normalize_name($first_name);
        $first_name_words = preg_split('/\s+/', preg_replace('/[^a-z\s]/i', '', $normalized_firstname));
        $last_name_normalized = normalize_name($last_name);

        // ADDED
        $extra_first_names = array_diff($first_name_words, $cust_name_words);

        $first_match = !empty(array_intersect($first_name_words, $cust_name_words));
        // $last_match = in_array($last_name_normalized, $cust_name_words);

        $last_name_words = preg_split('/\s+/', preg_replace('/[^a-z\s]/i', '', $last_name_normalized));

        $last_match = true;
        foreach ($last_name_words as $word) {
            if (!in_array($word, $cust_name_words)) {
                $last_match = false;
                break;
            }
        }

        $all_response_names_exist_in_customer = true;
        foreach ($first_name_words as $word) {
            if (!in_array($word, $cust_name_words)) {
                $all_response_names_exist_in_customer = false;
                break;
            }
        }

        $is_valid = (
            $expected_branch === $normalized_branch &&
            $first_match &&
            $last_match &&
            $all_response_names_exist_in_customer
        );

        $employee_check_stmt = $conn->prepare("SELECT 1 FROM employees WHERE LOWER(LTRIM(RTRIM(first_name))) = LOWER(LTRIM(RTRIM(?))) AND LOWER(LTRIM(RTRIM(last_name))) = LOWER(LTRIM(RTRIM(?)))");

        // VALIDATION FOR EMPLOYEES BEFORE ISSUING A TICKET
        $employee_check_stmt->execute([normalize_name($first_name), normalize_name($last_name)]);
        $employee_exists = $employee_check_stmt->fetchColumn();

        if ($employee_exists) {

            insert_overall_findings($conn, $overall_findings_table, [
                'Branch' => $branch_val,
                'First_Name' => $first_name,
                'Last_Name' => $last_name,
                'Invoice_Number' => $inv_val,
                'Mobile_Number' => $mobile_val,
                'Email' => $email_val,
                'Postal_Address' => $postal_address
            ], "Employee record - ticket not issued");

            continue;
        }

        if ($is_valid) {

            $inv_amount = floatval($customer['inv_amount']);

            try {
                $inv_amount = floatval($customer['inv_amount']);

                $docId = $row['id'] ?? null;
                $existing_mismatch_stmt = $conn->prepare("SELECT Updated FROM [$mismatch_table] WHERE docId = ?");
                $existing_mismatch_stmt->execute([$docId]);
                $mismatch_result = $existing_mismatch_stmt->fetch(PDO::FETCH_ASSOC);

                if ($inv_amount > 100000) {
                    if ($mismatch_result && intval($mismatch_result['Updated']) === 1) {
                        // Insert into raffle_entries because Updated = 1
                        $num_tickets = floor($inv_amount / 5000);
                        if ($num_tickets < 1) continue;

                        // $raffle_check_stmt->execute([$branch_val, $inv_val, $first_name, $last_name]);
                        // $existing = $raffle_check_stmt->fetchColumn();

                        // $tickets_to_insert = $num_tickets - $existing;
                        // if ($tickets_to_insert <= 0) continue;

                        $raffle_check_stmt->execute([$branch_val, $inv_val]);
                        $existing = $raffle_check_stmt->fetchColumn();
                        if ($existing) continue; // already issued → skip

                        $tickets_to_insert = $num_tickets;

                        for ($i = 0; $i < $tickets_to_insert; $i++) {
                            $areaCode = $customer['areaCode'] ?? null;
                            $total_count = $tickets_to_insert;
                            $excluded = 0;

                            $raffle_stmt->execute([
                                $total_count,
                                $branch_val,
                                $first_name,
                                $last_name,
                                $inv_val,
                                $mobile_val,
                                $email_val,
                                $postal_address,
                                $areaCode,
                                $excluded,
                                0
                            ]);
                            $ticketed++;
                        }
                    } else {
                        // Not Updated yet → insert or keep in mismatch
                        $finding_text = "amount";

                        if (!$mismatch_result) {
                            $insert_mismatch_stmt = $conn->prepare("INSERT INTO [$mismatch_table]
                                ([docId], [Branch], [First_Name], [Last_Name], [Invoice_Number], [Mobile_Number], [Email], [Findings], [Postal_Address], [Area_Code], [Updated])
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $insert_mismatch_stmt->execute([
                                $docId,
                                $branch_val,
                                $first_name,
                                $last_name,
                                $inv_val,
                                $mobile_val,
                                $email_val,
                                $finding_text,
                                $postal_address,
                                isset($customer['areaCode']) ? (int)$customer['areaCode'] : null,
                                0
                            ]);
                        }
                    }

                    insert_overall_findings($conn, $overall_findings_table, [
                        'Branch' => $branch_val,
                        'First_Name' => $first_name,
                        'Last_Name' => $last_name,
                        'Invoice_Number' => $inv_val,
                        'Mobile_Number' => $mobile_val,
                        'Email' => $email_val,
                        'Postal_Address' => $postal_address
                    ], "Mismatch found - amount or updated flag");

                    // Skip normal ticketing
                    continue;
                }
            } catch (PDOException $e) {
                errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
            }

            // Normal ticket logic below
            $num_tickets = floor($inv_amount / 5000);
            if ($num_tickets < 1) continue;

            // $raffle_check_stmt->execute([$branch_val, $inv_val, $first_name, $last_name]);
            // $existing = $raffle_check_stmt->fetchColumn();

            // $tickets_to_insert = $num_tickets - $existing;
            // if ($tickets_to_insert <= 0) continue;

            $raffle_check_stmt->execute([$branch_val, $inv_val]);
            $existing = $raffle_check_stmt->fetchColumn();
            if ($existing) continue; // already issued → skip

            $tickets_to_insert = $num_tickets;


            $existing_count_stmt = $conn->prepare("SELECT COUNT(*) FROM [$raffle_table]
            WHERE [Branch] = ? AND [Invoice_Number] = ? AND [First_Name] = ? AND [Last_Name] = ?");
            $existing_count_stmt->execute([$branch_val, $inv_val, $first_name, $last_name]);
            $existing_count = (int)$existing_count_stmt->fetchColumn();

            for ($i = 0; $i < $tickets_to_insert; $i++) {
                $areaCode = $customer['areaCode'] ?? null;
                $total_count = $tickets_to_insert;

                $excluded = $inv_amount > 100000 ? 1 : 0;

                $raffle_stmt->execute([
                    $total_count,
                    $branch_val,
                    $first_name,
                    $last_name,
                    $inv_val,
                    $mobile_val,
                    $email_val,
                    $postal_address,
                    $areaCode,
                    $excluded,
                    0
                ]);
                $ticketed++;
            }
        } else {

            // Extract names
            $cust_raw = strtolower(trim($customer['cust_name'] ?? ''));
            $cust_parts = explode(',', $cust_raw, 2);
            $cust_last = trim($cust_parts[0] ?? '');
            $cust_first = trim($cust_parts[1] ?? '');

            $response_first = strtolower(trim($first_name));
            $response_last = strtolower(trim($last_name));
            $response_inv = strtolower(trim($inv_val));
            $customer_inv = strtolower(trim($customer['inv'] ?? ''));

            // Name valid if last names match and response first is in customer first
            $name_valid = (
                $response_last === $cust_last &&
                str_contains($cust_first, $response_first)
            );

            // Invoice match
            $invoice_valid = ($response_inv === $customer_inv);

            // Finding decision (only name or invoice, never both)
            if (!$name_valid) {
                $finding_text = "name";
            } elseif (!$invoice_valid) {
                $finding_text = "invoice";
            } else {
                $finding_text = "";
            }

            file_put_contents("output.txt", "== FINDING CHECK ==\n", FILE_APPEND);
            file_put_contents("output.txt", "cust_name: $cust_raw\n", FILE_APPEND);
            file_put_contents("output.txt", "response_name: $response_first $response_last\n", FILE_APPEND);
            file_put_contents("output.txt", "response_inv: $response_inv\n", FILE_APPEND);
            file_put_contents("output.txt", "customer_inv: $customer_inv\n", FILE_APPEND);
            file_put_contents("output.txt", "name_valid: " . ($name_valid ? 'true' : 'false') . "\n", FILE_APPEND);
            file_put_contents("output.txt", "invoice_valid: " . ($invoice_valid ? 'true' : 'false') . "\n", FILE_APPEND);
            file_put_contents("output.txt", "finding_text: $finding_text\n\n", FILE_APPEND);


            if ($docId) {
                $mismatch_check_stmt->execute([$docId]);
                if (!$mismatch_check_stmt->fetch()) {
                    $mismatch_stmt = $conn->prepare("INSERT INTO [$mismatch_table]
                        ([docId], [Branch], [First_Name], [Last_Name], [Invoice_Number], [Mobile_Number], [Email], [Findings], [Postal_Address], [Area_Code], [Updated])
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $mismatch_stmt->execute([
                        $docId,
                        $branch_val,
                        $first_name,
                        $last_name,
                        $inv_val,
                        $mobile_val,
                        $email_val,
                        $finding_text,
                        $postal_address,
                        isset($customer['areaCode']) ? (int)$customer['areaCode'] : null,
                        0
                    ]);
                }
            }

            // $mismatch_stmt->execute([
            //     $docId,
            //     $branch_val,
            //     $first_name,
            //     $last_name,
            //     $inv_val,
            //     $mobile_val,
            //     $email_val,
            //     $finding_text, // <- this must reflect the latest logic
            //     $postal_address,
            //     isset($customer['areaCode']) ? (int)$customer['areaCode'] : null,
            //     0
            // ]);


            insert_overall_findings($conn, $overall_findings_table, [
                'Branch' => $branch_val,
                'First_Name' => $first_name,
                'Last_Name' => $last_name,
                'Invoice_Number' => $inv_val,
                'Mobile_Number' => $mobile_val,
                'Email' => $email_val,
                'Postal_Address' => $postal_address
            ], $finding_text);
        }
    }

    echo "<br>✅ Processed " . count($rows) . " response(s).<br>";
    echo "🎟️ $ticketed ticket(s) generated.<br>";
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    die("Connection failed: " . $e->getMessage());
}
