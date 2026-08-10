<?php

// if (php_sapi_name() !== 'cli') {
//     $authorized_users = ['karlalooo' => 'iap', 'merruel' => 'iap', 'jummie' => 'iap'];
//     $user_ = $_SERVER['PHP_AUTH_USER'] ?? '';
//     $pass_ = $_SERVER['PHP_AUTH_PW'] ?? '';

//     if (!isset($authorized_users[$user_]) || $authorized_users[$user_] !== $pass_) {
//         $realm = 'Developer Toggle ' . time();
//         header('WWW-Authenticate: Basic realm="' . $realm . '"');
//         header('HTTP/1.0 401 Unauthorized');
//         echo 'ASA 😝';
//         exit;
//     }
// }

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

function generate_name_variants($str)
{
    $variants = [$str];
    $n_to_enye = str_ireplace('n', 'ñ', $str);
    $enye_to_n = str_ireplace('ñ', 'n', $str);

    if (!in_array($n_to_enye, $variants, true)) {
        $variants[] = $n_to_enye;
    }
    if (!in_array($enye_to_n, $variants, true)) {
        $variants[] = $enye_to_n;
    }
    return array_unique($variants);
}

$servername = "192.168.101.68";
$db = "ANNIVERSAYA";
$username = "sa";
$password = "SB1Admin";

try {
    $conn = new PDO("sqlsrv:server=$servername;database=$db;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $table_name = 'responses';
    $raffle_table = 'raffle_entries';
    $mismatch_table = 'mismatch';
    $overall_findings_table = 'overall_findings';

    // Ensure mismatch table exists
    $conn->exec("IF OBJECT_ID('$mismatch_table', 'U') IS NULL
    CREATE TABLE $mismatch_table (
        id INT IDENTITY PRIMARY KEY,
        Batch VARCHAR(50),
        docId INT,
        cust_Id INT,
        TicketCount INT,
        DocNum INT,
        Branch VARCHAR(255),
        First_Name VARCHAR(255),
        Last_Name VARCHAR(255),
        Suffix VARCHAR(10),
        Invoice_Number VARCHAR(255),
        InvAmt VARCHAR(255),
        InvName VARCHAR(255),
        Mobile_Number VARCHAR(255),
        Email VARCHAR(255),
        Findings VARCHAR(255),
        Postal_Address VARCHAR(MAX),
        Updated NVARCHAR(1),
        Exported INT, 
        Validated INT
    )");

    // Check if the 'mismatch' table exists before trying to create it
    $table_exists = $conn->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$mismatch_table'");
    if (!$table_exists->fetch()) {
        $conn->exec("CREATE TABLE $mismatch_table (
            id INT IDENTITY PRIMARY KEY,
            Batch VARCHAR(50),
            docId INT,
            cust_Id INT,
            TicketCount INT,
            DocNum INT,
            Branch VARCHAR(255),
            First_Name VARCHAR(255),
            Last_Name VARCHAR(255),
            Suffix VARCHAR(10),
            Invoice_Number VARCHAR(255),
            InvAmt VARCHAR(255),
            InvName VARCHAR(255),
            Mobile_Number VARCHAR(255),
            Email VARCHAR(255),
            Findings VARCHAR(255),
            Postal_Address VARCHAR(MAX),
            Updated NVARCHAR(1),
            Exported INT,
            Validated INT
        )");
    }

    // Ensure invalid_invoices table exists
    $conn->exec("IF OBJECT_ID('invalid_invoices', 'U') IS NULL
        CREATE TABLE invalid_invoices (
            id INT IDENTITY PRIMARY KEY,
            docId INT,
            Branch VARCHAR(255),
            First_Name VARCHAR(255),
            Last_Name VARCHAR(255),
            Suffix VARCHAR(10),
            Invoice_Number VARCHAR(255),
            Mobile_Number VARCHAR(255),
            Email VARCHAR(255),
            Findings VARCHAR(255),
            Postal_Address VARCHAR(MAX)
        )");

    $stmt = $conn->query("SELECT * FROM [$table_name] WHERE SAPDocNum IS NOT NULL");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $raffle_stmt = $conn->prepare("INSERT INTO [$raffle_table] 
        ([Counter], [cust_Id], [DocNum], [Branch], [First_Name], [Last_Name], [Suffix], [Invoice_Number], [InvAmt], [Mobile_Number], [Email], [Excluded], [Exported])
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // ORIGINAL
    $existing_check = $conn->prepare("SELECT COUNT(*) FROM [$raffle_table] WHERE [Branch] = ? AND [Invoice_Number] = ? AND (([First_Name] = ? AND [Last_Name] = ? AND [Suffix] = ?) OR ([First_Name] = ? AND [Last_Name] = ? AND [Suffix] = ?))");
    // MISMATCH
    // $mismatch_stmt = $conn->prepare("INSERT INTO [$mismatch_table] (docId, cust_Id, TicketCount, DocNum, Branch, First_Name, Last_Name, Suffix, Invoice_Number, InvAmt, InvName, InvDate, Mobile_Number, Email, Findings, Postal_Address, Updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $mismatch_stmt = $conn->prepare("INSERT INTO [$mismatch_table] (docId, cust_Id, TicketCount, DocNum, Branch, First_Name, Last_Name, Suffix, Invoice_Number, InvAmt, InvName, Mobile_Number, Email, Findings, Postal_Address, Updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    // OVERALL FINDINGS
    $overall = $conn->prepare("INSERT INTO [$overall_findings_table] (docId, branch, first_name, last_name, suffix, inv, mobile, email, postal, findings) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    // INVALID INVOICES
    $invalid_invoice_stmt = $conn->prepare("INSERT INTO invalid_invoices (docId, Branch, First_Name, Last_Name, Suffix, Invoice_Number, Mobile_Number, Email, Findings, Postal_Address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    function overall($overall, $id, $branch, $first, $last, $suffix, $inv, $mobile, $email, $postal, $findings)
    {
        $overall->execute([
            $id,
            $branch,
            $first,
            $last,
            $suffix,
            $inv,
            $mobile,
            $email,
            $postal,
            $findings
        ]);
    }

    // VALIDATION
    // $validation = $conn->prepare("EXEC Validation ?,?");
    $validation = $conn->prepare("EXEC ValidResponses");
    // EMPLOYEE VALIDATION
    $employee_check_stmt = $conn->prepare("SELECT TOP 1 * FROM employees WHERE LOWER(LTRIM(RTRIM(first_name))) = LOWER(LTRIM(RTRIM(?))) AND LOWER(LTRIM(RTRIM(last_name))) = LOWER(LTRIM(RTRIM(?)))");

    $ticketed = 0;

    foreach ($rows as $row) {
        $docId = $row['id'];
        $first_name = trim($row['First_Name'] ?? '');
        $last_name = trim($row['Last_Name'] ?? '');
        $suffix = trim($row['Suffix'] ?? '');

        $validated = strtoupper(trim($row['Validated'] ?? 'N'));
        $invalid = strtoupper(trim($row['InValid'] ?? 'N'));
        $cancel = strtoupper(trim($row['Cancel'] ?? 'N'));
        $sms = strtoupper(trim($row['SMS'] ?? 'N'));

        // Treat '0' as 'N'
        $validated = ($validated === 'Y') ? 'Y' : 'N';
        $invalid = ($invalid === 'Y') ? 'Y' : 'N';
        $cancel = ($cancel === 'Y') ? 'Y' : 'N';
        $sms = ($sms === 'Y') ? 'Y' : 'N';

        $updated = 0;

        $validation->execute();

        $results = [];

        $results = $validation->fetchAll(PDO::FETCH_ASSOC);

        $valid_match_found = false;

        $existsStmt = $conn->prepare("SELECT 1 FROM [$overall_findings_table] WHERE docId = ?");

        $existsStmt->execute([$docId]);

        if ($existsStmt->fetchColumn()) {
            continue;
        }

        foreach ($results as $validation_result) {

            $total_amt = (float) ($validation_result['InvAmt'] ?? null);
            $sap_docnum = $validation_result['SAPDocNum'] ?? null;
            $firstName = trim($validation_result['First_Name'] ?? null);
            $lastName = trim($validation_result['Last_Name'] ?? null);
            $ticketCount = $validation_result['TicketCount'];
            $invName = trim($validation_result['InvName']);

            $id = $validation_result['id'] ?? null;
            $customerId = $validation_result['cust_Id'] ?? null;
            $mobile = $validation_result['Mobile_Number'] ?? null;
            $email = $validation_result['Email'] ?? null;
            $branchPurchased = $validation_result['Branch_Purchased'] ?? null;
            $customerInvoice = $validation_result['Invoice_Number'] ?? null;
            $postal = $validation_result['Postal_Address'] ?? null;

            $employee_check_stmt->execute([$first_name, $last_name]);
            $is_employee = $employee_check_stmt->fetch(PDO::FETCH_ASSOC);

            if ($total_amt >= 5000) {
                if ($is_employee) {
                    $conn->prepare("UPDATE [$table_name] SET InValid = 'Y' WHERE id = ?")->execute([$id]);

                    $exists_overall = $conn->prepare("SELECT COUNT(*) FROM [$overall_findings_table] WHERE docId = ?");
                    $exists_overall->execute([$id]);

                    if (!$exists_overall->fetchColumn()) {
                        overall(
                            $overall,
                            $id,
                            $branchPurchased,
                            $firstName,
                            $lastName,
                            $suffix,
                            $customerInvoice,
                            $mobile,
                            $email,
                            $postal,
                            $finding_type = 'employee'
                        );
                    }

                    $exists_check = $conn->prepare("SELECT COUNT(*) FROM invalid_invoices WHERE docId = ?");
                    $exists_check->execute([$id]);

                    if (!$exists_check->fetchColumn()) {
                        $invalid_invoice_stmt->execute(
                            [
                                $id,
                                $branchPurchased,
                                $firstName,
                                $lastName,
                                $suffix,
                                $customerInvoice,
                                $mobile,
                                $email,
                                'employee',
                                $postal
                            ]
                        );
                    }

                    continue;
                } else {
                    // 100K UP
                    if ($total_amt >= 100000) {
                        $exists_check = $conn->prepare("SELECT COUNT(*) FROM [$mismatch_table] WHERE docId = ?");
                        $exists_check->execute([$id]);

                        if (!$exists_check->fetchColumn()) {
                            $mismatch_stmt->execute(
                                [
                                    $id,
                                    $customerId,
                                    $ticketCount,
                                    $sap_docnum,
                                    $branchPurchased,
                                    $firstName,
                                    $lastName,
                                    $suffix,
                                    $customerInvoice,
                                    $total_amt,
                                    $invName,
                                    $mobile,
                                    $email,
                                    '100K up', // REASON
                                    $postal,
                                    $updated
                                ]
                            );

                            $exists_overall = $conn->prepare("SELECT COUNT(*) FROM [$overall_findings_table] WHERE docId = ?");
                            $exists_overall->execute([$id]);

                            if (!$exists_overall->fetchColumn()) {
                                overall(
                                    $overall,
                                    $id,
                                    $branchPurchased,
                                    $firstName,
                                    $lastName,
                                    $suffix,
                                    $customerInvoice,
                                    $mobile,
                                    $email,
                                    $postal,
                                    $finding_type = '100K up' // REASON
                                );
                            }
                            $conn->prepare("UPDATE [$table_name] SET Validated = 'Y' WHERE id = ?")->execute([$id]);
                        }
                    } else {
                        $existing_check->execute(
                            [
                                $branchPurchased,
                                $customerInvoice,
                                $firstName,
                                $lastName,
                                $suffix,
                                $lastName,
                                $firstName,
                                $suffix
                            ]
                        );

                        if (!$existing_check->fetchColumn()) {
                            if ($ticketCount > 0) {

                                $firstExists = stripos($invName, $firstName) !== false;
                                $lastExists  = stripos($invName, $lastName) !== false;

                                if ($firstExists && $lastExists) {
                                    $exists_doc = $conn->prepare("SELECT COUNT(*) FROM [$raffle_table] WHERE [DocNum] = ?");
                                    $exists_doc->execute([$sap_docnum]);

                                    if (!$exists_doc->fetchColumn()) {
                                        for ($i = 0; $i < $ticketCount; $i++) {
                                            $raffle_stmt->execute(
                                                [
                                                    $ticketCount,
                                                    $customerId,
                                                    $sap_docnum,
                                                    $branchPurchased,
                                                    $firstName,
                                                    $lastName,
                                                    $suffix,
                                                    $customerInvoice,
                                                    $total_amt,
                                                    $mobile,
                                                    $email,
                                                    0,
                                                    0
                                                ]
                                            );
                                        }
                                        $ticketed += $ticketCount;
                                        $conn->prepare("UPDATE [$table_name] SET Validated = 'Y' WHERE id = ?")->execute([$id]);
                                    }
                                } else {
                                    $exists_check = $conn->prepare("SELECT COUNT(*) FROM [$mismatch_table] WHERE docId = ?");
                                    $exists_check->execute([$id]);

                                    if (!$exists_check->fetchColumn()) {
                                        $mismatch_stmt->execute(
                                            [
                                                $id,
                                                $customerId,
                                                $ticketCount,
                                                $sap_docnum,
                                                $branchPurchased,
                                                $firstName,
                                                $lastName,
                                                $suffix,
                                                $customerInvoice,
                                                $total_amt,
                                                $invName,
                                                $mobile,
                                                $email,
                                                'Name mismatch', // REASON
                                                $postal,
                                                $updated
                                            ]
                                        );

                                        $exists_overall = $conn->prepare("SELECT COUNT(*) FROM [$overall_findings_table] WHERE docId = ?");
                                        $exists_overall->execute([$id]);

                                        if (!$exists_overall->fetchColumn()) {
                                            try {
                                                overall(
                                                    $overall,
                                                    $id,
                                                    $branchPurchased,
                                                    $firstName,
                                                    $lastName,
                                                    $suffix,
                                                    $customerInvoice,
                                                    $mobile,
                                                    $email,
                                                    $postal,
                                                    $finding_type = 'Name mismatch' // REASON
                                                );
                                            } catch (PDOException $e) {
                                                errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
                                            }
                                        }
                                        $conn->prepare("UPDATE [$table_name] SET Validated = 'Y' WHERE id = ?")->execute([$id]);
                                    }
                                }
                            }
                        }
                    }
                }
                // NEXT --->>> BELOW 5K
            } else {
                if ($firstName === "" || $lastName === "" || $firstName === NULL || $lastName === NULL) {
                    continue;
                }

                $exists_invalid = $conn->prepare("SELECT COUNT(*) FROM invalid_invoices WHERE docId = ?");
                $exists_invalid->execute([$id]);

                if (!$exists_invalid->fetchColumn()) {
                    $invalid_invoice_stmt->execute(
                        [
                            $id,
                            $branchPurchased,
                            $firstName,
                            $lastName,
                            $suffix,
                            $customerInvoice,
                            $mobile,
                            $email,
                            '5K Below', // REASON
                            $postal
                        ]
                    );
                }

                $exists_overall = $conn->prepare("SELECT COUNT(*) FROM [$overall_findings_table] WHERE docId = ?");
                $exists_overall->execute([$id]);

                if (!$exists_overall->fetchColumn()) {
                    overall(
                        $overall,
                        $id,
                        $branchPurchased,
                        $firstName,
                        $lastName,
                        $suffix,
                        $customerInvoice,
                        $mobile,
                        $email,
                        $postal,
                        $finding_type = '5K Below' // REASON
                    );
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
