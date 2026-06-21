<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Helpers\ActionButtonHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectMgmt\ProjectGasSlip;
use App\Models\ProjectMgmt\ProjectVehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ProjectGasSlipController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:projects_mgmt.view')->only(['index', 'list', 'show', 'vehicleDrivers']);
        $this->middleware('permission:projects_mgmt.create')->only(['create', 'store']);
        $this->middleware('permission:projects_mgmt.edit')->only(['edit', 'update', 'toggleDone']);
        $this->middleware('permission:projects_mgmt.delete')->only(['destroy']);
    }

    public function index()
    {
        $vehicles = ProjectVehicle::where('status', 'active')->orderBy('plate_name')->get();
        return view('modules.project-mgmt.gas-slips.index', compact('vehicles'));
    }

    public function list(Request $request)
    {
        $query = ProjectGasSlip::query()
            ->select('project_gas_slips.*')
            ->with(['vehicle', 'drivers'])
            ->orderByDesc('project_gas_slips.id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::eloquent($query)
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');
                if (! $search) return;
                $query->where(function ($q) use ($search) {
                    $q->where('po_no', 'ILIKE', "%{$search}%")
                      ->orWhereHas('vehicle', fn ($v) => $v->where('plate_name', 'ILIKE', "%{$search}%")->orWhere('vehicle_code', 'ILIKE', "%{$search}%"))
                      ->orWhereHas('drivers', fn ($d) => $d->where('first_name', 'ILIKE', "%{$search}%")->orWhere('last_name', 'ILIKE', "%{$search}%")->orWhere('email', 'ILIKE', "%{$search}%"));
                });
            })
            ->addColumn('done_checkbox', function ($slip) {
                $checked = strtolower($slip->status) === 'returned' ? 'checked' : '';
                $disabled = auth()->user()->can('projects_mgmt.edit') ? '' : 'disabled';
                return '<div class="form-check d-flex justify-content-center"><input class="form-check-input gas-slip-done-toggle" type="checkbox" data-url="' . e(route('project-gas-slips.toggle-done', $slip->id)) . '" ' . $checked . ' ' . $disabled . '></div>';
            })
            ->addColumn('plate_name', fn ($slip) => optional($slip->vehicle)->plate_name ?: '-')
            ->addColumn('drivers_text', fn ($slip) => $slip->drivers->map(fn ($u) => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ($u->email ?? 'User #' . $u->id))->filter()->implode(', ') ?: '-')
            ->addColumn('amount_formatted', fn ($slip) => '₱ ' . number_format((float) $slip->amount, 2))
            ->addColumn('status_badge', function ($slip) {
                return strtolower($slip->status) === 'returned'
                    ? '<span class="text-success fw-semibold">Returned</span>'
                    : '<span class="text-warning fw-semibold">Issued</span>';
            })
            ->addColumn('show_url', fn ($slip) => route('project-gas-slips.show', $slip->id))
            ->addColumn('action', function ($slip) {
                $edit = auth()->user()->can('projects_mgmt.edit') ? route('project-gas-slips.edit', $slip->id) : null;
                $delete = auth()->user()->can('projects_mgmt.delete') ? route('project-gas-slips.destroy', $slip->id) : null;

                return ActionButtonHelper::editDelete(
                    $edit,
                    $delete,
                    $slip->po_no,
                    'delete-project-gas-slip',
                    'Edit Gas Slip',
                    'Delete Gas Slip'
                );
            })
            ->rawColumns(['done_checkbox', 'status_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        $vehicles = $this->vehicleOptions();
        $vehicleDrivers = $this->vehicleDriversMap();
        return view('modules.project-mgmt.gas-slips.create', compact('vehicles', 'vehicleDrivers'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedGasSlip($request);
        $slip = new ProjectGasSlip();
        $slip->fill([
            'po_no' => $validated['po_no'],
            'project_vehicle_id' => $validated['project_vehicle_id'],
            'location' => $validated['location'] ?? null,
            'amount' => $validated['amount'],
            'issued_date' => $validated['issued_date'],
            'returned_date' => null,
            'status' => 'issued',
            'remarks' => $validated['remarks'] ?? null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
        $this->handleAttachment($request, $slip);
        $slip->save();
        $slip->drivers()->sync($validated['driver_ids'] ?? []);
        return redirect()->route('project-gas-slips.index')->with('success', 'Gas slip created successfully.');
    }

    public function show(ProjectGasSlip $projectGasSlip)
    {
        $projectGasSlip->load(['vehicle', 'drivers', 'createdBy', 'updatedBy']);
        return view('modules.project-mgmt.gas-slips.show', compact('projectGasSlip'));
    }

    public function edit(ProjectGasSlip $projectGasSlip)
    {
        $projectGasSlip->load(['vehicle.drivers', 'drivers']);
        $vehicles = $this->vehicleOptions();
        $vehicleDrivers = $this->vehicleDriversMap();
        return view('modules.project-mgmt.gas-slips.edit', compact('projectGasSlip', 'vehicles', 'vehicleDrivers'));
    }

    public function update(Request $request, ProjectGasSlip $projectGasSlip)
    {
        $validated = $this->validatedGasSlip($request, true);
        $projectGasSlip->fill([
            'po_no' => $validated['po_no'],
            'project_vehicle_id' => $validated['project_vehicle_id'],
            'location' => $validated['location'] ?? null,
            'amount' => $validated['amount'],
            'issued_date' => $validated['issued_date'],
            'returned_date' => $validated['returned_date'] ?? $projectGasSlip->returned_date,
            'remarks' => $validated['remarks'] ?? null,
            'updated_by' => auth()->id(),
        ]);
        $this->handleAttachment($request, $projectGasSlip);
        $projectGasSlip->save();
        $projectGasSlip->drivers()->sync($validated['driver_ids'] ?? []);
        return redirect()->route('project-gas-slips.show', $projectGasSlip->id)->with('success', 'Gas slip updated successfully.');
    }

    public function destroy(Request $request, ProjectGasSlip $projectGasSlip)
    {
        if ($projectGasSlip->attachment_path) Storage::disk('public')->delete($projectGasSlip->attachment_path);
        $projectGasSlip->delete();
        $message = 'Gas slip deleted successfully.';
        if ($request->expectsJson() || $request->ajax()) return response()->json(['status' => true, 'message' => $message]);
        return redirect()->route('project-gas-slips.index')->with('success', $message);
    }

    public function toggleDone(Request $request, ProjectGasSlip $projectGasSlip)
    {
        $done = $request->boolean('done');
        $projectGasSlip->update([
            'status' => $done ? 'returned' : 'issued',
            'returned_date' => $done ? now() : null,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'status' => true,
            'done' => $done,
            'status_label' => $done ? 'Returned' : 'Issued',
            'returned_date' => optional($projectGasSlip->fresh()->returned_date)->format('M d, Y h:i A'),
        ]);
    }

    public function vehicleDrivers(ProjectVehicle $vehicle)
    {
        return response()->json($vehicle->drivers()->get()->map(fn ($u) => [
            'id' => $u->id,
            'name' => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ($u->email ?? 'User #' . $u->id),
        ]));
    }

    private function validatedGasSlip(Request $request, bool $isEdit = false): array
    {
        return $request->validate([
            'po_no' => ['required', 'string', 'max:255'],
            'project_vehicle_id' => ['required', 'exists:project_vehicles,id'],
            'driver_ids' => ['required', 'array', 'min:1'],
            'driver_ids.*' => ['exists:users,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'issued_date' => ['required', 'date'],
            'returned_date' => ['nullable', 'date'],
            'attachment' => ['nullable', 'file', 'max:5120'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function vehicleOptions()
    {
        return ProjectVehicle::with('drivers')->where('status', 'active')->orderBy('plate_name')->get();
    }

    private function vehicleDriversMap()
    {
        return ProjectVehicle::with('drivers')->get()->mapWithKeys(function ($vehicle) {
            return [$vehicle->id => $vehicle->drivers->map(fn ($u) => [
                'id' => $u->id,
                'name' => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ($u->email ?? 'User #' . $u->id),
            ])->values()];
        });
    }

    private function handleAttachment(Request $request, ProjectGasSlip $slip): void
    {
        if (! $request->hasFile('attachment')) return;
        if ($slip->exists && $slip->attachment_path) Storage::disk('public')->delete($slip->attachment_path);
        $file = $request->file('attachment');
        $path = $file->store('project-gas-slips', 'public');
        $slip->attachment_path = $path;
        $slip->attachment_original_name = $file->getClientOriginalName();
        $slip->attachment_mime_type = $file->getMimeType();
        $slip->attachment_size = $file->getSize();
    }
}
