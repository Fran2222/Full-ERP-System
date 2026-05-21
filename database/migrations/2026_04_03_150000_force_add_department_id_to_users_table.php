<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'department_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable();
            });
        }

        $foreignExists = DB::select("
            SELECT constraint_name
            FROM information_schema.table_constraints
            WHERE table_name = 'users'
              AND constraint_type = 'FOREIGN KEY'
              AND constraint_name = 'users_department_id_foreign'
        ");

        if (empty($foreignExists)) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('department_id')
                    ->references('id')
                    ->on('departments')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'department_id')) {
            $foreignExists = DB::select("
                SELECT constraint_name
                FROM information_schema.table_constraints
                WHERE table_name = 'users'
                  AND constraint_type = 'FOREIGN KEY'
                  AND constraint_name = 'users_department_id_foreign'
            ");

            if (!empty($foreignExists)) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropForeign(['department_id']);
                });
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('department_id');
            });
        }
    }
};