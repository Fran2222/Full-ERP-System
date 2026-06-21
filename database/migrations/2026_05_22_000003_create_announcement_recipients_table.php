<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncementRecipientsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('announcement_recipients')) {
            Schema::create('announcement_recipients', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('announcement_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                $table->unique(['announcement_id', 'user_id'], 'announcement_recipient_unique');

                $table->foreign('announcement_id')
                    ->references('id')
                    ->on('announcements')
                    ->cascadeOnDelete();

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();
            });
        }

        // Migrate existing one-employee announcements into the new multiple-recipient table.
        if (Schema::hasColumn('announcements', 'memo_to_user_id')) {
            $now = now();

            DB::table('announcements')
                ->whereNotNull('memo_to_user_id')
                ->orderBy('id')
                ->chunkById(100, function ($announcements) use ($now) {
                    $rows = [];

                    foreach ($announcements as $announcement) {
                        $rows[] = [
                            'announcement_id' => $announcement->id,
                            'user_id' => $announcement->memo_to_user_id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    if (! empty($rows)) {
                        DB::table('announcement_recipients')->insertOrIgnore($rows);
                    }
                });
        }
    }

    public function down()
    {
        Schema::dropIfExists('announcement_recipients');
    }
}
