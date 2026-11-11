@echo off
title POS - Live Cloudflare Tunnel - Port 8018
color 0A
echo ========================================================
echo           POS System - Live Internet Launcher
echo ========================================================
echo.

:: 1. Locate PHP
set "PHP_CMD=php"
where php >nul 2>&1
if %ERRORLEVEL% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PHP_CMD=C:\xampp\php\php.exe"
    ) else (
        echo [ERROR] PHP executable not found. Please install PHP or XAMPP.
        pause
        exit /b 1
    )
)

:: 2. Check or Download cloudflared.exe
set "CF_EXE=cloudflared.exe"
where cloudflared >nul 2>&1
if %ERRORLEVEL% equ 0 (
    set "CF_EXE=cloudflared"
) else (
    if not exist "cloudflared.exe" (
        if exist "%USERPROFILE%\.gemini\antigravity\cloudflared.exe" (
            set "CF_EXE=%USERPROFILE%\.gemini\antigravity\cloudflared.exe"
        ) else (
            echo [*] Downloading portable Cloudflare tunnel client...
            powershell -Command "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe' -OutFile 'cloudflared.exe'"
            if not exist "cloudflared.exe" (
                echo [ERROR] Failed to download cloudflared. Please download manually from:
                echo https://github.com/cloudflare/cloudflared/releases
                pause
                exit /b 1
            )
        )
    )
)

:: 3. Start PHP Server in background
echo [*] Starting POS Server on http://127.0.0.1:8018 ...
start /b "" "%PHP_CMD%" -S 127.0.0.1:8018 -t "%~dp0public" >nul 2>&1
timeout /t 2 /nobreak >nul

:: 4. Start Cloudflare Tunnel
echo.
echo ========================================================
echo [*] Starting Live Cloudflare Public URL...
echo     Your secure HTTPS link will appear below:
echo ========================================================
echo.
"%CF_EXE%" tunnel --url http://127.0.0.1:8018
pause
