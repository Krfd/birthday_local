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
            /* background: linear-gradient(135deg, #e0eafc, #cfdef3); */
            background:
                linear-gradient(rgba(180, 30, 30, 0.45), rgba(180, 30, 30, 0.45)),
                url("assets/img/newbg.jpg") no-repeat center center / cover;
            /* height: 70vh; */
            height: 90vh;
            min-height: auto;
            font-family: sans-serif;
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
                                <label for="old" class="form-label fw-semibold">Old Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="old" id="old" maxlength="20" required>
                            </div>
                            <div class="col">
                                <label for="new" class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="new" id="new" maxlength="20" required>
                            </div>
                            <div class="col">
                                <label for="confirm" class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="confirm" id="confirm" maxlength="20" required>
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

    <!-- MISMATCHED RECORDS -->
    <?php
    $customers = $conn->prepare("SELECT * FROM mismatch WHERE Updated = 0 ORDER BY id DESC");
    $customers->execute();

    $getCustomers = $customers->fetchAll(PDO::FETCH_OBJ);
    if ($getCustomers) {
        foreach ($getCustomers as $customer) {

            $id = $customer->id;
            $docId = $customer->docId;
            $cus_fname = $customer->First_Name;
            $cus_lname = $customer->Last_Name;
            $suffix = $customer->Suffix;
            $cus_inv = $customer->Invoice_Number;
            $cus_branch = $customer->Branch;
            $cus_findings = $customer->Findings;

            echo '<div class="modal fade p-4" id="user_' . $id . '">
              <div class="modal-dialog" role="document">
                  <div class="modal-content rounded-3 shadow">
                      <div class="modal-body p-4">
                        <div class="d-flex align-items-center">
                            <h5 class="text-center fw-bold text-secondary-emphasis text-center">Update Customer</h5>
                            <button class="btn btn-sm btn-close ms-auto" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="updateMismatch_' . $id . '" class="mt-3 mb-0" method="post">
                            <input type="hidden" name="first" value="' . $cus_fname . '"/>
                            <input type="hidden" name="last" value="' . $cus_lname . '"/>
                            <input type="hidden" name="branch" value="' . $cus_branch . '"/>
                            <input type="hidden" name="docId" value="' . $docId . '"/>
                            <input type="hidden" name="user_id" value="' . $userId . '"/>
                            <input type="hidden" name="user_dept" value="' . $dept . '"/>
                            <input type="hidden" name="inv" value="' . $cus_inv . '"/>
                            <input type="hidden" name="username" value="' . $user_name . '"/>';

            if ($cus_findings === "name") {
                echo '<div class="mt-3">
                                <label for="fname_' . $id . '" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="fname" id="fname_' . $id . '" required autocomplete="off" value="' . $cus_fname . '">
                            </div>
                            <div class="mt-3 d-flex justify-content-between gap-3">
                                <div>
                                    <label for="lname_' . $id . '" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="lname" id="lname_' . $id . '" required autocomplete="off" value="' . $cus_lname . '">
                                </div>
                                <div>
                                    <label for="suffix_' . $id . '" class="form-label">Suffix </label>
                                    <input type="text" class="form-control" name="suffix" id="suffix_' . $id . '" autocomplete="off" value="' . $suffix . '">
                                </div>
                            </div>';
            }

            echo ' <div class="flex nowrap p-0">
                                <button type="submit" class="btn d-block mx-auto btn-link fs-6 text-decoration-none m-0 mt-3 rounded-0" id="submit_' . $id . '">
                                    <strong>Update</strong>
                                </button>
                            </div>
                        </form>
                      </div>
                  </div>
              </div>
          </div>';
        }
    }
    ?>

    <div class="position-relative py-3 mt-3">
        <div class="position-absolute start-0 m-3 ps-2">
            <button id="logout-btn" style="height: 50px; width: 50px" class="btn border border-danger bg-danger-subtle btn-sm position-absolute rounded-5 top-0 start-0 m-3" title="Logout" data-bs-toggle="modal" data-bs-target="#logout">
                <i class="bi bi-box-arrow-left d-flex justify-content-center align-items-center text-danger"></i>
            </button>
        </div>
    </div>
    <div class="container-fluid mt-3 d-flex flex-column flex-lg-row justify-content-between mt-3 mt-md-5 gap-3 align-items-start">
        <!-- CUSTOMER ENTRIES -->
        <div class="container bg-light rounded-5 p-5 col-12 col-lg-8 shadow" style="height: 70vh; max-height: 70vh;">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="fw-semibold"><span class="text-primary-emphasis"><?php echo $user_name ?></span> - <span class="text-danger">Findings</span></h1>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#update" title="Update Password"><i class="bi bi-pen"></i> <span class="d-none d-md-inline-block">Account</span></button>
            </div>
            <div class="table-responsive overflow-y-scroll" style="height: 55vh">
                <table class="table datatables" id="mismatch">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Branch</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Invoice Number</th>
                            <th>Mobile Number</th>
                            <th>Email</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        $getEntries = [];

                        if (!empty($region)) {
                            // Convert $region string to array if not already an array
                            if (!is_array($region)) {
                                $region = explode(',', $region); // split string by commas
                                // trim whitespace around each region name
                                $region = array_map('trim', $region);
                            }

                            if (!empty($region)) {
                                // Prepare placeholders for PDO query
                                $placeholders = implode(',', array_fill(0, count($region), '?'));

                                // Query branch_category for branches in the given regions
                                $stmtBranches = $conn->prepare("SELECT branch FROM branch_category WHERE region IN ($placeholders)");
                                $stmtBranches->execute($region);
                                $branchesResult = $stmtBranches->fetchAll(PDO::FETCH_COLUMN);

                                if (!empty($branchesResult)) {
                                    // Prepare placeholders for branches
                                    $placeholdersBranches = implode(',', array_fill(0, count($branchesResult), '?'));

                                    $entries = $conn->prepare("SELECT * FROM mismatch m 
                                    WHERE NOT EXISTS (
                                        SELECT 1 FROM raffle_entries r 
                                        WHERE r.Branch = m.Branch 
                                        AND r.First_Name = m.First_Name 
                                        AND r.Last_Name = m.Last_Name 
                                        AND r.Invoice_Number = m.Invoice_Number 
                                        AND r.Mobile_Number = m.Mobile_Number 
                                        AND r.Email = m.Email
                                    ) 
                                    AND Updated = 0
                                    AND Branch IN ($placeholdersBranches)
                                    ORDER BY id DESC");

                                    $entries->execute($branchesResult);
                                    $getEntries = $entries->fetchAll(PDO::FETCH_OBJ);
                                }
                            }
                        }

                        if ($getEntries) {
                            foreach ($getEntries as $entry) {
                                $id = $entry->id;
                                $branch = $entry->Branch;
                                $first = $entry->First_Name;
                                $last = $entry->Last_Name;
                                $inv = $entry->Invoice_Number;
                                $mobile = $entry->Mobile_Number;
                                $email =  $entry->Email;
                                $remarks =  $entry->Findings;

                                if ($remarks === "amount") {
                                    $remarks = "Exceeded amount to 100,000";
                                } else if ($remarks === "cancellation") {
                                    $remarks = "Has cancelled invoice";
                                } else if ($remarks === "name") {
                                    $remarks = "Name mismatched";
                                }

                                echo '<tr>
                                    <td>' . $id . '</td>
                                    <td>' . $branch . '</td>
                                    <td>' . $first . '</td>
                                    <td>' . $last . '</td>
                                    <td>' . $inv . '</td>
                                    <td>' . $mobile . '</td>
                                    <td>' . $email . '</td>
                                    <td>' . $remarks . '</td>
                                    <td>';

                                if ($entry->Findings == "amount") {
                                    echo '<button class="btn btn-success btn-sm amount" data-id="' . $id . '" title="Approve exceeded amount?" data-branch="' . $branch . '" data-inv="' . $inv . '">
                                            <i class="bi bi-check-lg"></i>
                                        </button>';
                                } else if ($entry->Findings === "cancellation") {
                                    echo '<button class="btn btn-danger btn-sm cancellation text-white" data-id="' . $id . '" title="Approve entry?" data-branch="' . $branch . '" data-inv="' . $inv . '">
                                            <i class="bi bi-check-lg"></i>
                                        </button>';
                                } else {
                                    echo '<button class="btn btn-primary btn-sm" data-bs-toggle="modal" title="Rename" data-bs-target="#user_' . $id . '">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>';
                                }

                                echo '</td>
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
    <script src="js/updateMismatch.js"></script>
    <script src="js/update.js"></script>
    <script>
        new DataTable('#mismatch');

        // AMOUNT
        $(".amount").click(function(e) {
            e.preventDefault();

            var inv = $(this).attr("data-inv");
            var branch = $(this).attr("data-branch");
            var id = $(this).attr("data-id");

            Swal.fire({
                title: "Approve this customer?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Confirm",
                confirmButtonColor: "#45eb45",
                cancelButtonText: "Back",
                cancelButtonColor: "#dc3545",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "GET",
                        url: "functions/approve.php",
                        data: {
                            inv,
                            branch,
                            id
                        },
                        success: function(res) {
                            switch (res) {
                                case "approved":
                                    Swal.fire({
                                        icon: "success",
                                        title: "Customer has been approved",
                                        iconColor: "#28a745",
                                        confirmButtonColor: "#45eb45",
                                        confirmButtonText: "Okay",
                                        allowOutsideClick: false,
                                    }).then(() => {
                                        location.reload();
                                    });
                                    break;
                                default:
                                    Swal.fire({
                                        icon: "error",
                                        title: "Something went wrong",
                                        iconColor: "#dc3545",
                                        allowOutsideClick: false,
                                    });
                            }
                        },
                    });
                }
            });
        });

        // CANCELLATION
        $(".cancellation").click(function(e) {
            e.preventDefault();

            var inv = $(this).attr("data-inv");
            var branch = $(this).attr("data-branch");
            var id = $(this).attr("data-id");

            Swal.fire({
                title: "Approve this customer?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Confirm",
                confirmButtonColor: "#45eb45",
                cancelButtonText: "Back",
                cancelButtonColor: "#dc3545",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "GET",
                        url: "functions/approve.php",
                        data: {
                            inv,
                            branch,
                            id
                        },
                        success: function(res) {
                            switch (res) {
                                case "approved":
                                    Swal.fire({
                                        icon: "success",
                                        title: "Customer has been approved",
                                        iconColor: "#28a745",
                                        confirmButtonColor: "#45eb45",
                                        confirmButtonText: "Okay",
                                        allowOutsideClick: false,
                                    }).then(() => {
                                        location.reload();
                                    });
                                    break;
                                default:
                                    Swal.fire({
                                        icon: "error",
                                        title: "Something went wrong",
                                        iconColor: "#dc3545",
                                        allowOutsideClick: false,
                                    });
                            }
                        },
                    });
                }
            });
        });
    </script>
</body>

</html>