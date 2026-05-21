<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\WarehouseSupplier;
use Illuminate\Http\Request;

class WarehouseSupplierController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'viewer'), 403);
        $suppliers = WarehouseSupplier::latest()->paginate(10);
        return view('warehouse.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $supplier = null;
        return view('warehouse.suppliers.form', compact('supplier'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:warehouse_suppliers,name',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:2000',
            'status' => 'required|in:active,inactive',
        ]);
        WarehouseSupplier::create($data);
        return redirect()->route('warehouse.suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function edit(WarehouseSupplier $supplier)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        return view('warehouse.suppliers.form', compact('supplier'));
    }

    public function update(Request $request, WarehouseSupplier $supplier)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:warehouse_suppliers,name,' . $supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:2000',
            'status' => 'required|in:active,inactive',
        ]);
        $supplier->update($data);
        return redirect()->route('warehouse.suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(WarehouseSupplier $supplier)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'admin'), 403);
        $supplier->delete();
        return redirect()->route('warehouse.suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
