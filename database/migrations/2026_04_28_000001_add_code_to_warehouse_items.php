<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('warehouse_items', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouse_items', 'code')) {
                $table->string('code')->nullable();
            }

            if (!Schema::hasColumn('warehouse_items', 'reorder_level')) {
                $table->integer('reorder_level')->default(0);
            }
        });

        // Auto-generate codes
        DB::statement("
            UPDATE warehouse_items 
            SET code = 'ITEM-' || LPAD(id::text, 5, '0')
            WHERE code IS NULL OR code = ''
        ");
    }

    public function down()
    {
        // DO NOTHING (safe)
    }
};