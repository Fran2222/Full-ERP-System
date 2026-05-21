<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBranchLocationToSalesReceiptsTable extends Migration
{
    public function up()
    {
        Schema::table('sales_receipts', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('customer_id')->constrained('branches')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->after('branch_id')->constrained('warehouse_locations')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('sales_receipts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('location_id');
            $table->dropConstrainedForeignId('branch_id');
        });
    }
}