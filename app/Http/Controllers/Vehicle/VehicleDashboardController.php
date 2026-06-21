<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use App\Models\VehicleDocument;
use App\Models\VehicleMaintenanceRecord;
use Illuminate\Support\Facades\Schema;

class VehicleDashboardController extends Controller
{
    public function index()
    {
        $totalVehicles = $this->countTable('vehicles');

        $availableVehicles = $this->vehicleStatusCount(['available']);
        $assignedVehicles = $this->vehicleStatusCount(['assigned', 'in use', 'in_use']);
        $maintenanceVehicles = $this->vehicleStatusCount(['under maintenance', 'under_maintenance', 'maintenance', 'for repair', 'for_repair']);

        $statusCounts = $this->vehicleStatusCounts();
        $activeAssignments = $this->activeAssignments();
        $expiringDocuments = $this->expiringDocuments();
        $recentMaintenance = $this->recentMaintenance();

        return view('vehicle.dashboard.index', [
            'assets' => [],
            'totalVehicles' => $totalVehicles,
            'availableVehicles' => $availableVehicles,
            'assignedVehicles' => $assignedVehicles,
            'maintenanceVehicles' => $maintenanceVehicles,
            'statusCounts' => $statusCounts,
            'activeAssignments' => $activeAssignments,
            'expiringDocuments' => $expiringDocuments,
            'recentMaintenance' => $recentMaintenance,
        ]);
    }

    protected function countTable(string $table): int
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        return (int) \DB::table($table)->count();
    }

    protected function vehicleStatusCount(array $names): int
    {
        if (!Schema::hasTable('vehicles')) {
            return 0;
        }

        $normalized = collect($names)->map(fn ($name) => strtolower(str_replace('_', ' ', $name)))->toArray();

        if (Schema::hasColumn('vehicles', 'vehicle_status_id') && Schema::hasTable('vehicle_statuses')) {
            return (int) Vehicle::query()
                ->leftJoin('vehicle_statuses', 'vehicles.vehicle_status_id', '=', 'vehicle_statuses.id')
                ->whereIn(\DB::raw("LOWER(REPLACE(vehicle_statuses.name, '_', ' '))"), $normalized)
                ->count();
        }

        if (Schema::hasColumn('vehicles', 'status')) {
            return (int) Vehicle::query()
                ->whereIn(\DB::raw("LOWER(REPLACE(status, '_', ' '))"), $normalized)
                ->count();
        }

        return 0;
    }

    protected function vehicleStatusCounts()
    {
        if (!Schema::hasTable('vehicles')) {
            return collect();
        }

        if (Schema::hasColumn('vehicles', 'vehicle_status_id') && Schema::hasTable('vehicle_statuses')) {
            return Vehicle::query()
                ->leftJoin('vehicle_statuses', 'vehicles.vehicle_status_id', '=', 'vehicle_statuses.id')
                ->selectRaw("COALESCE(vehicle_statuses.name, 'No Status') as label, COUNT(*) as total")
                ->groupBy('vehicle_statuses.name')
                ->orderBy('label')
                ->get();
        }

        if (Schema::hasColumn('vehicles', 'status')) {
            return Vehicle::query()
                ->selectRaw("COALESCE(status, 'No Status') as label, COUNT(*) as total")
                ->groupBy('status')
                ->orderBy('label')
                ->get();
        }

        return collect([
            (object) ['label' => 'Total Vehicles', 'total' => Vehicle::count()],
        ]);
    }

    protected function activeAssignments()
    {
        if (!class_exists(VehicleAssignment::class) || !Schema::hasTable('vehicle_assignments')) {
            return collect();
        }

        $query = VehicleAssignment::query()
            ->with(['vehicle', 'driver', 'branch', 'members.user'])
            ->latest('id')
            ->limit(5);

        if (Schema::hasColumn('vehicle_assignments', 'status')) {
            $query->where('status', 'active');
        }

        return $query->get();
    }

    protected function expiringDocuments()
    {
        if (!class_exists(VehicleDocument::class) || !Schema::hasTable('vehicle_documents') || !Schema::hasColumn('vehicle_documents', 'expiry_date')) {
            return collect();
        }

        return VehicleDocument::query()
            ->with('vehicle')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString())
            ->orderBy('expiry_date')
            ->limit(5)
            ->get();
    }

    protected function recentMaintenance()
    {
        if (!class_exists(VehicleMaintenanceRecord::class) || !Schema::hasTable('vehicle_maintenance_records')) {
            return collect();
        }

        $dateColumn = Schema::hasColumn('vehicle_maintenance_records', 'maintenance_date') ? 'maintenance_date' : 'id';

        return VehicleMaintenanceRecord::query()
            ->with(['vehicle', 'maintenanceType'])
            ->latest($dateColumn)
            ->limit(5)
            ->get();
    }
}
