<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->constrained('warehouse_items')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('item_code')->nullable();
            $table->string('item_name');
            $table->text('description')->nullable();

            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('received_quantity', 15, 2)->default(0);

            $table->string('unit_name')->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);

            $table->timestamps();

            $table->index(['purchase_order_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};