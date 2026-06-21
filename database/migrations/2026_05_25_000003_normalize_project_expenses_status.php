<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE project_expenses SET status = 'pending' WHERE status IS NULL OR LOWER(status) NOT IN ('pending', 'liquidated')");
    }

    public function down(): void
    {
        // No rollback needed. Expenses should only use pending/liquidated statuses.
    }
};
