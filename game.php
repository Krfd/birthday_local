<?php include("config/config.php"); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />

  <title>IAP - Raffle Promo</title>
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
  <script type="module" src="js/start.js"></script>
</body>

</html>