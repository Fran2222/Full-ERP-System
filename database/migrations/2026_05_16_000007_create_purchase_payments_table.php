<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();

            $table->string('payment_no')->unique();

            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('supplier_id')->nullable();

            $table->foreignId('accounting_bank_account_id')
                ->constrained('accounting_bank_accounts')
                ->restrictOnDelete();

            $table->foreignId('accounting_journal_entry_id')
                ->nullable()
                ->constrained('accounting_journal_entries')
                ->nullOnDelete();

            $table->date('payment_date');
            $table->string('reference_no')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('description')->nullable();

            $table->string('status')->default('posted');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('void_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['purchase_order_id', 'status']);
            $table->index(['supplier_id', 'status']);
            $table->index(['payment_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};