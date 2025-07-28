<?php

include("config/connection.php");

$insert_regions  = $conn->prepare("INSERT INTO regions (region) VALUES
('BICOL & EASTERN VISAYAS'),
('BOHOL'),
('CENTRAL VISAYAS'),
('ILOILO CITY'),
('MINDORO'),
('NEGROS'),
('NORTHERN LUZON'),
('NORTHERN MINDANAO'),
('PANAY 1'),
('PANAY 2'),
('PALAWAN'),
('SOUTH COTABATO & SULTAN KUDARAT'),
('SOUTH LUZON'),
('SOUTH NCR & CAVITE'),
('SOUTHERN LUZON'),
('SOUTHERN MINDANAO'),
('ZAMBOANGA');");


// Execute the prepared statement
if ($insert_regions->execute()) {
    echo "Regions inserted successfully!";
} else {
    echo "Error inserting regions.";
}
