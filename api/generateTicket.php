<?php
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

// DB connection
$servername = "192.168.101.68";
$db = "RAFFLE";
$username = "sa";
$password = "SB1Admin";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    die("Connection failed: " . $e->getMessage());
}

$table_name = 'responses';
$raffle_table = 'raffle_entries';
$mismatch_table = 'mismatch';

// Fetch CSV

// COMPANY GMAIL
$csv_url = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQhn0T0QXcsdnrllDEsSo4sQxAiFJ5jlbvYGiDkON_NHzE6hB7d10XSlaM-2qvWinKld7qFRXErkN3h/pub?gid=1957502855&single=true&output=csv';

// MY GMAIL
// $csv_url = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vTvJ4AEY7CL61rV-JqfyUqTt0siV2IgBKWRJH1CFXRQfbtxXZoZHpYcDNsIDwiY3ZNtlXoXs0_Pd1GO/pub?gid=637539049&single=true&output=csv';
$csv_content = file_get_contents($csv_url);
if ($csv_content === false) die("❌ Error fetching CSV.");
echo "✅ CSV fetched successfully!<br>";

// Parse CSV
$rows = array_map('str_getcsv', explode("\n", $csv_content));
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
    if ($col === 'comapy_name_if_any') return 'company_name';
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

$columns_sql = [];
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

// Create mismatch table if not exists
$conn->exec("
IF OBJECT_ID('$mismatch_table', 'U') IS NULL
    EXEC('CREATE TABLE [$mismatch_table] (
        [id] INT IDENTITY PRIMARY KEY,
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

// Required column indexes
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

// Prepare statements
$placeholders = rtrim(str_repeat('?,', count($db_columns)), ',');
$columns_sql_str = implode(',', array_map(fn($col) => "[$col]", $db_columns));
$insert_stmt = $conn->prepare("INSERT INTO [$table_name] ($columns_sql_str) VALUES ($placeholders)");

$check_stmt = $conn->prepare("
    SELECT 1 FROM [$table_name]
    WHERE [{$db_column_map['branch_purchased']}] = ? AND [{$db_column_map['invoice_number']}] = ? 
          AND [{$db_column_map['first_name']}] = ? AND [{$db_column_map['last_name']}] = ?
");

$customer_stmt = $conn->prepare("
    SELECT inv_amount FROM customers
    WHERE LOWER(LTRIM(RTRIM(inv))) = LOWER(?) AND LOWER(LTRIM(RTRIM(branch))) = LOWER(?)
");

$mismatch_check_stmt = $conn->prepare("
    SELECT 1 FROM [$mismatch_table]
    WHERE [Branch] = ? AND [First_Name] = ? AND [Last_Name] = ? AND [Invoice_Number] = ?
");

$raffle_stmt = $conn->prepare("
    INSERT INTO [$raffle_table] 
    ([Branch], [First_Name], [Last_Name], [Company], [Invoice_Number], [Mobile_Number], [Email])
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$raffle_check_stmt = $conn->prepare("
    SELECT COUNT(*) FROM [$raffle_table]
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
    $inserted++;

    $inv_val = $row[$invoiceIndex];
    $branch_val = $row[$branchIndex];
    $first_name = $row[$firstNameIndex];
    $last_name  = $row[$lastNameIndex];
    $email_val  = $row[$emailIndex] ?? '';
    $mobile_val = $row[$mobileIndex] ?? '';
    $company_val = ($companyIndex !== null && isset($row[$companyIndex])) ? $row[$companyIndex] : '';

    $customer_stmt->execute([$inv_val, $branch_val]);
    $customer = $customer_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        $mismatch_check_stmt->execute([$branch_val, $first_name, $last_name, $inv_val]);
        if (!$mismatch_check_stmt->fetch()) {
            $mismatch_stmt = $conn->prepare("
                INSERT INTO [$mismatch_table]
                ([Branch], [First_Name], [Last_Name], [Company], [Invoice_Number], [Mobile_Number], [Email])
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $mismatch_stmt->execute([
                $branch_val,
                $first_name,
                $last_name,
                $company_val,
                $inv_val,
                $mobile_val,
                $email_val
            ]);
        }
        continue;
    }

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
}

echo "<br>✅ $inserted response(s) inserted.<br>";
echo "🎟️ $ticketed ticket(s) generated.<br>";
