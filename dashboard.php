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
            height: 100vh;
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

    <!-- MISMATCHED RECORDS -->
    <?php
    $customers = $conn->prepare("SELECT * FROM mismatch ORDER BY id DESC");
    $customers->execute();

    $getCustomers = $customers->fetchAll(PDO::FETCH_OBJ);
    if ($getCustomers) {
        foreach ($getCustomers as $customer) {

            $cus_ticket = $customer->id;
            $cus_fname = $customer->First_Name;
            $cus_lname = $customer->Last_Name;
            $cus_inv = $customer->Invoice_Number;
            $cus_branch = $customer->Branch;

            echo '<div class="modal fade p-4" id="user_' . $cus_ticket . '">
              <div class="modal-dialog" role="document">
                  <div class="modal-content rounded-3 shadow">
                      <div class="modal-body p-4">
                        <div class="d-flex align-items-center">
                            <h5 class="text-center fw-bold text-secondary-emphasis text-center">Update User</h5>
                            <button class="btn btn-sm btn-close ms-auto" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="updateMismatch_' . $cus_ticket . '" class="mt-3 mb-0" method="post">
                            <input type="hidden" name="first" value="' . $cus_fname . '"/>
                            <input type="hidden" name="last" value="' . $cus_lname . '"/>
                            <input type="hidden" name="branch" value="' . $cus_branch . '"/>
                            <input type="hidden" name="user_id" value="' . $userId . '"/>
                            <input type="hidden" name="user_dept" value="' . $dept . '"/>
                            <input type="hidden" name="inv" value="' . $cus_inv . '"/>
                            <input type="hidden" name="username" value="' . $user_name . '"/>
                            <div class="form-floating">
                                <input type="text" class="form-control" name="fname" id="fname_' . $cus_ticket . '" placeholder="First Name" required autocomplete="off" value="' . $cus_fname . '">
                                <label for="fname_' . $cus_ticket . '" class="form-label">First Name</label>
                            </div>
                            <div class="form-floating mt-3">
                                <input type="text" class="form-control" name="lname" id="lname_' . $cus_ticket . '" placeholder="Last Name" required autocomplete="off" value="' . $cus_lname . '">
                                <label for="lname_' . $cus_ticket . '" class="form-label">Last Name</label>
                            </div>
                            <div class="flex nowrap p-0">
                                <button type="submit" class="btn d-block mx-auto btn-link fs-6 text-decoration-none m-0 mt-3 rounded-0" id="submit_' . $cus_ticket . '">
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
        <!-- Left Side Buttons -->
        <div class="position-absolute start-0 m-3 ps-2">
            <button class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#logout"><i class="bi bi-box-arrow-left"></i></button>
            <br>
        </div>
        <!-- Center Heading -->
        <h1 class="fw-bold text-center m-0"><?php echo $user_name ?></h1>
    </div>
    <div class="container-fluid mt-3 d-flex flex-column flex-lg-row justify-content-between mt-5 gap-3 align-items-start">
        <!-- CUSTOMER ENTRIES -->
        <div class="container bg-light rounded-1 p-3 col-12 col-lg-8 shadow-sm">
            <h1 class="fw-semibold">Findings</h1>
            <div class="table-responsive">
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        $entries = $conn->prepare("SELECT * FROM mismatch ORDER BY id DESC");
                        $entries->execute();

                        $getEntries = $entries->fetchAll(PDO::FETCH_OBJ);

                        if ($getEntries) {
                            foreach ($getEntries as $entry) {
                                $ticket = $entry->id;
                                $branch = $entry->Branch;
                                $first = $entry->First_Name;
                                $last = $entry->Last_Name;
                                $inv = $entry->Invoice_Number;
                                $mobile = $entry->Mobile_Number;
                                $email =  $entry->Email;

                                echo '<tr>
                                <td>' . $ticket . '</td>
                                <td>' . $branch . '</td>
                                <td>' . $first . '</td>
                                <td>' . $last . '</td>
                                <td>' . $inv . '</td>
                                <td>' . $mobile . '</td>
                                <td>' . $email . '</td>
                                <td>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" title="Rename" data-bs-target="#user_' . $ticket . '">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </td>
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
    <script>
        new DataTable('#mismatch');
    </script>
</body>

</html>