<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleMaintenanceRecord;
use App\Models\VehicleMaintenanceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class VehicleMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = VehicleMaintenanceRecord::query()
            ->with(['vehicle.type', 'maintenanceType', 'reportedBy', 'performedBy'])
            ->latest('maintenance_date')
            ->latest('id');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('issue_or_concern', 'like', "%{$search}%")
                    ->orWhere('action_taken', 'like', "%{$search}%")
                    ->orWhere('parts_replaced', 'like', "%{$search}%")
                    ->orWhere('shop_or_mechanic', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                        $vehicleQuery->where('vehicle_code', 'like', "%{$search}%")
                            ->orWhere('plate_number', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->input('vehicle_id'));
        }

        if ($request->filled('maintenance_type_id')) {
            $query->where('maintenance_type_id', $request->input('maintenance_type_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $records = $query->paginate(10)->appends($request->query());

        return view('vehicle.maintenance.index', [
            'assets' => [],
            'records' => $records,
            'vehicles' => $this->vehiclesForDropdown(),
            'types' => $this->maintenanceTypesForDropdown(),
            'statuses' => $this->statuses(),
            'filters' => $request->only(['search', 'vehicle_id', 'maintenance_type_id', 'status']),
        ]);
    }

    public function create()
    {
        return view('vehicle.maintenance.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('vehicle-maintenance', 'public');
        }

        VehicleMaintenanceRecord::create($data);

        return redirect()
            ->route('vehicle.maintenance.index')
            ->with('success', 'Maintenance record saved successfully.');
    }

    public function show(VehicleMaintenanceRecord $maintenance)
    {
        $maintenance->load(['vehicle.type', 'maintenanceType', 'reportedBy', 'performedBy']);

        return view('vehicle.maintenance.show', [
            'assets' => [],
            'maintenance' => $maintenance,
        ]);
    }

    public function edit(VehicleMaintenanceRecord $maintenance)
    {
        return view('vehicle.maintenance.edit', $this->formData($maintenance));
    }

    public function update(Request $request, VehicleMaintenanceRecord $maintenance)
    {
        $data = $this->validatedData($request);
        $data['updated_by'] = Auth::id();

        if ($request->hasFile('attachment')) {
            if (!empty($maintenance->attachment_path)) {
                Storage::disk('public')->delete($maintenance->attachment_path);
            }

            $data['attachment_path'] = $request->file('attachment')->store('vehicle-maintenance', 'public');
        }

        $maintenance->update($data);

        return redirect()
            ->route('vehicle.maintenance.index')
            ->with('success', 'Maintenance record updated successfully.');
    }

    public function destroy(VehicleMaintenanceRecord $maintenance)
    {
        if (!empty($maintenance->attachment_path)) {
            Storage::disk('public')->delete($maintenance->attachment_path);
        }

        $maintenance->delete();

        return redirect()
            ->route('vehicle.maintenance.index')
            ->with('success', 'Maintenance record deleted successfully.');
    }

    protected function formData(?VehicleMaintenanceRecord $maintenance = null): array
    {
        return [
            'assets' => [],
            'maintenance' => $maintenance,
            'vehicles' => $this->vehiclesForDropdown(),
            'types' => $this->maintenanceTypesForDropdown(),
            'users' => $this->usersForDropdown(),
            'statuses' => $this->statuses(),
        ];
    }

    protected function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'maintenance_type_id' => ['nullable', 'integer', 'exists:vehicle_maintenance_types,id'],
            'reported_by' => ['nullable', 'integer', 'exists:users,id'],
            'performed_by' => ['nullable', 'integer', 'exists:users,id'],
            'maintenance_date' => ['required', 'date'],
            'odometer' => ['nullable', 'numeric', 'min:0'],
            'issue_or_concern' => ['nullable', 'string', 'max:2000'],
            'action_taken' => ['nullable', 'string', 'max:2000'],
            'parts_replaced' => ['nullable', 'string', 'max:2000'],
            'shop_or_mechanic' => ['nullable', 'string', 'max:255'],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
            'parts_cost' => ['nullable', 'numeric', 'min:0'],
            'other_cost' => ['nullable', 'numeric', 'min:0'],
            'next_maintenance_date' => ['nullable', 'date'],
            'next_maintenance_odometer' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:open,in_progress,completed,cancelled'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $labor = (float) ($validated['labor_cost'] ?? 0);
        $parts = (float) ($validated['parts_cost'] ?? 0);
        $other = (float) ($validated['other_cost'] ?? 0);

        return [
            'vehicle_id' => $validated['vehicle_id'],
            'maintenance_type_id' => $validated['maintenance_type_id'] ?? null,
            'reported_by' => $validated['reported_by'] ?? null,
            'performed_by' => $validated['performed_by'] ?? null,
            'maintenance_date' => $validated['maintenance_date'],
            'odometer' => $validated['odometer'] ?? null,
            'issue_or_concern' => $validated['issue_or_concern'] ?? null,
            'action_taken' => $validated['action_taken'] ?? null,
            'parts_replaced' => $validated['parts_replaced'] ?? null,
            'shop_or_mechanic' => $validated['shop_or_mechanic'] ?? null,
            'labor_cost' => $labor,
            'parts_cost' => $parts,
            'other_cost' => $other,
            'total_cost' => $labor + $parts + $other,
            'next_maintenance_date' => $validated['next_maintenance_date'] ?? null,
            'next_maintenance_odometer' => $validated['next_maintenance_odometer'] ?? null,
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] ?? null,
        ];
    }

    protected function vehiclesForDropdown()
    {
        return Vehicle::query()
            ->orderBy('vehicle_code')
            ->orderBy('plate_number')
            ->get();
    }

    protected function maintenanceTypesForDropdown()
    {
        return VehicleMaintenanceType::query()
            ->where(function ($q) {
                if (Schema::hasColumn('vehicle_maintenance_types', 'status')) {
                    $q->where('status', 'active')->orWhereNull('status');
                }
            })
            ->orderBy('name')
            ->get();
    }

    protected function usersForDropdown()
    {
        $query = \App\Models\User::query();

        foreach (['last_name', 'first_name', 'middle_name', 'email', 'id'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                $query->orderBy($column);
            }
        }

        return $query->get();
    }

    protected function statuses(): array
    {
        return [
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }
}
