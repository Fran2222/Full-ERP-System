<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('announcements', 'memo_to')) {
                $table->string('memo_to')->nullable()->after('title');
            }

            if (! Schema::hasColumn('announcements', 'memo_from')) {
                $table->string('memo_from')->nullable()->after('memo_to');
            }

            if (! Schema::hasColumn('announcements', 'memo_date')) {
                $table->date('memo_date')->nullable()->after('memo_from');
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'memo_date')) {
                $table->dropColumn('memo_date');
            }

            if (Schema::hasColumn('announcements', 'memo_from')) {
                $table->dropColumn('memo_from');
            }

            if (Schema::hasColumn('announcements', 'memo_to')) {
                $table->dropColumn('memo_to');
            }
        });
    }
};
