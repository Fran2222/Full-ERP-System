<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\WarehouseCategory;
use Illuminate\Http\Request;

class WarehouseCategoryController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'viewer'), 403);
        $categories = WarehouseCategory::latest()->paginate(10);
        return view('warehouse.categories.index', compact('categories'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $category = null;
        return view('warehouse.categories.form', compact('category'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:warehouse_categories,name',
            'description' => 'nullable|string|max:2000',
            'status' => 'required|in:active,inactive',
        ]);
        WarehouseCategory::create($data);
        return redirect()->route('warehouse.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(WarehouseCategory $category)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        return view('warehouse.categories.form', compact('category'));
    }

    public function update(Request $request, WarehouseCategory $category)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'encoder'), 403);
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:warehouse_categories,name,' . $category->id,
            'description' => 'nullable|string|max:2000',
            'status' => 'required|in:active,inactive',
        ]);
        $category->update($data);
        return redirect()->route('warehouse.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(WarehouseCategory $category)
    {
        abort_unless(auth()->user()->hasModuleAccess('warehouse', 'admin'), 403);
        $category->delete();
        return redirect()->route('warehouse.categories.index')->with('success', 'Category deleted successfully.');
    }
}
