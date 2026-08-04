@echo off

:: Change directory to where PHP files are located
cd /d "C:\xampp\htdocs\anniversaya\api"

:: Run generateTicket.php
@REM "C:\xampp\php\php.exe" generateTicket.php

:: Uncomment the following line if you want to log the output for generateTicket.php
:: "C:\xampp\php\php.exe" generateTicket.php >> "C:\xampp\htdocs\anniversaya\api\log.txt" 2>&1

:: Run responses.php
"C:\xampp\php\php.exe" responses.php

:: Uncomment the following line if you want to log the output for responses.php
:: "C:\xampp\php\php.exe" responses.php >> "C:\xampp\htdocs\anniversaya\api\log.txt" 2>&1
