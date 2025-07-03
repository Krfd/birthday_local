<?php include('../config/config.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Administrator</title>
  <link rel="icon" href="../assets/img/logo.jpg" />
  <link rel="stylesheet" href="../css/bootstrap.min.css" />
  <link rel="stylesheet" href="../css/vendor/bootstrap-icons.css" />
  <link rel="stylesheet" href="../js/datatables/dataTables.css" />
  <link rel="stylessheet" href="../css/sweetalert2.min.css" />
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
            <a href="../config/config.php?logout=true" class="text-decoration-none"><strong>Logout</strong></a>
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

  <!-- ADD A DEPARTMENT -->
  <div
    class="modal p-4 py-md-5 fade"
    tabindex="-1"
    role="dialog"
    id="addDept">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content rounded-3 shadow">
        <div class="modal-body p-4">
          <div class="d-flex">
            <h5 class="mb-0 fw-semibold text-center">Add Department</h5>
            <button class="ms-auto btn-close" data-bs-dismiss="modal"></button>
          </div>
          <hr>
          <form method="post" class="mt-3" id="formAddDept">
            <div class="mt-3">
              <label for="dept">Department <span class="text-danger">*</span></label>
              <input type="text" class="form-control mt-2" name="department" maxlength="20" required>
            </div>
            <button
              type="submit"
              class="btn btn-lg btn-link fs-6 text-decoration-none col-6 pt-3 m-0 rounded-0 d-block mx-auto">
              <strong>Add</strong>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- ADD USER -->
  <div
    class="modal p-4 py-md-5 fade"
    tabindex="-1"
    role="dialog"
    id="addUser">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content rounded-3 shadow">
        <div class="modal-body p-4">
          <div class="d-flex">
            <h5 class="mb-0 fw-semibold text-center">Add User</h5>
            <button class="ms-auto btn-close" data-bs-dismiss="modal"></button>
          </div>
          <hr>
          <form method="post" class="mt-3" id="formAddUser">
            <div>
              <label for="username">Username <span class="text-danger">*</span></label>
              <input type="text" class="form-control mt-2" name="username" maxlength="20" required>
            </div>
            <div class="mt-3">
              <label for="dept">Department <span class="text-danger">*</span></label>
              <select name="dept" id="dept" class="form-select mt-2" required>
                <?php

                $depts = $conn->prepare("SELECT department FROM departments ORDER BY department");
                $depts->execute();

                $getDepts = $depts->fetchAll(PDO::FETCH_OBJ);

                if ($getDepts) {
                  foreach ($getDepts as $department) {
                    echo '<option>' . $department->department . '</option>';
                  }
                } else {
                  echo '<option value="" selected>No Department</option>';
                }

                ?>
              </select>
            </div>
            <button
              type="submit"
              class="btn btn-lg btn-link fs-6 text-decoration-none col-6 pt-3 m-0 rounded-0 d-block mx-auto">
              <strong>Add</strong>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- LOGS -->
  <div
    class="modal p-4 py-md-5 fade"
    tabindex="-1"
    role="dialog"
    id="logs">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
      <div class="modal-content rounded-3 shadow">
        <div class="modal-body p-4">
          <div class="d-flex">
            <h5 class="mb-0 fw-semibold text-center">Activity Logs</h5>
            <button class="ms-auto btn-close" data-bs-dismiss="modal"></button>
          </div>
          <hr>
          <div class="table-responsive">
            <table class="table datatables" id="logsTable">
              <thead>
                <tr>
                  <th>USER ID</th>
                  <th>INVOICE #</th>
                  <th>CUSTOMER NAME</th>
                  <th>AUTHOR</th>
                  <th>DEPARTMENT</th>
                  <th style="width: 150px">MODIFIED</th>
                </tr>
              </thead>
              <tbody>
                <?php

                $logs = $conn->prepare("SELECT * FROM logs ORDER BY id");
                $logs->execute();

                $getLogs = $logs->fetchAll(PDO::FETCH_OBJ);

                if ($getLogs) {
                  foreach ($getLogs as $log) {

                    echo '<tr>
                      <td>' . $log->user_id . '</td> 
                      <td>' . $log->customer_inv . '</td> 
                      <td>' . $log->customer_name . '</td>
                      <td>' . $log->username . '</td>
                      <td>' . $log->dept . '</td>
                      <td style="width: 150px">' . date('F, d, Y - h:i:s', strtotime($log->modified)) . '</td>
                    </tr>';
                  }
                }

                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- WINNERS -->
  <div
    class="modal p-4 py-md-5 fade"
    tabindex="-1"
    role="dialog"
    id="showWinners">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content rounded-3 shadow">
        <div class="modal-body p-4">
          <div class="d-flex">
            <h5 class="mb-0 fw-semibold text-center text-success"><i class="bi bi-trophy-fill"></i> WINNERS</h5>
            <button class="ms-auto btn-close" data-bs-dismiss="modal"></button>
          </div>
          <hr>
          <div class="table-responsive">
            <table class="table datatables" id="winnerTable">
              <thead>
                <tr>
                  <th>E-TICKET</th>
                  <th>CUSTOMER</th>
                  <th>TIMESTAMP</th>
                </tr>
              </thead>
              <tbody>
                <?php

                $winners = $conn->prepare("SELECT * FROM winners ORDER BY id DESC");
                $winners->execute();

                $getWinners = $winners->fetchAll(PDO::FETCH_OBJ);

                if ($getWinners) {
                  foreach ($getWinners as $win) {

                    echo '<tr>
                      <td>' . $win->winningTicket . '</td> 
                      <td>' . $win->winner . '</td> 
                    <td>' . date('F. d, Y - h:i:s A', strtotime($win->dateWon)) . '</td>
                    </tr>';
                  }
                }

                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="position-relative py-3 mt-3">
    <!-- Left Side Buttons -->
    <div class="position-absolute start-0 m-3 ps-2">
      <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#logout"><i class="bi bi-box-arrow-left"></i> Logout</button>
      <br>
      <button type="button" class="btn btn-sm btn-secondary mt-2" data-bs-toggle="modal" data-bs-target="#logs"><i class="bi bi-alarm-fill"></i> Logs</button>
      <br>
      <button type="button" class="btn btn-sm btn-dark mt-2" data-bs-toggle="modal" data-bs-target="#addDept"><i class="bi bi-plus"></i> Add Department</button>
      <br>
      <button type="button" class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addUser"><i class="bi bi-plus"></i> User</button>
      <br>
      <button type="button" class="btn btn-success btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#showWinners"><i class="bi bi-trophy-fill"></i> Winners</button>
    </div>
    <!-- Center Heading -->
    <h1 class="fw-bold text-center m-0">Administrator</h1>
  </div>
  <div class="container-fluid mt-3 d-flex flex-column flex-lg-row justify-content-between mt-5 gap-3 align-items-start">
    <!-- CUSTOMER ENTRIES -->
    <div class="container bg-light rounded-1 p-3 col-12 col-lg-8 shadow-sm">
      <h1 class="fw-semibold">Customer Entries</h1>
      <div class="table-responsive">
        <table class="table datatables" id="customers">
          <thead>
            <tr>
              <th class="text-center">E-Ticket</th>
              <th class="text-center">Branch</th>
              <th class="text-center">First Name</th>
              <th class="text-center">Last Name</th>
              <th class="text-center">Invoice Number</th>
              <th class="text-center">Mobile Number</th>
              <th class="text-center">Email</th>
            </tr>
          </thead>
          <tbody>
            <?php

            $entries = $conn->prepare("SELECT * FROM raffle_entries ORDER BY Ticket_Number DESC");
            $entries->execute();

            $getEntries = $entries->fetchAll(PDO::FETCH_OBJ);

            if ($getEntries) {
              foreach ($getEntries as $entry) {
                $ticket = $entry->Ticket_Number;
                $branch = $entry->Branch;
                $first = $entry->First_Name;
                $last = $entry->Last_Name;
                $inv = $entry->Invoice_Number;
                $mobile = $entry->Mobile_Number;
                $email =  $entry->Email;

                echo '<tr>
                <td class="text-center">' . $ticket . '</td>
                <td class="text-center">' . $branch . '</td>
                <td class="text-center">' . $first . '</td>
                <td class="text-center">' . $last . '</td>
                <td class="text-center">' . $inv . '</td>
                <td class="text-center">' . $mobile . '</td>
                <td class="text-center">' . $email . '</td>
                </tr>';
              }
            }

            ?>
          </tbody>
        </table>
      </div>
    </div>
    <!-- EMPLOYEE ACCESS -->
    <!-- <div class="container bg-light rounded-1 p-3 col-12 col-lg-4 shadow-sm">
      <div class="d-flex justify-content-between align-items-center">
        <h1 class="fw-semibold">Employee's Access</h1>
        <div class="d-flex gap-2 justify-content-end">
          <form method="post" action="clear_table.php" onsubmit="return confirm('Are you sure you want to clear the table?')">
            <button type="submit" name="clear_table" class="btn btn-sm btn-dark">Clear Table</button>
          </form>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table datatables" id="employees">
          <thead>
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Department</th>
            </tr>
          </thead>
          <tbody> -->

    <?php

    // $users = $conn->prepare("SELECT id, username, dept FROM users WHERE username != :username ORDER BY id");
    // $users->bindParam(":username", $user_name);
    // $users->execute();

    // $getUsers = $users->fetchAll(PDO::FETCH_OBJ);

    // if ($getUsers) {
    //   foreach ($getUsers as $user) {
    //     $user_id = $user->id;
    //     $username = $user->username;
    //     $dept = $user->dept;

    //     if (empty($dept) || $dept === "") {
    //       $dept = "Administrator";
    //     }

    //     echo '<tr>
    //     <td>' . $user_id . '</td>
    //     <td>' . $username . '</td>
    //     <td>' . $dept . '</td>
    //   </tr>';
    //   }
    // }

    ?>
    <!-- </tbody>
    </table>
  </div>
  </div> -->
  </div>

  <script src="../js/sweetalert/sweetalert2.all.min.js"></script>
  <script src="../js/jquery-3.7.1.min.js"></script>
  <script src="../js/datatables/dataTables.js"></script>
  <script src="../js/bootstrap.bundle.min.js"></script>
  <script src="js/addDept.js"></script>
  <script src="js/addUser.js"></script>
  <script src="js/script.js"></script>
  <script>
    new DataTable('#employees');
    new DataTable('#customers');
    new DataTable('#logsTable');
  </script>
</body>

</html>