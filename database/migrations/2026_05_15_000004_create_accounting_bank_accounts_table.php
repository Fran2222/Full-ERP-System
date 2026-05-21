<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountingBankAccountsTable extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accounting_account_id')
                ->constrained('accounting_accounts')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('name');
            $table->string('type', 50)->default('bank');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
            $table->index('accounting_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_bank_accounts');
    }
}
