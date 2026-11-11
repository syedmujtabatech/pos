# POS - Retail Point of Sale & Inventory Management System

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11%20%2F%2012-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel" />
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap 5" />
  <img src="https://img.shields.io/badge/Database-SQLite%20%2F%20MySQL-00758F?style=for-the-badge&logo=sqlite&logoColor=white" alt="Database" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License" />
</p>

**POS** is a high-speed, modern, and intuitive Point of Sale (POS) & Inventory Management system engineered for retail counters, grocery stores, supermarkets, and general merchants. Built with Laravel and Bootstrap 5, it delivers frictionless counter checkout, instant thermal receipt printing, live customer Khata (credit/udhar) tracking, and real-time inventory management with zero artificial shift roadblocks.

---

## 🌟 Key Features

### 1. ⚡ High-Speed Counter POS Terminal (`/pos`)
- **Instant Readiness**: No mandatory drawer-count or shift-opening roadblocks. The counter is immediately accessible and ready to bill from the first click.
- **Barcode Scanner Integration**: Fast barcode scanner input with `F2` focus shortcut and instant Enter-to-add line matching.
- **Visual Product Tap Grid**: Clean, responsive product tiles with category pills filter, stock indicators, and single-click quantity increments.
- **Live Reactive Cart**: Full line-item management with quantity adjustment (`+` / `-`), line discounts, and one-click cart clearing.
- **Hold / Park Sale (Multi-Customer Queue)**: Park an active customer's cart with one click to attend to another waiting customer, and resume parked carts anytime with zero latency.

### 2. 💳 Multi-Channel Payment & Change Calculator
- **Cash Payments**: Real-time cash tendered input with quick denomination shortcuts (`Exact`, `Rs. 500`, `Rs. 1,000`, `Rs. 5,000`) and instant Change Due calculation.
- **Digital / Card Payments**: Native support for POS Card terminals and mobile wallets (JazzCash, EasyPaisa, Bank Transfer).
- **Customer Khata (Credit / Udhar)**: Direct credit sales that automatically update customer ledger balances without manual journaling.

### 3. 🖨️ 80mm Thermal Receipt Generator
- Generates high-fidelity 80mm / 58mm POS thermal slips with store branding, itemized breakdown, tax/discounts, tendered cash, and change returned.
- Direct browser print dialog integration via `window.print()` with print-optimized CSS.

### 4. 👥 Customer Khata & Debt Ledger (`/customers`)
- Customer directory tracking credit limits, total purchases, and outstanding receivables.
- Fast inline customer registration modal accessible directly from the POS checkout screen.
- Payment collection (Wasooli) recording to clear debt balances.

### 5. 📦 Products & Inventory Management (`/products`)
- Complete product catalog tracking barcodes, SKUs, cost prices, selling margins, and real-time stock levels.
- Reorder level indicators and low-stock warning filters.
- Barcode generation & printable sheet export.

### 6. 📊 Sales History & Invoices Log (`/pos/invoices`)
- Real-time sales journal with search by invoice number, customer phone, date, and payment mode.
- 1-click thermal receipt reprinting for any past invoice.

---

## 🚀 Quick Setup & Deployment Guide

### Option 1: ⚡ 1-Click Windows Setup (Easiest & Fastest)

1. **Clone the repository:**
   ```bash
   git clone https://github.com/syedmujtabatech/pos.git
   cd pos
   ```
2. **Double-click `setup.bat`:**
   - Automatically prepares `.env`
   - Installs Composer packages
   - Creates SQLite database and seeds default store products, users & settings.
3. **Run Locally:**
   - Double-click **`run.bat`** (Starts server and auto-opens `http://127.0.0.1:8000/pos` in your browser).

---

### Option 2: 🌐 Make It LIVE on the Internet (Free Instant HTTPS URL)

Want to share the live POS terminal with clients or access it from a smartphone/tablet counter without paying for hosting?

- Double-click **`run_live_cloudflare.bat`**
- It boots the POS server and creates a secure Cloudflare HTTPS tunnel.
- You will instantly get a public URL (e.g. `https://random-subdomain.trycloudflare.com`) that can be opened on any mobile, tablet, barcode scanner device, or PC worldwide!

---

### Option 3: 🐳 Docker Deployment (Production & VPS)

For deploying to Docker, AWS, DigitalOcean, Render, or Railway:

```bash
git clone https://github.com/syedmujtabatech/pos.git
cd pos
docker compose up -d --build
```
Access the application at `http://localhost:8000`.

---

### Option 4: 🐧 Linux / macOS Quick Launch

```bash
git clone https://github.com/syedmujtabatech/pos.git
cd pos
chmod +x run.sh
./run.sh
```

---

### Option 5: 🛠️ Manual Installation (Terminal CLI)

```bash
# 1. Clone & enter
git clone https://github.com/syedmujtabatech/pos.git
cd pos

# 2. Dependencies & environment
composer install
cp .env.example .env
php artisan key:generate

# 3. Database & Seeds
touch database/database.sqlite
php artisan migrate:fresh --seed

# 4. Start Server
php artisan serve --port=8000
```

---

## 🔑 Default Credentials (Demo)

| Role | Username | Password | Notes |
|---|---|---|---|
| **Store Owner / Admin** | `admin` | `password` | Full system access & settings |
| **Counter Cashier** | `cashier` | `password` | POS billing terminal access |
| **Store Manager** | `manager` | `password` | Inventory & reports oversight |

*1-Click fast login buttons are also provided on the login page for effortless persona switching.*

---

## 📄 License
This project is open-source under the [MIT License](LICENSE).
