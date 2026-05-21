<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            $table->string('po_no')->unique();

            $table->foreignId('supplier_id')
                ->constrained('warehouse_suppliers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('po_date');
            $table->date('expected_date')->nullable();

            $table->foreignId('location_id')
                ->nullable()
                ->constrained('warehouse_locations')
                ->nullOnDelete();

            $table->string('reference_no')->nullable();
            $table->string('ship_via')->nullable();
            $table->string('payment_terms')->nullable();

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->string('status')->default('draft');
            // draft, ordered, partially_received, received, cancelled

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['supplier_id', 'status']);
            $table->index(['po_date', 'expected_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};