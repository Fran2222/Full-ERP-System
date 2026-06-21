<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE project_rfps ALTER COLUMN project_id DROP NOT NULL');
    }

    public function down(): void
    {
        // Keep project_id nullable to avoid failing rollback when existing RFP records have no project.
    }
};
