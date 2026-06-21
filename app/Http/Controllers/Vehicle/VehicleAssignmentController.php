<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use App\Models\VehicleAssignmentMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VehicleAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = VehicleAssignment::query()
            ->with(['vehicle.type', 'vehicle.status', 'driver', 'branch', 'department', 'members.user'])
            ->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('purpose', 'like', "%{$search}%")
                    ->orWhere('project_site_text', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                        $vehicleQuery->where('vehicle_code', 'like', "%{$search}%")
                            ->orWhere('plate_number', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    })
                    ->orWhereHas('driver', function ($driverQuery) use ($search) {
                        $this->applyUserSearch($driverQuery, $search);
                    });
            });
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->input('vehicle_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        $assignments = $query->paginate(10)->appends($request->query());

        return view('vehicle.assignments.index', [
            'assets' => [],
            'assignments' => $assignments,
            'vehicles' => $this->vehiclesForDropdown(),
            'branches' => $this->branchesForDropdown(),
            'statuses' => ['active', 'ended', 'cancelled'],
            'filters' => $request->only(['search', 'vehicle_id', 'status', 'branch_id']),
        ]);
    }

    public function create()
    {
        return view('vehicle.assignments.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($request, $data) {
            if ($request->boolean('end_existing_assignment')) {
                VehicleAssignment::where('vehicle_id', $data['vehicle_id'])
                    ->where('status', 'active')
                    ->update([
                        'status' => 'ended',
                        'end_date' => now()->toDateString(),
                        'ended_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);
            }

            $assignment = VehicleAssignment::create($data + [
                'assigned_by' => Auth::id(),
            ]);

            $this->syncMembers($assignment, $request->input('member_ids', []));
            $this->syncVehicleDefaults($assignment);
        });

        return redirect()
            ->route('vehicle.assignments.index')
            ->with('success', 'Vehicle assignment saved successfully.');
    }

    public function show(VehicleAssignment $assignment)
    {
        $assignment->load(['vehicle.type', 'vehicle.status', 'driver', 'branch', 'department', 'members.user']);

        return view('vehicle.assignments.show', [
            'assets' => [],
            'assignment' => $assignment,
        ]);
    }

    public function edit(VehicleAssignment $assignment)
    {
        $assignment->load(['members']);

        return view('vehicle.assignments.edit', $this->formData($assignment));
    }

    public function update(Request $request, VehicleAssignment $assignment)
    {
        $data = $this->validatedData($request, $assignment);

        DB::transaction(function () use ($request, $assignment, $data) {
            $assignment->update($data);
            $this->syncMembers($assignment, $request->input('member_ids', []));
            $this->syncVehicleDefaults($assignment);
        });

        return redirect()
            ->route('vehicle.assignments.index')
            ->with('success', 'Vehicle assignment updated successfully.');
    }

    public function destroy(VehicleAssignment $assignment)
    {
        DB::transaction(function () use ($assignment) {
            $assignment->members()->delete();
            $assignment->delete();
        });

        return redirect()
            ->route('vehicle.assignments.index')
            ->with('success', 'Vehicle assignment deleted successfully.');
    }

    protected function formData(?VehicleAssignment $assignment = null): array
    {
        return [
            'assets' => [],
            'assignment' => $assignment,
            'vehicles' => $this->vehiclesForDropdown(),
            'drivers' => $this->usersForDropdown(),
            'members' => $this->usersForDropdown(),
            'branches' => $this->branchesForDropdown(),
            'departments' => $this->departmentsForDropdown(),
            'selectedMembers' => $assignment
                ? $assignment->members()->pluck('user_id')->filter()->map(fn ($id) => (string) $id)->toArray()
                : [],
        ];
    }

    protected function validatedData(Request $request, ?VehicleAssignment $assignment = null): array
    {
        $rules = [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'driver_id' => ['nullable', 'integer', 'exists:users,id'],
            'branch_id' => ['nullable', 'integer'],
            'department_id' => ['nullable', 'integer'],
            'project_id' => ['nullable', 'integer'],
            'project_site_text' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:active,ended,cancelled'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['nullable', 'integer', 'exists:users,id'],
        ];

        if (Schema::hasTable('branches') && Schema::hasColumn('vehicle_assignments', 'branch_id')) {
            $rules['branch_id'][] = 'exists:branches,id';
        }

        if (Schema::hasTable('departments') && Schema::hasColumn('vehicle_assignments', 'department_id')) {
            $rules['department_id'][] = 'exists:departments,id';
        }

        $validated = $request->validate($rules);

        return [
            'vehicle_id' => $validated['vehicle_id'],
            'driver_id' => $validated['driver_id'] ?? null,
            'branch_id' => $validated['branch_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'project_site_text' => $validated['project_site_text'] ?? null,
            'purpose' => $validated['purpose'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] ?? null,
            'ended_by' => ($validated['status'] ?? null) === 'ended' ? Auth::id() : null,
        ];
    }

    protected function syncMembers(VehicleAssignment $assignment, array $memberIds): void
    {
        $memberIds = collect($memberIds)
            ->filter()
            ->unique()
            ->values();

        $assignment->members()->delete();

        foreach ($memberIds as $memberId) {
            VehicleAssignmentMember::create([
                'vehicle_assignment_id' => $assignment->id,
                'user_id' => $memberId,
                'role_in_vehicle' => 'Team Member',
            ]);
        }
    }

    protected function syncVehicleDefaults(VehicleAssignment $assignment): void
    {
        if ($assignment->status !== 'active') {
            return;
        }

        $vehicle = $assignment->vehicle;
        if (!$vehicle) {
            return;
        }

        $updates = [];

        if (Schema::hasColumn('vehicles', 'assigned_branch_id')) {
            $updates['assigned_branch_id'] = $assignment->branch_id;
        }

        if (Schema::hasColumn('vehicles', 'default_driver_id')) {
            $updates['default_driver_id'] = $assignment->driver_id;
        }

        if (!empty($updates)) {
            $vehicle->update($updates);
        }
    }

    protected function vehiclesForDropdown()
    {
        return Vehicle::query()
            ->with(['type', 'status'])
            ->orderBy('vehicle_code')
            ->orderBy('plate_number')
            ->get();
    }

    protected function usersForDropdown()
    {
        $query = User::query();

        foreach (['last_name', 'first_name', 'middle_name', 'email', 'id'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                $query->orderBy($column);
            }
        }

        return $query->get();
    }

    protected function applyUserSearch($query, string $search): void
    {
        $availableColumns = collect(['name', 'first_name', 'middle_name', 'last_name', 'email'])
            ->filter(fn ($column) => Schema::hasColumn('users', $column))
            ->values();

        if ($availableColumns->isEmpty()) {
            $query->where('id', $search);
            return;
        }

        $query->where(function ($subQuery) use ($availableColumns, $search) {
            foreach ($availableColumns as $index => $column) {
                if ($index === 0) {
                    $subQuery->where($column, 'like', "%{$search}%");
                } else {
                    $subQuery->orWhere($column, 'like', "%{$search}%");
                }
            }
        });
    }

    protected function branchesForDropdown()
    {
        if (!class_exists(Branch::class) || !Schema::hasTable('branches')) {
            return collect();
        }

        $query = Branch::query();

        foreach (['name', 'branch_name', 'id'] as $column) {
            if (Schema::hasColumn('branches', $column)) {
                $query->orderBy($column);
            }
        }

        return $query->get();
    }

    protected function departmentsForDropdown()
    {
        if (!class_exists(Department::class) || !Schema::hasTable('departments')) {
            return collect();
        }

        $query = Department::query();

        foreach (['name', 'department_name', 'id'] as $column) {
            if (Schema::hasColumn('departments', $column)) {
                $query->orderBy($column);
            }
        }

        return $query->get();
    }
}
