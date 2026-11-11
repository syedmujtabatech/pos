@echo off
title POS - Retail Point of Sale
color 0A
echo ========================================================
echo Starting POS Retail Counter Server...
echo ========================================================

:: Check if setup has been done
if not exist ".env" (
    echo [*] First time setup detected. Running setup.bat...
    call "%~dp0setup.bat"
)

:: Locate PHP
set "PHP_CMD=php"
where php >nul 2>&1
if %ERRORLEVEL% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PHP_CMD=C:\xampp\php\php.exe"
    )
)

echo Opening browser at http://127.0.0.1:8000/pos ...
timeout /t 2 /nobreak >nul
start http://127.0.0.1:8000/pos

"%PHP_CMD%" artisan serve --port=8000
pause
