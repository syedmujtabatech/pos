@echo off
title ApexPOS - Live Cloudflare Launcher - Port 8018
color 0A
echo ========================================================
echo Starting ApexPOS Retail Counter Server on Port 8018...
echo ========================================================
start /b "" "C:\xampp\php\php.exe" -S 127.0.0.1:8018 -t "D:\inapp-v1.0.0\backend\public"
timeout /t 2 /nobreak >nul
echo Starting Cloudflare Tunnel for Live Access...
"C:\Users\Mujtaba Arshad\.gemini\antigravity\cloudflared.exe" tunnel --url http://127.0.0.1:8018
pause
