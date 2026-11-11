<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Products Catalog
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/barcodes', [ProductController::class, 'barcodes'])->name('products.barcodes');
    Route::match(['get', 'post'], '/products/bulk-rates', [ProductController::class, 'bulkRates'])->name('products.bulk-rates');
    Route::get('/products/export-csv', [ProductController::class, 'exportCsv'])->name('products.export-csv');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::post('/products/{product}/toggle', [ProductController::class, 'toggleStatus'])->name('products.toggle');

    // Master Data
    Route::get('/categories', [MasterDataController::class, 'categories'])->name('categories.index');
    Route::post('/categories', [MasterDataController::class, 'storeCategory'])->name('categories.store');
    Route::get('/brands', [MasterDataController::class, 'brands'])->name('brands.index');
    Route::post('/brands', [MasterDataController::class, 'storeBrand'])->name('brands.store');
    Route::post('/manufacturers', [MasterDataController::class, 'storeManufacturer'])->name('manufacturers.store');
    Route::get('/units', [MasterDataController::class, 'units'])->name('units.index');
    Route::post('/units', [MasterDataController::class, 'storeUnit'])->name('units.store');
    Route::get('/locations', [MasterDataController::class, 'locations'])->name('locations.index');
    Route::post('/locations', [MasterDataController::class, 'storeLocation'])->name('locations.store');
    Route::get('/areas', [MasterDataController::class, 'areas'])->name('areas.index');
    Route::post('/areas', [MasterDataController::class, 'storeArea'])->name('areas.store');

    // Suppliers
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::get('/suppliers/{supplier}/ledger', [SupplierController::class, 'ledger'])->name('suppliers.ledger');

    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::get('/customers/{customer}/ledger', [CustomerController::class, 'ledger'])->name('customers.ledger');
    Route::get('/customers-aging', [CustomerController::class, 'aging'])->name('customers.aging');

    // Purchasing & GRN
    Route::get('/purchases/orders', [PurchaseController::class, 'orders'])->name('purchases.orders');
    Route::get('/purchases/orders/create', [PurchaseController::class, 'createOrder'])->name('purchases.orders.create');
    Route::post('/purchases/orders', [PurchaseController::class, 'storeOrder'])->name('purchases.orders.store');
    Route::get('/purchases/orders/{order}', [PurchaseController::class, 'showOrder'])->name('purchases.orders.show');
    Route::get('/purchases/grn', [PurchaseController::class, 'grns'])->name('purchases.grn');
    Route::get('/purchases/grn/create', [PurchaseController::class, 'createGrn'])->name('purchases.grn.create');
    Route::post('/purchases/grn', [PurchaseController::class, 'storeGrn'])->name('purchases.grn.store');
    Route::get('/purchases/grn/{grn}', [PurchaseController::class, 'showGrn'])->name('purchases.grn.show');
    Route::get('/purchases/invoices', [PurchaseController::class, 'invoices'])->name('purchases.invoices');
    Route::get('/purchases/payments', [PurchaseController::class, 'payments'])->name('purchases.payments');
    Route::post('/purchases/payments', [PurchaseController::class, 'storePayment'])->name('purchases.payments.store');
    Route::get('/purchases/returns', [PurchaseController::class, 'returns'])->name('purchases.returns');

    // Sales & Wholesale
    Route::get('/sales/wholesale', [SaleController::class, 'wholesale'])->name('sales.wholesale');
    Route::get('/sales/wholesale-list', [SaleController::class, 'wholesale'])->name('sales.wholesale.index');
    Route::get('/sales/wholesale/create', [SaleController::class, 'createWholesale'])->name('sales.wholesale.create');
    Route::post('/sales/wholesale', [SaleController::class, 'storeWholesale'])->name('sales.wholesale.store');
    Route::get('/sales/invoice/{invoice}', [SaleController::class, 'showInvoice'])->name('sales.show-invoice');
    Route::get('/sales/invoice/{invoice}/thermal', [SaleController::class, 'printThermal'])->name('sales.print-thermal');
    Route::get('/sales/invoice/{invoice}/print', [SaleController::class, 'printA4'])->name('sales.print-a4');
    Route::post('/sales/invoice/{invoice}/void', [SaleController::class, 'voidInvoice'])->name('sales.void-invoice');
    Route::get('/sales/quotations', [SaleController::class, 'quotations'])->name('sales.quotations');
    Route::get('/sales/orders', [SaleController::class, 'orders'])->name('sales.orders');
    Route::get('/sales/challans', [SaleController::class, 'challans'])->name('sales.challans');
    Route::get('/sales/returns', [SaleController::class, 'returns'])->name('sales.returns');
    Route::get('/sales/returns-list', [SaleController::class, 'returns'])->name('sales.returns.index');
    Route::get('/sales/returns/create', [SaleController::class, 'createReturn'])->name('sales.returns.create');
    Route::post('/sales/returns', [SaleController::class, 'storeReturn'])->name('sales.returns.store');

    // Retail Point of Sale (POS)
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::post('/pos/customer', [PosController::class, 'quickCustomer'])->name('pos.quick-customer');
    Route::get('/pos/receipt/{invoice}', [PosController::class, 'getReceipt'])->name('pos.receipt');
    Route::get('/pos/invoices', [PosController::class, 'invoices'])->name('pos.invoices');

    // Inventory & Warehouses
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/ledger', [InventoryController::class, 'ledger'])->name('inventory.ledger');
    Route::get('/inventory/transfers', [InventoryController::class, 'transfers'])->name('inventory.transfers');
    Route::get('/inventory/transfers/create', [InventoryController::class, 'createTransfer'])->name('inventory.transfers.create');
    Route::post('/inventory/transfers', [InventoryController::class, 'storeTransfer'])->name('inventory.transfers.store');
    Route::post('/inventory/transfers/{transfer}/receive', [InventoryController::class, 'receiveTransfer'])->name('inventory.transfers.receive');
    Route::get('/inventory/adjustments', [InventoryController::class, 'adjustments'])->name('inventory.adjustments');
    Route::get('/inventory/adjustments/create', [InventoryController::class, 'createAdjustment'])->name('inventory.adjustments.create');
    Route::post('/inventory/adjustments', [InventoryController::class, 'storeAdjustment'])->name('inventory.adjustments.store');
    Route::get('/inventory/counts', [InventoryController::class, 'counts'])->name('inventory.counts');
    Route::get('/inventory/counts/create', [InventoryController::class, 'createCount'])->name('inventory.counts.create');
    Route::post('/inventory/counts', [InventoryController::class, 'storeCount'])->name('inventory.counts.store');

    // Finance & Cash Book
    Route::get('/finance/receipts', [FinanceController::class, 'receipts'])->name('finance.receipts');
    Route::post('/finance/receipts', [FinanceController::class, 'storeReceipt'])->name('finance.receipts.store');
    Route::get('/finance/cheques', [FinanceController::class, 'cheques'])->name('finance.cheques');
    Route::post('/finance/cheques/{cheque}/bounce', [FinanceController::class, 'bounceCheque'])->name('finance.cheques.bounce');
    Route::post('/finance/cheques/{cheque}/clear', [FinanceController::class, 'clearCheque'])->name('finance.cheques.clear');
    Route::get('/finance/expenses', [FinanceController::class, 'expenses'])->name('finance.expenses');
    Route::post('/finance/expenses', [FinanceController::class, 'storeExpense'])->name('finance.expenses.store');
    Route::get('/finance/cash-book', [FinanceController::class, 'cashBook'])->name('finance.cash-book');
    Route::get('/finance/bank-book', [FinanceController::class, 'bankBook'])->name('finance.bank-book');
    Route::get('/finance/day-closing', [FinanceController::class, 'dayClosing'])->name('finance.day-closing');
    Route::post('/finance/day-closing', [FinanceController::class, 'storeDayClosing'])->name('finance.day-closing.store');

    // Reports (all 28 SRS reports)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Approvals Queue
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('/approvals/{approval}/decide', [ApprovalController::class, 'decide'])->name('approvals.decide');

    // Users & Roles Management (RBAC)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/roles', [UserController::class, 'storeRole'])->name('roles.store');
    Route::put('/roles/{role}', [UserController::class, 'updateRole'])->name('roles.update');

    // Settings & Backup
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/backup', [SettingController::class, 'createBackup'])->name('settings.backup');

    // Internal Staff Messaging
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead'])->name('messages.mark-read');
    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

    // System Activity Logs & Login History (Admin)
    Route::get('/admin/activity-logs', [LogController::class, 'activityLogs'])->name('admin.activity-logs');
    Route::get('/admin/login-history', [LogController::class, 'loginHistory'])->name('admin.login-history');
});
