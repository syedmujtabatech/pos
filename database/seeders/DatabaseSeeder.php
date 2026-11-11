<?php

namespace Database\Seeders;

use App\Models\ApprovalRequest;
use App\Models\Area;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\CashBookEntry;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryBatch;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Rack;
use App\Models\Bin;
use App\Models\Role;
use App\Models\Setting;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use App\Models\UnitConversion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $roles = [
            ['name' => 'Owner / Super Admin', 'slug' => 'owner', 'description' => 'Full access to financial, valuation, profit, users & settings'],
            ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Approves orders, stock adjustments, voids, credit limit overrides'],
            ['name' => 'Store Keeper', 'slug' => 'store_keeper', 'description' => 'Goods receipt, dispatch, packing, inventory counts'],
            ['name' => 'Cashier', 'slug' => 'cashier', 'description' => 'POS billing, cash drawer shifts, counter receipts'],
            ['name' => 'Salesman', 'slug' => 'salesman', 'description' => 'Book customer orders, track assigned customer balances & recovery'],
            ['name' => 'Purchase Officer', 'slug' => 'purchase_officer', 'description' => 'Supplier management and purchase order creation'],
            ['name' => 'Accountant', 'slug' => 'accountant', 'description' => 'Receipts, payments, expenses, ledgers, tax & financial reports'],
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['slug' => $r['slug']], $r);
        }

        $ownerRole = Role::where('slug', 'owner')->first();
        $managerRole = Role::where('slug', 'manager')->first();
        $storeKeeperRole = Role::where('slug', 'store_keeper')->first();
        $cashierRole = Role::where('slug', 'cashier')->first();
        $salesmanRole = Role::where('slug', 'salesman')->first();
        $purchaseRole = Role::where('slug', 'purchase_officer')->first();
        $accountantRole = Role::where('slug', 'accountant')->first();

        // 2. Users
        $defaultPassword = Hash::make('password');

        $admin = User::firstOrCreate(['username' => 'admin'], [
            'name' => 'Haji Muhammad Noor',
            'email' => 'admin@alnoortraders.pk',
            'password' => $defaultPassword,
            'role_id' => $ownerRole->id,
            'phone' => '0300-1112233',
            'is_active' => true,
        ]);

        $manager = User::firstOrCreate(['username' => 'manager'], [
            'name' => 'Tariq Mehmood',
            'email' => 'manager@alnoortraders.pk',
            'password' => $defaultPassword,
            'role_id' => $managerRole->id,
            'phone' => '0301-2223344',
            'is_active' => true,
        ]);

        $storekeeper = User::firstOrCreate(['username' => 'storekeeper'], [
            'name' => 'Bilal Ahmed',
            'email' => 'store@alnoortraders.pk',
            'password' => $defaultPassword,
            'role_id' => $storeKeeperRole->id,
            'phone' => '0302-3334455',
            'is_active' => true,
        ]);

        $cashier = User::firstOrCreate(['username' => 'cashier'], [
            'name' => 'Hamza Ali',
            'email' => 'cashier@alnoortraders.pk',
            'password' => $defaultPassword,
            'role_id' => $cashierRole->id,
            'phone' => '0303-4445566',
            'is_active' => true,
        ]);

        $salesman1 = User::firstOrCreate(['username' => 'salesman1'], [
            'name' => 'Imran Khan (Salesman 1)',
            'email' => 'imran@alnoortraders.pk',
            'password' => $defaultPassword,
            'role_id' => $salesmanRole->id,
            'phone' => '0304-5556677',
            'is_active' => true,
        ]);

        $salesman2 = User::firstOrCreate(['username' => 'salesman2'], [
            'name' => 'Kamran Akmal (Salesman 2)',
            'email' => 'kamran@alnoortraders.pk',
            'password' => $defaultPassword,
            'role_id' => $salesmanRole->id,
            'phone' => '0305-6667788',
            'is_active' => true,
        ]);

        $salesman3 = User::firstOrCreate(['username' => 'salesman3'], [
            'name' => 'Zeeshan Siddiqui (Salesman 3)',
            'email' => 'zeeshan@alnoortraders.pk',
            'password' => $defaultPassword,
            'role_id' => $salesmanRole->id,
            'phone' => '0306-7778899',
            'is_active' => true,
        ]);

        $purchaser = User::firstOrCreate(['username' => 'purchaser'], [
            'name' => 'Usman Ghani',
            'email' => 'purchaser@alnoortraders.pk',
            'password' => $defaultPassword,
            'role_id' => $purchaseRole->id,
            'phone' => '0307-8889900',
            'is_active' => true,
        ]);

        $accountant = User::firstOrCreate(['username' => 'accountant'], [
            'name' => 'Farhan Qureshi',
            'email' => 'accountant@alnoortraders.pk',
            'password' => $defaultPassword,
            'role_id' => $accountantRole->id,
            'phone' => '0308-9990011',
            'is_active' => true,
        ]);

        $accountant2 = User::firstOrCreate(['username' => 'accountant2'], [
            'name' => 'Muhammad Bilal (Accountant)',
            'email' => 'bilal.accounts@alnoortraders.pk',
            'password' => $defaultPassword,
            'role_id' => $accountantRole->id,
            'phone' => '0309-1112233',
            'is_active' => true,
        ]);

        // 3. Settings
        $settings = [
            'company_name' => 'Al-Noor Traders',
            'company_name_ur' => 'النور ٹریڈرز',
            'tagline' => 'Wholesale & Retail General Store & FMCG Distribution',
            'address' => 'Plot 42, Wholesale Market, Jodia Bazaar, Karachi, Pakistan',
            'phone' => '+92 21 32410000 / 0300-1112233',
            'email' => 'info@alnoortraders.pk',
            'ntn' => '42301-9876543-1',
            'strn' => '327787654321',
            'currency' => 'PKR',
            'currency_symbol' => 'Rs.',
            'negative_stock_allowed' => '0',
            'cashier_max_discount' => '5',
            'manager_max_discount' => '15',
            'expiry_alert_days' => '90',
            'low_stock_threshold' => '15',
            'default_tax_percent' => '0',
            'invoice_footer_note' => 'Thank you for your business! Maal received check kar ke receive karein.',
            'receipt_printer_type' => 'thermal_80mm',
        ];

        foreach ($settings as $k => $v) {
            Setting::updateOrCreate(['key' => $k], ['value' => $v]);
        }

        // 4. Locations (Godown & Shop)
        $godown = Location::firstOrCreate(['code' => 'GODOWN-01'], [
            'name' => 'Main Godown',
            'type' => 'godown',
            'address' => 'Central Warehouse, Mauripur Road / SITE Area, Karachi',
            'phone' => '021-32551122',
            'is_active' => true,
        ]);

        $shop = Location::firstOrCreate(['code' => 'SHOP-01'], [
            'name' => 'Retail Shop & Wholesale Counter',
            'type' => 'retail_shop',
            'address' => 'Shop 12-14, Main Market, Jodia Bazaar, Karachi',
            'phone' => '021-32410000',
            'is_active' => true,
        ]);

        // Racks & Bins
        $rackA = Rack::firstOrCreate(['location_id' => $godown->id, 'code' => 'RACK-A'], ['name' => 'Section A (Food & Grocery)']);
        $rackB = Rack::firstOrCreate(['location_id' => $godown->id, 'code' => 'RACK-B'], ['name' => 'Section B (Cosmetics & Household)']);
        $bin1 = Bin::firstOrCreate(['rack_id' => $rackA->id, 'code' => 'BIN-A1'], ['name' => 'Shelf A-1', 'is_damaged_bin' => false]);
        $binDamaged = Bin::firstOrCreate(['rack_id' => $rackB->id, 'code' => 'BIN-DMG'], ['name' => 'Damaged / Quarantine Bin', 'is_damaged_bin' => true]);

        // 5. Units
        $carton = ProductUnit::firstOrCreate(['short_name' => 'ctn'], ['name' => 'Carton', 'is_base_unit' => false]);
        $dozen = ProductUnit::firstOrCreate(['short_name' => 'doz'], ['name' => 'Dozen', 'is_base_unit' => false]);
        $piece = ProductUnit::firstOrCreate(['short_name' => 'pcs'], ['name' => 'Piece', 'is_base_unit' => true]);
        $kg = ProductUnit::firstOrCreate(['short_name' => 'kg'], ['name' => 'Kilogram', 'is_base_unit' => true]);
        $pack = ProductUnit::firstOrCreate(['short_name' => 'pack'], ['name' => 'Pack', 'is_base_unit' => false]);

        // 6. Areas (Karachi)
        $areas = [
            ['name' => 'Jodia Bazaar', 'city' => 'Karachi', 'route_code' => 'KHI-JB'],
            ['name' => 'Saddar Market', 'city' => 'Karachi', 'route_code' => 'KHI-SDR'],
            ['name' => 'Clifton & DHA', 'city' => 'Karachi', 'route_code' => 'KHI-CLF'],
            ['name' => 'Gulshan-e-Iqbal', 'city' => 'Karachi', 'route_code' => 'KHI-GLS'],
            ['name' => 'Korangi Industrial & Residential', 'city' => 'Karachi', 'route_code' => 'KHI-KRG'],
            ['name' => 'North Nazimabad', 'city' => 'Karachi', 'route_code' => 'KHI-NNZ'],
            ['name' => 'Kharadar & Mithadar', 'city' => 'Karachi', 'route_code' => 'KHI-KHD'],
            ['name' => 'Malir & Shah Faisal', 'city' => 'Karachi', 'route_code' => 'KHI-MLR'],
        ];

        $areaModels = [];
        foreach ($areas as $a) {
            $areaModels[$a['name']] = Area::firstOrCreate(['name' => $a['name']], $a);
        }

        // 7. Categories
        $categoriesData = [
            ['name' => 'Grocery & Food Grains', 'name_ur' => 'گروسری اور اجناس', 'slug' => 'grocery'],
            ['name' => 'Spices & Recipe Mixes', 'name_ur' => 'مصالحہ جات اور ریسیپی مکس', 'slug' => 'spices'],
            ['name' => 'Tea & Beverages', 'name_ur' => 'چائے اور مشروبات', 'slug' => 'beverages'],
            ['name' => 'Personal Care & Cosmetics', 'name_ur' => 'پرسنل کیئر اور کاسمیٹکس', 'slug' => 'personal-care'],
            ['name' => 'Household & Cleaning', 'name_ur' => 'صفائی اور گھریلو اشیاء', 'slug' => 'cleaning'],
            ['name' => 'Cooking Oil & Ghee', 'name_ur' => 'کوکنگ آئل اور گھی', 'slug' => 'oil-ghee'],
            ['name' => 'Confectionery & Biscuits', 'name_ur' => 'بسکٹ اور کنفیکشنری', 'slug' => 'biscuits'],
        ];

        $catModels = [];
        foreach ($categoriesData as $c) {
            $catModels[$c['slug']] = Category::firstOrCreate(['slug' => $c['slug']], $c);
        }

        // 8. Brands & Manufacturers
        $brandsData = [
            'Shan Foods', 'National Foods', 'Tapal Tea', 'Dalda', 'Habib Oil',
            'Lux', 'Surf Excel', 'Colgate', 'Dettol', 'Peek Freans', 'Sunsilk',
            'Harpic', 'Vim', 'Rooh Afza', 'Olpers', 'Nestle Everyday'
        ];
        $brandModels = [];
        foreach ($brandsData as $b) {
            $slug = strtolower(str_replace(' ', '-', $b));
            $brandModels[$slug] = Brand::firstOrCreate(['slug' => $slug], ['name' => $b]);
        }

        $mfgData = [
            'Unilever Pakistan Limited', 'Shan Foods (Pvt) Ltd', 'National Foods Ltd',
            'Tapal Tea (Pvt) Ltd', 'Reckitt Benckiser Pakistan', 'Colgate-Palmolive Pakistan',
            'English Biscuit Manufacturers (EBM)', 'Dalda Foods Ltd', 'Hamdard Laboratories'
        ];
        $mfgModels = [];
        foreach ($mfgData as $m) {
            $slug = strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $m));
            $mfgModels[$slug] = Manufacturer::firstOrCreate(['slug' => $slug], ['name' => $m]);
        }

        // 9. Suppliers
        $suppliersData = [
            [
                'name' => 'Shan Foods Distributors (Karachi Central)',
                'contact_person' => 'Muhammad Kashif',
                'phone' => '0321-2233445',
                'email' => 'kashif@shanfoods-dist.pk',
                'address' => 'Plot 28, Sector 15, Korangi Industrial Area, Karachi',
                'ntn' => '1234567-1',
                'credit_limit' => 2500000,
                'opening_balance' => 350000,
                'current_balance' => 350000,
                'payment_terms' => 'Net 30 Days',
            ],
            [
                'name' => 'Unilever Pakistan Authorized Agency',
                'contact_person' => 'Adeel Farooq',
                'phone' => '0300-9876543',
                'email' => 'adeel@unileveragency.pk',
                'address' => 'Avari Plaza, Fatima Jinnah Road, Karachi',
                'ntn' => '2345678-2',
                'credit_limit' => 5000000,
                'opening_balance' => 850000,
                'current_balance' => 850000,
                'payment_terms' => 'Net 15 Days',
            ],
            [
                'name' => 'Tapal Tea Distribution House',
                'contact_person' => 'Nadeem Akhter',
                'phone' => '0333-3344556',
                'email' => 'nadeem@tapaldist.pk',
                'address' => 'Plot 40, Korangi Creek Road, Karachi',
                'ntn' => '3456789-3',
                'credit_limit' => 3000000,
                'opening_balance' => 420000,
                'current_balance' => 420000,
                'payment_terms' => 'Net 21 Days',
            ],
            [
                'name' => 'Dalda Foods Supply Co.',
                'contact_person' => 'Rizwan Sheikh',
                'phone' => '0345-4455667',
                'email' => 'rizwan@dalda-supply.pk',
                'address' => 'SITE Area, Karachi',
                'ntn' => '4567890-4',
                'credit_limit' => 4000000,
                'opening_balance' => 600000,
                'current_balance' => 600000,
                'payment_terms' => 'Net 30 Days',
            ],
            [
                'name' => 'Reckitt Benckiser Distribution',
                'contact_person' => 'Salman Zubair',
                'phone' => '0312-5566778',
                'email' => 'salman@reckitt-agency.pk',
                'address' => 'Sharah-e-Faisal, Karachi',
                'ntn' => '5678901-5',
                'credit_limit' => 2000000,
                'opening_balance' => 210000,
                'current_balance' => 210000,
                'payment_terms' => 'Net 15 Days',
            ],
        ];

        $supplierModels = [];
        foreach ($suppliersData as $s) {
            $supplierModels[$s['name']] = Supplier::firstOrCreate(['name' => $s['name']], $s);
        }

        // 10. Customers
        $customersData = [
            [
                'name' => 'Haji Bashir Ahmed',
                'shop_name' => 'Al-Madina Super Store',
                'phone' => '0300-2415161',
                'email' => 'almadina@store.pk',
                'address' => 'Main Bohri Bazaar, Saddar, Karachi',
                'area_id' => $areaModels['Saddar Market']->id,
                'assigned_salesman_id' => $salesman1->id,
                'customer_type' => 'wholesale',
                'opening_balance' => 125000,
                'current_balance' => 125000,
                'credit_limit' => 500000,
                'payment_terms_days' => 30,
            ],
            [
                'name' => 'Sheikh Iqbal',
                'shop_name' => 'Bismillah General Store',
                'phone' => '0321-9988776',
                'email' => 'bismillah@store.pk',
                'address' => 'Daryalal Street, Jodia Bazaar, Karachi',
                'area_id' => $areaModels['Jodia Bazaar']->id,
                'assigned_salesman_id' => $salesman2->id,
                'customer_type' => 'wholesale',
                'opening_balance' => 240000,
                'current_balance' => 240000,
                'credit_limit' => 800000,
                'payment_terms_days' => 30,
            ],
            [
                'name' => 'Chaudhry Arshad',
                'shop_name' => 'Karachi Cash & Carry',
                'phone' => '0333-1122334',
                'email' => 'karachicashcarry@gmail.com',
                'address' => 'Block 13-D, University Road, Gulshan-e-Iqbal, Karachi',
                'area_id' => $areaModels['Gulshan-e-Iqbal']->id,
                'assigned_salesman_id' => $salesman3->id,
                'customer_type' => 'wholesale',
                'opening_balance' => 380000,
                'current_balance' => 380000,
                'credit_limit' => 1000000,
                'payment_terms_days' => 45,
            ],
            [
                'name' => 'Mirza Asim Beg',
                'shop_name' => 'Subhan Allah Mart',
                'phone' => '0301-4433221',
                'email' => 'subhanallah@mart.pk',
                'address' => 'Badar Commercial, Phase 5, DHA, Karachi',
                'area_id' => $areaModels['Clifton & DHA']->id,
                'assigned_salesman_id' => $salesman1->id,
                'customer_type' => 'wholesale',
                'opening_balance' => 95000,
                'current_balance' => 95000,
                'credit_limit' => 600000,
                'payment_terms_days' => 30,
            ],
            [
                'name' => 'Abdul Rasheed',
                'shop_name' => 'New Quetta Tea & General Store',
                'phone' => '0345-8877665',
                'email' => 'quetta@store.pk',
                'address' => 'Kharadar Chowk, Karachi',
                'area_id' => $areaModels['Kharadar & Mithadar']->id,
                'assigned_salesman_id' => $salesman2->id,
                'customer_type' => 'wholesale',
                'opening_balance' => 180000,
                'current_balance' => 180000,
                'credit_limit' => 400000,
                'payment_terms_days' => 30,
            ],
            [
                'name' => 'Counter Cash Customer',
                'shop_name' => 'Walk-in Retail Customer',
                'phone' => '0300-0000000',
                'email' => null,
                'address' => 'Counter Sales, Jodia Bazaar, Karachi',
                'area_id' => $areaModels['Jodia Bazaar']->id,
                'assigned_salesman_id' => null,
                'customer_type' => 'retail',
                'opening_balance' => 0,
                'current_balance' => 0,
                'credit_limit' => 0,
                'payment_terms_days' => 0,
            ],
        ];

        $customerModels = [];
        foreach ($customersData as $c) {
            $customerModels[$c['shop_name']] = Customer::firstOrCreate(['shop_name' => $c['shop_name']], $c);
        }

        // 11. Products
        $productsData = [
            [
                'name' => 'Shan Biryani Masala 50g',
                'name_ur' => 'شان بریانی مصالحہ 50 گرام',
                'sku' => 'SHAN-BM-50',
                'barcode' => '788821001011',
                'category_id' => $catModels['spices']->id,
                'brand_id' => $brandModels['shan-foods']->id,
                'base_unit_id' => $piece->id,
                'purchase_cost' => 110.00,
                'wholesale_price' => 125.00,
                'retail_price' => 140.00,
                'min_margin_percent' => 5.0,
                'reorder_level' => 100,
                'expiry_tracking' => true,
                'godown_qty' => 1200,
                'shop_qty' => 150,
                'batch' => 'BATCH-SHAN-2026A',
                'expiry' => '2027-08-30',
            ],
            [
                'name' => 'National Tomato Ketchup 800g Pouch',
                'name_ur' => 'نیشنل ٹماٹو کیچپ 800 گرام',
                'sku' => 'NAT-KET-800',
                'barcode' => '788821001022',
                'category_id' => $catModels['grocery']->id,
                'brand_id' => $brandModels['national-foods']->id,
                'base_unit_id' => $piece->id,
                'purchase_cost' => 380.00,
                'wholesale_price' => 420.00,
                'retail_price' => 460.00,
                'min_margin_percent' => 5.0,
                'reorder_level' => 50,
                'expiry_tracking' => true,
                'godown_qty' => 600,
                'shop_qty' => 80,
                'batch' => 'BATCH-NAT-2026B',
                'expiry' => '2027-04-15',
            ],
            [
                'name' => 'Tapal Danedar Tea 450g Pouch',
                'name_ur' => 'ٹاپل دانے دار چائے 450 گرام',
                'sku' => 'TAP-DND-450',
                'barcode' => '788821001033',
                'category_id' => $catModels['beverages']->id,
                'brand_id' => $brandModels['tapal-tea']->id,
                'base_unit_id' => $piece->id,
                'purchase_cost' => 680.00,
                'wholesale_price' => 740.00,
                'retail_price' => 790.00,
                'min_margin_percent' => 5.0,
                'reorder_level' => 80,
                'expiry_tracking' => true,
                'godown_qty' => 850,
                'shop_qty' => 120,
                'batch' => 'BATCH-TAP-261',
                'expiry' => '2027-11-20',
            ],
            [
                'name' => 'Dalda Cooking Oil 1 Litre Pouch',
                'name_ur' => 'ڈالڈا کوکنگ آئل 1 لیٹر پاؤچ',
                'sku' => 'DAL-OIL-1L',
                'barcode' => '788821001044',
                'category_id' => $catModels['oil-ghee']->id,
                'brand_id' => $brandModels['dalda']->id,
                'base_unit_id' => $piece->id,
                'purchase_cost' => 510.00,
                'wholesale_price' => 540.00,
                'retail_price' => 565.00,
                'min_margin_percent' => 3.0,
                'reorder_level' => 150,
                'expiry_tracking' => true,
                'godown_qty' => 1500,
                'shop_qty' => 200,
                'batch' => 'BATCH-DAL-998',
                'expiry' => '2027-06-30',
            ],
            [
                'name' => 'Lux Beauty Soap Rose 140g',
                'name_ur' => 'لکس بیوٹی صابن 140 گرام',
                'sku' => 'LUX-ROSE-140',
                'barcode' => '788821001055',
                'category_id' => $catModels['personal-care']->id,
                'brand_id' => $brandModels['lux']->id,
                'base_unit_id' => $piece->id,
                'purchase_cost' => 135.00,
                'wholesale_price' => 150.00,
                'retail_price' => 165.00,
                'min_margin_percent' => 6.0,
                'reorder_level' => 120,
                'expiry_tracking' => true,
                'godown_qty' => 2400,
                'shop_qty' => 300,
                'batch' => 'BATCH-LUX-442',
                'expiry' => '2028-02-15',
            ],
            [
                'name' => 'Surf Excel Washing Powder 1kg Pouch',
                'name_ur' => 'سرف ایکسل واشنگ پاؤڈر 1 کلو',
                'sku' => 'SURF-EXC-1KG',
                'barcode' => '788821001066',
                'category_id' => $catModels['cleaning']->id,
                'brand_id' => $brandModels['surf-excel']->id,
                'base_unit_id' => $piece->id,
                'purchase_cost' => 520.00,
                'wholesale_price' => 570.00,
                'retail_price' => 620.00,
                'min_margin_percent' => 5.0,
                'reorder_level' => 60,
                'expiry_tracking' => false,
                'godown_qty' => 800,
                'shop_qty' => 110,
                'batch' => 'BATCH-SURF-102',
                'expiry' => null,
            ],
            [
                'name' => 'Colgate Maximum Cavity Protection 100g',
                'name_ur' => 'کولگیٹ ٹوتھ پیسٹ 100 گرام',
                'sku' => 'COLG-MCP-100',
                'barcode' => '788821001077',
                'category_id' => $catModels['personal-care']->id,
                'brand_id' => $brandModels['colgate']->id,
                'base_unit_id' => $piece->id,
                'purchase_cost' => 170.00,
                'wholesale_price' => 195.00,
                'retail_price' => 220.00,
                'min_margin_percent' => 7.0,
                'reorder_level' => 90,
                'expiry_tracking' => true,
                'godown_qty' => 1100,
                'shop_qty' => 140,
                'batch' => 'BATCH-COL-701',
                'expiry' => '2027-10-10',
            ],
            [
                'name' => 'Dettol Antiseptic Liquid 250ml',
                'name_ur' => 'ڈیٹول اینٹی سیپٹک 250 ملی',
                'sku' => 'DET-ANT-250',
                'barcode' => '788821001088',
                'category_id' => $catModels['cleaning']->id,
                'brand_id' => $brandModels['dettol']->id,
                'base_unit_id' => $piece->id,
                'purchase_cost' => 310.00,
                'wholesale_price' => 350.00,
                'retail_price' => 390.00,
                'min_margin_percent' => 6.0,
                'reorder_level' => 40,
                'expiry_tracking' => true,
                'godown_qty' => 500,
                'shop_qty' => 70,
                'batch' => 'BATCH-DET-331',
                'expiry' => '2028-05-30',
            ],
            [
                'name' => 'Peek Freans Sooper Biscuit Family Pack',
                'name_ur' => 'سوپر بسکٹ فیملی پیک',
                'sku' => 'SOOP-FAM-PK',
                'barcode' => '788821001099',
                'category_id' => $catModels['biscuits']->id,
                'brand_id' => $brandModels['peek-freans']->id,
                'base_unit_id' => $piece->id,
                'purchase_cost' => 95.00,
                'wholesale_price' => 108.00,
                'retail_price' => 120.00,
                'min_margin_percent' => 5.0,
                'reorder_level' => 150,
                'expiry_tracking' => true,
                'godown_qty' => 1800,
                'shop_qty' => 250,
                'batch' => 'BATCH-SOOP-99',
                'expiry' => '2026-12-31', // Near-expiry demo!
            ],
            [
                'name' => 'Rooh Afza Syrup 800ml Bottle',
                'name_ur' => 'روح افزاء شربت 800 ملی',
                'sku' => 'ROOH-AFZ-800',
                'barcode' => '788821001100',
                'category_id' => $catModels['beverages']->id,
                'brand_id' => $brandModels['rooh-afza']->id,
                'base_unit_id' => $piece->id,
                'purchase_cost' => 390.00,
                'wholesale_price' => 430.00,
                'retail_price' => 470.00,
                'min_margin_percent' => 5.0,
                'reorder_level' => 60,
                'expiry_tracking' => true,
                'godown_qty' => 650,
                'shop_qty' => 90,
                'batch' => 'BATCH-ROOH-01',
                'expiry' => '2027-09-01',
            ],
            [
                'name' => 'Harpic Power Plus 500ml Original',
                'name_ur' => 'ہارپک پاور پلس 500 ملی',
                'sku' => 'HARP-PWR-500',
                'barcode' => '788821001111',
                'category_id' => $catModels['cleaning']->id,
                'brand_id' => $brandModels['harpic']->id,
                'base_unit_id' => $piece->id,
                'purchase_cost' => 240.00,
                'wholesale_price' => 270.00,
                'retail_price' => 300.00,
                'min_margin_percent' => 5.0,
                'reorder_level' => 50,
                'expiry_tracking' => false,
                'godown_qty' => 400,
                'shop_qty' => 60,
                'batch' => 'BATCH-HARP-20',
                'expiry' => null,
            ],
            [
                'name' => 'Shan Korma Masala 50g',
                'name_ur' => 'شان قورمہ مصالحہ 50 گرام',
                'sku' => 'SHAN-KM-50',
                'barcode' => '788821001122',
                'category_id' => $catModels['spices']->id,
                'brand_id' => $brandModels['shan-foods']->id,
                'base_unit_id' => $piece->id,
                'purchase_cost' => 110.00,
                'wholesale_price' => 125.00,
                'retail_price' => 140.00,
                'min_margin_percent' => 5.0,
                'reorder_level' => 80,
                'expiry_tracking' => true,
                'godown_qty' => 900,
                'shop_qty' => 110,
                'batch' => 'BATCH-SHAN-KOR',
                'expiry' => '2027-07-20',
            ],
        ];

        $productModels = [];
        foreach ($productsData as $pData) {
            $godownQty = $pData['godown_qty'];
            $shopQty = $pData['shop_qty'];
            $batchNo = $pData['batch'];
            $expiry = $pData['expiry'];

            unset($pData['godown_qty'], $pData['shop_qty'], $pData['batch'], $pData['expiry']);

            $product = Product::firstOrCreate(['sku' => $pData['sku']], $pData);
            $productModels[$product->sku] = $product;

            // Unit conversions: 1 Carton = 24 Pieces (or 48 depending on item)
            UnitConversion::firstOrCreate([
                'product_id' => $product->id,
                'from_unit_id' => $carton->id,
                'to_unit_id' => $piece->id,
            ], [
                'conversion_factor' => 24.00,
            ]);

            UnitConversion::firstOrCreate([
                'product_id' => $product->id,
                'from_unit_id' => $dozen->id,
                'to_unit_id' => $piece->id,
            ], [
                'conversion_factor' => 12.00,
            ]);

            // Batches & Initial Stock
            // Godown Batch
            $batchGodown = InventoryBatch::firstOrCreate([
                'product_id' => $product->id,
                'location_id' => $godown->id,
                'batch_number' => $batchNo . '-GDN',
            ], [
                'expiry_date' => $expiry,
                'cost_price' => $product->purchase_cost,
                'quantity' => $godownQty,
                'status' => 'active',
            ]);

            StockBalance::updateOrCreate(
                ['product_id' => $product->id, 'location_id' => $godown->id],
                ['on_hand' => $godownQty, 'reserved' => 0, 'available' => $godownQty]
            );

            StockMovement::create([
                'movement_date' => now()->subDays(15),
                'movement_type' => 'opening',
                'product_id' => $product->id,
                'location_id' => $godown->id,
                'batch_id' => $batchGodown->id,
                'quantity' => $godownQty,
                'unit_cost' => $product->purchase_cost,
                'reference_type' => 'OPENING',
                'reference_id' => 'INIT-GDN',
                'user_id' => $admin->id,
                'balance_after' => $godownQty,
                'remarks' => 'Opening balance verified from warehouse physical count',
            ]);

            // Shop Batch
            $batchShop = InventoryBatch::firstOrCreate([
                'product_id' => $product->id,
                'location_id' => $shop->id,
                'batch_number' => $batchNo . '-SHP',
            ], [
                'expiry_date' => $expiry,
                'cost_price' => $product->purchase_cost,
                'quantity' => $shopQty,
                'status' => 'active',
            ]);

            StockBalance::updateOrCreate(
                ['product_id' => $product->id, 'location_id' => $shop->id],
                ['on_hand' => $shopQty, 'reserved' => 0, 'available' => $shopQty]
            );

            StockMovement::create([
                'movement_date' => now()->subDays(15),
                'movement_type' => 'opening',
                'product_id' => $product->id,
                'location_id' => $shop->id,
                'batch_id' => $batchShop->id,
                'quantity' => $shopQty,
                'unit_cost' => $product->purchase_cost,
                'reference_type' => 'OPENING',
                'reference_id' => 'INIT-SHP',
                'user_id' => $admin->id,
                'balance_after' => $shopQty,
                'remarks' => 'Initial retail counter display stock',
            ]);
        }

        // 12. Expense Categories
        $expCats = [
            ['name' => 'Shop Rent', 'description' => 'Jodia Bazaar retail counter monthly rent'],
            ['name' => 'Godown Rent', 'description' => 'Central warehouse monthly rent'],
            ['name' => 'Electricity & Utility Bills', 'description' => 'K-Electric bill and utilities'],
            ['name' => 'Vehicle Fuel & Freight', 'description' => 'Delivery Suzuki van petrol, maintenance and transport charges'],
            ['name' => 'Staff Salaries', 'description' => 'Salesmen commissions and staff basic salaries'],
            ['name' => 'Tea, Refreshment & Meals', 'description' => 'Daily staff tea, biscuits and customer refreshments'],
            ['name' => 'Packaging & Stationery', 'description' => 'Boxes, tapes, bill books, barcode paper rolls'],
            ['name' => 'Shop & Godown Maintenance', 'description' => 'Lighting, shelves repair and shutter maintenance'],
        ];

        $expCatModels = [];
        foreach ($expCats as $ec) {
            $expCatModels[$ec['name']] = ExpenseCategory::firstOrCreate(['name' => $ec['name']], $ec);
        }

        // Sample Expenses
        Expense::create([
            'expense_number' => 'EXP-00001',
            'category_id' => $expCatModels['Tea, Refreshment & Meals']->id,
            'location_id' => $shop->id,
            'expense_date' => now()->toDateString(),
            'amount' => 1250.00,
            'payment_method' => 'cash',
            'payee' => 'Quetta Al-Madina Hotel',
            'description' => 'Daily morning and evening tea for staff and visiting wholesale customers',
            'created_by' => $cashier->id,
        ]);

        Expense::create([
            'expense_number' => 'EXP-00002',
            'category_id' => $expCatModels['Vehicle Fuel & Freight']->id,
            'location_id' => $godown->id,
            'expense_date' => now()->toDateString(),
            'amount' => 4500.00,
            'payment_method' => 'cash',
            'payee' => 'PSO Petrol Pump Mauripur',
            'description' => 'Fuel for delivery van (Route: Saddar & Clifton deliveries)',
            'created_by' => $manager->id,
        ]);

        CashBookEntry::record('outflow', 1250.00, 'expense', 'EXP-00001', 'Staff tea refreshment');
        CashBookEntry::record('outflow', 4500.00, 'expense', 'EXP-00002', 'Van fuel delivery charges');

        // 13. Sample Wholesale Invoice
        $cMadina = $customerModels['Al-Madina Super Store'];
        $pBiryani = $productModels['SHAN-BM-50'];
        $pTea = $productModels['TAP-DND-450'];
        $pOil = $productModels['DAL-OIL-1L'];

        $inv1 = Invoice::create([
            'invoice_number' => 'INV-00001',
            'invoice_type' => 'wholesale',
            'customer_id' => $cMadina->id,
            'salesman_id' => $salesman1->id,
            'location_id' => $godown->id,
            'invoice_date' => now()->subDays(2)->toDateString(),
            'payment_type' => 'credit',
            'subtotal' => 61000.00,
            'product_discount' => 1000.00,
            'invoice_discount' => 0.00,
            'tax' => 0.00,
            'delivery_charges' => 500.00,
            'grand_total' => 60500.00,
            'paid_amount' => 20000.00,
            'due_amount' => 40500.00,
            'cogs_total' => 53400.00,
            'profit_total' => 7100.00,
            'status' => 'posted',
            'created_by' => $salesman1->id,
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv1->id,
            'product_id' => $pBiryani->id,
            'quantity' => 100,
            'base_quantity' => 100,
            'unit_price' => 125.00,
            'unit_cost' => 110.00,
            'discount' => 500.00,
            'tax' => 0.00,
            'total_price' => 12000.00,
            'total_cost' => 11000.00,
            'profit' => 1000.00,
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv1->id,
            'product_id' => $pTea->id,
            'quantity' => 40,
            'base_quantity' => 40,
            'unit_price' => 740.00,
            'unit_cost' => 680.00,
            'discount' => 500.00,
            'tax' => 0.00,
            'total_price' => 29100.00,
            'total_cost' => 27200.00,
            'profit' => 1900.00,
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv1->id,
            'product_id' => $pOil->id,
            'quantity' => 36,
            'base_quantity' => 36,
            'unit_price' => 540.00,
            'unit_cost' => 510.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'total_price' => 19440.00,
            'total_cost' => 18360.00,
            'profit' => 1080.00,
        ]);

        InvoicePayment::create([
            'invoice_id' => $inv1->id,
            'payment_method' => 'cash',
            'amount' => 20000.00,
            'reference_no' => 'CASH-ADV',
        ]);

        CashBookEntry::record('inflow', 20000.00, 'wholesale_receipt', 'INV-00001', 'Partial payment on delivery from Al-Madina Super Store');

        // Deduct inventory movement
        StockMovement::recordMovement($pBiryani->id, $godown->id, -100, 'sale_invoice', 'INV', 'INV-00001', $pBiryani->purchase_cost, null, 'Sale to Al-Madina Super Store', $salesman1->id);
        StockMovement::recordMovement($pTea->id, $godown->id, -40, 'sale_invoice', 'INV', 'INV-00001', $pTea->purchase_cost, null, 'Sale to Al-Madina Super Store', $salesman1->id);
        StockMovement::recordMovement($pOil->id, $godown->id, -36, 'sale_invoice', 'INV', 'INV-00001', $pOil->purchase_cost, null, 'Sale to Al-Madina Super Store', $salesman1->id);

        // Update customer balance: added due amount 40,500
        $cMadina->current_balance += 40500.00;
        $cMadina->save();

        // 14. Sample Retail POS Invoice
        $cRetail = $customerModels['Walk-in Retail Customer'];
        $pSoap = $productModels['LUX-ROSE-140'];
        $pBiscuit = $productModels['SOOP-FAM-PK'];

        $inv2 = Invoice::create([
            'invoice_number' => 'POS-00001',
            'invoice_type' => 'retail_pos',
            'customer_id' => $cRetail->id,
            'salesman_id' => null,
            'location_id' => $shop->id,
            'invoice_date' => now()->toDateString(),
            'payment_type' => 'cash',
            'subtotal' => 810.00,
            'product_discount' => 0.00,
            'invoice_discount' => 10.00,
            'tax' => 0.00,
            'delivery_charges' => 0.00,
            'grand_total' => 800.00,
            'paid_amount' => 800.00,
            'due_amount' => 0.00,
            'cogs_total' => 660.00,
            'profit_total' => 140.00,
            'status' => 'posted',
            'created_by' => $cashier->id,
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv2->id,
            'product_id' => $pSoap->id,
            'quantity' => 3,
            'base_quantity' => 3,
            'unit_price' => 165.00,
            'unit_cost' => 135.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'total_price' => 495.00,
            'total_cost' => 405.00,
            'profit' => 90.00,
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv2->id,
            'product_id' => $pBiscuit->id,
            'quantity' => 2,
            'base_quantity' => 2,
            'unit_price' => 120.00,
            'unit_cost' => 95.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'total_price' => 240.00,
            'total_cost' => 190.00,
            'profit' => 50.00,
        ]);

        InvoicePayment::create([
            'invoice_id' => $inv2->id,
            'payment_method' => 'cash',
            'amount' => 800.00,
            'reference_no' => 'COUNTER-CASH',
        ]);

        CashBookEntry::record('inflow', 800.00, 'pos_sale', 'POS-00001', 'Retail Counter sale POS-00001');

        StockMovement::recordMovement($pSoap->id, $shop->id, -3, 'sale_invoice', 'INV', 'POS-00001', $pSoap->purchase_cost, null, 'Counter retail sale', $cashier->id);
        StockMovement::recordMovement($pBiscuit->id, $shop->id, -2, 'sale_invoice', 'INV', 'POS-00001', $pBiscuit->purchase_cost, null, 'Counter retail sale', $cashier->id);

        // 15. Sample Purchase Order & GRN
        $sShan = $supplierModels['Shan Foods Distributors (Karachi Central)'];
        $po1 = PurchaseOrder::create([
            'po_number' => 'PO-00001',
            'supplier_id' => $sShan->id,
            'location_id' => $godown->id,
            'order_date' => now()->subDays(5)->toDateString(),
            'expected_date' => now()->addDays(2)->toDateString(),
            'status' => 'received',
            'subtotal' => 110000.00,
            'discount' => 2000.00,
            'tax' => 0.00,
            'total' => 108000.00,
            'notes' => 'Urgent replenishment for Biryani and Korma masalas',
            'created_by' => $purchaser->id,
            'approved_by' => $manager->id,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po1->id,
            'product_id' => $pBiryani->id,
            'quantity' => 500,
            'base_quantity' => 500,
            'unit_cost' => 110.00,
            'discount' => 1000.00,
            'tax' => 0.00,
            'total' => 54000.00,
            'received_quantity' => 500,
        ]);

        $grn1 = GoodsReceipt::create([
            'grn_number' => 'GRN-00001',
            'purchase_order_id' => $po1->id,
            'supplier_id' => $sShan->id,
            'location_id' => $godown->id,
            'received_date' => now()->subDays(3)->toDateString(),
            'status' => 'posted',
            'freight_charge' => 1500.00,
            'other_charges' => 0.00,
            'landed_cost_total' => 109500.00,
            'notes' => 'Received in good sealed condition',
            'received_by' => $storekeeper->id,
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id' => $grn1->id,
            'product_id' => $pBiryani->id,
            'received_qty' => 500,
            'base_qty' => 500,
            'unit_cost' => 110.00,
            'allocated_landed_cost' => 3.00, // allocated freight per unit
            'batch_number' => 'BATCH-SHAN-2026A-NEW',
            'expiry_date' => '2027-09-30',
            'total_cost' => 56500.00,
        ]);

        SupplierInvoice::create([
            'invoice_number' => 'SUP-INV-8821',
            'goods_receipt_id' => $grn1->id,
            'supplier_id' => $sShan->id,
            'invoice_date' => now()->subDays(3)->toDateString(),
            'due_date' => now()->addDays(27)->toDateString(),
            'subtotal' => 108000.00,
            'freight' => 1500.00,
            'tax' => 0.00,
            'total_amount' => 109500.00,
            'paid_amount' => 50000.00,
            'balance_amount' => 59500.00,
            'status' => 'partial',
        ]);

        SupplierPayment::create([
            'payment_number' => 'PAY-00001',
            'supplier_id' => $sShan->id,
            'payment_date' => now()->subDays(1)->toDateString(),
            'payment_method' => 'bank',
            'amount' => 50000.00,
            'reference_no' => 'HBL-ONLINE-99281',
            'status' => 'completed',
            'notes' => 'Cheque/Online transfer against SUP-INV-8821',
            'created_by' => $accountant->id,
        ]);

        // 16. Notifications
        Notification::create([
            'type' => 'near_expiry',
            'title' => 'Near Expiry Alert: Peek Freans Sooper Biscuit',
            'message' => 'Peek Freans Sooper Biscuit Family Pack (Batch BATCH-SOOP-99) expires on 2026-12-31. Plan clearance or supplier return.',
            'url' => '/reports?report=near_expiry',
            'is_read' => false,
        ]);

        Notification::create([
            'type' => 'low_stock',
            'title' => 'Low Stock Alert: Dettol Antiseptic 250ml',
            'message' => 'Current stock at Retail Shop has reached 70 units (Reorder point: 40). Create purchase order soon.',
            'url' => '/inventory',
            'is_read' => false,
        ]);

        Notification::create([
            'type' => 'approval_pending',
            'title' => 'Pending Approval: Special Credit Exception',
            'message' => 'Salesman Imran Khan requested Rs. 50,000 credit limit override for Subhan Allah Mart.',
            'url' => '/approvals',
            'is_read' => false,
        ]);

        // 17. Sample Approval Request
        ApprovalRequest::create([
            'request_type' => 'credit_limit_override',
            'requested_by' => $salesman1->id,
            'approver_id' => null,
            'status' => 'pending',
            'reference_type' => 'Customer',
            'reference_id' => $customerModels['Subhan Allah Mart']->id,
            'data' => [
                'customer_name' => 'Subhan Allah Mart',
                'current_balance' => 95000,
                'credit_limit' => 600000,
                'requested_increase' => 100000,
            ],
            'reason' => 'Customer is opening a new branch and needs extra carton stock on 30-day credit',
        ]);

        // 18. Audit Log
        AuditLog::log('system_seed', null, null, ['status' => 'initialized'], 'System database seeded with complete SRS master data, stock, and business parameters.');
    }
}
