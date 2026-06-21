<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'remarks')) {
                $table->text('remarks')->nullable()->after('address');
            }

            if (!Schema::hasColumn('clients', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('clients', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};