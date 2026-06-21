<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Helpers\ActionButtonHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectMgmt\ProjectVehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProjectVehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:projects_mgmt.view')->only(['index', 'list', 'show']);
        $this->middleware('permission:projects_mgmt.create')->only(['create', 'store']);
        $this->middleware('permission:projects_mgmt.edit')->only(['edit', 'update']);
        $this->middleware('permission:projects_mgmt.delete')->only(['destroy']);
    }

    public function index()
    {
        return view('modules.project-mgmt.vehicles.index');
    }

    public function list(Request $request)
    {
        $query = ProjectVehicle::query()->with('drivers')->select('project_vehicles.*')->orderByDesc('project_vehicles.id');

        return DataTables::eloquent($query)
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');
                if (! $search) return;
                $query->where(function ($q) use ($search) {
                    $q->where('vehicle_code', 'ILIKE', "%{$search}%")
                      ->orWhere('plate_name', 'ILIKE', "%{$search}%")
                      ->orWhereHas('drivers', function ($driver) use ($search) {
                          $driver->where('first_name', 'ILIKE', "%{$search}%")
                              ->orWhere('last_name', 'ILIKE', "%{$search}%")
                              ->orWhere('email', 'ILIKE', "%{$search}%");
                      });
                });
            })
            ->addColumn('drivers_badges', function ($vehicle) {
                if ($vehicle->drivers->isEmpty()) {
                    return '-';
                }

                return '<div class="vehicle-members-wrap">'
                    . $vehicle->drivers->map(function ($user) {
                        $name = $this->userName($user);
                        $initials = $this->userInitials($user);
                        return '<span class="vehicle-member-avatar" title="' . e($name) . '">' . e($initials) . '</span>';
                    })->implode('')
                    . '</div>';
            })
            ->addColumn('status_badge', fn ($vehicle) => strtolower($vehicle->status) === 'active' ? '<span class="text-success fw-semibold">Active</span>' : '<span class="text-danger fw-semibold">Inactive</span>')
            ->addColumn('show_url', fn ($vehicle) => route('project-vehicles.show', $vehicle->id))
            ->addColumn('action', function ($vehicle) {
                $edit = auth()->user()->can('projects_mgmt.edit') ? route('project-vehicles.edit', $vehicle->id) : null;
                $delete = auth()->user()->can('projects_mgmt.delete') ? route('project-vehicles.destroy', $vehicle->id) : null;

                return ActionButtonHelper::editDelete(
                    $edit,
                    $delete,
                    $vehicle->vehicle_code,
                    'delete-project-vehicle',
                    'Edit Vehicle',
                    'Delete Vehicle'
                );
            })
            ->rawColumns(['drivers_badges', 'status_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        $users = $this->userOptions();
        $nextCode = $this->nextVehicleCode();
        return view('modules.project-mgmt.vehicles.create', compact('users', 'nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedVehicle($request);

        DB::transaction(function () use ($validated) {
            $last = ProjectVehicle::orderByDesc('sequence_no')->value('sequence_no');
            $sequence = ((int) $last) + 1;
            $vehicle = ProjectVehicle::create([
                'sequence_no' => $sequence,
                'vehicle_code' => 'VEH-' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                'plate_name' => $validated['plate_name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            $vehicle->drivers()->sync($validated['driver_ids'] ?? []);
        });

        return redirect()->route('project-vehicles.index')->with('success', 'Vehicle created successfully.');
    }

    public function show(ProjectVehicle $projectVehicle)
    {
        $projectVehicle->load(['drivers', 'createdBy', 'updatedBy']);
        return view('modules.project-mgmt.vehicles.show', compact('projectVehicle'));
    }

    public function edit(ProjectVehicle $projectVehicle)
    {
        $projectVehicle->load('drivers');
        $users = $this->userOptions();
        return view('modules.project-mgmt.vehicles.edit', compact('projectVehicle', 'users'));
    }

    public function update(Request $request, ProjectVehicle $projectVehicle)
    {
        $validated = $this->validatedVehicle($request);
        $projectVehicle->update([
            'plate_name' => $validated['plate_name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'updated_by' => auth()->id(),
        ]);
        $projectVehicle->drivers()->sync($validated['driver_ids'] ?? []);
        return redirect()->route('project-vehicles.show', $projectVehicle->id)->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Request $request, ProjectVehicle $projectVehicle)
    {
        if ($projectVehicle->gasSlips()->exists()) {
            $message = 'Vehicle cannot be deleted because it already has gas slip records.';
            if ($request->expectsJson() || $request->ajax()) return response()->json(['status' => false, 'message' => $message], 422);
            return back()->with('error', $message);
        }
        $projectVehicle->delete();
        $message = 'Vehicle deleted successfully.';
        if ($request->expectsJson() || $request->ajax()) return response()->json(['status' => true, 'message' => $message]);
        return redirect()->route('project-vehicles.index')->with('success', $message);
    }

    private function validatedVehicle(Request $request): array
    {
        return $request->validate([
            'plate_name' => ['required', 'string', 'max:255'],
            'driver_ids' => ['required', 'array', 'min:1'],
            'driver_ids.*' => ['exists:users,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    private function nextVehicleCode(): string
    {
        $sequence = ((int) ProjectVehicle::orderByDesc('sequence_no')->value('sequence_no')) + 1;
        return 'VEH-' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    private function userOptions()
    {
        return User::query()->where(function ($q) { $q->whereNull('status')->orWhere('status', '!=', 'inactive'); })->orderBy('first_name')->orderBy('last_name')->get();
    }


    private function userInitials(User $user): string
    {
        $first = trim((string) ($user->first_name ?? ''));
        $last = trim((string) ($user->last_name ?? ''));

        if ($first !== '' || $last !== '') {
            return strtoupper(substr($first, 0, 1) . substr($last, 0, 1));
        }

        $name = trim((string) ($user->name ?? ''));
        if ($name !== '') {
            $parts = preg_split('/\s+/', $name);
            return strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
        }

        return 'U';
    }

    private function userName(User $user): string
    {
        return trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->name ?? $user->email ?? 'User #' . $user->id);
    }
}
