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

            $id = $customer->id;
            $docId = $customer->docId;
            $cus_fname = $customer->First_Name;
            $cus_lname = $customer->Last_Name;
            $cus_inv = $customer->Invoice_Number;
            $cus_branch = $customer->Branch;

            echo '<div class="modal fade p-4" id="user_' . $id . '">
              <div class="modal-dialog" role="document">
                  <div class="modal-content rounded-3 shadow">
                      <div class="modal-body p-4">
                        <div class="d-flex align-items-center">
                            <h5 class="text-center fw-bold text-secondary-emphasis text-center">Update User</h5>
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
                            <input type="hidden" name="username" value="' . $user_name . '"/>
                            <div>
                                <label for="branch_' . $id . '" class="form-label">Branch <span class="text-danger">*</span></label>
                                <select class="form-select" id="branch_' . $id . '" name="new_branch" required autocomplete="off">
                                ';

            $branches = $conn->prepare("SELECT branch FROM branches ORDER BY branch");
            $branches->execute();

            $getBranches = $branches->fetchAll(PDO::FETCH_OBJ);

            if ($getBranches) {
                foreach ($getBranches as $branchName) {
                    $branch = $branchName->branch;

                    if ($cus_branch === $branch) {
                        echo '<option selected value="' . $branch . '">' . $branch . '</option>';
                    } else {
                        echo '<option value="' . $branch . '">' . $branch . '</option>';
                    }
                }
            }

            echo '</select>
                            </div>
                            <div class="mt-3">
                                <label for="fname_' . $id . '" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="fname" id="fname_' . $id . '" required autocomplete="off" value="' . $cus_fname . '">
                            </div>
                            <div class="mt-3">
                                <label for="lname_' . $id . '" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="lname" id="lname_' . $id . '" required autocomplete="off" value="' . $cus_lname . '">
                            </div>
                            <div class="flex nowrap p-0">
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
        <!-- Left Side Buttons -->
        <div class="position-absolute start-0 m-3 ps-2">
            <button class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#logout"><i class="bi bi-box-arrow-left"></i></button>
            <br>
        </div>
        <!-- Center Heading -->
        <h1 class="fw-bold text-center m-0"><?php echo $user_name ?></h1>
    </div>
    <div class="container-fluid mt-3 d-flex flex-column flex-lg-row justify-content-between mt-3 mt-md-5 gap-3 align-items-start">
        <!-- CUSTOMER ENTRIES -->
        <div class="container bg-light rounded-1 p-3 col-12 col-lg-8 shadow-sm mb-5" style="height: 70vh; max-height: 70vh;">
            <h1 class="fw-semibold">Findings</h1>
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

                        $entries = $conn->prepare("SELECT * FROM mismatch m WHERE NOT EXISTS (SELECT 1 FROm raffle_entries r 
                        WHERE r.Branch = m.Branch 
                        AND r.First_Name = m.First_Name 
                        AND r.Last_Name = m.Last_Name 
                        AND r.Invoice_Number = m.Invoice_Number 
                        AND r.Mobile_Number = m.Mobile_Number 
                        AND r.Email = m.Email) ORDER BY id DESC");
                        $entries->execute();

                        $getEntries = $entries->fetchAll(PDO::FETCH_OBJ);

                        $excluded_area_codes = ['136', '137', '995', '998', '999'];

                        if ($getEntries) {
                            foreach ($getEntries as $entry) {
                                $ticket = $entry->id;
                                $branch = $entry->Branch;
                                $first = $entry->First_Name;
                                $last = $entry->Last_Name;
                                $inv = $entry->Invoice_Number;
                                $mobile = $entry->Mobile_Number;
                                $email =  $entry->Email;
                                $remarks =  $entry->Findings;

                                echo '<tr>
                                <td>' . $ticket . '</td>
                                <td>' . $branch . '</td>
                                <td>' . $first . '</td>
                                <td>' . $last . '</td>
                                <td>' . $inv . '</td>
                                <td>' . $mobile . '</td>
                                <td>' . $email . '</td>
                                <td>' . $remarks . '</td>
                                <td>';
                                $area_code = $entry->Area_Code ?? '';
                                $disable_action = in_array($area_code, $excluded_area_codes);

                                echo '<button class="btn btn-primary btn-sm" data-bs-toggle="modal" title="Rename" data-bs-target="#user_' . $ticket . '" ' . ($disable_action ? 'disabled' : '') . '>
                                        <i class="bi bi-pencil-square"></i>
                                    </button>';

                                echo '
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