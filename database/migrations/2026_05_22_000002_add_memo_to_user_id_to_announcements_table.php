<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMemoToUserIdToAnnouncementsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('announcements', 'memo_to_user_id')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->unsignedBigInteger('memo_to_user_id')->nullable()->after('memo_to');

                $table->foreign('memo_to_user_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('announcements', 'memo_to_user_id')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropForeign(['memo_to_user_id']);
                $table->dropColumn('memo_to_user_id');
            });
        }
    }
}
