<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\VehicleDocumentType;
use App\Models\VehicleFuelType;
use App\Models\VehicleMaintenanceType;
use App\Models\VehicleStatus;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class VehicleSetupController extends Controller
{
    public function index()
    {
        return view('vehicle.setup.index', [
            'assets' => [],
            'vehicleTypes' => $this->safeList(VehicleType::class, 'vehicle_types'),
            'vehicleStatuses' => $this->safeList(VehicleStatus::class, 'vehicle_statuses'),
            'maintenanceTypes' => $this->safeList(VehicleMaintenanceType::class, 'vehicle_maintenance_types'),
            'documentTypes' => $this->safeList(VehicleDocumentType::class, 'vehicle_document_types'),
            'fuelTypes' => $this->safeList(VehicleFuelType::class, 'vehicle_fuel_types'),
        ]);
    }

    public function store(Request $request, string $group)
    {
        [$modelClass, $table] = $this->resolveGroup($group);

        if (!$modelClass || !Schema::hasTable($table)) {
            return back()->with('error', 'Invalid setup group or missing table.');
        }

        $data = $this->validatedData($request, $table);

        $row = new $modelClass();
        $row->fill($data);
        $row->save();

        return back()->with('success', $this->groupLabel($group) . ' saved successfully.');
    }

    public function update(Request $request, string $group, int $id)
    {
        [$modelClass, $table] = $this->resolveGroup($group);

        if (!$modelClass || !Schema::hasTable($table)) {
            return back()->with('error', 'Invalid setup group or missing table.');
        }

        $row = $modelClass::query()->findOrFail($id);
        $data = $this->validatedData($request, $table, $id);

        $row->fill($data);
        $row->save();

        return back()->with('success', $this->groupLabel($group) . ' updated successfully.');
    }

    public function destroy(string $group, int $id)
    {
        [$modelClass, $table] = $this->resolveGroup($group);

        if (!$modelClass || !Schema::hasTable($table)) {
            return back()->with('error', 'Invalid setup group or missing table.');
        }

        $row = $modelClass::query()->findOrFail($id);

        try {
            $row->delete();
            return back()->with('success', $this->groupLabel($group) . ' deleted successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Cannot delete this record because it may already be used by vehicle records.');
        }
    }

    protected function validatedData(Request $request, string $table, ?int $id = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'string', 'max:50'],
        ];

        $validated = $request->validate($rules);

        $data = [];

        if (Schema::hasColumn($table, 'name')) {
            $data['name'] = $validated['name'];
        }

        if (Schema::hasColumn($table, 'code')) {
            $data['code'] = $request->filled('code')
                ? Str::upper(Str::slug($request->input('code'), '_'))
                : Str::upper(Str::slug($validated['name'], '_'));
        }

        if (Schema::hasColumn($table, 'description')) {
            $data['description'] = $validated['description'] ?? null;
        }

        if (Schema::hasColumn($table, 'remarks')) {
            $data['remarks'] = $validated['description'] ?? null;
        }

        if (Schema::hasColumn($table, 'status')) {
            $data['status'] = $validated['status'] ?? 'active';
        }

        if (Schema::hasColumn($table, 'is_active')) {
            $data['is_active'] = ($validated['status'] ?? 'active') === 'active';
        }

        return $data;
    }

    protected function safeList(string $modelClass, string $table)
    {
        if (!class_exists($modelClass) || !Schema::hasTable($table)) {
            return collect();
        }

        $query = $modelClass::query();

        if (Schema::hasColumn($table, 'name')) {
            $query->orderBy('name');
        } else {
            $query->orderBy('id');
        }

        return $query->get();
    }

    protected function resolveGroup(string $group): array
    {
        return match ($group) {
            'vehicle-types' => [VehicleType::class, 'vehicle_types'],
            'vehicle-statuses' => [VehicleStatus::class, 'vehicle_statuses'],
            'maintenance-types' => [VehicleMaintenanceType::class, 'vehicle_maintenance_types'],
            'document-types' => [VehicleDocumentType::class, 'vehicle_document_types'],
            'fuel-types' => [VehicleFuelType::class, 'vehicle_fuel_types'],
            default => [null, null],
        };
    }

    protected function groupLabel(string $group): string
    {
        return match ($group) {
            'vehicle-types' => 'Vehicle type',
            'vehicle-statuses' => 'Vehicle status',
            'maintenance-types' => 'Maintenance type',
            'document-types' => 'Document type',
            'fuel-types' => 'Fuel type',
            default => 'Setup record',
        };
    }
}
