import { users } from "./data.js";
const ticketNumbers = users.flatMap((user) => user.ticketNumbers.map(String));
const customerNames = users.map((user) => user.customerName);

// const startButton = document.getElementById("start-btn");
const startButton = document.getElementById("start-btn") || null;
const invoices = ticketNumbers;
const names = customerNames;

let excludedTicketNumbers = new Set();

let isRolling = false;

// Prevent refresh/reload keys
window.addEventListener("keydown", (e) => {
  if (
    e.key === "F5" ||
    (e.key.toLowerCase() === "r" && (e.ctrlKey || e.metaKey))
  ) {
    e.preventDefault();
  }
});

// Confirm before closing/reloading the page
window.addEventListener("beforeunload", (e) => {
  e.preventDefault();
  e.returnValue = "";
});

let finalInvoice = ""; // Global so it's accessible in both functions

function startRaffle(externalTrigger = false) {
  if (isRolling) return;
  isRolling = true;

  if (!externalTrigger && startButton) startButton.disabled = true;

  // ✅ Clear old state
  document.getElementById("winner-display").textContent = "";
  finalInvoice = ""; // Make sure to reset it when starting a new roll

  // Check if there are no tickets available
  const availableInvoices = invoices.filter(
    (ticket) => !excludedTicketNumbers.has(ticket)
  );

  // Check if there are no tickets available
  if (invoices.length === 0) {
    alert("No tickets available for the raffle.");
    startButton.disabled = false; // Re-enable the button so the user can try again
    isRolling = false; // Reset the rolling state
    return;
  }

  const digits = document.querySelectorAll("#invoice-display .digit");
  digits.forEach((digitEl) => {
    digitEl.textContent = "?"; // or "" if you want blank at the start
    digitEl.classList.remove("rolling", "locked");
    digitEl.style.animation = "none";
    digitEl.offsetHeight; // Force reflow to restart animation
    digitEl.style.animation = "";
  });

  document.querySelector(".chance-display").style.display = "block";
  document.getElementById("matchingTickets").textContent = "0";

  if (!finalInvoice) {
    const winnerIndex =
      crypto.getRandomValues(new Uint32Array(1))[0] % invoices.length;
    finalInvoice = invoices[winnerIndex];

    // Save to sync.php so others know
    fetch("sync.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ status: "rolling", finalInvoice }),
    });
  }

  let currentDigit = digits.length - 1;

  function rollNextDigit() {
    if (currentDigit < 0) {
      document.querySelector(".chance-display").style.display = "none";
      showWinner();
      return;
    }

    const digitEl = digits[currentDigit];
    const finalDigit = finalInvoice[currentDigit];
    const totalRolls = 20;
    let rollCount = 0;

    let lastRandomDigit = -1;

    function slowRoll() {
      if (rollCount >= totalRolls) {
        digitEl.textContent = finalDigit;

        digitEl.classList.remove("rolling");
        digitEl.classList.remove("locked");
        void digitEl.offsetWidth;
        digitEl.classList.add("locked");

        const finalInvoiceStr = finalInvoice
          .toString()
          .padStart(digits.length, "0");
        const revealedDigits = finalInvoiceStr.slice(currentDigit);
        const matchCount = invoices.filter((inv) =>
          inv.endsWith(revealedDigits)
        ).length;
        document.getElementById(
          "matchingTickets"
        ).textContent = `${matchCount}`;

        currentDigit--;
        setTimeout(rollNextDigit, 3000);
        return;
      }

      let randomDigit;
      do {
        randomDigit = Math.floor(Math.random() * 10);
      } while (randomDigit === lastRandomDigit);

      lastRandomDigit = randomDigit; // Save for next round

      digitEl.textContent = randomDigit;
      digitEl.classList.remove("rolling");
      digitEl.style.animation = "none";
      digitEl.offsetHeight;
      digitEl.style.animation = "";
      digitEl.classList.add("rolling");

      rollCount++;

      let delay;
      if (rollCount < totalRolls * 0.6) {
        delay = 40;
      } else if (rollCount < totalRolls * 0.8) {
        delay = 80;
      } else {
        delay = 400;
      }

      setTimeout(slowRoll, delay);
    }

    slowRoll();
  }
  rollNextDigit();

  fetch("./sync.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ status: "rolling", finalInvoice }),
  });
}

function submitWinnerDetails(winnerName, winnerTicket) {
  fetch("./submit-winner.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      winnerName: winnerName,
      winnerTicket: winnerTicket,
    }),
  })
    .then((response) => {
      // Check if the response is OK (status 200-299)
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }

      // Check if the response is empty before parsing JSON
      return response.text().then((text) => {
        if (text) {
          return JSON.parse(text); // Parse JSON if the response is not empty
        } else {
          throw new Error("Empty response from server");
        }
      });
    })
    .then((data) => {
      if (data.status === "success") {
        // alert("Winner: " + winnerName);
      } else {
        console.error("Error:", data.message);
        // alert("Error: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error submitting winner details:", error);
      alert("There was an error submitting the winner details.");
    });
}

function showWinner() {
  const winnerTicket = parseInt(finalInvoice);
  const winnerUser = users.find((user) =>
    user.ticketNumbers.includes(winnerTicket)
  );

  const winnerName = winnerUser ? winnerUser.customerName : "Unknown";

  // Add all ticket numbers associated with this winner to the excluded list
  if (winnerUser) {
    winnerUser.ticketNumbers.forEach((ticket) =>
      excludedTicketNumbers.add(ticket)
    );
  }

  const winnerDisplay = document.getElementById("winner-display");
  let sliderIndex = 0;
  let maxSlide = 20;

  const nameSlider = setInterval(() => {
    const randomName = names[Math.floor(Math.random() * names.length)];
    winnerDisplay.innerHTML = `Winner<br><strong>${randomName}</strong>`;

    sliderIndex++;
    if (sliderIndex >= maxSlide) {
      clearInterval(nameSlider);
      winnerDisplay.innerHTML = `Winner<br><strong>${winnerName}</strong>`;
      showConfetti();

      submitWinnerDetails(winnerName, winnerTicket);
    }
  }, 100);
}

function showConfetti() {
  const end = Date.now() + 15 * 1000;
  const colors = ["#bb0000", "#ffffff"];

  (function frame() {
    confetti({
      particleCount: 2,
      angle: 60,
      spread: 55,
      origin: { x: 0 },
      colors: colors,
    });
    confetti({
      particleCount: 2,
      angle: 120,
      spread: 55,
      origin: { x: 1 },
      colors: colors,
    });

    if (Date.now() < end) {
      requestAnimationFrame(frame);
    } else {
      // ✅ Enable start button AFTER confetti ends
      if (startButton) startButton.disabled = false;

      // ✅ Reset raffle state so next click works
      isRolling = false;
      hasStarted = false;
      finalInvoice = "";

      // ✅ Update sync.php with "done" state
      // fetch("./sync.php", {
      //   method: "POST",
      //   headers: { "Content-Type": "application/json" },
      //   body: JSON.stringify({ status: "done" }),
      // });

      fetch("./sync.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          status: "done",
          excludedTicketNumbers: [...excludedTicketNumbers], // Send excluded ticket numbers
        }),
      });
    }
  })();
}

if (startButton) startButton.addEventListener("click", startRaffle);

let hasStarted = false;

setInterval(() => {
  fetch("./sync.php")
    .then((res) => res.json())
    .then((state) => {
      if (state.status === "idle") {
        finalInvoice = "";
        hasStarted = false;
        isRolling = false;

        const digits = document.querySelectorAll("#invoice-display .digit");
        digits.forEach((digitEl) => {
          digitEl.textContent = "0";
          digitEl.classList.remove("rolling", "locked");
          digitEl.style.animation = "none";
        });

        const winnerDisplay = document.getElementById("winner-display");
        if (winnerDisplay) winnerDisplay.innerHTML = "";

        const matchingTickets = document.getElementById("matchingTickets");
        if (matchingTickets) matchingTickets.textContent = "0";

        const chanceDisplay = document.querySelector(".chance-display");
        if (chanceDisplay) chanceDisplay.style.display = "none";
      }

      if (state.status === "rolling" && !isRolling && !hasStarted) {
        hasStarted = true;
        startRaffle(false);
      }
    })
    .catch((err) => console.error("Polling error:", err));
}, 1000);
