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

    /* Firefox */
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
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content rounded-3 shadow">
        <div class="modal-body p-4">
          <div class="d-flex">
            <h5 class="mb-0 fw-semibold text-center">Add User</h5>
            <button class="ms-auto btn-close" data-bs-dismiss="modal"></button>
          </div>
          <hr>
          <form method="post" class="mt-3" id="formAddUser">
            <div class="mt-3 d-flex justify-content-between align-items-center gap-3">
              <div class="col">
                <label for="username">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control mt-2" name="username" maxlength="20" required>
              </div>
              <div class="col">
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
            </div>

            <div class="mt-3" id="region-container" style="display: none;">
              <p>Region(s):</p>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px;">
                <?php

                $usedRegions = [];

                $stmtUsers = $conn->prepare("SELECT region FROM users WHERE region IS NOT NULL");
                $stmtUsers->execute();
                $userRegions = $stmtUsers->fetchAll(PDO::FETCH_COLUMN);

                foreach ($userRegions as $userRegionStr) {
                  if (!empty($userRegionStr)) {
                    $regionsArray = explode(',', $userRegionStr);
                    foreach ($regionsArray as $reg) {
                      $usedRegions[] = trim($reg);
                    }
                  }
                }

                // Remove duplicates
                $usedRegions = array_unique($usedRegions);

                // Step 2: Get all available regions from `regions` table
                $regionLists = $conn->prepare("SELECT id, region FROM regions ORDER BY region");
                $regionLists->execute();
                $getRegions = $regionLists->fetchAll(PDO::FETCH_OBJ);

                if ($getRegions) {
                  foreach ($getRegions as $reg) {
                    $region_id = $reg->id;
                    $region = $reg->region;

                    // Check if region is already assigned
                    $isDisabled = in_array($region, $usedRegions) ? 'disabled' : '';

                    echo '<div>
                      <input type="checkbox" name="region[]" value="' . htmlspecialchars($region) . '" id="region_' . $region_id . '" ' . $isDisabled . ' />
                      <label for="region_' . $region_id . '">' . htmlspecialchars($region) . '</label>
                    </div>';
                  }
                }
                ?>
              </div>
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

  <!-- WINNERS -->
  <div
    class="modal p-4 py-md-5 fade"
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
    id="showWinners">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
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
                  <th>AREA</th>
                  <th>CATEGORY</th>
                  <th>PRIZE</th>
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
                      <td>' . $win->area . '</td>
                      <td>' . $win->category . '</td>
                      <td>' . $win->prize . '</td>
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

  <!-- ONLINE PASSWORDS -->
  <div
    class="modal p-4 py-md-5 fade"
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
    id="userLogs">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
      <div class="modal-content rounded-3 shadow">
        <div class="modal-body p-4">
          <div class="d-flex">
            <h5 class="mb-0 fw-semibold text-center">Security Logs</h5>
            <button class="ms-auto btn-close" data-bs-dismiss="modal"></button>
          </div>
          <hr>
          <div class="table-responsive">
            <table class="table datatables" id="logsTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>User</th>
                  <th>Department</th>
                  <th>Old Password</th>
                  <th>New Password</th>
                  <th style="width: 150px">Modified</th>
                </tr>
              </thead>
              <tbody>
                <?php

                $activities = $conn->prepare("SELECT id, username, dept, prev_pass, new_pass, modified FROM users WHERE dept = 'ONLINE' ORDER BY id");
                // $activities->bindParam(":id", $userId);
                $activities->execute();

                $getPasswords = $activities->fetchAll(PDO::FETCH_OBJ);

                if ($getPasswords) {
                  foreach ($getPasswords as $act) {

                    $prev = $act->prev_pass;
                    $new = $act->new_pass;

                    if (empty($prev) || empty($prev)) {
                      $prev = 'N/A';
                      $new = 'N/A';
                    }

                    echo '<tr>
                      <td>' . $act->id . '</td> 
                      <td>' . $act->username . '</td> 
                      <td>' . $act->dept . '</td>
                      <td>' . $prev . '</td>
                      <td>' . $new . '</td>
                      <td style="width: 150px">' . date('F, d, Y - h:i:s A', strtotime($act->modified)) . '</td>
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
            <h5 class="mb-0 fw-semibold text-center text-dark">USER ACCESS <i class="bi bi-question-circle-fill" data-bs-toggle="modal" data-bs-target="#userLogs" title="View Logs"></i></h5>
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



  <div class="position-relative py-5">
    <!-- Center Heading -->
    <h1 class="fw-bold text-center m-0 text-uppercase">Administrator</h1>
    <!-- BUTTONS -->
    <div class="container mt-3 d-flex justify-content-center align-items-center gap-3">
      <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#logout"><i class="bi bi-box-arrow-left"></i> <span class="d-none d-md-inline-block">Logout</span></button>
      <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#logs"><i class="bi bi-alarm-fill"></i> <span class="d-none d-md-inline-block">Logs</span></button>
      <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#addDept" title="Add Department"><i class="bi bi-people-fill"></i> <span class="d-none d-md-inline-block">Add Department</span></button>
      <button type="button" class="btn btn-sm btn-light text-dark" data-bs-toggle="modal" data-bs-target="#update" title="Update Password"><i class="bi bi-pen"></i> <span class="d-none d-md-inline-block">Account</span></button>
      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUser" title="Add User"><i class="bi bi-person-fill-add"></i> <span class="d-none d-md-inline-block">New User</span></button>
      <button type="button" class="btn btn-sm btn-info text-light" data-bs-toggle="modal" data-bs-target="#users"><i class="bi bi-person-gear"></i> <span class="d-none d-md-inline-block">Users</span></button>
      <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#showWinners"><i class="bi bi-trophy-fill"></i> <span class="d-none d-md-inline-block">Winners</span></button>
    </div>
    <!-- TABLES -->
    <div class="container-fluid d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-start mt-3 mt-md-5">
      <!-- CUSTOMER ENTRIES -->
      <div class="container bg-light rounded-1 p-3 col-12 col-lg-10 shadow-sm">
        <h1 class="fw-semibold">Customer Entries</h1>
        <div class="table-responsive">
          <table class="table datatables" id="customers">
            <thead>
              <tr>
                <th class="text-center">E-Ticket</th>
                <th class="text-center">Total</th>
                <th class="text-center">Branch</th>
                <th class="text-center">First Name</th>
                <th class="text-center">Last Name</th>
                <th class="text-center">Invoice Number</th>
                <th class="text-center">Mobile Number</th>
                <th class="text-center">Email</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php

              $entries = $conn->prepare("SELECT * FROM raffle_entries ORDER BY Ticket_Number ASC");
              $entries->execute();
              $getEntries = $entries->fetchAll(PDO::FETCH_OBJ);

              $groupedEntries = [];

              if ($getEntries) {
                foreach ($getEntries as $entry) {
                  $key = $entry->First_Name . '|' . $entry->Last_Name . '|' . $entry->Mobile_Number . '|' . $entry->Email . '|' . $entry->Branch . '|' . $entry->Invoice_Number;
                  $inv = $entry->Invoice_Number;
                  $is_excluded = $entry->Excluded;

                  if (!isset($groupedEntries[$key])) {
                    $groupedEntries[$key] = [
                      'first' => $entry->First_Name,
                      'last' => $entry->Last_Name,
                      'suffix' => $entry->Suffix,
                      'mobile' => $entry->Mobile_Number,
                      'email' => $entry->Email,
                      'branch' => $entry->Branch,
                      'invoice' => $entry->Invoice_Number,
                      'is_excluded' => $entry->Excluded,
                      'tickets' => [],
                      'total_tickets' => $entry->Counter
                    ];
                  }

                  $groupedEntries[$key]['tickets'][] = $entry->Ticket_Number;
                }

                foreach ($groupedEntries as $data) {
                  $tickets = $data['tickets'];
                  sort($tickets);

                  if (count($tickets) === 1) {
                    $ticketDisplay = $tickets[0];
                  } else {
                    $ticketDisplay = min($tickets) . ' - ' . max($tickets);
                  }

                  echo '<tr>
                          <td>' . $ticketDisplay . '</td>
                          <td>' . $data['total_tickets'] . '</td>
                          <td>' . $data['branch'] . '</td>
                          <td>' . $data['first'] . ' ' . $data['suffix'] . '</td>
                          <td>' . $data['last'] . '</td>
                          <td>' . $data['invoice'] . '</td>
                          <td>' . $data['mobile'] . '</td>
                          <td>' . $data['email'] . '</td>
                          <td>';
                  if ($data['is_excluded'] == 0) {
                    echo '<button class="btn btn-danger btn-sm exclude" title="Exclude entry" data-inv="' . $data['invoice'] . '" data-branch="' . $data['branch'] . '"><i class="bi bi-ban"></i></button>';
                  } else {
                    echo '<button class="btn btn-success btn-sm include" title="Include entry" data-inv="' . $data['invoice'] . '" data-branch="' . $data['branch'] . '"><i class="bi bi-recycle"></i></button>';
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
  </div>

  <script src="../js/sweetalert/sweetalert2.all.min.js"></script>
  <script src="../js/jquery-3.7.1.min.js"></script>
  <script src="../js/datatables/dataTables.js"></script>
  <script src="../js/bootstrap.bundle.min.js"></script>
  <script src="js/addDept.js"></script>
  <script src="js/addUser.js"></script>
  <script src="js/script.js"></script>
  <script src="js/control.js"></script>
  <script src="js/include.js"></script>
  <script src="js/exclude.js"></script>
  <script src="js/update.js"></script>
  <script>
    new DataTable('#employees');
    new DataTable('#customers');
    new DataTable('#logsTable');
    new DataTable('#winnerTable');
    new DataTable('#userAccessTable');


    const deptSelect = document.getElementById('dept');
    const regionContainer = document.getElementById('region-container');

    // Toggle regions based on department selection
    deptSelect.addEventListener('change', function() {
      if (deptSelect.value === 'ONLINE') {
        regionContainer.style.display = 'block'; // Show the regions
      } else {
        regionContainer.style.display = 'none'; // Hide the regions
      }
    });

    // Initially check if the department is "ONLINE" when the page loads
    if (deptSelect.value === 'ONLINE') {
      regionContainer.style.display = 'block';
    }
  </script>
</body>

</html>