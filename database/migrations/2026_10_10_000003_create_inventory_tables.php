<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Inventory Batches (FEFO/FIFO tracking)
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('batch_number');
            $table->date('expiry_date')->nullable();
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('quantity', 12, 2)->default(0); // Current quantity in this batch
            $table->string('status')->default('active'); // active, expired, quarantined, depleted
            $table->timestamps();
            $table->index(['product_id', 'location_id', 'expiry_date']);
        });

        // Stock Balances (Cached summary per product & location)
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->decimal('on_hand', 12, 2)->default(0);
            $table->decimal('reserved', 12, 2)->default(0);
            $table->decimal('available', 12, 2)->default(0); // on_hand - reserved
            $table->timestamps();
            $table->unique(['product_id', 'location_id']);
        });

        // Stock Movements (Permanent Immutable Ledger - Golden Rule)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->dateTime('movement_date');
            $table->string('movement_type'); // opening, purchase_grn, sale_invoice, sale_return, purchase_return, transfer_in, transfer_out, adjustment_in, adjustment_out, damage, internal_use
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('inventory_batches')->nullOnDelete();
            $table->decimal('quantity', 12, 2); // Positive for in, negative for out
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->string('reference_type')->nullable(); // GRN, INV, RET, TRF, ADJ
            $table->string('reference_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('balance_after', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'location_id', 'movement_date']);
        });

        // Stock Transfers (Godown <-> Shop)
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('from_location_id')->constrained('locations');
            $table->foreignId('to_location_id')->constrained('locations');
            $table->string('status')->default('draft'); // draft, in_transit, received, cancelled
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('dispatched_at')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('unit_id')->nullable()->constrained('product_units');
            $table->decimal('quantity', 12, 2);
            $table->decimal('base_quantity', 12, 2);
            $table->foreignId('batch_id')->nullable()->constrained('inventory_batches')->nullOnDelete();
            $table->timestamps();
        });

        // Stock Adjustments
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('location_id')->constrained('locations');
            $table->string('type'); // increase, decrease, damage, write_off
            $table->string('status')->default('draft'); // draft, pending_approval, approved, rejected
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained('stock_adjustments')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('batch_id')->nullable()->constrained('inventory_batches')->nullOnDelete();
            $table->decimal('current_qty', 12, 2);
            $table->decimal('adjusted_qty', 12, 2);
            $table->decimal('diff_qty', 12, 2);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->timestamps();
        });

        // Stock Counts (Stock Taking & Variance)
        Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();
            $table->string('count_number')->unique();
            $table->foreignId('location_id')->constrained('locations');
            $table->date('count_date');
            $table->string('status')->default('draft'); // draft, in_progress, completed, approved
            $table->foreignId('conducted_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_count_id')->constrained('stock_counts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('system_qty', 12, 2);
            $table->decimal('counted_qty', 12, 2);
            $table->decimal('variance_qty', 12, 2);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_count_items');
        Schema::dropIfExists('stock_counts');
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_balances');
        Schema::dropIfExists('inventory_batches');
    }
};
