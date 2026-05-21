<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\WarehouseCategory;
use App\Models\Warehouse\WarehouseItem;
use App\Models\Warehouse\WarehouseUnit;
use Illuminate\Http\Request;

class WarehouseItemController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'viewer'), 403);
        $items = WarehouseItem::with(['category', 'unit'])->latest()->paginate(10);
        return view('warehouse.items.index', compact('items'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $item = null;
        $categories = WarehouseCategory::where('status', 'active')->orderBy('name')->get();
        $units = WarehouseUnit::where('status', 'active')->orderBy('name')->get();
        return view('warehouse.items.form', compact('item', 'categories', 'units'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $data = $request->validate([
            'item_code' => 'required|string|max:100|unique:warehouse_items,item_code',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:warehouse_categories,id',
            'unit_id' => 'nullable|exists:warehouse_units,id',
            'description' => 'nullable|string|max:2000',
            'minimum_stock' => 'required|numeric|min:0',
            'track_serial' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
        ]);
        $data['item_code'] = strtoupper($data['item_code']);
        $data['track_serial'] = $request->boolean('track_serial');
        WarehouseItem::create($data);
        return redirect()->route('warehouse.items.index')->with('success', 'Item created successfully.');
    }

    public function edit(WarehouseItem $item)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $categories = WarehouseCategory::where('status', 'active')->orderBy('name')->get();
        $units = WarehouseUnit::where('status', 'active')->orderBy('name')->get();
        return view('warehouse.items.form', compact('item', 'categories', 'units'));
    }

    public function update(Request $request, WarehouseItem $item)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $data = $request->validate([
            'item_code' => 'required|string|max:100|unique:warehouse_items,item_code,' . $item->id,
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:warehouse_categories,id',
            'unit_id' => 'nullable|exists:warehouse_units,id',
            'description' => 'nullable|string|max:2000',
            'minimum_stock' => 'required|numeric|min:0',
            'track_serial' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
        ]);
        $data['item_code'] = strtoupper($data['item_code']);
        $data['track_serial'] = $request->boolean('track_serial');
        $item->update($data);
        return redirect()->route('warehouse.items.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(WarehouseItem $item)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'admin'), 403);
        $item->delete();
        return redirect()->route('warehouse.items.index')->with('success', 'Item deleted successfully.');
    }
}
