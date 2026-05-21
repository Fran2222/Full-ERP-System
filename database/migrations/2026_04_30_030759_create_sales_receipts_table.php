<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesReceiptsTable extends Migration
{
    public function up()
    {
        Schema::create('sales_receipts', function (Blueprint $table) {
            $table->id();

            $table->string('receipt_no')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();

            $table->date('receipt_date');
            $table->string('payment_method')->default('Cash');
            $table->string('reference_no')->nullable();

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);

            $table->string('status')->default('paid');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_receipts');
    }
}