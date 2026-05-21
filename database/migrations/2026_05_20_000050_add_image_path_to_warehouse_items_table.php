<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImagePathToWarehouseItemsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('warehouse_items') && ! Schema::hasColumn('warehouse_items', 'image_path')) {
            Schema::table('warehouse_items', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('description');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('warehouse_items') && Schema::hasColumn('warehouse_items', 'image_path')) {
            Schema::table('warehouse_items', function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }
    }
}
