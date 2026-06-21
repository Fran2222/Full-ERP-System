<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceJobOrderItemUsageTables extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('service_job_order_items')) {
            Schema::create('service_job_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_job_order_id')->index();
                $table->unsignedBigInteger('warehouse_item_id')->nullable()->index();
                $table->unsignedBigInteger('warehouse_location_id')->nullable()->index();
                $table->decimal('quantity', 12, 2)->default(0);
                $table->decimal('unit_cost', 14, 2)->default(0);
                $table->decimal('total_cost', 14, 2)->default(0);
                $table->string('usage_type')->default('used');
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('service_job_order_serials')) {
            Schema::create('service_job_order_serials', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_job_order_item_id')->index();
                $table->unsignedBigInteger('warehouse_item_serial_id')->nullable()->index();
                $table->string('serial_no')->nullable();
                $table->string('status')->default('used');
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('service_job_order_serials');
        Schema::dropIfExists('service_job_order_items');
    }
}
