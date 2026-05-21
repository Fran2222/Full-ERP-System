<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales_receipt_item_serials')) {
            return;
        }

        Schema::create('sales_receipt_item_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_receipt_item_id')
                ->constrained('sales_receipt_items')
                ->cascadeOnDelete();
            $table->foreignId('warehouse_item_serial_id')
                ->constrained('warehouse_item_serials')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['sales_receipt_item_id', 'warehouse_item_serial_id'], 'sris_item_serial_unique');
            $table->index('warehouse_item_serial_id', 'sris_serial_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_receipt_item_serials');
    }
};
