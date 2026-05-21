<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Warehouse\WarehouseLocation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseLocationController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'viewer'), 403);
        $locations = WarehouseLocation::with('branch')->latest()->paginate(10);
        return view('warehouse.locations.index', compact('locations'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $location = null;
        $branches = Branch::where('status', 'active')->orderBy('name')->get();
        return view('warehouse.locations.form', compact('location', 'branches'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('warehouse_locations', 'code')->where(fn ($q) => $q->where('branch_id', $request->branch_id)),
            ],
            'description' => 'nullable|string|max:2000',
            'status' => 'required|in:active,inactive',
        ]);
        $data['code'] = strtoupper($data['code']);
        WarehouseLocation::create($data);
        return redirect()->route('warehouse.locations.index')->with('success', 'Location created successfully.');
    }

    public function edit(WarehouseLocation $location)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $branches = Branch::where('status', 'active')->orderBy('name')->get();
        return view('warehouse.locations.form', compact('location', 'branches'));
    }

    public function update(Request $request, WarehouseLocation $location)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('warehouse_locations', 'code')->where(fn ($q) => $q->where('branch_id', $request->branch_id))->ignore($location->id),
            ],
            'description' => 'nullable|string|max:2000',
            'status' => 'required|in:active,inactive',
        ]);
        $data['code'] = strtoupper($data['code']);
        $location->update($data);
        return redirect()->route('warehouse.locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy(WarehouseLocation $location)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'admin'), 403);
        $location->delete();
        return redirect()->route('warehouse.locations.index')->with('success', 'Location deleted successfully.');
    }
}
