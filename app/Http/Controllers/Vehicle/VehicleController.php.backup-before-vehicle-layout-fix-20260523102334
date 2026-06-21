<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleStatus;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    private function authorizeVehicleAccess(string $level = 'viewer'): void
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        if ($user->hasAnyRole([
            'Super Admin',
            'Super Administrator',
            'Admin',
            'super admin',
            'super-admin',
            'superadmin',
            'admin',
        ])) {
            return;
        }

        $moduleKeys = [
            'vehicle management',
            'vehicle_management',
            'vehicle-management',
            'vehicles',
            'vehicle',
        ];

        foreach ($moduleKeys as $moduleKey) {
            if (method_exists($user, 'hasModuleAccess') && $user->hasModuleAccess($moduleKey, $level)) {
                return;
            }
        }

        abort(403, 'Unauthorized Vehicle Management access.');
    }

    public function index(Request $request)
    {
        $this->authorizeVehicleAccess('viewer');

        $query = Vehicle::query()
            ->with(['type', 'status', 'branch', 'defaultDriver'])
            ->latest('id');

        if ($request->filled('status_id')) {
            $query->where('status_id', $this->requestInt($request, 'status_id'));
        }

        if ($request->filled('vehicle_type_id')) {
            $query->where('vehicle_type_id', $this->requestInt($request, 'vehicle_type_id'));
        }

        if ($request->filled('branch_id')) {
            $query->where('assigned_branch_id', $this->requestInt($request, 'branch_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('vehicle_code', 'like', "%{$search}%")
                    ->orWhere('plate_number', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('engine_no', 'like', "%{$search}%")
                    ->orWhere('chassis_no', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->paginate(10)->withQueryString();

        return view('vehicle.vehicles.index', [
            'vehicles' => $vehicles,
            'statuses' => VehicleStatus::orderBy('sort_order')->orderBy('name')->get(),
            'types' => VehicleType::orderBy('name')->get(),
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $this->authorizeVehicleAccess('manager');

        return view('vehicle.vehicles.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->authorizeVehicleAccess('manager');

        $data = $this->validatedData($request);

        DB::transaction(function () use (&$data, $request) {
            if (empty($data['vehicle_code'])) {
                $data['vehicle_code'] = $this->generateVehicleCode();
            }

            if (empty($data['status_id'])) {
                $data['status_id'] = $this->defaultStatusId();
            }

            if ($request->hasFile('photo')) {
                $data['photo_path'] = $request->file('photo')->store('vehicle_photos', 'public');
            }

            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            Vehicle::create($data);
        });

        return redirect()
            ->route('vehicle-management.vehicles')
            ->with('success', 'Vehicle saved successfully.');
    }

    public function show(Vehicle $vehicle)
    {
        $this->authorizeVehicleAccess('viewer');

        $vehicle->load([
            'type',
            'status',
            'branch',
            'defaultDriver',
            'activeAssignment.driver',
            'maintenanceRecords.type',
            'documents',
        ]);

        return view('vehicle.vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        $this->authorizeVehicleAccess('manager');

        return view('vehicle.vehicles.edit', array_merge(
            $this->formData(),
            ['vehicle' => $vehicle]
        ));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorizeVehicleAccess('manager');

        $data = $this->validatedData($request, $vehicle);

        DB::transaction(function () use ($vehicle, &$data, $request) {
            if ($request->hasFile('photo')) {
                if ($vehicle->photo_path && Storage::disk('public')->exists($vehicle->photo_path)) {
                    Storage::disk('public')->delete($vehicle->photo_path);
                }

                $data['photo_path'] = $request->file('photo')->store('vehicle_photos', 'public');
            }

            $data['updated_by'] = auth()->id();

            $vehicle->update($data);
        });

        return redirect()
            ->route('vehicle-management.vehicles.show', $vehicle)
            ->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $this->authorizeVehicleAccess('admin');

        abort_if($vehicle->assignments()->where('status', 'active')->exists(), 422, 'This vehicle still has an active assignment.');

        $vehicle->delete();

        return redirect()
            ->route('vehicle-management.vehicles')
            ->with('success', 'Vehicle archived successfully.');
    }

    private function requestInt(Request $request, string $key): ?int
    {
        $value = $request->input($key);

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function formData(): array
    {
        return [
            'types' => VehicleType::where('status', 'active')->orderBy('name')->get(),
            'statuses' => VehicleStatus::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
            'branches' => Branch::where(function ($q) {
                    $q->whereNull('status')->orWhere('status', 'active');
                })
                ->orderBy('name')
                ->get(),
            'drivers' => User::where(function ($q) {
                    $q->whereNull('status')->orWhere('status', 'active');
                })
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
        ];
    }

    private function validatedData(Request $request, ?Vehicle $vehicle = null): array
    {
        $vehicleId = $vehicle?->id;

        return $request->validate([
            'vehicle_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('vehicles', 'vehicle_code')->ignore($vehicleId),
            ],
            'plate_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('vehicles', 'plate_number')->ignore($vehicleId),
            ],
            'vehicle_type_id' => ['required', 'exists:vehicle_types,id'],
            'status_id' => ['nullable', 'exists:vehicle_statuses,id'],
            'assigned_branch_id' => ['nullable', 'exists:branches,id'],
            'default_driver_id' => ['nullable', 'exists:users,id'],
            'brand' => ['nullable', 'string', 'max:150'],
            'model' => ['nullable', 'string', 'max:150'],
            'year_model' => ['nullable', 'integer', 'min:1900', 'max:' . ((int) date('Y') + 1)],
            'color' => ['nullable', 'string', 'max:80'],
            'fuel_type' => ['nullable', 'string', 'max:80'],
            'engine_no' => ['nullable', 'string', 'max:150'],
            'chassis_no' => ['nullable', 'string', 'max:150'],
            'current_odometer' => ['nullable', 'integer', 'min:0'],
            'acquisition_date' => ['nullable', 'date'],
            'acquisition_cost' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }

    private function defaultStatusId(): ?int
    {
        return VehicleStatus::where('is_default', true)->value('id')
            ?: VehicleStatus::where('status', 'active')->orderBy('sort_order')->value('id');
    }

    private function generateVehicleCode(): string
    {
        $next = ((int) Vehicle::withTrashed()->max('id')) + 1;

        do {
            $code = 'VEH-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
            $exists = Vehicle::withTrashed()->where('vehicle_code', $code)->exists();
            $next++;
        } while ($exists);

        return $code;
    }
}
