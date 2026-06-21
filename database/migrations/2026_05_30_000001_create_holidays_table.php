<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('holidays')) {
            return;
        }

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

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->index(['holiday_date', 'branch_id', 'is_active']);
            $table->unique(['holiday_date', 'name', 'branch_id'], 'holidays_date_name_branch_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
