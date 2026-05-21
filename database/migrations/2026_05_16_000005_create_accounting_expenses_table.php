<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountingExpensesTable extends Migration
{
    public function up()
    {
        Schema::create('accounting_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no')->unique();
            $table->date('expense_date');
            $table->foreignId('accounting_bank_account_id')->constrained('accounting_bank_accounts')->cascadeOnDelete();
            $table->foreignId('expense_account_id')->constrained('accounting_accounts')->cascadeOnDelete();
            $table->foreignId('accounting_journal_entry_id')->nullable()->constrained('accounting_journal_entries')->nullOnDelete();
            $table->string('payee')->nullable();
            $table->string('reference_no')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('posted');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamps();

            $table->index(['expense_date', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounting_expenses');
    }
}
