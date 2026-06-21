<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use App\Models\VehicleDocument;
use App\Models\VehicleMaintenanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VehicleReportController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = Vehicle::query()
            ->with(['type', 'status'])
            ->orderBy('vehicle_code')
            ->get();

        $totalVehicles = $vehicles->count();

        $statusCounts = $this->vehicleStatusCounts();
        $activeAssignments = $this->activeAssignments();
        $recentMaintenance = $this->recentMaintenance();
        $documentsExpiring = $this->documentsExpiring();
        $maintenanceCostSummary = $this->maintenanceCostSummary();
        $maintenanceByVehicle = $this->maintenanceByVehicle();

        return view('vehicle.reports.index', [
            'assets' => [],
            'totalVehicles' => $totalVehicles,
            'statusCounts' => $statusCounts,
            'activeAssignments' => $activeAssignments,
            'recentMaintenance' => $recentMaintenance,
            'documentsExpiring' => $documentsExpiring,
            'maintenanceCostSummary' => $maintenanceCostSummary,
            'maintenanceByVehicle' => $maintenanceByVehicle,
        ]);
    }

    protected function vehicleStatusCounts()
    {
        if (!Schema::hasTable('vehicles')) {
            return collect();
        }

        $query = Vehicle::query();

        if (Schema::hasColumn('vehicles', 'vehicle_status_id') && Schema::hasTable('vehicle_statuses')) {
            return $query
                ->leftJoin('vehicle_statuses', 'vehicles.vehicle_status_id', '=', 'vehicle_statuses.id')
                ->selectRaw("COALESCE(vehicle_statuses.name, 'No Status') as label, COUNT(*) as total")
                ->groupBy('vehicle_statuses.name')
                ->orderBy('label')
                ->get();
        }

        if (Schema::hasColumn('vehicles', 'status')) {
            return $query
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

        return VehicleAssignment::query()
            ->with(['vehicle', 'driver', 'branch', 'members.user'])
            ->where(function ($q) {
                if (Schema::hasColumn('vehicle_assignments', 'status')) {
                    $q->where('status', 'active');
                }
            })
            ->latest('id')
            ->limit(10)
            ->get();
    }

    protected function recentMaintenance()
    {
        if (!class_exists(VehicleMaintenanceRecord::class) || !Schema::hasTable('vehicle_maintenance_records')) {
            return collect();
        }

        $query = VehicleMaintenanceRecord::query()
            ->with(['vehicle', 'maintenanceType'])
            ->latest(Schema::hasColumn('vehicle_maintenance_records', 'maintenance_date') ? 'maintenance_date' : 'id')
            ->limit(10);

        return $query->get();
    }

    protected function documentsExpiring()
    {
        if (!class_exists(VehicleDocument::class) || !Schema::hasTable('vehicle_documents') || !Schema::hasColumn('vehicle_documents', 'expiry_date')) {
            return collect();
        }

        return VehicleDocument::query()
            ->with(['vehicle'])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->whereDate('expiry_date', '<=', now()->addDays(60)->toDateString())
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();
    }

    protected function maintenanceCostSummary(): array
    {
        if (!Schema::hasTable('vehicle_maintenance_records')) {
            return [
                'thisMonth' => 0,
                'thisYear' => 0,
                'allTime' => 0,
            ];
        }

        $dateColumn = Schema::hasColumn('vehicle_maintenance_records', 'maintenance_date') ? 'maintenance_date' : 'created_at';
        $costColumn = Schema::hasColumn('vehicle_maintenance_records', 'total_cost') ? 'total_cost' : null;

        if (!$costColumn) {
            return [
                'thisMonth' => 0,
                'thisYear' => 0,
                'allTime' => 0,
            ];
        }

        $base = DB::table('vehicle_maintenance_records');

        return [
            'thisMonth' => (clone $base)
                ->whereYear($dateColumn, now()->year)
                ->whereMonth($dateColumn, now()->month)
                ->sum($costColumn),
            'thisYear' => (clone $base)
                ->whereYear($dateColumn, now()->year)
                ->sum($costColumn),
            'allTime' => (clone $base)->sum($costColumn),
        ];
    }

    protected function maintenanceByVehicle()
    {
        if (!Schema::hasTable('vehicle_maintenance_records') || !Schema::hasTable('vehicles')) {
            return collect();
        }

        $costColumn = Schema::hasColumn('vehicle_maintenance_records', 'total_cost') ? 'total_cost' : null;

        if (!$costColumn) {
            return collect();
        }

        return DB::table('vehicle_maintenance_records')
            ->join('vehicles', 'vehicle_maintenance_records.vehicle_id', '=', 'vehicles.id')
            ->selectRaw("vehicles.vehicle_code, vehicles.plate_number, COUNT(vehicle_maintenance_records.id) as records_count, SUM(vehicle_maintenance_records.{$costColumn}) as total_cost")
            ->groupBy('vehicles.vehicle_code', 'vehicles.plate_number')
            ->orderByDesc('total_cost')
            ->limit(10)
            ->get();
    }
}
