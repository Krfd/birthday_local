<?php
// URL to encode
$url = "https://forms.gle/w4WErKkSaYtVMcQ87";

// Generate QR code via api.qrserver.com
$qrImage = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($url) . "&size=200x200";
?>

<!DOCTYPE html>
<html>

<head>
    <title>QR Code</title>
    <style>
        body {
            text-align: center;
            margin-top: 100px;
            font-family: sans-serif;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
        }
    </style>
</head>

<body>

    <h2>Anniversaya Raffle Promo</h2>
    <img src="<?= $qrImage ?>" alt="QR Code" id="qrcode">

    <br>
    <button onclick="window.print()">SCAN TO PROCEED</button>

</body>

</html>