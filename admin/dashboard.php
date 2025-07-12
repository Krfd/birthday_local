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
    aria-hidden="true"
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
    aria-hidden="true"
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
    aria-hidden="true"
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
    aria-hidden="true"
    id="logs">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
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
                  <th>BRANCH</th>
                  <th>CUSTOMER</th>
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
                      <td>' . $log->customer_branch . '</td>
                      <td>' . $log->new_customer_name . '</td>
                      <td>' . $log->username . '</td>
                      <td>' . $log->dept . '</td>
                      <td style="width: 150px">' . date('F, d, Y - h:i:s A', strtotime($log->modified)) . '</td>
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

  <!-- SETUP -->
  <div
    class="modal p-4 py-md-5 fade"
    tabindex="-1"
    role="dialog"
    id="setup">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content rounded-3 shadow">
        <div class="modal-body p-4">
          <div class="d-flex">
            <h5 class="mb-0 fw-semibold text-center">SETUP DATE COVERAGE</h5>
            <button class="ms-auto btn-close" data-bs-dismiss="modal"></button>
          </div>
          <hr>
          <form method="post" class="mt-3" id="setupDate">
            <div class="d-flex justify-content-between">
              <div class="d-flex gap-1 align-items-center">
                <label for="from">From: </label>
                <input type="date" class="form-control" name="from" id="from" required>
              </div>
              <div class="d-flex gap-1 align-items-center">
                <label for="to">To: </label>
                <input type="date" class="form-control" name="to" id="to" required>
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

  <!-- WINNERS -->
  <div
    class="modal p-4 py-md-5 fade"
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
    id="showWinners">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content rounded-3 shadow">
        <div class="modal-body p-4">
          <div class="d-flex">
            <h5 class="mb-0 fw-semibold text-center text-dark">WINNERS</h5>
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

  <!-- CONTROLLER ACCESS -->
  <div class="modal p-4 py-md-5 fade" tabindex="-1"
    role="dialog"
    aria-hidden="true"
    id="users">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
      <div class="modal-content rounded-3 shadow">
        <div class="modal-body p-4">
          <div class="d-flex">
            <h5 class="mb-0 fw-semibold text-center text-dark">USER ACCESS</h5>
            <button class="ms-auto btn-close" data-bs-dismiss="modal"></button>
          </div>
          <hr>
          <div class="table-responsive">
            <table class="table datataables" id="userAccessTable">
              <thead>
                <tr>
                  <th>USER</th>
                  <th>DEPARTMENT</th>
                  <th>STATUS</th>
                  <th>ACTION</th>
                </tr>
              </thead>
              <tbody>
                <?php

                $controllers = $conn->prepare("SELECT id, username, dept, disabled FROM users WHERE dept != ''");
                $controllers->execute();

                $getController = $controllers->fetchAll(PDO::FETCH_OBJ);

                if ($getController) {
                  foreach ($getController as $access) {
                    $access_id = $access->id;
                    $access_user = $access->username;
                    $access_dept = $access->dept;
                    $isDisabled = $access->disabled;

                    echo '<tr>
                      <td>' . $access_user . '</td>
                      <td>' . $access_dept . '</td>
                      <td>';

                    if ($isDisabled == 1) {
                      echo '<span class="badge bg-danger-subtle">
                          <span class="bg-danger rounded-circle d-inline-block me-1" style="width: 10px; height: 10px;"></span>
                          Inactive
                        </span>';
                    } else {
                      echo '<span class="badge bg-primary-subtle">
                          <span class="bg-success rounded-circle d-inline-block me-1" style="width: 10px; height: 10px;"></span>
                          Active
                        </span>';
                    }

                    echo '
                      </td>
                      <td>';



                    if ($isDisabled == 1) {
                      echo '<button class="btn btn-sm btn-success enable" data-id="' . $access_id . '" title="Enable">Enable</button>'; // <span class="bi bi-check-circle"></span>
                    } else {
                      echo '<button class="btn btn-sm btn-danger disable" data-id="' . $access_id . '" title="Disable">Disable</button>'; // <span class="bi bi-ban"></span>
                    }

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
    </div>
  </div>

  <div class="position-relative py-5">
    <!-- Center Heading -->
    <h1 class="fw-bold text-center m-0 text-uppercase">Administrator</h1>
    <!-- BUTTONS -->
    <div class="container mt-3 d-flex justify-content-center align-items-center gap-3">
      <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#logout"><i class="bi bi-box-arrow-left"></i> <span class="d-none d-md-inline-block">Logout</span></button>
      <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#logs"><i class="bi bi-alarm-fill"></i> <span class="d-none d-md-inline-block">Logs</span></button>
      <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#addDept" title="Add Department"><i class="bi bi-people-fill"></i> <span class="d-none d-md-inline-block">Add Department</span></button>
      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUser" title="Add User"><i class="bi bi-person-fill-add"></i> <span class="d-none d-md-inline-block">User</span></button>
      <button type="button" class="btn btn-sm btn-light text-dark" data-bs-toggle="modal" data-bs-target="#setup"><i class="bi bi-gear-fill"></i> <span class="d-none d-md-inline-block">Setup</span></button>
      <button type="button" class="btn btn-sm btn-info text-light" data-bs-toggle="modal" data-bs-target="#users"><i class="bi bi-person-gear"></i> <span class="d-none d-md-inline-block">Access</span></button>
      <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#showWinners"><i class="bi bi-trophy-fill"></i> <span class="d-none d-md-inline-block">Winners</span></button>
    </div>
    <div class="container-fluid d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-start mt-3 mt-md-5">
      <!-- CUSTOMER ENTRIES -->
      <div class="container bg-light rounded-1 p-3 col-12 col-lg-10 shadow-sm">
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
    </div>
  </div>

  <script src="../js/sweetalert/sweetalert2.all.min.js"></script>
  <script src="../js/jquery-3.7.1.min.js"></script>
  <script src="../js/datatables/dataTables.js"></script>
  <script src="../js/bootstrap.bundle.min.js"></script>
  <script src="js/addDept.js"></script>
  <script src="js/addUser.js"></script>
  <script src="js/script.js"></script>
  <script src="js/setup.js"></script>
  <script src="js/control.js"></script>
  <script>
    new DataTable('#employees');
    new DataTable('#customers');
    new DataTable('#logsTable');
    new DataTable('#winnerTable');
    new DataTable('#userAccessTable');
  </script>
</body>

</html>