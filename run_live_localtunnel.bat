@echo off
title InApp Al-Noor Backend - LocalTunnel - Port 8018
color 0B
echo ========================================================
echo Starting Al-Noor Backend Server on Port 8018...
echo ========================================================
start /b "" "C:\xampp\php\php.exe" -S 127.0.0.1:8018 -t "D:\inapp-v1.0.0\backend\public"
timeout /t 2 /nobreak >nul
echo Starting LocalTunnel for InApp Backend...
npx localtunnel --port 8018 --subdomain inapp-alnoor
pause
