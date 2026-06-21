<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            if (! Schema::hasColumn('leave_balances', 'adjustment_remarks')) {
                $table->text('adjustment_remarks')->nullable()->after('remaining');
            }

            if (! Schema::hasColumn('leave_balances', 'adjusted_by')) {
                $table->foreignId('adjusted_by')->nullable()->after('adjustment_remarks')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('leave_balances', 'adjusted_at')) {
                $table->timestamp('adjusted_at')->nullable()->after('adjusted_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            if (Schema::hasColumn('leave_balances', 'adjusted_by')) {
                $table->dropConstrainedForeignId('adjusted_by');
            }

            if (Schema::hasColumn('leave_balances', 'adjusted_at')) {
                $table->dropColumn('adjusted_at');
            }

            if (Schema::hasColumn('leave_balances', 'adjustment_remarks')) {
                $table->dropColumn('adjustment_remarks');
            }
        });
    }
};
