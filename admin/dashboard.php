<?php include('../config/config.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Administrator</title>
  <link rel="icon" href="../assets/img/logo.png" />
  <link rel="stylesheet" href="../css/bootstrap.min.css" />
  <link rel="stylesheet" href="../css/vendor/bootstrap-icons.css" />
  <link rel="stylesheet" href="../js/datatables/dataTables.css" />
  <link rel="stylessheet" href="../css/sweetalert2.min.css" />
  <style>
    body {
      height: 100%;
      min-height: 100vh;
      font-family: sans-serif;
      background:
        linear-gradient(rgba(180, 30, 30, 0.45), rgba(180, 30, 30, 0.45)),
        url("../assets/img/newbg.jpg") no-repeat center center / cover;
    }

    * {
      scrollbar-width: thin;
      scrollbar-color: #3b82f6 #e0e7ff
    }

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

  <!-- WINNERS -->
  <div
    class="modal p-4 py-md-5 fade"
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
    id="showWinners">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
      <div class="modal-content rounded-3 shadow">
        <div class="modal-body p-4" style="max-height: 700px">
          <div class="d-flex">
            <h5 class="mb-0 fw-semibold text-center text-dark">WINNERS</h5>
            <button class="ms-auto btn-close" data-bs-dismiss="modal"></button>
          </div>
          <hr>
          <div class="table-responsive" style="max-height: 500px">
            <table class="table datatables" id="winnerTable">
              <thead>
                <tr>
                  <th class="text-start">E-TICKET</th>
                  <th>CUSTOMER</th>
                  <th class="text-center">CATEGORY</th>
                  <th class="text-center">PRIZE</th>
                  <th>TIMESTAMP</th>
                </tr>
              </thead>
              <tbody>
                <?php

                try {
                  $winners = $conn->prepare("EXEC DisplayWinners");
                  $winners->execute();

                  $getWinners = $winners->fetchAll(PDO::FETCH_OBJ);

                  if ($getWinners) {
                    foreach ($getWinners as $win) {

                      $catBg = '';
                      $category = $win->category;

                      if ($category === "Consolation") {
                        $catBg = "badge bg-info";
                      } else if ($category === "Grand Prize") {
                        $catBg = "badge bg-warning";
                      } else {
                        $catBg = "badge bg-primary";
                      }

                      echo '<tr>
                      <td class="text-primary text-center">' . $win->winningTicket . '</td> 
                      <td class="text-primary fw-bold">' . $win->winner . '</td> 
                      <td class="text-center"><span class="' . $catBg . '">' . $category . '</span></td>
                      <td class="text-center"><span class="badge bg-success">' . $win->prize . '</span></td>
                    <td>' . date('F. d, Y - h:i:s A', strtotime($win->dateWon)) . '</td>
                    </tr>';
                    }
                  }
                } catch (PDOException $e) {
                  errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
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
    <h1 class="fw-bold text-center m-0 text-uppercase text-light">Administrator</h1>
    <div class="container mt-3 d-flex justify-content-center align-items-center gap-3">
      <button type="button" class="btn btn-sm btn bg-danger-subtle border border-danger text-dark" data-bs-toggle="modal" data-bs-target="#logout"><i class="bi bi-box-arrow-left"></i> <span class="d-none d-md-inline-block">Logout</span></button>
      <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#showWinners"><i class="bi bi-trophy-fill"></i> <span class="d-none d-md-inline-block">Winners</span></button>
    </div>
    <!-- TABLES -->
    <div class="container-fluid d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-start mt-3 mt-md-5">
      <!-- CUSTOMER ENTRIES -->
      <div class="d-block mx-auto bg-light rounded-5 p-5 col-12 col-lg-10 shadow">
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
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php

              try {
                $entries = $conn->prepare("EXEC Get_Entries");
                $entries->execute();
                $getEntries = $entries->fetchAll(PDO::FETCH_OBJ);

                if ($getEntries) {
                  foreach ($getEntries as $entry) {

                    echo '<tr>
                    <td class="text-primary">' . $entry->Ticket_Number . '</td>
                    <td class="text-center"><span class="badge bg-primary">' . $entry->Branch . '</span></td>
                    <td class="text-center">' . $entry->First_Name . ' ' . $entry->Suffix . '</td>
                    <td class="text-center">' . $entry->Last_Name . '</td>
                    <td class="text-center text-success">' . $entry->Invoice . '</td>
                    <td class="text-center">' . $entry->Mobile_Number . '</td>
                    <td>';

                    if ($entry->Excluded == 0) {
                      echo '<button class="btn btn-danger btn-sm exclude" 
                            title="Exclude entry" 
                            data-branch="' . $entry->Branch . '">
                            <i class="bi bi-ban"></i>
                      </button>';
                    } else {
                      echo '<button class="btn btn-success btn-sm include" 
                            title="Include entry" 
                            data-branch="' . $entry->Branch . '">
                            <i class="bi bi-recycle"></i>
                      </button>';
                    }

                    echo '</td>
                  </tr>';
                  }
                }
              } catch (PDOException $e) {
                echo $e->getMessage();
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
  <script src="js/script.js"></script>
  <script src="js/control.js"></script>
  <script src="js/include.js"></script>
  <script src="js/exclude.js"></script>
  <script src="js/update.js"></script>
  <script src="js/proceed.js"></script>
  <!-- <script src="js/retrieve.js"></script> -->
  <script>
    new DataTable('#employees');
    new DataTable('#customers');
    new DataTable('#winnerTable');

    document.addEventListener("DOMContentLoaded", function() {
      const mismatchBtn = document.getElementById("mismatchBtn");
      const tableBody = document.querySelector("#mismatchTable tbody");

      if (!mismatchBtn || !tableBody) {
        return;
      }

      const rows = tableBody.querySelectorAll("tr");

      let hasData = false;

      rows.forEach(row => {

        const cells = row.querySelectorAll("td");

        if (
          cells.length > 1 &&
          !row.textContent.trim().includes("No matching records found")
        ) {
          hasData = true;
        }
      });

      mismatchBtn.disabled = !hasData;
    });

    document.getElementById("mismatchBtn").addEventListener("click", () => {

      const mismatchBtn = document.getElementById("mismatchBtn");
      mismatchBtn.disabled = true

      $.ajax({
        type: "GET",
        url: "functions/exportmismatch.php",
        xhrFields: {
          responseType: "blob"
        },
        success: function(data, status, xhr) {

          const blob = new Blob([data], {
            type: "text/csv"
          });

          const url = window.URL.createObjectURL(blob);

          const link = document.createElement("a");

          link.href = url;
          // link.download = "mismatch.csv";

          // document.body.appendChild(link);

          // link.click();

          // link.remove();

          // window.URL.revokeObjectURL(url);
          const disposition = xhr.getResponseHeader("Content-Disposition");
          let filename = "batch.csv";

          if (disposition && disposition.includes("filename=")) {
            filename = disposition
              .split("filename=")[1]
              .replace(/"/g, "");
          }

          link.download = filename;

          document.body.appendChild(link);
          link.click();

          link.remove();
          window.URL.revokeObjectURL(url);

          Swal.fire({
            icon: "success",
            title: "Exported successfully",
            confirmButtonText: "OK"
          }).then(() => {
            window.location.href = "dashboard.php"
          })
        },
        error: function(xhr) {

          console.error(xhr.responseText);

          mismatchBtn.disabled = false;
        }
      });
    });

    // MIGRATE RECYCLE DATA
    document.getElementById("appendTicketsBtn").addEventListener("click", () => {

      const recycleBtn = document.getElementById("appendTicketsBtn");
      recycleBtn.disabled = true

      $.ajax({
        type: "GET",
        url: "functions/appendRaffleTickets.php",
        dataType: "json",
        success: function(response) {
          if (response.isSuccess == "success") {
            Swal.fire({
              icon: "success",
              title: "Migration completed",
              confirmButtonText: "OK"
            }).then(() => {
              location.reload();
            })
          } else {
            Swal.fire({
              icon: "error",
              title: "Migration failed",
              text: response.message || "No success response received."
            });

            recycleBtn.disabled = false;
          }
        },
        error: function(xhr) {
          console.error("AJAX Error:", status, error);
          console.error("Response:", xhr.responseText);

          Swal.fire({
            icon: "error",
            title: "Migration failed",
            text: "An error occurred while migrating the recycle data."
          });

          recycleBtn.disabled = false;
        }
      });
    });
  </script>
</body>

</html>