<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('warehouse_locations')) {
            return;
        }

        Schema::table('warehouse_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouse_locations', 'name')) {
                $table->string('name')->nullable();
            }

            if (!Schema::hasColumn('warehouse_locations', 'status')) {
                $table->boolean('status')->default(true);
            }
        });

        if (Schema::hasColumn('warehouse_locations', 'location_name')) {
            DB::statement("UPDATE warehouse_locations SET name = location_name WHERE name IS NULL OR name = ''");
        }

        if (Schema::hasColumn('warehouse_locations', 'warehouse_name')) {
            DB::statement("UPDATE warehouse_locations SET name = warehouse_name WHERE name IS NULL OR name = ''");
        }

        DB::statement("UPDATE warehouse_locations SET name = 'Location-' || LPAD(id::text, 5, '0') WHERE name IS NULL OR name = ''");
    }

    public function down()
    {
        // Safe rollback: do nothing to avoid losing existing warehouse location data.
    }
};
