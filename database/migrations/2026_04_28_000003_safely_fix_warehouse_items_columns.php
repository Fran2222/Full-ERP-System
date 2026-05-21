<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('warehouse_items')) {
            return;
        }

        Schema::table('warehouse_items', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouse_items', 'code')) {
                $table->string('code')->nullable();
            }
            if (!Schema::hasColumn('warehouse_items', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('warehouse_items', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable();
            }
            if (!Schema::hasColumn('warehouse_items', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable();
            }
            if (!Schema::hasColumn('warehouse_items', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable();
            }
            if (!Schema::hasColumn('warehouse_items', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('warehouse_items', 'cost_price')) {
                $table->decimal('cost_price', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('warehouse_items', 'selling_price')) {
                $table->decimal('selling_price', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('warehouse_items', 'reorder_level')) {
                $table->integer('reorder_level')->default(0);
            }
            if (!Schema::hasColumn('warehouse_items', 'is_serialized')) {
                $table->boolean('is_serialized')->default(false);
            }
            if (!Schema::hasColumn('warehouse_items', 'status')) {
                $table->string('status')->default('active');
            }
        });

        if (Schema::hasColumn('warehouse_items', 'code')) {
            DB::statement("UPDATE warehouse_items SET code = 'ITEM-' || LPAD(id::text, 5, '0') WHERE code IS NULL OR code = ''");
        }
        if (Schema::hasColumn('warehouse_items', 'name')) {
            DB::statement("UPDATE warehouse_items SET name = COALESCE(name, code, 'Unnamed Item') WHERE name IS NULL OR name = ''");
        }
    }

    public function down()
    {
        // intentionally empty to protect existing item data
    }
};
