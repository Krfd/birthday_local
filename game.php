<?php include("config/config.php"); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />

  <title>The Greatest Anniversary Appliance giveaway</title>
  <link rel="icon" href="assets/img/logo.jpg" />
  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      background: url("assets/img/background.jpg") no-repeat center center / cover;
      text-align: center;
      justify-content: center;
      align-items: center;
      height: 100vh;
      padding: 2rem;
      min-height: 100vh;
    }

    @font-face {
      font-family: 'Martellas';
      src: url('./fonts/martellas.ttf') format('truetype');
      font-weight: normal;
      font-style: normal;
    }

    #winner-display {
      margin-top: 50px;
      font-size: 4em;
      min-height: 50px;
      color: #ffff;
    }

    #invoice-display {
      font-size: 4em;
      color: #333;
    }

    .digit.roll {
      animation: rollDigit 0.3s ease-in-out;
    }

    .digit.rolling {
      animation: rollBounce 0.2s ease-out;
      border: 5px solid #4f2b0e !important;
    }

    .digit:hover {
      transform: scale(1.05);
      cursor: pointer;
    }

    @keyframes rollBounce {
      0% {
        transform: scale(1) translateY(0);
        opacity: 1;
      }

      25% {
        transform: scale(1.1) translateY(-10%);
        opacity: 0.8;
      }

      50% {
        transform: scale(1) translateY(5%);
        opacity: 1;
      }

      100% {
        transform: scale(1) translateY(0);
      }
    }

    .digit.locked {
      animation: bounceIn 0.4s ease;
      border: 5px solid #ffce00 !important;
    }

    @keyframes bounceIn {
      0% {
        transform: translateY(-100%) scale(1.2);
        opacity: 0;
      }

      60% {
        transform: translateY(10%) scale(1.1);
        opacity: 1;
      }

      80% {
        transform: translateY(-5%) scale(1.05);
      }

      100% {
        transform: translateY(0) scale(1);
      }
    }

    .digit {
      width: 80px;
      height: 100px;
      background: #f0f0f0;
      border-radius: 20px;
      box-shadow: 6px 6px 12px rgba(0, 0, 0, 0.1),
        -6px -6px 12px rgba(255, 255, 255, 0.6);
      display: inline-flex;
      justify-content: center;
      align-items: center;
      font-size: 4rem;
      font-weight: bold;
      text-align: center;
      transition: all 0.2s ease;
      border: 5px solid #4f2b0e !important;

      /* NEW: these lines fix the animation alignment */
      position: relative;
      overflow: hidden;
      line-height: 100px;
      /* match height to center text vertically */
    }


    .digit:active {
      box-shadow: inset 5px 5px 10px #bebebe, inset -5px -5px 10px #ffffff;
    }

    .font {
      position: relative;
      color: #ffce00;
      font-family: 'Martellas', sans-serif;
      letter-spacing: 10px;
      display: inline-block;
      text-align: center;
      width: 100%;
    }

    .winnerFont {
      position: relative;
      color: #fff !important;
      font-family: 'Martellas', sans-serif;
      letter-spacing: 10px;
      display: inline-block;
      text-align: center;
      width: 100%;
    }

    .font::before {
      content: attr(data-text);
      position: absolute;
      top: 0;
      left: 50%;
      transform: translateX(-50%);
      color: transparent;
      -webkit-text-stroke: 20px #4f2b0e;
      text-stroke: 20px #4f2b0e;
      z-index: -1;
      pointer-events: none;
      white-space: nowrap;
    }

    .winnerFont::before {
      content: attr(data-text);
      position: absolute;
      top: 0;
      left: 50%;
      transform: translateX(-50%);
      color: transparent;
      -webkit-text-stroke: 20px #4f2b0e;
      text-stroke: 20px #4f2b0e;
      z-index: -1;
      pointer-events: none;
      white-space: nowrap;
    }
  </style>
</head>

<body>
  <div class="container mt-5 pt-3">
    <div class="mt-5 pt-5">
      <div id="invoice-display" class="mt-5 pt-5">
        <h2 class="pt-5" style="font-size: clamp(1.5rem, 5vw, 5rem); text-align: center;">
          <span class="fw-bold font" data-text="E-TICKET NUMBER">E-TICKET NUMBER</span>
        </h2>
        <span class="digit border rounded-3 shadow-sm px-3" style="color: #4f2b0e">?</span>
        <span class="digit border rounded-3 shadow-sm px-3" style="color: #4f2b0e">?</span>
        <span class="digit border rounded-3 shadow-sm px-3" style="color: #4f2b0e">?</span>
        <span class="digit border rounded-3 shadow-sm px-3" style="color: #4f2b0e">?</span>
        <span class="digit border rounded-3 shadow-sm px-3" style="color: #4f2b0e">?</span>
        <span class="digit border rounded-3 shadow-sm px-3" style="color: #4f2b0e">?</span>
      </div>
    </div>
    <div class="mt-5">
      <h2 style="font-size: clamp(1.5rem, 5vw, 5rem); text-align: center;">
        <span class="fw-bold font" data-text="WINNER">WINNER</span>
      </h2>
      <div id="winner-display" class="font" aria-live="assertive">
        <!-- <span id="winner-label"></span> -->
        <span id="winner-name"></span>
      </div>
    </div>
  </div>
  <script src="js/bootstrap.bundle.min.js"></script>
  <script src="js/canvas-confetti.js"></script>
  <!-- <script type="module" src="js/start.js"></script> -->

  <script>
    let invoices = [];
    let names = [];
    let excludedTicketNumbers = new Set();
    let excludedCustomerNames = new Set();
    let isRolling = false;
    let finalInvoice = "";
    let users = [];
    let hasStarted = false;
    let currentRegion = "";
    let currentCategory = "";
    let currentPrize = "";

    let lastStatus = null;

    let pollingInterval = setInterval(() => {
      fetch("./sync.php")
        .then((res) => res.json())
        .then((state) => {
          if (!state || typeof state !== "object") {
            console.warn("⚠️ Invalid state from sync.php:", state);
            return;
          }

          currentRegion = state.region?.trim() || "";
          currentCategory = state.category?.trim() || "";
          currentPrize = state.prize?.trim() || "";
          finalInvoice = state.finalInvoice?.trim() || "";

          if (lastStatus !== state.status) {
            lastStatus = state.status;

            if (state.status === "rolling") {
              const reloadKey = "raffle_rolling_reloaded";
              if (!sessionStorage.getItem(reloadKey)) {
                sessionStorage.setItem(reloadKey, "true");
                location.reload();
              }
            } else {
              sessionStorage.removeItem("raffle_rolling_reloaded");
            }
          }
        })
        .catch((err) => console.error("Polling error:", err));
    }, 2000);

    fetch("api/getEntries.php")
      .then((res) => res.json())
      .then((data) => {
        // ✅ Rehydrate excluded customer names
        if (data.excludedCustomerNames) {
          excludedCustomerNames = new Set(data.excludedCustomerNames);
        }
        users = data
          .filter((u) => !excludedCustomerNames.has(u.customerName))
          .map((u) => ({
            customerName: u.customerName,
            ticketNumbers: u.ticketNumbers,
          }));

        invoices = users.flatMap((user) => user.ticketNumbers.map(String));
        names = users.map((user) => user.customerName);

        return fetch("sync.php");
      })
      .then((res) => res.json())
      .then((state) => {

        currentRegion = state.region || "";
        currentCategory = state.category || "";
        currentPrize = state.prize || "";

        if (state.excludedTicketNumbers) {
          for (const ticket of state.excludedTicketNumbers) {
            excludedTicketNumbers.add(ticket);
          }
        }

        if (state.status === "rolling" && !hasStarted && !isRolling) {
          finalInvoice = state.finalInvoice;
          hasStarted = true;
          startRaffle();
        }
      });

    function startRaffle() {
      const reloadKey = `raffle_refreshed_for_${finalInvoice}`;
      const alreadyReloaded = sessionStorage.getItem(reloadKey);

      if (!alreadyReloaded) {
        sessionStorage.setItem(reloadKey, "yes");

        console.log("🔁 Refreshing before raffle starts...");
        location.reload();
        return; // STOP raffle, reload happens
      }

      if (isRolling || invoices.length === 0) return;

      isRolling = true;

      document.getElementById("winner-display").textContent = "";
      finalInvoice = "";

      const availableInvoices = invoices.filter((ticket) => {
        const user = users.find((u) =>
          u.ticketNumbers.map(String).includes(ticket),
        );
        return (
          !excludedTicketNumbers.has(ticket) &&
          user &&
          !excludedCustomerNames.has(user.customerName)
        );
      });

      if (availableInvoices.length === 0) {
        document.getElementById("winner-display").innerHTML =
          `<strong data-text="Unknown" class="winnerFont">Unknown</strong>`;
        isRolling = false;
        hasStarted = false;
        finalInvoice = "";

        console.log(`DONE: `)
        console.log({
          status,
          currentRegion,
          currentCategory,
          currentPrize
        });
        console.log(``)

        fetch("./sync.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            status: "done",
            finalInvoice,
            excludedTicketNumbers: Array.from(excludedTicketNumbers),
            excludedCustomerNames: Array.from(excludedCustomerNames),
            region: currentRegion,
            category: currentCategory,
            prize: currentPrize,
          }),
        });

        return;
      }

      const digits = document.querySelectorAll("#invoice-display .digit");
      digits.forEach((digitEl) => {
        digitEl.textContent = "?";
        digitEl.classList.remove("rolling", "locked");
        digitEl.style.animation = "none";
        digitEl.offsetHeight;
        digitEl.style.animation = "";
      });

      if (!finalInvoice) {
        // RANDOM WINNER HERE
        const winnerIndex =
          crypto.getRandomValues(new Uint32Array(1))[0] % availableInvoices.length;
        finalInvoice = availableInvoices[winnerIndex];
        console.log(
          `WINNER INDEX : ${winnerIndex} | FINAL INVOICE : ${finalInvoice}`,
        );

        fetch("sync.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            status: "rolling",
            finalInvoice,
            region: currentRegion,
            category: currentCategory,
            prize: currentPrize,
          }),
        });
      }

      function rollNextDigit() {
        const digits = document.querySelectorAll("#invoice-display .digit");
        const digitCount = digits.length;
        const totalRolls = 20;
        const isGrandPrize = currentCategory === "Grand Prize";
        const totalDuration = isGrandPrize ? 5000 : 3000; // ms
        const perDigitDuration = totalDuration / digitCount;
        const intervalDelay = perDigitDuration / totalRolls;

        // let currentDigitIndex = digitCount - 1; // Start from last digit

        // Create a random order of digit indices

        const order = [...Array(digitCount).keys()];

        for (let i = order.length - 1; i > 0; i--) {
          const j = Math.floor(Math.random() * (i + 1));
          [order[i], order[j]] = [order[j], order[i]];
        }

        let orderIndex = 0;
        let finishedDigits = 0;

        function rollSingleDigit(index) {
          let count = 0;
          const interval = setInterval(() => {
            count++;
            const nextDigit = Math.floor(Math.random() * 10);
            digits[index].textContent = nextDigit;

            digits[index].classList.remove("rolling", "locked");
            digits[index].style.animation = "none";
            digits[index].offsetHeight;
            digits[index].style.animation = "";
            digits[index].classList.add("rolling");

            if (count >= totalRolls) {
              clearInterval(interval);
              digits[index].textContent = finalInvoice[index];
              digits[index].classList.remove("rolling");
              digits[index].offsetWidth;
              digits[index].classList.add("locked");

              // currentDigitIndex--;
              // if (currentDigitIndex >= 0) {
              //   setTimeout(() => rollSingleDigit(currentDigitIndex), 0);
              // } else {
              //   showWinner();
              // }
              orderIndex++;
              if (orderIndex < order.length) {
                setTimeout(() => rollSingleDigit(order[orderIndex]), 0);
              } else {
                showWinner();
              }
            }
          }, intervalDelay);
        }

        // rollSingleDigit(currentDigitIndex);
        rollSingleDigit(order[0]);
      }
      // console.log(`UPDATED RANDOMIZER`)

      rollNextDigit();

      console.log(`ROLLING: `)
      console.log({
        status,
        currentRegion,
        currentCategory,
        currentPrize
      });
      console.log(``)

      fetch("./sync.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          status: "rolling",
          finalInvoice,
        }),
      });
    }

    function submitWinnerDetails(winnerName, winnerTicket) {
      console.log(
        `Submitting winner details: ${winnerName} - ${winnerTicket} - ${currentRegion} - ${currentCategory} - ${currentPrize}`,
      );

      fetch("./functions/submit-winner.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            winnerName,
            winnerTicket,
            region: currentRegion,
            category: currentCategory,
            prize: currentPrize,
          }),
        })
        .then(async (response) => {

          const text = await response.text();

          if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${text}`);
          }

          return JSON.parse(text);
        })
        .then((data) => {

          if (data.status === "success") {} else {
            throw new Error(data.message);
          }
        })
        .catch((error) => {
          console.error("ACTUAL ERROR:", error);
        });
    }

    function showWinner() {
      const winnerTicket = parseInt(finalInvoice);
      const winnerUser = users.find((user) =>
        user.ticketNumbers.map(String).includes(String(winnerTicket)),
      );

      let winnerName = winnerUser ? winnerUser.customerName : "Unknown";

      if (!winnerName || winnerName === "Unknown") {
        console.error("Error: Winner's name is undefined or invalid.");
        winnerName = "Unknown"; // Default to "Unknown" to avoid crashing
      }

      // UPDATES THE SET IN MEMORY TO SURE:
      // THEIR TICKETS CAN'T WIN AGAIN
      // THEIR NAME WON'T BE SELECTED AGAIN
      if (winnerUser) {
        winnerUser.ticketNumbers.forEach((ticket) =>
          excludedTicketNumbers.add(String(ticket)),
        );

        excludedCustomerNames.add(winnerUser.customerName);

        users = users.filter((u) => u.customerName !== winnerUser.customerName);
        invoices = invoices.filter(
          (ticket) => !winnerUser.ticketNumbers.includes(parseInt(ticket)),
        );
        names = users.map((u) => u.customerName); // Make sure names are updated
      }

      const winnerDisplay = document.getElementById("winner-display");

      winnerDisplay.innerHTML = `<strong data-text="${winnerName}" class="winnerFont">${winnerName}</strong>`; // Final winner name
      showConfetti(winnerName, winnerTicket);
      submitWinnerDetails(winnerName, winnerTicket);
    }

    function showConfetti(winnerName, winnerTicket) {
      const duration = currentCategory === "Grand Prize" ? 5000 : 3000;
      const end = Date.now() + duration;

      const colors = ["#3a8004", "#b39100", "#0a66cc", "#b02e00"];
      const confettiColors =
        currentCategory === "Grand Prize" ? ["#FFD700", "#FFA500", "#FFF8DC"] :
        colors;

      (function frame() {
        confetti({
          particleCount: 8,
          angle: 60,
          spread: 60,
          origin: {
            x: 0
          },
          colors: confettiColors,
        });

        confetti({
          particleCount: 8,
          angle: 120,
          spread: 60,
          origin: {
            x: 1
          },
          colors: confettiColors,
        });

        if (Date.now() < end) {
          requestAnimationFrame(frame);
        } else {
          // ✅ Reset raffle state
          isRolling = false;
          hasStarted = false;
          finalInvoice = "";

          excludedCustomerNames.add(winnerName);

          console.log(`TO BE EXCLUDED: `)
          console.log({
            status,
            currentRegion,
            currentCategory,
            currentPrize
        });

          // UPDATES EXCLUSIONS TO RAFFLE_STATE.JSON VIA SYNC.PHP
          fetch("./sync.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json"
            },
            body: JSON.stringify({
              status: "done",
              finalInvoice,
              excludedTicketNumbers: Array.from(excludedTicketNumbers),
              excludedCustomerNames: Array.from(excludedCustomerNames),
              region: currentRegion,
              category: currentCategory,
              prize: currentPrize,
            }),
          });
        }
      })();
      sessionStorage.clear();
      sessionStorage.removeItem(`raffle_refreshed_for_${finalInvoice}`);
    }
  </script>
</body>

</html>