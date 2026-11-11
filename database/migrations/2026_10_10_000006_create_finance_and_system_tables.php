<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Customer Receipts (Udhaar recovery)
        Schema::create('customer_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('salesman_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('receipt_date');
            $table->string('payment_method'); // cash, bank, cheque
            $table->decimal('amount', 14, 2);
            $table->string('reference_no')->nullable();
            $table->string('cheque_no')->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('status')->default('cleared'); // cleared, pending, bounced
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('customer_receipt_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_receipt_id')->constrained('customer_receipts')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->decimal('allocated_amount', 14, 2);
            $table->timestamps();
        });

        // Cheques Management
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // received_customer, paid_supplier
            $table->string('cheque_number');
            $table->string('bank_name');
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('amount', 14, 2);
            $table->string('party_type'); // customer, supplier
            $table->unsignedBigInteger('party_id');
            $table->string('status')->default('received'); // received, deposited, cleared, bounced
            $table->text('bounced_reason')->nullable();
            $table->dateTime('bounced_at')->nullable();
            $table->timestamps();
        });

        // Expense Categories & Expenses
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Rent, Electricity, Transport, Salaries, Refreshments, Maintenance
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->foreignId('category_id')->constrained('expense_categories');
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->date('expense_date');
            $table->decimal('amount', 14, 2);
            $table->string('payment_method')->default('cash'); // cash, bank
            $table->string('payee')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Cash Book & Bank Book
        Schema::create('cash_book_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->string('type'); // inflow, outflow
            $table->string('source'); // pos_sale, wholesale_receipt, supplier_payment, expense, opening_float
            $table->string('reference_id')->nullable();
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_after', 14, 2)->default(0);
            $table->text('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('bank_book_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->string('type'); // deposit, withdrawal
            $table->string('bank_name');
            $table->string('reference_id')->nullable();
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_after', 14, 2)->default(0);
            $table->text('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Day End Closing
        Schema::create('day_closings', function (Blueprint $table) {
            $table->id();
            $table->date('closing_date');
            $table->foreignId('location_id')->constrained('locations');
            $table->decimal('total_sales', 14, 2)->default(0);
            $table->decimal('total_purchases', 14, 2)->default(0);
            $table->decimal('total_receipts', 14, 2)->default(0);
            $table->decimal('total_payments', 14, 2)->default(0);
            $table->decimal('total_expenses', 14, 2)->default(0);
            $table->decimal('cash_in_hand', 14, 2)->default(0);
            $table->boolean('is_closed')->default(true);
            $table->foreignId('closed_by')->constrained('users');
            $table->dateTime('closed_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['closing_date', 'location_id']);
        });

        // System Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // low_stock, near_expiry, credit_limit, overdue_60_days, cheque_bounce, approval_pending
            $table->string('title');
            $table->text('message');
            $table->string('url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->dateTime('read_at')->nullable();
            $table->timestamps();
        });

        // Document Sequences (Concurrency Safe Number Generation)
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix')->unique(); // INV, PO, GRN, REC, PAY, TRF, ADJ, RET, CN, EXP, ORD
            $table->unsignedBigInteger('next_number')->default(1);
            $table->timestamps();
        });

        // Backup Logs
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('status')->default('success'); // success, failed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
        Schema::dropIfExists('document_sequences');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('day_closings');
        Schema::dropIfExists('bank_book_entries');
        Schema::dropIfExists('cash_book_entries');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('cheques');
        Schema::dropIfExists('customer_receipt_allocations');
        Schema::dropIfExists('customer_receipts');
    }
};
