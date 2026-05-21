<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            if (! Schema::hasColumn('positions', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('id');
                $table->foreign('department_id')
                    ->references('id')
                    ->on('departments')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            if (Schema::hasColumn('positions', 'department_id')) {
                try {
                    $table->dropForeign(['department_id']);
                } catch (Throwable $e) {
                    // Ignore if foreign key name differs or does not exist.
                }

                $table->dropColumn('department_id');
            }
        });
    }
};
