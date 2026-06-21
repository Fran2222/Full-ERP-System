<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Helpers\ActionButtonHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectMgmt\RfpType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RfpTypeController extends Controller
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
        return view('modules.project-mgmt.rfp-types.index');
    }

    public function list(Request $request)
    {
        $rfpTypes = RfpType::query()->select('rfp_types.*');

        if ($request->filled('status')) {
            $rfpTypes->where('status', $request->status);
        }

        return DataTables::eloquent($rfpTypes)
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');
                if (! $search) {
                    return;
                }

                $query->where(function ($q) use ($search) {
                    $q->where('code', 'ILIKE', "%{$search}%")
                        ->orWhere('name', 'ILIKE', "%{$search}%")
                        ->orWhere('description', 'ILIKE', "%{$search}%")
                        ->orWhere('status', 'ILIKE', "%{$search}%");
                });
            })
            ->addColumn('created_at_formatted', fn ($type) => optional($type->created_at)->format('M d, Y') ?: '-')
            ->addColumn('show_url', fn ($type) => route('rfp-types.show', $type->id))
            ->addColumn('action', function ($type) {
                $editUrl = auth()->user()->can('projects_mgmt.edit') ? route('rfp-types.edit', $type->id) : null;
                $deleteUrl = auth()->user()->can('projects_mgmt.delete') ? route('rfp-types.destroy', $type->id) : null;

                return ActionButtonHelper::editDelete($editUrl, $deleteUrl, $type->name, 'delete-rfp-type', 'Edit RFP Type', 'Delete RFP Type');
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('modules.project-mgmt.rfp-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:rfp_types,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        RfpType::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('rfp-types.index')->with('success', 'RFP type created successfully.');
    }

    public function show(RfpType $rfpType)
    {
        return view('modules.project-mgmt.rfp-types.show', compact('rfpType'));
    }

    public function edit(RfpType $rfpType)
    {
        return view('modules.project-mgmt.rfp-types.edit', compact('rfpType'));
    }

    public function update(Request $request, RfpType $rfpType)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:rfp_types,code,' . $rfpType->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $rfpType->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('rfp-types.index')->with('success', 'RFP type updated successfully.');
    }

    public function destroy(Request $request, RfpType $rfpType)
    {
        if ($rfpType->rfps()->exists()) {
            $message = 'Cannot delete this RFP type because it is already used by RFP records.';
            if ($request->ajax()) {
                return response()->json(['status' => false, 'message' => $message], 422);
            }
            return redirect()->route('rfp-types.index')->with('error', $message);
        }

        $rfpType->delete();

        if ($request->ajax()) {
            return response()->json(['status' => true, 'message' => 'RFP type deleted successfully.']);
        }

        return redirect()->route('rfp-types.index')->with('success', 'RFP type deleted successfully.');
    }
}
