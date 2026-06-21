<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'supplier_arrived_at')) {
                $table->timestamp('supplier_arrived_at')->nullable()->after('approved_at');
            }
            if (! Schema::hasColumn('purchase_orders', 'supplier_arrived_by')) {
                $table->unsignedBigInteger('supplier_arrived_by')->nullable()->after('supplier_arrived_at');
            }
            if (! Schema::hasColumn('purchase_orders', 'supplier_arrival_notes')) {
                $table->text('supplier_arrival_notes')->nullable()->after('supplier_arrived_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'supplier_arrival_notes')) {
                $table->dropColumn('supplier_arrival_notes');
            }
            if (Schema::hasColumn('purchase_orders', 'supplier_arrived_by')) {
                $table->dropColumn('supplier_arrived_by');
            }
            if (Schema::hasColumn('purchase_orders', 'supplier_arrived_at')) {
                $table->dropColumn('supplier_arrived_at');
            }
        });
    }
};