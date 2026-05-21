<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('warehouse_item_serials')) {
            return;
        }

        Schema::create('warehouse_item_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('warehouse_items')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('warehouse_locations')->nullOnDelete();

            $table->string('serial_number');
            $table->string('status')->default('available');

            $table->foreignId('stock_in_movement_id')
                ->nullable()
                ->constrained('warehouse_stock_movements')
                ->nullOnDelete();

            $table->foreignId('stock_out_movement_id')
                ->nullable()
                ->constrained('warehouse_stock_movements')
                ->nullOnDelete();

            $table->timestamp('issued_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['item_id', 'serial_number']);
            $table->index(['item_id', 'location_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_item_serials');
    }
};