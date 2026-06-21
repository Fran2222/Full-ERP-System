<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrmFollowUpsTable extends Migration
{
    public function up()
    {
        Schema::create('crm_follow_ups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('crm_lead_id')
                ->constrained('crm_leads')
                ->cascadeOnDelete();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // call, email, meeting, site_visit, quotation_follow_up, payment_follow_up, others
            $table->string('follow_up_type')->default('call');

            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // pending, completed, missed, cancelled
            $table->string('status')->default('pending');

            $table->text('remarks')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['crm_lead_id', 'status']);
            $table->index(['assigned_to', 'scheduled_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('crm_follow_ups');
    }
}