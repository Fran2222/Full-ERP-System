<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('color', 30)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('vehicle_maintenance_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('vehicle_types')->insert([
            ['code' => 'L300', 'name' => 'L300', 'status' => 'active', 'remarks' => null, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'VAN', 'name' => 'Van', 'status' => 'active', 'remarks' => null, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'PICKUP', 'name' => 'Pickup', 'status' => 'active', 'remarks' => null, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'MOTORCYCLE', 'name' => 'Motorcycle', 'status' => 'active', 'remarks' => null, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'SEDAN', 'name' => 'Sedan', 'status' => 'active', 'remarks' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('vehicle_statuses')->insert([
            ['code' => 'AVAILABLE', 'name' => 'Available', 'color' => 'success', 'sort_order' => 1, 'is_default' => true, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ASSIGNED', 'name' => 'Assigned', 'color' => 'primary', 'sort_order' => 2, 'is_default' => false, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'IN_USE', 'name' => 'In Use', 'color' => 'info', 'sort_order' => 3, 'is_default' => false, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'UNDER_MAINTENANCE', 'name' => 'Under Maintenance', 'color' => 'warning', 'sort_order' => 4, 'is_default' => false, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'FOR_REPAIR', 'name' => 'For Repair', 'color' => 'danger', 'sort_order' => 5, 'is_default' => false, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'UNAVAILABLE', 'name' => 'Unavailable', 'color' => 'secondary', 'sort_order' => 6, 'is_default' => false, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'RETIRED', 'name' => 'Retired', 'color' => 'dark', 'sort_order' => 7, 'is_default' => false, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('vehicle_maintenance_types')->insert([
            ['code' => 'PMS', 'name' => 'PMS / General Maintenance', 'status' => 'active', 'remarks' => null, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'OIL_CHANGE', 'name' => 'Oil Change', 'status' => 'active', 'remarks' => null, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'TIRE_REPLACEMENT', 'name' => 'Tire Replacement', 'status' => 'active', 'remarks' => null, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'BATTERY_REPLACEMENT', 'name' => 'Battery Replacement', 'status' => 'active', 'remarks' => null, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'BRAKE_REPAIR', 'name' => 'Brake Repair', 'status' => 'active', 'remarks' => null, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ENGINE_REPAIR', 'name' => 'Engine Repair', 'status' => 'active', 'remarks' => null, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'AIRCON_REPAIR', 'name' => 'Aircon Repair', 'status' => 'active', 'remarks' => null, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'EMERGENCY_REPAIR', 'name' => 'Emergency Repair', 'status' => 'active', 'remarks' => null, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'OTHER', 'name' => 'Other', 'status' => 'active', 'remarks' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_types');
        Schema::dropIfExists('vehicle_statuses');
        Schema::dropIfExists('vehicle_types');
    }
};
