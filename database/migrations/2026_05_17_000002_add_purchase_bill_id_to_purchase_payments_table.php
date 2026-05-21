<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_payments', 'purchase_bill_id')) {
                $table->foreignId('purchase_bill_id')
                    ->nullable()
                    ->after('purchase_order_id')
                    ->constrained('purchase_bills')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_payments', 'purchase_bill_id')) {
                $table->dropConstrainedForeignId('purchase_bill_id');
            }
        });
    }
};
