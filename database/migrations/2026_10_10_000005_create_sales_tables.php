<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // POS Shifts
        Schema::create('pos_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_id')->constrained('users');
            $table->foreignId('location_id')->constrained('locations');
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->decimal('opening_cash', 12, 2)->default(0);
            $table->decimal('closing_cash_system', 12, 2)->default(0);
            $table->decimal('closing_cash_counted', 12, 2)->default(0);
            $table->decimal('difference', 12, 2)->default(0);
            $table->string('status')->default('open'); // open, closed
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Quotations
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('salesman_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('quote_date');
            $table->date('valid_until')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->string('status')->default('draft'); // draft, sent, accepted, rejected, converted
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('unit_id')->nullable()->constrained('product_units');
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->timestamps();
        });

        // Sales Orders (Salesmen booking, credit check, reserved stock)
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('salesman_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('location_id')->constrained('locations');
            $table->date('order_date');
            $table->string('status')->default('pending'); // pending, approved, packed, delivered, cancelled
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('delivery_charges', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->boolean('credit_override_approved')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('unit_id')->nullable()->constrained('product_units');
            $table->decimal('quantity', 12, 2);
            $table->decimal('base_quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->boolean('is_reserved')->default(true);
            $table->timestamps();
        });

        // Delivery Challans
        Schema::create('delivery_challans', function (Blueprint $table) {
            $table->id();
            $table->string('challan_number')->unique();
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers');
            $table->date('delivery_date');
            $table->string('vehicle_no')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('status')->default('dispatched'); // dispatched, delivered
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Invoices (Wholesale & Retail POS)
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('invoice_type')->default('wholesale'); // wholesale, retail_pos
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('salesman_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('location_id')->constrained('locations');
            $table->foreignId('pos_shift_id')->nullable()->constrained('pos_shifts')->nullOnDelete();
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
            $table->date('invoice_date');
            $table->string('payment_type')->default('cash'); // cash, credit, mixed, bank, cheque
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('product_discount', 14, 2)->default(0);
            $table->decimal('invoice_discount', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('delivery_charges', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('due_amount', 14, 2)->default(0);
            $table->decimal('cogs_total', 14, 2)->default(0); // Cost of Goods Sold preserved
            $table->decimal('profit_total', 14, 2)->default(0); // Gross Profit
            $table->string('status')->default('posted'); // posted, voided
            $table->text('void_reason')->nullable();
            $table->foreignId('void_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('batch_id')->nullable()->constrained('inventory_batches')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('product_units');
            $table->decimal('quantity', 12, 2);
            $table->decimal('base_quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('unit_cost', 12, 2); // Recorded cost for COGS
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total_price', 14, 2);
            $table->decimal('total_cost', 14, 2);
            $table->decimal('profit', 14, 2);
            $table->timestamps();
        });

        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('payment_method'); // cash, card, bank, cheque
            $table->decimal('amount', 14, 2);
            $table->string('reference_no')->nullable();
            $table->timestamps();
        });

        // Sales Returns & Credit Notes
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('location_id')->constrained('locations');
            $table->date('return_date');
            $table->decimal('refund_amount', 14, 2)->default(0);
            $table->string('return_status')->default('approved'); // pending, approved
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained('sales_returns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('batch_id')->nullable()->constrained('inventory_batches')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('product_units');
            $table->decimal('quantity', 12, 2);
            $table->decimal('base_quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->string('condition')->default('saleable'); // saleable, damaged
            $table->boolean('is_restocked')->default(true);
            $table->timestamps();
        });

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('credit_note_number')->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('sales_return_id')->nullable()->constrained('sales_returns')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('status')->default('issued'); // issued, adjusted
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
        Schema::dropIfExists('invoice_payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('delivery_challans');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('pos_shifts');
    }
};
