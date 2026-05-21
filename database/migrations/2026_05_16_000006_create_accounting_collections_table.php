<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountingCollectionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_collections', function (Blueprint $table) {
            $table->id();
            $table->string('collection_no')->unique();
            $table->date('collection_date');
            $table->foreignId('accounting_bank_account_id')->constrained('accounting_bank_accounts')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('credit_account_id')->constrained('accounting_accounts')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('accounting_journal_entry_id')->nullable()->constrained('accounting_journal_entries')->cascadeOnUpdate()->nullOnDelete();
            $table->string('payer')->nullable();
            $table->string('reference_no')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('posted');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamps();

            $table->index(['collection_date', 'status']);
            $table->index('accounting_bank_account_id');
            $table->index('credit_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_collections');
    }
}
