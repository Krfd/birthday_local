<?php include('config/config.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $user_name ?></title>
    <link rel="icon" href="assets/img/logo.jpg" />
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/vendor/bootstrap-icons.css" />
    <link rel="stylesheet" href="js/datatables/dataTables.css" />
    <link rel="stylessheet" href="css/sweetalert2.min.css" />
    <style>
        body {
            background: linear-gradient(135deg, #e0eafc, #cfdef3);
            height: 70vh;
            font-family: sans-serif;
        }

        * {
            scrollbar-width: thin;
            scrollbar-color: #3b82f6 #e0e7ff
        }

        /* Chrome, Safari, Edge (WebKit-based) */
        *::-webkit-scrollbar {
            width: 2px;
            height: 2px;
        }

        *::-webkit-scrollbar-track {
            background: #e0e7ff;
            border-radius: 4px;
        }

        *::-webkit-scrollbar-thumb {
            background-color: #3b82f6;
            border-radius: 4px;
            border: 1px solid transparent;
            background-clip: content-box;
        }

        *::-webkit-scrollbar-thumb:hover {
            background-color: #1e40af;
        }

        #entries thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            /* Match your table's background */
            z-index: 10;
        }
    </style>
</head>

<body>
    <!-- LOG OUT -->
    <div
        class="modal p-4 py-md-5 fade"
        tabindex="-1"
        role="dialog"
        id="logout">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-body p-4 text-center">
                    <h5 class="mb-0 fw-semibold">Log out your account?</h5>
                </div>
                <div class="modal-footer flex-nowrap p-0">
                    <button
                        type="button"
                        class="btn btn-lg btn-link fs-6 text-decoration-none col-6 py-3 m-0 rounded-0 border-end">
                        <a href="config/config.php?logout=true" class="text-decoration-none"><strong>Logout</strong></a>
                    </button>
                    <button
                        type="button"
                        class="btn btn-lg btn-link fs-6 text-decoration-none col-6 py-3 m-0 rounded-0"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- UPDATE ACCOUNT -->
    <div
        class="modal p-4 py-md-5 fade"
        tabindex="-1"
        role="dialog"
        aria-hidden="true"
        id="update">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-body p-4">
                    <div class="d-flex">
                        <h5 class="mb-0 fw-semibold text-center">Update Password</h5>
                        <button class="ms-auto btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <hr>
                    <form method="post" class="mt-3" id="updatePasswordForm">
                        <input type="hidden" name="user_id" value="<?php echo $userId ?>" required>
                        <div class="mt-3 d-flex flex-column gap-3 w-auto">
                            <div class="col">
                                <label for="old">Old Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control mt-1" name="old" id="old" maxlength="20" required>
                            </div>
                            <div class="col">
                                <label for="new" class="form-label">New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control mt-1" name="new" id="new" maxlength="20" required>
                            </div>
                            <div class="col">
                                <label for="confirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control mt-1" name="confirm" id="confirm" maxlength="20" required>
                            </div>
                        </div>
                        <button
                            type="submit"
                            class="btn btn-lg btn-link fs-6 text-decoration-none col-6 pt-3 m-0 rounded-0 d-block mx-auto">
                            <strong>Save</strong>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="position-relative py-3 mt-3">
        <!-- Left Side Buttons -->
        <div class="position-absolute start-0 m-3 ps-2">
            <button class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#logout"><i class="bi bi-box-arrow-left"></i> Logout</button>
            <br>
            <button type="button" class="btn btn-sm btn-light text-dark mt-3" data-bs-toggle="modal" data-bs-target="#update" title="Update Password"><i class="bi bi-pen"></i> <span class="d-none d-md-inline-block">Account</span></button>
        </div>
        <!-- Center Heading -->
        <h1 class="fw-bold text-center m-0 text-uppercase"><?php echo $user_name . ' - ' . $dept ?></h1>
    </div>
    <div class="container-fluid mt-3 d-flex flex-column flex-lg-row justify-content-between mt-3 mt-md-5 gap-3 align-items-start">
        <!-- CUSTOMER ENTRIES -->
        <div class="container bg-light rounded-1 p-3 col-12 col-lg-8 shadow-sm mb-5" style="height: 70vh; max-height: 70vh;">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="fw-semibold">Customer Entries</h1>
                <button class="btn btn-sm btn-success float-end fw-bold shadow-sm" id="exportBtn">Export CSV</button>
            </div>
            <div class="table-responsive overflow-y-scroll" style="height: 55vh">
                <table class="table datatables" id="entries">
                    <thead>
                        <tr>
                            <th>Mobile Number</th>
                            <th>E-Ticket</th>
                            <th>Total</th>
                            <th>Branch</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Invoice Number</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        $entries = $conn->prepare("SELECT * FROM raffle_entries WHERE Excluded = 0 AND Exported = 0 ORDER BY Ticket_Number ASC");
                        $entries->execute();
                        $getEntries = $entries->fetchAll(PDO::FETCH_OBJ);

                        $groupedEntries = [];

                        if ($getEntries) {
                            foreach ($getEntries as $entry) {
                                // Group key now includes Branch to avoid cross-branch grouping
                                $key = $entry->First_Name . '|' . $entry->Last_Name . '|' . $entry->Mobile_Number . '|' . $entry->Email . '|' . $entry->Branch . '|' . $entry->Invoice_Number;

                                if (!isset($groupedEntries[$key])) {
                                    $groupedEntries[$key] = [
                                        'first' => $entry->First_Name,
                                        'last' => $entry->Last_Name,
                                        'mobile' => $entry->Mobile_Number,
                                        'email' => $entry->Email,
                                        'branch' => $entry->Branch,
                                        'invoices' => [],
                                        'tickets' => [],
                                        'total_tickets' => $entry->Counter
                                    ];
                                }

                                // Add ticket number
                                $groupedEntries[$key]['tickets'][] = $entry->Ticket_Number;

                                // Add ticket count
                                $groupedEntries[$key]['total_tickets'] = $entry->Counter;

                                // Add invoice number if not already in the list
                                if (!in_array($entry->Invoice_Number, $groupedEntries[$key]['invoices'])) {
                                    $groupedEntries[$key]['invoices'][] = $entry->Invoice_Number;
                                }
                            }

                            // Display grouped data
                            foreach ($groupedEntries as $data) {
                                $tickets = $data['tickets'];
                                sort($tickets); // Sort ticket numbers

                                // Format ticket display
                                if (count($tickets) === 1) {
                                    $ticketDisplay = $tickets[0];
                                } else {
                                    $ticketDisplay = min($tickets) . ' - ' . max($tickets);
                                }

                                // Join all invoice numbers
                                $invoiceDisplay = implode(', ', $data['invoices']);

                                // Output table row
                                echo '<tr>
                                    <td>' . htmlspecialchars($data['mobile']) . '</td>
                                    <td>' . htmlspecialchars($ticketDisplay) . '</td>
                                    <td>' . htmlspecialchars($data['total_tickets']) . '</td>
                                    <td>' . htmlspecialchars($data['branch']) . '</td>
                                    <td>' . htmlspecialchars($data['first']) . '</td>
                                    <td>' . htmlspecialchars($data['last']) . '</td>
                                    <td>' . htmlspecialchars($invoiceDisplay) . '</td>
                                    <td>' . htmlspecialchars($data['email']) . '</td>
                                </tr>';
                            }
                        }

                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="js/sweetalert/sweetalert2.all.min.js"></script>
    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/datatables/dataTables.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/update.js"></script>
    <script>
        $(document).ready(function() {
            $('#entries').DataTable({
                "order": [
                    [1, 'asc']
                ]
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const exportBtn = document.getElementById("exportBtn");
            const tableBody = document.querySelector("#entries tbody");

            // Check if there are any <tr> elements inside <tbody>
            const hasRows = tableBody.querySelectorAll("tr").length > 0;

            // Disable the button if no rows
            if (!hasRows) {
                exportBtn.disabled = true;
            }
        });

        document.getElementById("exportBtn").addEventListener("click", () => {

            const exportBtn = document.getElementById("exportBtn");
            exportBtn.disabled = true

            // Create an invisible iframe to download the CSV
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = 'functions/export_csv.php';
            document.body.appendChild(iframe);

            // Redirect after a short delay (adjust time if needed)
            setTimeout(() => {
                window.location.href = "ar.php";
            }, 3000); // Wait 3 seconds to ensure download starts
        });
    </script>
</body>

</html>