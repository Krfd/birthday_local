let invoices = [];
let names = [];
let excludedTicketNumbers = new Set();
let excludedCustomerNames = [];
let isRolling = false;
let finalInvoice = ""; // Global so it's accessible in both functions
let users = [];
let hasStarted = false;

// Declare pollingInterval outside of the functions
let pollingInterval = setInterval(() => {
  fetch("./sync.php")
    .then((res) => res.json())
    .then((state) => {
      if (state.status === "idle") {
        finalInvoice = "";
        hasStarted = false;
        isRolling = false;
      }

      // Reset UI elements if needed (not doing this for now in your code)
      if (state.status === "rolling" && !isRolling && !hasStarted) {
        hasStarted = true;
        startRaffle();
      }
    })
    .catch((err) => console.error("Polling error:", err));
}, 1000);

// Fetch entries
fetch("api/getEntries.php")
  .then((res) => res.json())
  .then((data) => {
    // ✅ Rehydrate excluded customer names
    if (data.excludedCustomerNames) {
      excludedCustomerNames = data.excludedCustomerNames;
    }

    // ✅ Apply customer filter before creating invoices/names
    users = data.filter((u) => !excludedCustomerNames.includes(u.customerName));

    invoices = users.flatMap((user) => user.ticketNumbers.map(String));
    names = users.map((user) => user.customerName);

    // Fetch sync state to re-apply exclusions
    return fetch("sync.php");
  })
  .then((res) => res.json())
  .then((state) => {
    // Exclude tickets based on state
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
    }
  });

console.log(`FETCHED DATA OUTSIDE API: ${users}`);

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

function startRaffle() {
  if (isRolling || invoices.length === 0) return;

  console.log(invoices);
  // if (isRolling) return;
  isRolling = true;

  // ✅ Clear old state
  document.getElementById("winner-display").textContent = "";
  finalInvoice = ""; // Make sure to reset it when starting a new roll

  // // Check if there are no tickets available
  // const availableInvoices = invoices.filter(
  //   (ticket) => !excludedTicketNumbers.has(ticket)
  // );

  // // Check if there are no tickets available
  // if (invoices.length === 0) {
  //   alert("No tickets available for the raffle.");
  //   isRolling = false; // Reset the rolling state
  //   return;
  // }

  const availableInvoices = invoices.filter(
    (ticket) => !excludedTicketNumbers.has(ticket)
  );

  if (availableInvoices.length === 0) {
    document.getElementById(
      "winner-display"
    ).innerHTML = `Winner<br><strong>Unknown</strong>`;
    isRolling = false;
    hasStarted = false;
    finalInvoice = "";

    fetch("./sync.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        status: "done",
        finalInvoice,
        excludedTicketNumbers: [...excludedTicketNumbers],
        excludedCustomerNames,
      }),
    });

    return;
  }

  const digits = document.querySelectorAll("#invoice-display .digit");
  digits.forEach((digitEl) => {
    digitEl.textContent = "?";
    digitEl.classList.remove("rolling", "locked");
    digitEl.style.animation = "none";
    digitEl.offsetHeight; // Force reflow to restart animation
    digitEl.style.animation = "";
  });

  document.querySelector(".chance-display").style.display = "block";
  document.getElementById("matchingTickets").textContent = "0";

  if (!finalInvoice) {
    // const winnerIndex =
    //   crypto.getRandomValues(new Uint32Array(1))[0] % invoices.length;
    // finalInvoice = invoices[winnerIndex];
    const winnerIndex =
      crypto.getRandomValues(new Uint32Array(1))[0] % availableInvoices.length;
    finalInvoice = availableInvoices[winnerIndex];

    // Save to sync.php so others know
    fetch("sync.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ status: "rolling", finalInvoice }),
    });
  }

  let currentDigit = digits.length - 1;

  // ORIGINAL
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

    // ORIGINAL
    // function slowRoll() {
    //   if (rollCount >= totalRolls) {
    //     digitEl.textContent = finalDigit;

    //     digitEl.classList.remove("rolling");
    //     digitEl.classList.remove("locked");
    //     void digitEl.offsetWidth;
    //     digitEl.classList.add("locked");

    //     const finalInvoiceStr = finalInvoice
    //       .toString()
    //       .padStart(digits.length, "0");
    //     const revealedDigits = finalInvoiceStr.slice(currentDigit);
    //     const matchCount = invoices.filter((inv) =>
    //       inv.endsWith(revealedDigits)
    //     ).length;
    //     document.getElementById(
    //       "matchingTickets"
    //     ).textContent = `${matchCount}`;

    //     currentDigit--;
    //     setTimeout(rollNextDigit, 1000);
    //     return;
    //   }

    //   let randomDigit;
    //   do {
    //     randomDigit = Math.floor(Math.random() * 10);
    //   } while (randomDigit === lastRandomDigit);

    //   lastRandomDigit = randomDigit; // Save for next round

    //   digitEl.textContent = randomDigit;
    //   digitEl.classList.remove("rolling");
    //   digitEl.style.animation = "none";
    //   digitEl.offsetHeight;
    //   digitEl.style.animation = "";
    //   digitEl.classList.add("rolling");

    //   rollCount++;

    //   let delay;
    //   if (rollCount < totalRolls * 0.6) {
    //     delay = 40;
    //   } else if (rollCount < totalRolls * 0.8) {
    //     delay = 80;
    //   } else {
    //     delay = 400;
    //   }

    //   setTimeout(slowRoll, delay);
    // }

    // CHAT GPT
    // function slowRoll() {
    //   if (rollCount >= totalRolls) {
    //     digitEl.textContent = finalDigit;

    //     digitEl.classList.remove("rolling");
    //     digitEl.classList.remove("locked");
    //     void digitEl.offsetWidth;
    //     digitEl.classList.add("locked");

    //     const finalInvoiceStr = finalInvoice
    //       .toString()
    //       .padStart(digits.length, "0");
    //     const revealedDigits = finalInvoiceStr.slice(currentDigit);
    //     const matchCount = invoices.filter((inv) =>
    //       inv.endsWith(revealedDigits)
    //     ).length;
    //     document.getElementById(
    //       "matchingTickets"
    //     ).textContent = `${matchCount}`;

    //     currentDigit--;
    //     setTimeout(rollNextDigit, 3000);
    //     return;
    //   }

    //   // 🔁 Incrementing digits from 0-9, then loop
    //   const nextDigit = rollCount % 10;
    //   digitEl.textContent = nextDigit;

    //   digitEl.classList.remove("rolling");
    //   digitEl.style.animation = "none";
    //   digitEl.offsetHeight;
    //   digitEl.style.animation = "";
    //   digitEl.classList.add("rolling");

    //   rollCount++;

    //   // ⏱️ Slower delay toward the end
    //   let delay;
    //   const progress = rollCount / totalRolls;

    //   if (progress < 0.6) {
    //     delay = 50;
    //   } else if (progress < 0.85) {
    //     delay = 150;
    //   } else {
    //     delay = 400; // Final few rolls are slow
    //   }

    //   setTimeout(slowRoll, delay);
    // }

    // NOT SO DRAMATIC
    // function slowRoll() {
    //   if (rollCount >= totalRolls) {
    //     // Once we're done with all the rolls, set the final digit and stop the rolling
    //     digitEl.textContent = finalDigit;

    //     // Lock the digit in place and stop animation
    //     digitEl.classList.remove("rolling");
    //     digitEl.classList.remove("locked");
    //     void digitEl.offsetWidth;
    //     digitEl.classList.add("locked");

    //     // Now, check for how many tickets match the revealed digits
    //     const finalInvoiceStr = finalInvoice
    //       .toString()
    //       .padStart(digits.length, "0");
    //     const revealedDigits = finalInvoiceStr.slice(currentDigit);
    //     const matchCount = invoices.filter((inv) =>
    //       inv.endsWith(revealedDigits)
    //     ).length;
    //     document.getElementById(
    //       "matchingTickets"
    //     ).textContent = `${matchCount}`;

    //     currentDigit--;
    //     setTimeout(rollNextDigit, 3000); // Add a break before rolling the next digit
    //     return;
    //   }

    //   // 🔁 Incrementing digits from 0-9, then loop
    //   const nextDigit = rollCount % 10;
    //   digitEl.textContent = nextDigit;

    //   // Reset the rolling animation to start a fresh cycle
    //   digitEl.classList.remove("rolling");
    //   digitEl.style.animation = "none";
    //   digitEl.offsetHeight; // Trigger a reflow
    //   digitEl.style.animation = "";
    //   digitEl.classList.add("rolling");

    //   rollCount++;

    //   // ⏱️ Slow down as we approach the final rolls
    //   let delay;
    //   const progress = rollCount / totalRolls;

    //   // **Speed up early on (start fast)**
    //   if (progress < 0.4) {
    //     delay = 50; // Fast start
    //   }
    //   // **Slow down gradually**
    //   else if (progress < 0.7) {
    //     delay = 150; // Slower roll as we approach the final digits
    //   }
    //   // **Very slow towards the end**
    //   else {
    //     delay = 400; // Slow roll just before the final digit
    //   }

    //   // Use `setTimeout` to adjust the speed of each roll
    //   setTimeout(slowRoll, delay);
    // }

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
        setTimeout(rollNextDigit, 3000); // Wait before next digit
        return;
      }

      const nextDigit = rollCount % 10;
      digitEl.textContent = nextDigit;

      digitEl.classList.remove("rolling");
      digitEl.style.animation = "none";
      digitEl.offsetHeight;
      digitEl.style.animation = "";
      digitEl.classList.add("rolling");

      rollCount++;

      // Dramatic slowing effect
      let delay;
      const progress = rollCount / totalRolls;

      if (progress < 0.3) {
        delay = 40; // Fast start
      } else if (progress < 0.6) {
        delay = 120;
      } else if (progress < 0.8) {
        delay = 250;
      } else if (progress < 0.95) {
        delay = 500; // Slow!
      } else {
        delay = 900; // Very slow before locking digit (max suspense)
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
      } else {
        console.error("Error:", data.message);
      }
    })
    .catch((error) => {
      console.error("Error submitting winner details:", error);
      alert("There was an error submitting the winner details.");
    });
}

// ORIGINAL
// function showWinner() {
//   const winnerTicket = parseInt(finalInvoice);
//   const winnerUser = users.find((user) =>
//     user.ticketNumbers.map(String).includes(String(winnerTicket))
//   );

//   const winnerName = winnerUser ? winnerUser.customerName : "Unknown";

//   if (winnerUser) {
//     // Exclude all this user's tickets
//     winnerUser.ticketNumbers.forEach((ticket) =>
//       excludedTicketNumbers.add(String(ticket))
//     );

//     // Exclude this customer
//     excludedCustomerNames.push(winnerUser.customerName);

//     // Update global `users`, `invoices`, `names`
//     users = users.filter((u) => u.customerName !== winnerUser.customerName);
//     invoices = invoices.filter(
//       (ticket) => !winnerUser.ticketNumbers.includes(parseInt(ticket))
//     );
//     names = users.map((u) => u.customerName);
//   }

//   const winnerDisplay = document.getElementById("winner-display");
//   let sliderIndex = 0;
//   let maxSlide = 20;

//   const nameSlider = setInterval(() => {
//     const randomName = names[Math.floor(Math.random() * names.length)];
//     winnerDisplay.innerHTML = `Winner<br><strong>${randomName}</strong>`;

//     sliderIndex++;
//     if (sliderIndex >= maxSlide) {
//       clearInterval(nameSlider);
//       winnerDisplay.innerHTML = `Winner<br><strong>${winnerName}</strong>`;
//       showConfetti(winnerName, winnerTicket);

//       submitWinnerDetails(winnerName, winnerTicket);
//     }
//   }, 100);
// }

// CHATGPT
// function showWinner() {
//   const winnerTicket = parseInt(finalInvoice);
//   const winnerUser = users.find((user) =>
//     user.ticketNumbers.map(String).includes(String(winnerTicket))
//   );

//   // Ensure winnerName is valid before proceeding
//   const winnerName = winnerUser ? winnerUser.customerName : "Unknown";

//   // Ensure winnerName is not undefined in case of any async timing issue
//   if (!winnerName || winnerName === "Unknown") {
//     console.error("Error: Winner's name is undefined or invalid.");
//     return; // Stop if no valid winner is found
//   }

//   if (winnerUser) {
//     // Exclude all this user's tickets
//     winnerUser.ticketNumbers.forEach((ticket) =>
//       excludedTicketNumbers.add(String(ticket))
//     );

//     // Exclude this customer
//     excludedCustomerNames.push(winnerUser.customerName);

//     // Update global `users`, `invoices`, `names`
//     users = users.filter((u) => u.customerName !== winnerUser.customerName);
//     invoices = invoices.filter(
//       (ticket) => !winnerUser.ticketNumbers.includes(parseInt(ticket))
//     );
//     names = users.map((u) => u.customerName);
//   }

//   const winnerDisplay = document.getElementById("winner-display");
//   let sliderIndex = 0;
//   let maxSlide = 20;

//   const nameSlider = setInterval(() => {
//     // Only choose a random name if there's at least one available invoice
//     if (names.length > 0) {
//       const randomName = names[Math.floor(Math.random() * names.length)];
//       winnerDisplay.innerHTML = `Winner<br><strong>${randomName}</strong>`;
//     }

//     sliderIndex++;
//     if (sliderIndex >= maxSlide) {
//       clearInterval(nameSlider);
//       winnerDisplay.innerHTML = `Winner<br><strong>${winnerName}</strong>`;
//       showConfetti(winnerName, winnerTicket);

//       submitWinnerDetails(winnerName, winnerTicket);
//     }
//   }, 100);
// }

function showWinner() {
  const winnerTicket = parseInt(finalInvoice);
  const winnerUser = users.find((user) =>
    user.ticketNumbers.map(String).includes(String(winnerTicket))
  );

  // Ensure winnerName is valid before proceeding
  let winnerName = winnerUser ? winnerUser.customerName : "Unknown";

  // Ensure winnerName is not undefined in case of any async timing issue
  if (!winnerName || winnerName === "Unknown") {
    console.error("Error: Winner's name is undefined or invalid.");
    winnerName = "Unknown"; // Default to "Unknown" to avoid crashing
  }

  // Update the UI and exclude tickets as necessary
  if (winnerUser) {
    // Exclude all this user's tickets
    winnerUser.ticketNumbers.forEach((ticket) =>
      excludedTicketNumbers.add(String(ticket))
    );

    // Exclude this customer
    excludedCustomerNames.push(winnerUser.customerName);

    // Update global `users`, `invoices`, `names`
    users = users.filter((u) => u.customerName !== winnerUser.customerName);
    invoices = invoices.filter(
      (ticket) => !winnerUser.ticketNumbers.includes(parseInt(ticket))
    );
    names = users.map((u) => u.customerName); // Make sure names are updated
  }

  const winnerDisplay = document.getElementById("winner-display");
  let sliderIndex = 0;
  let maxSlide = 20;

  const nameSlider = setInterval(() => {
    // Only choose a random name if there's at least one available invoice
    if (names.length > 0) {
      const randomName = names[Math.floor(Math.random() * names.length)];
      winnerDisplay.innerHTML = `Winner<br><strong>${randomName}</strong>`;
    }

    sliderIndex++;
    if (sliderIndex >= maxSlide) {
      clearInterval(nameSlider);
      winnerDisplay.innerHTML = `Winner<br><strong>${winnerName}</strong>`; // Final winner name
      showConfetti(winnerName, winnerTicket);

      submitWinnerDetails(winnerName, winnerTicket);
    }
  }, 100); // Shuffle names every 100ms
}

function showConfetti(winnerName, winnerTicket) {
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
      // ✅ Reset raffle state so next click works
      isRolling = false;
      hasStarted = false;
      finalInvoice = "";

      excludedCustomerNames.push(winnerName);

      fetch("./sync.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          status: "done",
          finalInvoice,
          excludedTicketNumbers: [...excludedTicketNumbers],
          excludedCustomerNames,
        }),
      });
    }
  })();
}
