<?php include("config/config.php"); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Control Panel</title>
  <link rel="icon" href="assets/img/logo.jpg" />
  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <link rel="stylesheet" href="js/datatables/dataTables.css" />
  <link rel="stylesheet" href="css/vendor/bootstrap-icons.css" />
  <link rel="stylesheet" href="css/control.css" />
  <style>
    body {
      background:
        linear-gradient(rgba(180, 30, 30, 0.45), rgba(180, 30, 30, 0.45)),
        url("assets/img/newbg.jpg") no-repeat center center / cover;
    }

    #winnerTable,
    #winnerTable th,
    #winnerTable td {
      border: none !important;
    }

    /* Add spacing between rows */
    #winnerTable tbody tr {
      border-bottom: 20px solid transparent;
    }

    #winnerTable tbody tr {
      background-color: #f8f9fa;
      border-radius: 10px;
    }

    #winnerTable {
      border-collapse: separate;
      border-spacing: 0 3px;
    }

    #winnerTable {
      border-collapse: separate;
      border-spacing: 0 10px;
    }

    #winnerTable tbody td {
      background: #fff;
    }

    #winnerTable thead tr th:first-child {
      border-radius: 100px 0 0 100px;
    }

    #winnerTable thead tr th:last-child {
      border-radius: 0 100px 100px 0;
    }

    #winnerTable tbody td:first-child:last-child {
      border-radius: 100px;
    }

    .overall-progress::-webkit-scrollbar {
      width: 5px;
    }

    .overall-progress::-webkit-scrollbar-track {
      background: #cecece;
      border-radius: 50px;
    }

    .overall-progress::-webkit-scrollbar-thumb {
      background: #33a1f1;
      border-radius: 50px;
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

  <button id="logout-btn" style="height: 50px; width: 50px" class="btn border border-danger bg-danger-subtle btn-sm position-absolute rounded-5 top-0 start-0 m-3" title="Logout" data-bs-toggle="modal" data-bs-target="#logout">
    <i class="bi bi-box-arrow-left d-flex justify-content-center align-items-center text-danger"></i>
  </button>
  <div class="container" style="padding-top: 250px">
    <h2 class="text-center fw-bold text-light">CONTROL PANEL</h2>
  </div>
  <div class="container d-flex justify-content-center mb-5 w-auto p-4 bg-danger-subtle shadow rounded-3 gap-5 w-50">
    <div class="d-flex flex-column gap-1 justify-content-start align-items-start col-8 me-auto">
      <div class="d-flex align-items-center gap-3 col-12">
        <label for="location" class="form-label fw-semibold">Location: </label>
        <select name="region" id="region" class="form-select w-75 ms-auto" required>
          <option value="" selected>Select Region</option>
          <option value="LUZON">Luzon</option>
          <option value="VISAYAS">Visayas</option>
          <option value="MINDANAO">Mindanao</option>
        </select>
      </div>
      <div class="d-flex align-items-center gap-3 col-12">
        <label for="category" class="form-label fw-semibold">Prize Category: </label>
        <select name="category" id="category" class="form-select w-75 ms-auto" required>
          <option value="" selected>Select Category</option>
        </select>
      </div>
      <div class="d-flex align-items-center gap-3 col-12">
        <label for="prize" class="form-label fw-semibold">Prize: </label>
        <input type="text" class="form-control w-75 ms-auto" name="prize" id="prize" readonly>
      </div>
    </div>
    <div class="col">
      <h2 class="fw-bold text-secondary text-center">Maximum Winner</h2>
      <div class="d-flex justify-content-center align-items-center gap-1 d-none" id="counterContainer">
        <h2 class="fw-bold text-success display-2" id="currentPrize">0</h2>
        <div class="display-3">/</div>
        <h4 class="fw-semibold text-danger display-5" id="maxPrize">3</h4>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="d-flex justify-content-center align-items-center gap-3">
      <button id="start-btn" class="btn btn-danger px-5 fs-4 rounded-5">
        START
      </button>
    </div>
    <div class="mt-5">
      <h1 class="fw-bold text-light text-center">WINNING BOARD</h1>
      <div class="table-responsive">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
          <div>
            <button id="downloadCSV" class="btn btn-danger px-4 fs-5 rounded-4">
              Download CSV
            </button>
          </div>
          <div id="winnerTable_filter" class="dataTables_filter"></div>
        </div>
        <div class="table-responsive overall-progress p-3 text-light" style="max-height: 450px">
          <table
            class="table datatables table-hover mt-3 text-center table-light table-striped overall-progress"
            id="winnerTable">
            <thead class="border">
              <tr>
                <th>#</th>
                <th>E-Ticket</th>
                <th>CUSTOMER</th>
                <th>AREA</th>
                <th>CATEGORY</th>
                <th>PRIZE</th>
                <th>TIMESTAMP</th>
              </tr>
            </thead>
            <tbody id="winnerTableBody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="js/jquery-3.7.1.min.js"></script>
  <script src="js/datatables/dataTables.js"></script>
  <script src="js/bootstrap.bundle.min.js"></script>
  <script>
    const region = document.getElementById('region');
    const category = document.getElementById('category');
    const prize = document.getElementById('prize');
    const startBtn = document.getElementById('start-btn');
    let raffleRunning = false;
    let currentPrize = document.getElementById("currentPrize")
    let maxPrize = document.getElementById("maxPrize")
    let counterContainer = document.getElementById("counterContainer")
    let payloadCounter = 0;
    let successfulPayloads = 0;
    let failedPayloads = 0;

    async function fetchCategories() {
      const loc = region.value;
      category.innerHTML = '<option value="" selected>Select Category</option>';
      prize.value = '';
      checkInputs();

      if (loc || loc === '') {
        counterContainer.classList.remove("d-block")
        counterContainer.classList.add("d-none")
      }

      if (loc) {
        const res = await fetch(`api/get_categories.php?location=${encodeURIComponent(loc)}`, );
        const categories = await res.json();

        categories.forEach(cat => {
          const option = document.createElement('option');
          option.value = cat;
          option.textContent = cat;
          category.appendChild(option);
        });
      }
    }

    async function fetchPrize() {
      const loc = region.value;
      const cat = category.value;
      startBtn.disabled = true

      if (loc && cat) {
        const res = await fetch(`api/get_prize.php?location=${encodeURIComponent(loc)}&category=${encodeURIComponent(cat)}`);
        const data = await res.json();

        if (data && data.product) {
          counterContainer.classList.remove("d-none")
          counterContainer.classList.add("d-block")
          prize.value = `${data.product} - ${data.brand} (${data.model})`;
          maxPrize.textContent = `${data.maxWinner}`
          currentPrize.textContent = `${data.currentCounter}`

          const maxWinner = Number(maxPrize.textContent);
          const currentCounter = Number(currentPrize.textContent);

          if (currentCounter === maxWinner) {
            startBtn.disabled = true
            return
          } else {
            startBtn.disabled = false
          }
        }
      }
      checkInputs();
    }

    function checkInputs() {

      const isValid =
        region.value &&
        category.value &&
        prize.value.trim();

      startBtn.disabled = raffleRunning || !isValid;
    }

    region.addEventListener('change', fetchCategories);
    category.addEventListener('change', fetchPrize);
    prize.addEventListener('input', checkInputs);

    window.addEventListener('DOMContentLoaded', () => {
      startBtn.disabled = true;
    });

    const winnerTable = new DataTable("#winnerTable", {
      pageLength: 8,
      lengthChange: false,
      columnDefs: [{
          targets: "_all",
          className: "align-middle text-center"
        },
        {
          targets: 5,
          width: "300px",
          className: "text-start"
        }
      ],
      order: [
        [0, "desc"]
      ],
    });

    const winnerTableBody = document.getElementById("winnerTableBody");

    let lastWinnerId = 0;
    let winnerInterval = null;

    function loadWinners() {
      fetch("api/showWinners.php")
        .then((res) => res.json())
        .then((data) => {
          if (data.status === "success" && data.winners.length > 0) {
            const winners = data.winners;

            const newestId = parseInt(winners[0].id);
            if (newestId > lastWinnerId) {
              lastWinnerId = newestId;
              renderWinners(winners);
              raffleRunning = false;
              checkInputs();
              fetchPrize()

              if (winnerInterval) clearInterval(winnerInterval);
            }
          }
        })
        .catch(console.error);
    }

    // FORMAT TIME ONLY
    function formatTime(dateStr) {
      const date = new Date(dateStr);
      return date.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
      });
    }

    function renderWinners(winners) {
      winnerTable.clear();

      let counter = 1;

      winners.reverse();

      winners.forEach((winner) => {
        let categoryBg = "bg-primary";
        let categoryText = "text-light";

        if (winner.category === "Consolation") {
          categoryBg = "bg-info";
        } else if (winner.category === "Grand Prize") {
          categoryBg = "bg-warning";
          categoryText = "text-dark";
        }

        winnerTable.row.add([
          `<span class="d-inline-block rounded-5 bg-info-subtle text-primary-emphasis p-2 fw-bold border border-primary text-center" style="width:40px;max-width:40px;">${counter++}</span>`,
          `<span class="fw-bold text-primary text-center">${winner.winningTicket}</span>`,
          `<span class="fw-bold text-center">${winner.winner}</span>`,
          `<span class="text-center">${winner.area}</span>`,
          `<span class="badge ${categoryBg} ${categoryText}">${winner.category}</span>`,
          `<span class="badge bg-success">${winner.prize}</span>
          `,

          formatTime(winner.dateWon)
        ]);
      });

      winnerTable.draw(false);
    }

    fetch("api/showWinners.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "success" && data.winners.length > 0) {
          lastWinnerId = parseInt(data.winners[0].id);
          renderWinners(data.winners);
        }
      });

    startBtn.addEventListener("click", async () => {
      console.log("CLICK", new Date().toISOString());

      raffleRunning = true;
      checkInputs();

      const selectedRegion = region.value;
      const selectedCategory = category.value;
      const selectedPrize = prize.value;

      payloadCounter++

      const payload = {
        requestNo: payloadCounter,
        status: "rolling",
        region: selectedRegion,
        category: selectedCategory,
        prize: selectedPrize
      }
      console.log(`Sending payload #${payloadCounter}`, payload);

      try {
        const response = await fetch("sync.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify(payload),
        });

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        successfulPayloads++;

        console.log(
          `✅ Payload #${payload.requestNo} sent successfully`,
          `Success: ${successfulPayloads}`,
          `Failed: ${failedPayloads}`
        );

      } catch (err) {
        failedPayloads++;

        console.error(
          `❌ Payload #${payload.requestNo} failed`,
          err,
          `Success: ${successfulPayloads}`,
          `Failed: ${failedPayloads}`
        );
      }

      if (winnerInterval) clearInterval(winnerInterval);
      winnerInterval = setInterval(loadWinners, 2000);
    });

    // EXPORT CSV FILE FOR WINNERS
    document.getElementById("downloadCSV").addEventListener("click", function() {
      const table = document.getElementById("winnerTable");
      let csv = [];
      for (let row of table.rows) {
        let cols = Array.from(row.cells).map(cell =>
          `"${cell.innerText.replace(/"/g, '""')}"`);
        csv.push(cols.join(","));
      }

      const csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", encodedUri);
      link.setAttribute("download", "winners.csv");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    });
  </script>
</body>

</html>