<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('holidays')) {
            Schema::create('holidays', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->date('holiday_date');
                $table->string('type')->default('regular');
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->boolean('is_paid')->default(true);
                $table->boolean('is_active')->default(true);
                $table->text('remarks')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('holidays', function (Blueprint $table) {
            if (!Schema::hasColumn('holidays', 'name')) {
                $table->string('name')->nullable();
            }

            if (!Schema::hasColumn('holidays', 'holiday_date')) {
                $table->date('holiday_date')->nullable();
            }

            if (!Schema::hasColumn('holidays', 'type')) {
                $table->string('type')->default('regular');
            }

            if (!Schema::hasColumn('holidays', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable();
            }

            if (!Schema::hasColumn('holidays', 'is_paid')) {
                $table->boolean('is_paid')->default(true);
            }

            if (!Schema::hasColumn('holidays', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }

            if (!Schema::hasColumn('holidays', 'remarks')) {
                $table->text('remarks')->nullable();
            }
        });

        if (Schema::hasColumn('holidays', 'date') && Schema::hasColumn('holidays', 'holiday_date')) {
            try {
                DB::statement('UPDATE holidays SET holiday_date = "date" WHERE holiday_date IS NULL AND "date" IS NOT NULL');
            } catch (\Throwable $exception) {
                // Ignore compatibility differences; new records from the HR Holidays form use holiday_date.
            }
        }

        if (Schema::hasColumn('holidays', 'title') && Schema::hasColumn('holidays', 'name')) {
            try {
                DB::statement('UPDATE holidays SET name = title WHERE name IS NULL AND title IS NOT NULL');
            } catch (\Throwable $exception) {
                // Ignore compatibility differences; new records from the HR Holidays form use name.
            }
        }
    }

    public function down(): void
    {
        // Safe no-op. This migration only ensures compatibility columns exist.
    }
};
