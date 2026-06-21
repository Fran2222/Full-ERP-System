<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_profiles', 'spouse_name')) {
                $table->string('spouse_name')->nullable()->after('civil_status');
            }

            if (! Schema::hasColumn('employee_profiles', 'father_name')) {
                $table->string('father_name')->nullable()->after('spouse_name');
            }

            if (! Schema::hasColumn('employee_profiles', 'mother_name')) {
                $table->string('mother_name')->nullable()->after('father_name');
            }

            if (! Schema::hasColumn('employee_profiles', 'highest_education_attainment')) {
                $table->string('highest_education_attainment', 100)->nullable()->after('mother_name');
            }

            if (! Schema::hasColumn('employee_profiles', 'course')) {
                $table->string('course')->nullable()->after('highest_education_attainment');
            }

            if (! Schema::hasColumn('employee_profiles', 'school')) {
                $table->string('school')->nullable()->after('course');
            }

            if (! Schema::hasColumn('employee_profiles', 'year_graduated')) {
                $table->unsignedSmallInteger('year_graduated')->nullable()->after('school');
            }

            if (! Schema::hasColumn('employee_profiles', 'regularization_date')) {
                $table->date('regularization_date')->nullable()->after('hire_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            foreach ([
                'regularization_date',
                'year_graduated',
                'school',
                'course',
                'highest_education_attainment',
                'mother_name',
                'father_name',
                'spouse_name',
            ] as $column) {
                if (Schema::hasColumn('employee_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
