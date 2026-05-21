<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\WarehouseUnit;
use Illuminate\Http\Request;

class WarehouseUnitController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'viewer'), 403);
        $units = WarehouseUnit::latest()->paginate(10);
        return view('warehouse.units.index', compact('units'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $unit = null;
        return view('warehouse.units.form', compact('unit'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:warehouse_units,name',
            'abbreviation' => 'required|string|max:30|unique:warehouse_units,abbreviation',
            'status' => 'required|in:active,inactive',
        ]);
        $data['abbreviation'] = strtoupper($data['abbreviation']);
        WarehouseUnit::create($data);
        return redirect()->route('warehouse.units.index')->with('success', 'Unit created successfully.');
    }

    public function edit(WarehouseUnit $unit)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        return view('warehouse.units.form', compact('unit'));
    }

    public function update(Request $request, WarehouseUnit $unit)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:warehouse_units,name,' . $unit->id,
            'abbreviation' => 'required|string|max:30|unique:warehouse_units,abbreviation,' . $unit->id,
            'status' => 'required|in:active,inactive',
        ]);
        $data['abbreviation'] = strtoupper($data['abbreviation']);
        $unit->update($data);
        return redirect()->route('warehouse.units.index')->with('success', 'Unit updated successfully.');
    }

    public function destroy(WarehouseUnit $unit)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'admin'), 403);
        $unit->delete();
        return redirect()->route('warehouse.units.index')->with('success', 'Unit deleted successfully.');
    }
}
