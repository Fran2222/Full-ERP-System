<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('first_name');
            }

            if (! Schema::hasColumn('users', 'suffix')) {
                $table->string('suffix', 20)->nullable()->after('last_name');
            }
        });

        Schema::table('employee_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_profiles', 'sex_of_birth')) {
                $table->string('sex_of_birth', 20)->nullable()->after('gender');
            }

            if (! Schema::hasColumn('employee_profiles', 'province')) {
                $table->string('province')->nullable()->after('civil_status');
            }

            if (! Schema::hasColumn('employee_profiles', 'city')) {
                $table->string('city')->nullable()->after('province');
            }

            if (! Schema::hasColumn('employee_profiles', 'barangay')) {
                $table->string('barangay')->nullable()->after('city');
            }

            if (! Schema::hasColumn('employee_profiles', 'employee_rate')) {
                $table->decimal('employee_rate', 15, 2)->nullable()->after('salary');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            foreach (['sex_of_birth', 'province', 'city', 'barangay', 'employee_rate'] as $column) {
                if (Schema::hasColumn('employee_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            foreach (['middle_name', 'suffix'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
