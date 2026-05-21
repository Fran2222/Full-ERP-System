<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseTransferWorkflowTables extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('warehouse_transfers')) {
            Schema::create('warehouse_transfers', function (Blueprint $table) {
                $table->id();
                $table->string('transfer_no')->unique();
                $table->unsignedBigInteger('from_branch_id')->nullable();
                $table->unsignedBigInteger('from_location_id');
                $table->unsignedBigInteger('to_branch_id')->nullable();
                $table->unsignedBigInteger('to_location_id');
                $table->string('status')->default('draft');
                $table->date('transfer_date')->nullable();
                $table->timestamp('dispatched_at')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('dispatched_by')->nullable();
                $table->unsignedBigInteger('received_by')->nullable();
                $table->unsignedBigInteger('cancelled_by')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['status', 'transfer_date']);
                $table->index(['from_branch_id', 'from_location_id']);
                $table->index(['to_branch_id', 'to_location_id']);
            });
        }

        if (! Schema::hasTable('warehouse_transfer_items')) {
            Schema::create('warehouse_transfer_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('transfer_id');
                $table->unsignedBigInteger('item_id');
                $table->decimal('quantity', 15, 2)->default(0);
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['transfer_id', 'item_id']);
            });
        }

        if (! Schema::hasTable('warehouse_transfer_item_serials')) {
            Schema::create('warehouse_transfer_item_serials', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('transfer_item_id');
                $table->unsignedBigInteger('warehouse_item_serial_id');
                $table->timestamps();

                $table->unique(['transfer_item_id', 'warehouse_item_serial_id'], 'wtis_unique_serial_per_line');
                $table->index('warehouse_item_serial_id');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('warehouse_transfer_item_serials');
        Schema::dropIfExists('warehouse_transfer_items');
        Schema::dropIfExists('warehouse_transfers');
    }
}
