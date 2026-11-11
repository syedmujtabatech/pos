<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Sales & Point of Sale
            ['name' => 'Access POS Counter', 'slug' => 'pos.access', 'module' => 'Sales & POS'],
            ['name' => 'Process POS Checkout', 'slug' => 'pos.checkout', 'module' => 'Sales & POS'],
            ['name' => 'View Wholesale Invoices', 'slug' => 'sales.view', 'module' => 'Sales & POS'],
            ['name' => 'Create Wholesale Invoices', 'slug' => 'sales.create', 'module' => 'Sales & POS'],
            ['name' => 'Edit Wholesale Invoices', 'slug' => 'sales.edit', 'module' => 'Sales & POS'],
            ['name' => 'Void & Cancel Invoices', 'slug' => 'sales.void', 'module' => 'Sales & POS'],
            ['name' => 'Process Sales Returns & Credit Notes', 'slug' => 'sales.returns', 'module' => 'Sales & POS'],
            ['name' => 'Manage Sales Orders', 'slug' => 'sales.orders', 'module' => 'Sales & POS'],
            ['name' => 'Manage Delivery Challans', 'slug' => 'sales.challans', 'module' => 'Sales & POS'],
            ['name' => 'Manage Price Quotations', 'slug' => 'sales.quotations', 'module' => 'Sales & POS'],

            // Inventory & Godowns
            ['name' => 'View Stock Balances', 'slug' => 'inventory.view', 'module' => 'Inventory & Godowns'],
            ['name' => 'View Stock Movement Ledger', 'slug' => 'inventory.ledger', 'module' => 'Inventory & Godowns'],
            ['name' => 'Dispatch & Receive Stock Transfers', 'slug' => 'inventory.transfer', 'module' => 'Inventory & Godowns'],
            ['name' => 'Perform Stock Adjustments & Damage', 'slug' => 'inventory.adjust', 'module' => 'Inventory & Godowns'],
            ['name' => 'Conduct Physical Audits & Variance', 'slug' => 'inventory.count', 'module' => 'Inventory & Godowns'],
            ['name' => 'View Products Catalog', 'slug' => 'products.view', 'module' => 'Inventory & Godowns'],
            ['name' => 'Create New Products', 'slug' => 'products.create', 'module' => 'Inventory & Godowns'],
            ['name' => 'Edit Products & Rates', 'slug' => 'products.edit', 'module' => 'Inventory & Godowns'],
            ['name' => 'Delete / Deactivate Products', 'slug' => 'products.delete', 'module' => 'Inventory & Godowns'],
            ['name' => 'Manage Warehouses & Dispatch Locations', 'slug' => 'locations.manage', 'module' => 'Inventory & Godowns'],
            ['name' => 'Manage Product Categories', 'slug' => 'categories.manage', 'module' => 'Inventory & Godowns'],
            ['name' => 'Manage Brands & Companies', 'slug' => 'brands.manage', 'module' => 'Inventory & Godowns'],
            ['name' => 'Manage Units & Conversions', 'slug' => 'units.manage', 'module' => 'Inventory & Godowns'],

            // Purchases & Suppliers
            ['name' => 'View Purchase Orders', 'slug' => 'purchases.view', 'module' => 'Purchases & Suppliers'],
            ['name' => 'Create Purchase Orders', 'slug' => 'purchases.create', 'module' => 'Purchases & Suppliers'],
            ['name' => 'Receive Goods (GRN) & Landed Cost', 'slug' => 'purchases.grn', 'module' => 'Purchases & Suppliers'],
            ['name' => 'Purchase Returns & Debit Notes', 'slug' => 'purchases.returns', 'module' => 'Purchases & Suppliers'],
            ['name' => 'View Suppliers List', 'slug' => 'suppliers.view', 'module' => 'Purchases & Suppliers'],
            ['name' => 'Create New Suppliers', 'slug' => 'suppliers.create', 'module' => 'Purchases & Suppliers'],
            ['name' => 'Edit Supplier Profiles', 'slug' => 'suppliers.edit', 'module' => 'Purchases & Suppliers'],
            ['name' => 'Delete Suppliers', 'slug' => 'suppliers.delete', 'module' => 'Purchases & Suppliers'],

            // Customers & Receivables
            ['name' => 'View Customers List', 'slug' => 'customers.view', 'module' => 'Customers & Receivables'],
            ['name' => 'Create New Customers', 'slug' => 'customers.create', 'module' => 'Customers & Receivables'],
            ['name' => 'Edit Customer Details', 'slug' => 'customers.edit', 'module' => 'Customers & Receivables'],
            ['name' => 'Delete Customers', 'slug' => 'customers.delete', 'module' => 'Customers & Receivables'],
            ['name' => 'View Udhaar Aging Analysis', 'slug' => 'customers.aging', 'module' => 'Customers & Receivables'],
            ['name' => 'Collect Udhaar Receipts', 'slug' => 'recovery.collect', 'module' => 'Customers & Receivables'],
            ['name' => 'Manage & Bounce Cheques', 'slug' => 'cheques.manage', 'module' => 'Customers & Receivables'],

            // Finance & Accounts
            ['name' => 'Record Daily Operating Expenses', 'slug' => 'expenses.manage', 'module' => 'Finance & Accounts'],
            ['name' => 'View Daily Cash Book', 'slug' => 'cashbook.view', 'module' => 'Finance & Accounts'],
            ['name' => 'View Bank Ledger', 'slug' => 'bankbook.view', 'module' => 'Finance & Accounts'],
            ['name' => 'Perform Day-End Counter Closing', 'slug' => 'dayclosing.manage', 'module' => 'Finance & Accounts'],

            // Management & Admin
            ['name' => 'Access 28 Business Reports', 'slug' => 'reports.view', 'module' => 'Management & Admin'],
            ['name' => 'View Cost Prices & Gross Profit', 'slug' => 'reports.profit', 'module' => 'Management & Admin'],
            ['name' => 'Authorize Manager Approvals', 'slug' => 'approvals.decide', 'module' => 'Management & Admin'],
            ['name' => 'Manage Staff Users & Role Permissions', 'slug' => 'users.manage', 'module' => 'Management & Admin'],
            ['name' => 'Company Settings & Database Backups', 'slug' => 'settings.manage', 'module' => 'Management & Admin'],
            ['name' => 'View Activity Audit Logs', 'slug' => 'logs.activity', 'module' => 'Management & Admin'],
            ['name' => 'View User Login History', 'slug' => 'logs.login', 'module' => 'Management & Admin'],
            ['name' => 'Manage Market Areas & Routes', 'slug' => 'areas.manage', 'module' => 'Customers & Receivables'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // 1. Owner / Super Admin (Gets ALL Permissions)
        $owner = Role::where('slug', 'owner')->first();
        if ($owner) {
            $owner->permissions()->sync(Permission::pluck('id')->toArray());
        }

        // 2. Manager Role
        $manager = Role::where('slug', 'manager')->first();
        if ($manager) {
            $managerPerms = Permission::whereIn('slug', [
                'pos.access', 'pos.checkout', 'sales.view', 'sales.create', 'sales.edit', 'sales.void',
                'sales.returns', 'sales.orders', 'sales.challans', 'sales.quotations',
                'inventory.view', 'inventory.ledger', 'inventory.transfer', 'inventory.adjust', 'inventory.count',
                'products.view', 'products.create', 'products.edit',
                'purchases.view', 'purchases.create', 'purchases.grn', 'purchases.returns',
                'suppliers.view', 'suppliers.create', 'suppliers.edit',
                'customers.view', 'customers.create', 'customers.edit', 'customers.aging', 'recovery.collect', 'cheques.manage',
                'expenses.manage', 'cashbook.view', 'bankbook.view', 'dayclosing.manage',
                'reports.view', 'reports.profit', 'approvals.decide',
                'logs.activity', 'logs.login', 'areas.manage',
                'locations.manage', 'categories.manage', 'brands.manage', 'units.manage'
            ])->pluck('id')->toArray();
            $manager->permissions()->sync($managerPerms);
        }

        // 3. Store Keeper Role
        $store = Role::where('slug', 'store_keeper')->first();
        if ($store) {
            $storePerms = Permission::whereIn('slug', [
                'inventory.view', 'inventory.ledger', 'inventory.transfer', 'inventory.adjust', 'inventory.count',
                'products.view', 'purchases.view', 'purchases.grn',
                'locations.manage', 'categories.manage', 'brands.manage', 'units.manage'
            ])->pluck('id')->toArray();
            $store->permissions()->sync($storePerms);
        }

        // 4. Cashier Role
        $cashier = Role::where('slug', 'cashier')->first();
        if ($cashier) {
            $cashierPerms = Permission::whereIn('slug', [
                'pos.access', 'pos.checkout', 'sales.view', 'sales.create', 'inventory.view',
                'customers.view', 'recovery.collect'
            ])->pluck('id')->toArray();
            $cashier->permissions()->sync($cashierPerms);
        }

        // 5. Salesman Role
        $salesman = Role::where('slug', 'salesman')->first();
        if ($salesman) {
            $salesPerms = Permission::whereIn('slug', [
                'sales.view', 'sales.create', 'sales.orders', 'sales.quotations',
                'customers.view', 'customers.create', 'recovery.collect', 'areas.manage'
            ])->pluck('id')->toArray();
            $salesman->permissions()->sync($salesPerms);
        }

        // 6. Purchase Officer Role
        $purchase = Role::where('slug', 'purchase_officer')->first();
        if ($purchase) {
            $purchasePerms = Permission::whereIn('slug', [
                'purchases.view', 'purchases.create', 'purchases.grn', 'purchases.returns',
                'suppliers.view', 'suppliers.create', 'suppliers.edit',
                'inventory.view', 'products.view',
                'locations.manage', 'categories.manage', 'brands.manage', 'units.manage'
            ])->pluck('id')->toArray();
            $purchase->permissions()->sync($purchasePerms);
        }

        // 7. Accountant Role
        $accountant = Role::where('slug', 'accountant')->first();
        if ($accountant) {
            $accPerms = Permission::whereIn('slug', [
                'customers.view', 'customers.aging', 'recovery.collect', 'cheques.manage',
                'suppliers.view', 'expenses.manage', 'cashbook.view', 'bankbook.view',
                'dayclosing.manage', 'reports.view', 'areas.manage'
            ])->pluck('id')->toArray();
            $accountant->permissions()->sync($accPerms);
        }
    }
}
