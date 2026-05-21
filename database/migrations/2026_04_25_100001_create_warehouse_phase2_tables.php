<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehousePhase2Tables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('warehouse_categories')) {
            Schema::create('warehouse_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('warehouse_units')) {
            Schema::create('warehouse_units', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('symbol')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('warehouse_suppliers')) {
            Schema::create('warehouse_suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('contact_person')->nullable();
                $table->string('contact_number')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('warehouse_locations')) {
            Schema::create('warehouse_locations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('warehouse_items')) {
            Schema::create('warehouse_items', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->unsignedBigInteger('category_id')->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->text('description')->nullable();
                $table->decimal('cost_price', 12, 2)->default(0);
                $table->decimal('selling_price', 12, 2)->default(0);
                $table->integer('reorder_level')->default(0);
                $table->boolean('is_serialized')->default(false);
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('warehouse_items');
        Schema::dropIfExists('warehouse_locations');
        Schema::dropIfExists('warehouse_suppliers');
        Schema::dropIfExists('warehouse_units');
        Schema::dropIfExists('warehouse_categories');
    }
}
