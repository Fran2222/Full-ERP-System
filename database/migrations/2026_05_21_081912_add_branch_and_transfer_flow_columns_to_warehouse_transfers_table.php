<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBranchAndTransferFlowColumnsToWarehouseTransfersTable extends Migration
{
    public function up()
    {
        Schema::table('warehouse_transfers', function (Blueprint $table) {
            if (! Schema::hasColumn('warehouse_transfers', 'from_branch_id')) {
                $table->unsignedBigInteger('from_branch_id')->nullable()->after('transfer_no');
            }

            if (! Schema::hasColumn('warehouse_transfers', 'to_branch_id')) {
                $table->unsignedBigInteger('to_branch_id')->nullable()->after('from_location_id');
            }

            if (! Schema::hasColumn('warehouse_transfers', 'status')) {
                $table->string('status')->default('draft')->after('remarks');
            }

            if (! Schema::hasColumn('warehouse_transfers', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('status');
            }

            if (! Schema::hasColumn('warehouse_transfers', 'dispatched_by')) {
                $table->unsignedBigInteger('dispatched_by')->nullable()->after('created_by');
            }

            if (! Schema::hasColumn('warehouse_transfers', 'received_by')) {
                $table->unsignedBigInteger('received_by')->nullable()->after('dispatched_by');
            }

            if (! Schema::hasColumn('warehouse_transfers', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable()->after('received_by');
            }

            if (! Schema::hasColumn('warehouse_transfers', 'dispatched_at')) {
                $table->timestamp('dispatched_at')->nullable()->after('cancelled_by');
            }

            if (! Schema::hasColumn('warehouse_transfers', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('dispatched_at');
            }

            if (! Schema::hasColumn('warehouse_transfers', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('received_at');
            }
        });
    }

    public function down()
    {
        Schema::table('warehouse_transfers', function (Blueprint $table) {
            $columns = [
                'from_branch_id',
                'to_branch_id',
                'status',
                'created_by',
                'dispatched_by',
                'received_by',
                'cancelled_by',
                'dispatched_at',
                'received_at',
                'cancelled_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('warehouse_transfers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}