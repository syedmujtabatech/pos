@echo off
title POS - 1-Click Automated Setup
color 0A
echo ================================================================
echo           POS System - 1-Click Environment Setup
echo ================================================================
echo.

:: 1. Check PHP
where php >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [ERROR] PHP is not found in system PATH.
    echo Please install PHP (e.g. from XAMPP: C:\xampp\php) and add it to PATH.
    pause
    exit /b 1
)

:: 2. Check Composer
where composer >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [WARNING] Composer is not found in PATH.
    echo If dependencies are already installed, skipping composer install.
) else (
    echo [*] Installing / updating Composer dependencies...
    call composer install --no-interaction --prefer-dist
)

:: 3. Prepare .env
if not exist ".env" (
    echo [*] Copying .env.example to .env...
    copy ".env.example" ".env" >nul
)

:: 4. Generate App Key
echo [*] Generating Application Key...
call php artisan key:generate --force

:: 5. Prepare SQLite Database
if not exist "database\database.sqlite" (
    echo [*] Creating SQLite database file...
    type nul > "database\database.sqlite"
)

:: 6. Run Migrations & Seeders
echo [*] Running Migrations and Seeding Store Data...
call php artisan migrate:fresh --seed --force

:: 7. Clear Caches
echo [*] Optimizing Caches...
call php artisan config:clear
call php artisan cache:clear
call php artisan view:clear

echo.
echo ================================================================
echo [SUCCESS] Setup completed successfully!
echo ================================================================
echo.
echo Default Logins:
echo   - Owner/Admin : Username: admin     ^| Password: password
echo   - Cashier     : Username: cashier   ^| Password: password
echo   - Manager     : Username: manager   ^| Password: password
echo.
echo To run locally: double click 'run.bat'
echo To make it LIVE on the Internet: double click 'run_live_cloudflare.bat'
echo.
pause
