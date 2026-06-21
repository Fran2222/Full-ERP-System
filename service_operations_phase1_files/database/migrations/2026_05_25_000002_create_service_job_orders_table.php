<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceJobOrdersTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('service_job_orders')) {
            Schema::create('service_job_orders', function (Blueprint $table) {
                $table->id();
                $table->string('job_order_no')->unique();
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->unsignedBigInteger('service_type_id')->nullable()->index();
                $table->unsignedBigInteger('service_status_id')->nullable()->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->unsignedBigInteger('assigned_to_user_id')->nullable()->index();
                $table->unsignedBigInteger('vehicle_id')->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('updated_by')->nullable()->index();
                $table->string('subject');
                $table->string('priority')->default('normal');
                $table->date('requested_date')->nullable();
                $table->dateTime('scheduled_at')->nullable();
                $table->dateTime('started_at')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->text('site_address')->nullable();
                $table->text('concern')->nullable();
                $table->text('remarks')->nullable();
                $table->string('status_text')->default('Pending');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('service_job_orders');
    }
}
