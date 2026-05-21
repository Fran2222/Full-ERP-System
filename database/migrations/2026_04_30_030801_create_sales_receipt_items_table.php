<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesReceiptItemsTable extends Migration
{
    public function up()
    {
        Schema::create('sales_receipt_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sales_receipt_id')->constrained('sales_receipts')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('warehouse_items')->nullOnDelete();

            $table->string('item_code')->nullable();
            $table->string('item_name');
            $table->text('description')->nullable();

            $table->decimal('quantity', 15, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_receipt_items');
    }
}