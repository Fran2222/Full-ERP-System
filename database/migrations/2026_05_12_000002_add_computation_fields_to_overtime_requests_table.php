<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('overtime_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('overtime_requests', 'overtime_type')) {
                $table->string('overtime_type')->nullable()->after('date_submitted');
            }

            if (! Schema::hasColumn('overtime_requests', 'daily_rate')) {
                $table->decimal('daily_rate', 12, 4)->nullable()->after('rate_per_hour');
            }

            if (! Schema::hasColumn('overtime_requests', 'overtime_multiplier')) {
                $table->decimal('overtime_multiplier', 8, 2)->nullable()->after('daily_rate');
            }

            if (! Schema::hasColumn('overtime_requests', 'night_differential_hours')) {
                $table->decimal('night_differential_hours', 8, 2)->nullable()->after('total_hours');
            }

            if (! Schema::hasColumn('overtime_requests', 'overtime_amount')) {
                $table->decimal('overtime_amount', 12, 4)->nullable()->after('night_differential_hours');
            }

            if (! Schema::hasColumn('overtime_requests', 'night_differential_amount')) {
                $table->decimal('night_differential_amount', 12, 4)->nullable()->after('overtime_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('overtime_requests', function (Blueprint $table) {
            foreach ([
                'night_differential_amount',
                'overtime_amount',
                'night_differential_hours',
                'overtime_multiplier',
                'daily_rate',
                'overtime_type',
            ] as $column) {
                if (Schema::hasColumn('overtime_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
