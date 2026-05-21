<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_bills')) {
            return;
        }

        Schema::create('purchase_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_no')->unique();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('warehouse_suppliers')->restrictOnDelete();
            $table->foreignId('accounting_journal_entry_id')->nullable()->constrained('accounting_journal_entries')->nullOnDelete();
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->string('reference_no')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('posted'); // posted, voided
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('void_reason', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['purchase_order_id', 'status']);
            $table->index(['supplier_id', 'status']);
            $table->index('bill_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_bills');
    }
};
