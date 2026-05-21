<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('overtime_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('overtime_requests', 'admin_reviewed_by')) {
                $table->foreignId('admin_reviewed_by')
                    ->nullable()
                    ->after('hr_remarks')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('overtime_requests', 'admin_reviewed_at')) {
                $table->timestamp('admin_reviewed_at')->nullable()->after('admin_reviewed_by');
            }

            if (! Schema::hasColumn('overtime_requests', 'admin_remarks')) {
                $table->text('admin_remarks')->nullable()->after('admin_reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('overtime_requests', function (Blueprint $table) {
            if (Schema::hasColumn('overtime_requests', 'admin_remarks')) {
                $table->dropColumn('admin_remarks');
            }

            if (Schema::hasColumn('overtime_requests', 'admin_reviewed_at')) {
                $table->dropColumn('admin_reviewed_at');
            }

            if (Schema::hasColumn('overtime_requests', 'admin_reviewed_by')) {
                $table->dropConstrainedForeignId('admin_reviewed_by');
            }
        });
    }
};
