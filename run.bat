@echo off
title ApexPOS - Smart Retail Point of Sale
echo ========================================================
echo Starting ApexPOS Retail Counter Server...
echo ========================================================
echo Opening browser at http://127.0.0.1:8000/pos ...
timeout /t 2 /nobreak >nul
start http://127.0.0.1:8000/pos
php artisan serve --port=8000
