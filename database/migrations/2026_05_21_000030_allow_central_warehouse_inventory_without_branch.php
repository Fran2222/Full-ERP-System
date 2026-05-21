<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('warehouse_inventories') && Schema::hasColumn('warehouse_inventories', 'branch_id')) {
            // PostgreSQL-safe change: allow stock to stay in a central warehouse/location without assigning a branch yet.
            DB::statement('ALTER TABLE warehouse_inventories ALTER COLUMN branch_id DROP NOT NULL');
        }
    }

    public function down(): void
    {
        // Intentionally no-op. Existing central/unassigned stock rows may have NULL branch_id,
        // so forcing NOT NULL again could break rollback on live data.
    }
};
