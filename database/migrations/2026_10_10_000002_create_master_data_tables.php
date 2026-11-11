<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Company Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Categories (with Urdu & English support, hierarchical)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ur')->nullable();
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Brands
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Manufacturers / Companies
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('contact')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Units of Measure
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Carton, Dozen, Piece, Kilogram
            $table->string('short_name'); // ctn, doz, pcs, kg
            $table->boolean('is_base_unit')->default(false);
            $table->timestamps();
        });

        // Warehouses & Locations (Main Godown, Retail Shop)
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Main Godown, Retail Shop
            $table->string('code')->unique(); // GODOWN, SHOP-1
            $table->string('type')->default('godown'); // godown, retail_shop
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Racks & Bins
        Schema::create('racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->timestamps();
        });

        Schema::create('bins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_id')->constrained('racks')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->boolean('is_damaged_bin')->default(false);
            $table->timestamps();
        });

        // Areas / Routes (Karachi distribution)
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Saddar, Jodia Bazaar, Clifton, Gulshan, Korangi
            $table->string('city')->default('Karachi');
            $table->string('route_code')->nullable();
            $table->timestamps();
        });

        // Suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('ntn')->nullable();
            $table->decimal('opening_balance', 14, 2)->default(0); // Payable
            $table->decimal('current_balance', 14, 2)->default(0);
            $table->decimal('credit_limit', 14, 2)->default(0);
            $table->string('payment_terms')->nullable(); // Net 30, Cash
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Customers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('shop_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('assigned_salesman_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_type')->default('wholesale'); // wholesale, retail
            $table->decimal('opening_balance', 14, 2)->default(0); // Receivable
            $table->decimal('current_balance', 14, 2)->default(0);
            $table->decimal('credit_limit', 14, 2)->default(0);
            $table->integer('payment_terms_days')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ur')->nullable(); // Urdu name
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('manufacturer_id')->nullable()->constrained('manufacturers')->nullOnDelete();
            $table->foreignId('base_unit_id')->nullable()->constrained('product_units')->nullOnDelete();
            $table->decimal('purchase_cost', 12, 2)->default(0);
            $table->decimal('retail_price', 12, 2)->default(0);
            $table->decimal('wholesale_price', 12, 2)->default(0);
            $table->decimal('min_margin_percent', 5, 2)->default(5.00); // Minimum margin warning
            $table->decimal('tax_percent', 5, 2)->default(0.00);
            $table->integer('reorder_level')->default(10);
            $table->integer('max_stock')->default(1000);
            $table->boolean('expiry_tracking')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Unit Conversions (e.g. 1 Carton = 12 Dozen = 144 Pieces)
        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->foreignId('from_unit_id')->constrained('product_units');
            $table->foreignId('to_unit_id')->constrained('product_units');
            $table->decimal('conversion_factor', 10, 4); // Multiply from_unit by this factor to get to_unit
            $table->timestamps();
        });

        // Customer Special Prices
        Schema::create('customer_special_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('special_price', 12, 2);
            $table->timestamps();
            $table->unique(['customer_id', 'product_id']);
        });

        // Product Price Change History
        Schema::create('product_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('old_cost', 12, 2)->nullable();
            $table->decimal('new_cost', 12, 2)->nullable();
            $table->decimal('old_retail_price', 12, 2)->nullable();
            $table->decimal('new_retail_price', 12, 2)->nullable();
            $table->decimal('old_wholesale_price', 12, 2)->nullable();
            $table->decimal('new_wholesale_price', 12, 2)->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_history');
        Schema::dropIfExists('customer_special_prices');
        Schema::dropIfExists('unit_conversions');
        Schema::dropIfExists('products');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('bins');
        Schema::dropIfExists('racks');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('product_units');
        Schema::dropIfExists('manufacturers');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('settings');
    }
};
