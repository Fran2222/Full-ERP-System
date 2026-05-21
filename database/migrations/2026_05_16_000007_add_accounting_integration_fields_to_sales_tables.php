<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountingIntegrationFieldsToSalesTables extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'accounting_journal_entry_id')) {
                $table->foreignId('accounting_journal_entry_id')
                    ->nullable()
                    ->after('created_by')
                    ->constrained('accounting_journal_entries')
                    ->nullOnDelete();
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'accounting_bank_account_id')) {
                $table->foreignId('accounting_bank_account_id')
                    ->nullable()
                    ->after('created_by')
                    ->constrained('accounting_bank_accounts')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('payments', 'accounting_journal_entry_id')) {
                $table->foreignId('accounting_journal_entry_id')
                    ->nullable()
                    ->after('accounting_bank_account_id')
                    ->constrained('accounting_journal_entries')
                    ->nullOnDelete();
            }
        });

        Schema::table('sales_receipts', function (Blueprint $table) {
            if (! Schema::hasColumn('sales_receipts', 'accounting_bank_account_id')) {
                $table->foreignId('accounting_bank_account_id')
                    ->nullable()
                    ->after('created_by')
                    ->constrained('accounting_bank_accounts')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('sales_receipts', 'accounting_journal_entry_id')) {
                $table->foreignId('accounting_journal_entry_id')
                    ->nullable()
                    ->after('accounting_bank_account_id')
                    ->constrained('accounting_journal_entries')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('sales_receipts', 'accounting_journal_entry_id')) {
                $table->dropConstrainedForeignId('accounting_journal_entry_id');
            }

            if (Schema::hasColumn('sales_receipts', 'accounting_bank_account_id')) {
                $table->dropConstrainedForeignId('accounting_bank_account_id');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'accounting_journal_entry_id')) {
                $table->dropConstrainedForeignId('accounting_journal_entry_id');
            }

            if (Schema::hasColumn('payments', 'accounting_bank_account_id')) {
                $table->dropConstrainedForeignId('accounting_bank_account_id');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'accounting_journal_entry_id')) {
                $table->dropConstrainedForeignId('accounting_journal_entry_id');
            }
        });
    }
}
