<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('created_by');
            }

            if (! Schema::hasColumn('purchase_orders', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            if (! Schema::hasColumn('purchase_orders', 'received_by')) {
                $table->unsignedBigInteger('received_by')->nullable()->after('approved_at');
            }

            if (! Schema::hasColumn('purchase_orders', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('received_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            foreach (['received_at', 'received_by', 'approved_at', 'approved_by'] as $column) {
                if (Schema::hasColumn('purchase_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
