<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Helpers\ActionButtonHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectMgmt\StoreName;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class StoreNameController extends Controller
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
        return view('modules.project-mgmt.store-names.index');
    }

    public function list(Request $request)
    {
        $query = StoreName::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('show_url', fn ($store) => route('store-names.show', $store->id))
            ->addColumn('action', function ($store) {
                $editUrl = auth()->user()->can('projects_mgmt.edit') ? route('store-names.edit', $store->id) : null;
                $deleteUrl = auth()->user()->can('projects_mgmt.delete') ? route('store-names.destroy', $store->id) : null;

                return ActionButtonHelper::editDelete($editUrl, $deleteUrl, $store->name, 'delete-store-name', 'Edit Store', 'Delete Store');
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('modules.project-mgmt.store-names.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatedStore($request);
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        StoreName::create($validated);

        return redirect()->route('store-names.index')->with('success', 'Store name created successfully.');
    }

    public function show(StoreName $storeName)
    {
        $storeName->loadCount('expenses');
        return view('modules.project-mgmt.store-names.show', compact('storeName'));
    }

    public function edit(StoreName $storeName)
    {
        return view('modules.project-mgmt.store-names.edit', compact('storeName'));
    }

    public function update(Request $request, StoreName $storeName)
    {
        $validated = $this->validatedStore($request, $storeName);
        $validated['updated_by'] = auth()->id();

        $storeName->update($validated);

        return redirect()->route('store-names.index')->with('success', 'Store name updated successfully.');
    }

    public function destroy(Request $request, StoreName $storeName)
    {
        if ($storeName->expenses()->exists()) {
            $message = 'Cannot delete this store because it is already used in expenses.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'message' => $message], 422);
            }
            return back()->with('error', $message);
        }

        $storeName->delete();
        $message = 'Store name deleted successfully.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => true, 'message' => $message]);
        }

        return redirect()->route('store-names.index')->with('success', $message);
    }

    private function validatedStore(Request $request, ?StoreName $storeName = null): array
    {
        return $request->validate([
            'code' => ['nullable', 'string', 'max:50', Rule::unique('store_names', 'code')->ignore($storeName?->id)],
            'name' => ['required', 'string', 'max:255', Rule::unique('store_names', 'name')->ignore($storeName?->id)],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:1000'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
