<?php

if (php_sapi_name() !== 'cli') {
    $authorized_users = ['karlalooo' => 'iap', 'merruel' => 'iap', 'jummie' => 'iap'];
    $user_ = $_SERVER['PHP_AUTH_USER'] ?? '';
    $pass_ = $_SERVER['PHP_AUTH_PW'] ?? '';

    if (!isset($authorized_users[$user_]) || $authorized_users[$user_] !== $pass_) {
        $realm = 'Developer Toggle ' . time();
        header('WWW-Authenticate: Basic realm="' . $realm . '"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'ASA 😝';
        exit;
    }
}

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
        ID INT IDENTITY PRIMARY KEY,
        docId INT,
        Branch VARCHAR(255),
        First_Name VARCHAR(255),
        Last_Name VARCHAR(255),
        Suffix VARCHAR(10),
        Invoice_Number VARCHAR(255),
        Mobile_Number VARCHAR(255),
        Email VARCHAR(255),
        Findings VARCHAR(255),
        Postal_Address VARCHAR(MAX),
        Updated NVARCHAR(1)
    )");

    // Check if the 'mismatch' table exists before trying to create it
    $table_exists = $conn->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$mismatch_table'");
    if (!$table_exists->fetch()) {
        $conn->exec("CREATE TABLE $mismatch_table (
            ID INT IDENTITY PRIMARY KEY,
            docId INT,
            Branch VARCHAR(255),
            First_Name VARCHAR(255),
            Last_Name VARCHAR(255),
            Suffix VARCHAR(10),
            Invoice_Number VARCHAR(255),
            Mobile_Number VARCHAR(255),
            Email VARCHAR(255),
            Findings VARCHAR(255),
            Postal_Address VARCHAR(MAX),
            Updated NVARCHAR(1)
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

    $stmt = $conn->query("SELECT * FROM [$table_name]");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $raffle_stmt = $conn->prepare("INSERT INTO [$raffle_table] 
        ([Counter], [DocNum], [Branch], [First_Name], [Last_Name], [Suffix], [Invoice_Number], [Mobile_Number], [Email], [Postal_Address], [Excluded], [Exported])
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // ORIGINAL
    $existing_check = $conn->prepare("SELECT COUNT(*) FROM [$raffle_table] WHERE [Branch] = ? AND [Invoice_Number] = ? AND (([First_Name] = ? AND [Last_Name] = ? AND [Suffix] = ?) OR ([First_Name] = ? AND [Last_Name] = ? AND [Suffix] = ?))");

    // MISMATCH
    $mismatch_stmt = $conn->prepare("INSERT INTO [$mismatch_table] (docId, Branch, First_Name, Last_Name, Suffix, Invoice_Number, Mobile_Number, Email, Findings, Postal_Address, Updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    // OVERALL FINDINGS
    $overall = $conn->prepare("INSERT INTO [$overall_findings_table] (docId, branch, first_name, last_name, suffix, inv, mobile, email, postal, findings) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    // INVALID INVOICES
    $invalid_invoice_stmt = $conn->prepare("INSERT INTO invalid_invoices (docId, Branch, First_Name, Last_Name, Suffix, Invoice_Number, Mobile_Number, Email, Findings, Postal_Address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");


    function overall($overall, $docId, $branch, $first, $last, $suffix, $inv, $mobile, $email, $postal, $findings)
    {

        $overall->execute([
            $docId,
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

    $ticketed = 0;

    foreach ($rows as $row) {
        $docId = $row['id'];
        $first_name = trim($row['First_Name'] ?? '');
        $last_name = trim($row['Last_Name'] ?? '');
        $suffix = trim($row['Suffix'] ?? '');
        $mobile_val = trim($row['Mobile_Number'] ?? '');
        $branch_val = trim($row['Branch_Purchased'] ?? '');
        $inv_val = trim($row['Invoice_Number'] ?? '');
        $email_val = trim(strtolower($row['Email'] ?? ''));
        $postal_address = trim($row['Postal_Address'] ?? '');

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

        // $validation_proc = $conn->prepare("EXEC CustomerValidation1 @mLName_ = ?, @mFName_ = ?, @mSuffix_ = ?, @mMDN_ = ?, @mBranch_ = ?");
        $validation_proc = $conn->prepare("EXEC CustomerValidation1 ?,?,?,?,?");

        $name_variants = generate_name_variants($first_name);
        $last_variants = generate_name_variants($last_name);
        $suffix_variants = generate_name_variants($suffix);

        $results = [];
        $matched = false;

        foreach ($name_variants as $fname) {
            foreach ($last_variants as $lname) {
                foreach ($suffix_variants as $sfx) {
                    $validation_proc->execute([$lname, $fname, $sfx, $inv_val, $branch_val]);
                    $results = $validation_proc->fetchAll(PDO::FETCH_ASSOC);
                    if (count($results) > 0) {
                        $first_name = $fname;
                        $last_name = $lname;
                        $suffix = $sfx;
                        $matched = true;
                        break 3;
                    }
                }
            }
        }

        $valid_match_found = false;

        foreach ($results as $validation_result) {
            if ($validation_result['LastName'] === 'Y' && $validation_result['FirstName'] === 'Y' && $validation_result['Suffix'] === 'Y') {
                $existing_check->execute([
                    $branch_val,
                    $inv_val,
                    $first_name,
                    $last_name,
                    $suffix,
                    $last_name,
                    $first_name,
                    $suffix
                ]);

                $total_amt = (float) ($validation_result['TotalAmt'] ?? 0);
                $ticket_count = floor($total_amt / 5000);

                if ($ticket_count > 0) {
                    if ($total_amt >= 100000) {
                        $exists_check = $conn->prepare("SELECT COUNT(*) FROM [$mismatch_table] WHERE docId = ?");
                        $exists_check->execute([$docId]);

                        if (!$exists_check->fetchColumn()) {
                            $mismatch_stmt->execute([
                                $docId,
                                $branch_val,
                                $first_name,
                                $last_name,
                                $suffix,
                                $inv_val,
                                $mobile_val,
                                $email_val,
                                'amount',
                                $postal_address,
                                $updated
                            ]);

                            $exists_overall = $conn->prepare("SELECT COUNT(*) FROM [$overall_findings_table] WHERE docId = ?");
                            $exists_overall->execute([$docId]);
                            if (!$exists_overall->fetchColumn()) {
                                overall(
                                    $overall,
                                    $docId,
                                    $branch_val,
                                    $first_name,
                                    $last_name,
                                    $suffix,
                                    $inv_val,
                                    $mobile_val,
                                    $email_val,
                                    $postal_address,
                                    $finding_type = 'amount'
                                );
                            }
                            $conn->prepare("UPDATE [$table_name] SET InValid = 'Y' WHERE id = ?")->execute([$docId]);
                        }
                    } else {
                        $existing_check->execute([
                            $branch_val,
                            $inv_val,
                            $first_name,
                            $last_name,
                            $suffix,
                            $last_name,
                            $first_name,
                            $suffix
                        ]);

                        if (!$existing_check->fetchColumn()) {
                            $sap_docnum = $validation_result['DocNum'] ?? null;

                            if ($sap_docnum !== null) {
                                $conn->prepare("UPDATE [$table_name] SET SAPDocNum = ? WHERE id = ?")->execute([$sap_docnum, $docId]);
                            }

                            // ✅ Check cancel fields before inserting
                            $cancel_sap_doc = trim($validation_result['CancelSISAPDoc'] ?? '');
                            $cancel_si_no = trim($validation_result['CancelSINo'] ?? '');
                            $cancel_si_date = trim($validation_result['CancelSIDate'] ?? '');

                            if (($cancel_sap_doc !== '' || $cancel_si_no !== '' || $cancel_si_date !== '') && $cancel === 'N') {
                                $doc_date = $validation_result['DocDate'] ?? '';
                                $cancel_date = $validation_result['CancelSIDate'] ?? '';

                                $insert_target = 'mismatch'; // default

                                if ($doc_date !== '' && $cancel_date !== '') {
                                    $doc_month = date('Y-m', strtotime($doc_date));
                                    $cancel_month = date('Y-m', strtotime($cancel_date));

                                    // Check if both are in the same month AND in June
                                    if ($doc_month === $cancel_month && date('m', strtotime($cancel_date)) === '06') {
                                        $insert_target = 'mismatch';
                                    } else {
                                        $insert_target = 'invalid_invoices';
                                    }
                                }

                                if ($insert_target === 'mismatch') {
                                    $exists_check = $conn->prepare("SELECT COUNT(*) FROM [$mismatch_table] WHERE docId = ?");
                                    $exists_check->execute([$docId]);
                                    if (!$exists_check->fetchColumn()) {
                                        $mismatch_stmt->execute([
                                            $docId,
                                            $branch_val,
                                            $first_name,
                                            $last_name,
                                            $suffix,
                                            $inv_val,
                                            $mobile_val,
                                            $email_val,
                                            'cancellation',
                                            $postal_address,
                                            $updated
                                        ]);
                                    }
                                } else {
                                    $exists_invalid = $conn->prepare("SELECT COUNT(*) FROM invalid_invoices WHERE docId = ?");
                                    $exists_invalid->execute([$docId]);
                                    if (!$exists_invalid->fetchColumn()) {
                                        $invalid_invoice_stmt->execute([
                                            $docId,
                                            $branch_val,
                                            $first_name,
                                            $last_name,
                                            $suffix,
                                            $inv_val,
                                            $mobile_val,
                                            $email_val,
                                            'cancellation',
                                            $postal_address
                                        ]);
                                    }
                                }

                                $exists_overall = $conn->prepare("SELECT COUNT(*) FROM [$overall_findings_table] WHERE docId = ?");
                                $exists_overall->execute([$docId]);
                                if (!$exists_overall->fetchColumn()) {
                                    overall(
                                        $overall,
                                        $docId,
                                        $branch_val,
                                        $first_name,
                                        $last_name,
                                        $suffix,
                                        $inv_val,
                                        $mobile_val,
                                        $email_val,
                                        $postal_address,
                                        'cancellation'
                                    );
                                }

                                // $conn->prepare("UPDATE [$table_name] SET InValid = 'Y', Cancel = 'Y' WHERE id = ?")->execute([$docId]);
                                $conn->prepare("UPDATE [$table_name] SET InValid = 'Y' WHERE id = ?")->execute([$docId]);
                                continue; // ⛔ skip raffle insertion
                            }

                            // ✅ Proceed with ticket insertion if cancellation fields are empty
                            $total_amt = (float) ($validation_result['TotalAmt'] ?? 0);
                            $ticket_count = floor($total_amt / 5000);

                            if ($ticket_count > 0) {
                                // Check if this specific SAPDocNum already has entries
                                $exists_doc = $conn->prepare("SELECT COUNT(*) FROM [$raffle_table] WHERE [DocNum] = ?");
                                $exists_doc->execute([$sap_docnum]);

                                if (!$exists_doc->fetchColumn()) {
                                    for ($i = 0; $i < $ticket_count; $i++) {
                                        $raffle_stmt->execute([
                                            $ticket_count,
                                            $sap_docnum,
                                            $branch_val,
                                            $first_name,
                                            $last_name,
                                            $suffix,
                                            $inv_val,
                                            $mobile_val,
                                            $email_val,
                                            $postal_address,
                                            0,
                                            0,
                                        ]);
                                    }

                                    $ticketed += $ticket_count;
                                    $conn->prepare("UPDATE [$table_name] SET Validated = 'Y' WHERE id = ?")->execute([$docId]);
                                }
                            }
                        }
                    }
                }
                $valid_match_found = true;
            } else if (($validation_result['LastName'] === 'N' && $validation_result['FirstName'] === 'N' && $validation_result['Suffix'] === 'N') ||
                ($validation_result['LastName'] === 'N' && $validation_result['FirstName'] === 'N' && $validation_result['Suffix'] === 'Y')
            ) {
                $exists_invalid = $conn->prepare("SELECT COUNT(*) FROM invalid_invoices WHERE docId = ?");
                $exists_invalid->execute([$docId]);
                if (!$exists_invalid->fetchColumn()) {
                    $invalid_invoice_stmt->execute([
                        $docId,
                        $branch_val,
                        $first_name,
                        $last_name,
                        $suffix,
                        $inv_val,
                        $mobile_val,
                        $email_val,
                        'invoice or branch',
                        $postal_address
                    ]);
                }
                $valid_match_found = false;

                $exists_overall = $conn->prepare("SELECT COUNT(*) FROM [$overall_findings_table] WHERE docId = ?");
                $exists_overall->execute([$docId]);
                if (!$exists_overall->fetchColumn()) {
                    overall(
                        $overall,
                        $docId,
                        $branch_val,
                        $first_name,
                        $last_name,
                        $suffix,
                        $inv_val,
                        $mobile_val,
                        $email_val,
                        $postal_address,
                        $finding_type = 'invoice or branch'
                    );
                }
                $conn->prepare("UPDATE [$table_name] SET InValid = 'Y' WHERE id = ?")->execute([$docId]);
            } else if ($validation_result['LastName'] === 'Y') {
                // Y - Y - N
                if ($validation_result['FirstName'] === 'Y' && $validation_result['Suffix'] === 'N') {
                    $exists_check = $conn->prepare("SELECT COUNT(*) FROM [$mismatch_table] WHERE docId = ?");
                    $exists_check->execute([$docId]);

                    if (!$exists_check->fetchColumn()) {
                        $mismatch_stmt->execute([
                            $docId,
                            $branch_val,
                            $first_name,
                            $last_name,
                            $suffix,
                            $inv_val,
                            $mobile_val,
                            $email_val,
                            'name',
                            $postal_address,
                            $updated
                        ]);
                    }
                    $valid_match_found = false;

                    $exists_overall = $conn->prepare("SELECT COUNT(*) FROM [$overall_findings_table] WHERE docId = ?");
                    $exists_overall->execute([$docId]);
                    if (!$exists_overall->fetchColumn()) {
                        overall(
                            $overall,
                            $docId,
                            $branch_val,
                            $first_name,
                            $last_name,
                            $suffix,
                            $inv_val,
                            $mobile_val,
                            $email_val,
                            $postal_address,
                            $finding_type = 'name'
                        );
                    }
                    $conn->prepare("UPDATE [$table_name] SET InValid = 'Y' WHERE id = ?")->execute([$docId]);
                } else if (($validation_result['FirstName'] === 'N' && $validation_result['Suffix'] === 'N') || ($validation_result['FirstName'] === 'N' && $validation_result['Suffix'] === 'Y')) {
                    $exists_check = $conn->prepare("SELECT COUNT(*) FROM [$mismatch_table] WHERE docId = ?");
                    $exists_check->execute([$docId]);

                    if (!$exists_check->fetchColumn()) {
                        $mismatch_stmt->execute([
                            $docId,
                            $branch_val,
                            $first_name,
                            $last_name,
                            $suffix,
                            $inv_val,
                            $mobile_val,
                            $email_val,
                            'name',
                            $postal_address,
                            $updated
                        ]);
                    }
                    $valid_match_found = false;

                    $exists_overall = $conn->prepare("SELECT COUNT(*) FROM [$overall_findings_table] WHERE docId = ?");
                    $exists_overall->execute([$docId]);
                    if (!$exists_overall->fetchColumn()) {
                        overall(
                            $overall,
                            $docId,
                            $branch_val,
                            $first_name,
                            $last_name,
                            $suffix,
                            $inv_val,
                            $mobile_val,
                            $email_val,
                            $postal_address,
                            $finding_type = 'name'
                        );
                    }
                    $conn->prepare("UPDATE [$table_name] SET InValid = 'Y' WHERE id = ?")->execute([$docId]);
                }
            } else if ($validation_result['LastName'] === 'N') {
                if (($validation_result['FirstName'] === 'Y' && $validation_result['Suffix'] === 'N') || ($validation_result['FirstName'] === 'Y' && $validation_result['Suffix'] === 'Y')) {
                    $exists_check = $conn->prepare("SELECT COUNT(*) FROM [$mismatch_table] WHERE docId = ?");
                    $exists_check->execute([$docId]);

                    if (!$exists_check->fetchColumn()) {
                        $mismatch_stmt->execute([
                            $docId,
                            $branch_val,
                            $first_name,
                            $last_name,
                            $suffix,
                            $inv_val,
                            $mobile_val,
                            $email_val,
                            'name',
                            $postal_address,
                            $updated
                        ]);
                    }
                    $valid_match_found = false;

                    $exists_overall = $conn->prepare("SELECT COUNT(*) FROM [$overall_findings_table] WHERE docId = ?");
                    $exists_overall->execute([$docId]);
                    if (!$exists_overall->fetchColumn()) {
                        overall(
                            $overall,
                            $docId,
                            $branch_val,
                            $first_name,
                            $last_name,
                            $suffix,
                            $inv_val,
                            $mobile_val,
                            $email_val,
                            $postal_address,
                            $finding_type = 'name'
                        );
                    }
                    $conn->prepare("UPDATE [$table_name] SET InValid = 'Y' WHERE id = ?")->execute([$docId]);
                }
            }
        }

        if (!$valid_match_found) {
            // Check if this record is already in mismatch_table
            $exists_check = $conn->prepare("SELECT COUNT(*) FROM [$mismatch_table] WHERE docId = ?");
            $exists_check->execute([$docId]);

            // Step 1: If not in mismatch_table, then continue with insertions
            if (!$exists_check->fetchColumn()) {
                // Check if it's already inserted in invalid_invoices table
                $exists_invalid = $conn->prepare("SELECT COUNT(*) FROM invalid_invoices WHERE docId = ?");
                $exists_invalid->execute([$docId]);

                // Step 2: If it's not already in invalid_invoices, then insert into the appropriate table
                if (!$exists_invalid->fetchColumn()) {

                    // Step 3: Determine finding type (either 'invoice or branch' or 'name')
                    $finding_type = (count($results) === 0) ? 'invoice or branch' : 'name';

                    // Step 4: Insert into ONLY one table (either mismatch or invalid_invoices)
                    if ($finding_type === 'invoice or branch') {
                        // Insert into invalid_invoices table
                        $invalid_invoice_stmt->execute([
                            $docId,
                            $branch_val,
                            $first_name,
                            $last_name,
                            $suffix,
                            $inv_val,
                            $mobile_val,
                            $email_val,
                            $finding_type,
                            $postal_address
                        ]);
                    } else {
                        // Insert into mismatch table
                        $mismatch_stmt->execute([
                            $docId,
                            $branch_val,
                            $first_name,
                            $last_name,
                            $suffix,
                            $inv_val,
                            $mobile_val,
                            $email_val,
                            $finding_type,
                            $postal_address,
                            $updated
                        ]);
                    }

                    $exists_overall = $conn->prepare("SELECT COUNT(*) FROM [$overall_findings_table] WHERE docId = ?");
                    $exists_overall->execute([$docId]);
                    if (!$exists_overall->fetchColumn()) {
                        overall(
                            $overall,
                            $docId,
                            $branch_val,
                            $first_name,
                            $last_name,
                            $suffix,
                            $inv_val,
                            $mobile_val,
                            $email_val,
                            $postal_address,
                            $finding_type
                        );
                    }

                    // Mark this record as invalid in the main table
                    $conn->prepare("UPDATE [$table_name] SET InValid = 'Y' WHERE id = ?")->execute([$docId]);
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
