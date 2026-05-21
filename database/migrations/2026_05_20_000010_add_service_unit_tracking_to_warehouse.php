<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('warehouse_items') && ! Schema::hasColumn('warehouse_items', 'is_service_unit')) {
            Schema::table('warehouse_items', function (Blueprint $table) {
                $table->boolean('is_service_unit')->default(false)->after('is_serialized');
            });
        }

        if (! Schema::hasTable('warehouse_service_unit_borrows')) {
            Schema::create('warehouse_service_unit_borrows', function (Blueprint $table) {
                $table->id();
                $table->string('borrow_no')->unique();
                $table->foreignId('employee_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('warehouse_items')->cascadeOnDelete();
                $table->foreignId('serial_id')->constrained('warehouse_item_serials')->cascadeOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('location_id')->nullable()->constrained('warehouse_locations')->nullOnDelete();
                $table->date('borrowed_at');
                $table->date('expected_return_at')->nullable();
                $table->timestamp('returned_at')->nullable();
                $table->string('status')->default('active')->index();
                $table->string('condition_out')->nullable();
                $table->string('condition_in')->nullable();
                $table->text('purpose')->nullable();
                $table->text('remarks')->nullable();
                $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['employee_user_id', 'status']);
                $table->index(['item_id', 'serial_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_service_unit_borrows');

        if (Schema::hasTable('warehouse_items') && Schema::hasColumn('warehouse_items', 'is_service_unit')) {
            Schema::table('warehouse_items', function (Blueprint $table) {
                $table->dropColumn('is_service_unit');
            });
        }
    }
};
