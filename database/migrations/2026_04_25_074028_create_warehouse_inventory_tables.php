<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseInventoryTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('warehouse_inventories')) {
            Schema::create('warehouse_inventories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('item_id')->constrained('warehouse_items')->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
                $table->foreignId('location_id')->constrained('warehouse_locations')->cascadeOnDelete();
                $table->decimal('quantity', 12, 2)->default(0);
                $table->timestamps();
                $table->unique(['item_id', 'branch_id', 'location_id']);
            });
        }

        if (!Schema::hasTable('warehouse_stock_movements')) {
            Schema::create('warehouse_stock_movements', function (Blueprint $table) {
                $table->id();
                $table->string('reference_no')->nullable();
                $table->enum('type', ['IN', 'OUT', 'TRANSFER', 'ADJUSTMENT']);
                $table->foreignId('item_id')->constrained('warehouse_items')->cascadeOnDelete();
                $table->foreignId('from_branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('from_location_id')->nullable()->constrained('warehouse_locations')->nullOnDelete();
                $table->foreignId('to_branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('to_location_id')->nullable()->constrained('warehouse_locations')->nullOnDelete();
                $table->decimal('quantity', 12, 2);
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('warehouse_stock_movements');
        Schema::dropIfExists('warehouse_inventories');
    }
}
 