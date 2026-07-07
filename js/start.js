let invoices = [];
let names = [];
let excludedTicketNumbers = new Set();
// let excludedCustomerNames = [];
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

      console.log("SYNC RESPONSE:", state);

      currentRegion = state.region?.trim() || "";
      currentCategory = state.category?.trim() || "";
      currentPrize = state.prize?.trim() || "";
      finalInvoice = state.finalInvoice?.trim() || "";

      console.log("CURRENT:", {
        currentRegion,
        currentCategory,
        currentPrize,
      });

      // excludedTicketNumbers = new Set(state.excludedTicketNumbers || []);
      // excludedCustomerNames = new Set(state.excludedCustomerNames || []);

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

// Fetch entries
fetch("api/getEntries.php")
  .then((res) => res.json())
  .then((data) => {
    // ✅ Rehydrate excluded customer names
    if (data.excludedCustomerNames) {
      // excludedCustomerNames = data.excludedCustomerNames;
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

    // Fetch sync state to re-apply exclusions
    return fetch("sync.php");
  })
  .then((res) => res.json())
  .then((state) => {
    // Exclude tickets based on state

    currentRegion = state.region || "";
    currentCategory = state.category || "";
    currentPrize = state.prize || "";

    // RESTORING THE EXCLUSIONS FROM RAFFLE_STATE.JSON WHEN PAGE RELOADS
    if (state.excludedTicketNumbers) {
      for (const ticket of state.excludedTicketNumbers) {
        excludedTicketNumbers.add(ticket);
      }
    }

    // If game was rolling before reload
    if (state.status === "rolling" && !hasStarted && !isRolling) {
      finalInvoice = state.finalInvoice;
      hasStarted = true;
      startRaffle();
      // console.log(`RAFFLE SHOULD START HERE!`);
    }
  });

function startRaffle() {
  // ✅ Only refresh ONCE per finalInvoice value (unique per roll)
  const reloadKey = `raffle_refreshed_for_${finalInvoice}`;
  const alreadyReloaded = sessionStorage.getItem(reloadKey);

  if (!alreadyReloaded) {
    // Mark it as reloaded so it doesn’t loop
    sessionStorage.setItem(reloadKey, "yes");

    // ✅ Trigger full reload
    console.log("🔁 Refreshing before raffle starts...");
    location.reload();
    return; // STOP raffle, reload happens
  }

  if (isRolling || invoices.length === 0) return;

  isRolling = true;

  // ✅ Clear old state
  document.getElementById("winner-display").textContent = "";
  finalInvoice = ""; // Make sure to reset it when starting a new roll

  // const availableInvoices = invoices.filter(
  //   (ticket) => !excludedTicketNumbers.has(ticket)
  // );
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

    fetch("./sync.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
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
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        status: "rolling",
        finalInvoice,
        // ADDED
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

    let currentDigitIndex = digitCount - 1; // Start from last digit

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

          // Move to the previous digit
          currentDigitIndex--;
          if (currentDigitIndex >= 0) {
            setTimeout(() => rollSingleDigit(currentDigitIndex), 0);
          } else {
            // const winnerTicket = parseInt(finalInvoice);
            // const winnerUser = users.find((user) =>
            //   user.ticketNumbers.map(String).includes(String(winnerTicket))
            // );

            showWinner();
          }
        }
      }, intervalDelay);
    }

    rollSingleDigit(currentDigitIndex);
  }

  rollNextDigit();

  fetch("./sync.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      status: "rolling",
      finalInvoice,
    }),
  });
}

// function submitWinnerDetails(winnerName, winnerTicket) {
//   console.log(
//     `Submitting winner details: ${winnerName} - ${winnerTicket} - ${currentRegion} - ${currentCategory} - ${currentPrize}`,
//   );
//   fetch("./functions/submit-winner.php", {
//     method: "POST",
//     headers: {
//       "Content-Type": "application/json",
//     },
//     body: JSON.stringify({
//       winnerName,
//       winnerTicket,
//       region: currentRegion,
//       category: currentCategory,
//       prize: currentPrize,
//     }),
//   })
//     .then((response) => {
//       // Check if the response is OK (status 200-299)
//       if (!response.ok) {
//         throw new Error("Network response was not ok");
//       }

//       // Check if the response is empty before parsing JSON
//       return response.text().then((text) => {
//         if (text) {
//           return JSON.parse(text); // Parse JSON if the response is not empty
//         } else {
//           throw new Error("Empty response from server");
//         }
//       });
//     })
//     .then((data) => {
//       if (data.status === "success") {
//       } else {
//         console.error("Error:", data.message);
//       }
//     })
//     .catch((error) => {
//       console.error("Error submitting winner details:", error);
//       alert("There was an error submitting the winner details.");
//     });
// }

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
      // console.log("HTTP STATUS:", response.status);

      const text = await response.text();

      // console.log("RAW PHP RESPONSE:");
      // console.log(text);

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${text}`);
      }

      return JSON.parse(text);
    })
    .then((data) => {
      // console.log("PARSED RESPONSE:", data);

      if (data.status === "success") {
        // console.log("Winner saved successfully");
      } else {
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

  // Ensure winnerName is not undefined in case of any async timing issue
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
    currentCategory === "Grand Prize"
      ? ["#FFD700", "#FFA500", "#FFF8DC"]
      : colors;

  (function frame() {
    confetti({
      particleCount: 8,
      angle: 60,
      spread: 60,
      origin: { x: 0 },
      colors: confettiColors,
    });

    confetti({
      particleCount: 8,
      angle: 120,
      spread: 60,
      origin: { x: 1 },
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

      // UPDATES EXCLUSIONS TO RAFFLE_STATE.JSON VIA SYNC.PHP
      fetch("./sync.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          status: "done",
          finalInvoice,
          // excludedTicketNumbers,
          // excludedCustomerNames,
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
