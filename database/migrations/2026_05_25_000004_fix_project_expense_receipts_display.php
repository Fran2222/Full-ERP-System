<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE project_expenses SET status = 'pending' WHERE status IS NULL OR LOWER(status) NOT IN ('pending', 'liquidated')");

        if (Schema::hasTable('project_expense_receipts')) {
            DB::statement("
                UPDATE project_expenses pe
                SET receipts_total_amount = COALESCE(x.total_amount, pe.receipts_total_amount)
                FROM (
                    SELECT project_expense_id, SUM(receipts_total_amount) AS total_amount
                    FROM project_expense_receipts
                    GROUP BY project_expense_id
                ) x
                WHERE pe.id = x.project_expense_id
            ");
        }
    }

    public function down(): void
    {
        // No rollback needed.
    }
};
