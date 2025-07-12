<?php include("config/config.php"); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />

  <title>IAP - Raffle Promo</title>
  <link rel="icon" href="assets/img/logo.jpg" />
  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/style.css" />
  <style>
    #carouselOne,
    #carouselTwo {
      width: 400px !important;
      max-width: 400px !important;
      flex: 0 0 400px !important;
      /* prevents flexbox from resizing */
    }

    #carouselOne .carousel-inner,
    #carouselTwo .carousel-inner {
      width: 400px !important;
      max-width: 400px !important;
      overflow: hidden;
    }

    #carouselOne .carousel-item,
    #carouselTwo .carousel-item {
      width: 400px !important;
      max-width: 400px !important;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="d-flex gap-4">
      <h1 class="fw-bold display-1" style="color: #990000">
        IMPERIAL APPLIANCE PLAZA
      </h1>
    </div>
    <h2 class="fw-semibold display-4 text-primary">RAFFLE GAME</h2>
    <div class="mt-5">
      <h2 class="fw-semibold">E-TICKET NUMBER</h2>
      <div id="invoice-display">
        <span class="digit border rounded-3 shadow-sm px-3">?</span>
        <span class="digit border rounded-3 shadow-sm px-3">?</span>
        <span class="digit border rounded-3 shadow-sm px-3">?</span>
        <span class="digit border rounded-3 shadow-sm px-3">?</span>
        <span class="digit border rounded-3 shadow-sm px-3">?</span>
        <span class="digit border rounded-3 shadow-sm px-3">?</span>
      </div>
    </div>
    <div id="winner-display" aria-live="assertive">
      <span id="winner-label"></span>
      <span id="winner-name"></span>
    </div>

    <div class="mt-3 chance-display">
      <h4>POSSIBLE WINNERS</h4>
      <div id="matchingTickets" class="text-success fs-3"></div>
    </div>

    <div class="d-flex justify-content-between mt-5 pt-3">
      <div
        id="carouselOne"
        class="carousel slide carousel-fade"
        data-bs-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img
              src="assets/img/download.jpg"
              class="d-block"
              style="
                  object-fit: cover;
                  height: 300px;
                  width: 400px;
                  max-width: 400px;
                "
              alt="..." />
            <div class="carousel-caption d-none d-md-block">
              <h5>First slide label</h5>
              <p>
                Some representative placeholder content for the first slide.
              </p>
            </div>
          </div>
          <div class="carousel-item">
            <img
              src="assets/img/img.jpg"
              class="d-block"
              style="
                  object-fit: cover;
                  height: 300px;
                  width: 400px;
                  max-width: 400px;
                "
              alt="..." />
            <div class="carousel-caption d-none d-md-block">
              <h5>Second slide label</h5>
              <p>
                Some representative placeholder content for the second slide.
              </p>
            </div>
          </div>
          <div class="carousel-item">
            <img
              src="assets/img/logo.jpg"
              class="d-block"
              style="
                  object-fit: cover;
                  height: 300px;
                  width: 400px;
                  max-width: 400px;
                "
              alt="..." />
            <div class="carousel-caption d-none d-md-block">
              <h5>Third slide label</h5>
              <p>
                Some representative placeholder content for the third slide.
              </p>
            </div>
          </div>
        </div>
      </div>
      <div
        id="carouselTwo"
        class="carousel slide carousel-fade"
        data-bs-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img
              src="assets/img/download.jpg"
              class="d-block"
              style="
                  object-fit: cover;
                  height: 300px;
                  width: 400px;
                  max-width: 400px;
                "
              alt="..." />
            <div class="carousel-caption d-none d-md-block">
              <h5>First slide label</h5>
              <p>
                Some representative placeholder content for the first slide.
              </p>
            </div>
          </div>
          <div class="carousel-item">
            <img
              src="assets/img/img.jpg"
              class="d-block"
              style="
                  object-fit: cover;
                  height: 300px;
                  width: 400px;
                  max-width: 400px;
                "
              alt="..." />
            <div class="carousel-caption d-none d-md-block">
              <h5>Second slide label</h5>
              <p>
                Some representative placeholder content for the second slide.
              </p>
            </div>
          </div>
          <div class="carousel-item">
            <img
              src="assets/img/logo.jpg"
              class="d-block"
              style="
                  object-fit: cover;
                  height: 300px;
                  width: 400px;
                  max-width: 400px;
                "
              alt="..." />
            <div class="carousel-caption d-none d-md-block">
              <h5>Third slide label</h5>
              <p>
                Some representative placeholder content for the third slide.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="js/bootstrap.bundle.min.js"></script>
  <script src="js/canvas-confetti.js"></script>
  <script type="module" src="js/start.js"></script>
</body>

</html>