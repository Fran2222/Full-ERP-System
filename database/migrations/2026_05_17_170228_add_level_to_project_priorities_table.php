<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddLevelToProjectPrioritiesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('project_priorities') && ! Schema::hasColumn('project_priorities', 'level')) {
            Schema::table('project_priorities', function (Blueprint $table) {
                $table->unsignedTinyInteger('level')->default(1);
            });
        }

        if (Schema::hasTable('project_priorities') && Schema::hasColumn('project_priorities', 'level')) {
            $priorityLevels = [
                'low' => 1,
                'medium' => 2,
                'high' => 3,
                'urgent' => 4,
            ];

            foreach ($priorityLevels as $name => $level) {
                DB::table('project_priorities')
                    ->whereRaw('LOWER(name) = ?', [$name])
                    ->orWhereRaw('LOWER(code) = ?', [$name])
                    ->update(['level' => $level]);
            }

            DB::table('project_priorities')
                ->whereRaw('LOWER(code) = ?', ['med'])
                ->update(['level' => 2]);

            DB::table('project_priorities')
                ->whereRaw('LOWER(code) = ?', ['urg'])
                ->update(['level' => 4]);
        }
    }

    public function down()
    {
        if (Schema::hasTable('project_priorities') && Schema::hasColumn('project_priorities', 'level')) {
            Schema::table('project_priorities', function (Blueprint $table) {
                $table->dropColumn('level');
            });
        }
    }
}