<?php
// URL to encode
$url = "https://docs.google.com/forms/d/e/1FAIpQLSfKChgVXpj0GVkPC_Ctoe6xAe2lWz6Cc5_uPcuomZjXulUqQw/viewform";

// Generate QR code via api.qrserver.com
$qrImage = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($url) . "&size=200x200";
?>

<!DOCTYPE html>
<html>

<head>
    <title>The Greatest Anniversary Appliance giveaway</title>
    <link rel="icon" href="assets/img/logo.png" />
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/vendor/bootstrap-icons.css" />
    <style>
        body {
            text-align: center;
            margin-top: 100px;
            font-family: sans-serif;
            background-color: #c2ddff;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
        }
    </style>
</head>

<body>
    <div class="container-fluid d-flex justify-content-evenly align-items-start">
        <div class="col-5 rounded-2 bg-primary shadow p-5 text-light">
            <div class="col-10 gap-3 justify-content-center d-block mx-auto">
                <h1 class="fw-bold">Imperial Appliance Plaza's Anniversaya Raffle</h1>
                <p><i>"52 Years of Serving Filipino Homes"</i></p>
                <h6 class="fw-semibold my-3">Promo Period: August 1-31, 2026</h6>
                <h5 class="fw-bold">Title: The Greatest Anniversary Appliance Giveaway</h5>
            </div>
            <div class="mt-3">
                <img class="p-3 rounded-2 border border-primary bg-primary-subtle shadow" src="<?= $qrImage ?>" alt="QR Code" id="qrcode">
            </div>
            <button class="btn btn-light text-dark shadow" onclick="window.print()">SCAN TO PROCEED</button>
        </div>
        <div class="col-5 text-start bg-light rounded-2 shadow p-5 d-flex flex-column gap-3">
            <div>
                <h5 class="fw-bold text-dark"><i class="bi bi-card-checklist text-primary"></i> Mechanics:</h3>
                    <ul class="d-flex flex-column">
                        <li>Every ₱5,000 single receipt purchase earns one (1) raffle entry.</li>
                        <li>Every additional ₱5,000 earns an additional raffle entry.</li>
                        <li>Applicable to cash and credit card (straight or installment) only.</li>
                        <li>Entries are automatically qualified for the raffle draw.</li>
                    </ul>
            </div>
            <div>
                <h6 class="fw-bold text-secondary-emphasis text-uppercase"><i class="bi bi-patch-check-fill text-primary"></i> 3 winners per prize nationwide</h6>
                <ul>
                    <li>1 Winner - Luzon</li>
                    <li>1 Winner - Visayas</li>
                    <li>1 Winner - Mindanao</li>
                </ul>
            </div>
            <div>
                <h6 class="fw-bold text-secondary-emphasis text-uppercase"><i class="bi bi-trophy-fill text-primary"></i> Prizes:</h6>
                <ul class="d-flex flex-column">
                    <li>55'' 4K HDR Smart TV.</li>
                    <li>7.4 cu.ft. Two Door Inverter Refrigerator</li>
                    <li>1.0 HP Inverter Window Type Aircon</li>
                    <li>Gas Range.</li>
                </ul>
            </div>
            <div>
                <h6 class="fw-bold text-secondary-emphasis text-uppercase"><i class="bi bi-award-fill text-primary"></i> Consolation (52 Winners)</h6>
                <ul>
                    <li>Breakfast Set</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>