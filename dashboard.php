<?php include('config/config.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $user_name ?></title>
    <link rel="icon" href="assets/img/logo.png" />
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
                            <th>Mobile Number</th>
                            <th>Email</th>
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
                                $mobile = $entry->Mobile_Number;
                                $email =  $entry->Email;

                                echo '<tr>
                                    <td>' . $id . '</td>
                                    <td>' . $branch . '</td>
                                    <td>' . $first . '</td>
                                    <td>' . $last . '</td>
                                    <td>' . $mobile . '</td>
                                    <td>' . $email . '</td>
                                    <td>';

                                if ($entry->Findings == "amount") {
                                    echo '<button class="btn btn-success btn-sm amount" data-id="' . $id . '" title="Approve exceeded amount?" data-branch="' . $branch . '">
                                            <i class="bi bi-check-lg"></i>
                                        </button>';
                                } else if ($entry->Findings === "cancellation") {
                                    echo '<button class="btn btn-danger btn-sm cancellation text-white" data-id="' . $id . '" title="Approve entry?" data-branch="' . $branch . '">
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