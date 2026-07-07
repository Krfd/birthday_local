<?php
// URL to encode
$url = "https://docs.google.com/forms/d/e/1FAIpQLSfKChgVXpj0GVkPC_Ctoe6xAe2lWz6Cc5_uPcuomZjXulUqQw/viewform";

// Generate QR code via api.qrserver.com
$qrImage = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($url) . "&size=200x200";
?>

<!DOCTYPE html>
<html>

<head>
    <title>AnniverSaya 2026!</title>
    <link rel="icon" href="assets/img/logo.jpg" />
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

    <h2 class="fw-bold text-secondary-emphasis">AnniverSaya 2026 Raffle Promo</h2>
    <div class="mt-3">
        <img class="p-3 rounded-2 border border-primary bg-primary-subtle shadow" src="<?= $qrImage ?>" alt="QR Code" id="qrcode">
    </div>
    <button class="btn btn-primary text-white shadow" onclick="window.print()">SCAN TO PROCEED</button>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>