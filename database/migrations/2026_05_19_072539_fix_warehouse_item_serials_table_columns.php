<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('warehouse_item_serials')) {
            Schema::create('warehouse_item_serials', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('item_id');
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('location_id')->nullable();
                $table->string('serial_number');
                $table->string('status')->default('available');
                $table->unsignedBigInteger('stock_in_movement_id')->nullable();
                $table->unsignedBigInteger('stock_out_movement_id')->nullable();
                $table->timestamp('issued_at')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->unique(['item_id', 'serial_number'], 'wis_item_serial_unique');
                $table->index(['item_id', 'location_id', 'status'], 'wis_item_location_status_idx');
            });

            return;
        }

        Schema::table('warehouse_item_serials', function (Blueprint $table) {
            if (! Schema::hasColumn('warehouse_item_serials', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('item_id');
            }

            if (! Schema::hasColumn('warehouse_item_serials', 'location_id')) {
                $table->unsignedBigInteger('location_id')->nullable()->after('branch_id');
            }

            if (! Schema::hasColumn('warehouse_item_serials', 'status')) {
                $table->string('status')->default('available')->after('serial_number');
            }

            if (! Schema::hasColumn('warehouse_item_serials', 'stock_in_movement_id')) {
                $table->unsignedBigInteger('stock_in_movement_id')->nullable()->after('status');
            }

            if (! Schema::hasColumn('warehouse_item_serials', 'stock_out_movement_id')) {
                $table->unsignedBigInteger('stock_out_movement_id')->nullable()->after('stock_in_movement_id');
            }

            if (! Schema::hasColumn('warehouse_item_serials', 'issued_at')) {
                $table->timestamp('issued_at')->nullable()->after('stock_out_movement_id');
            }

            if (! Schema::hasColumn('warehouse_item_serials', 'remarks')) {
                $table->text('remarks')->nullable()->after('issued_at');
            }

            if (! Schema::hasColumn('warehouse_item_serials', 'created_at')) {
                $table->timestamps();
            }
        });

        try {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS wis_item_serial_unique ON warehouse_item_serials (item_id, serial_number)');
        } catch (\Throwable $e) {
            // skip if duplicate old data exists
        }

        try {
            DB::statement('CREATE INDEX IF NOT EXISTS wis_item_location_status_idx ON warehouse_item_serials (item_id, location_id, status)');
        } catch (\Throwable $e) {
            // skip index issue safely
        }
    }

    public function down(): void
    {
        // Do not drop columns automatically to protect existing serial records.
    }
};