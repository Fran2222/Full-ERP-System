<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('announcements', 'display_days')) {
                $table->unsignedInteger('display_days')->nullable()->after('published_at');
            }

            if (! Schema::hasColumn('announcements', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('display_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'expires_at')) {
                $table->dropColumn('expires_at');
            }

            if (Schema::hasColumn('announcements', 'display_days')) {
                $table->dropColumn('display_days');
            }
        });
    }
};
