<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\WarehouseCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    private function access(string $permission): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->can($permission)
                || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
            ),
            403,
            'Unauthorized warehouse category action.'
        );
    }

    public function index(Request $request)
    {
        $this->access('warehouse.categories.view');

        if ($request->ajax()) {
            $categories = WarehouseCategory::query()
                ->select('warehouse_categories.*');

            $canEditCategory = $this->canAccess('warehouse.categories.edit');
            $canDeleteCategory = $this->canAccess('warehouse.categories.delete');

            return DataTables::eloquent($categories)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('name', 'ilike', '%' . $search . '%')
                                ->orWhere('description', 'ilike', '%' . $search . '%');
                        });
                    }
                })
                ->editColumn('name', function ($row) {
                    return '<div class="fw-semibold text-dark">' . e($row->name) . '</div>';
                })
                ->editColumn('description', function ($row) {
                    return '<span class="text-secondary">' . e($row->description ?: '-') . '</span>';
                })
                ->editColumn('status', function ($row) {
                    if ($row->status) {
                        return '<span class="badge rounded-pill bg-success-subtle text-success px-3 py-2">Active</span>';
                    }

                    return '<span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-2">Inactive</span>';
                })
                ->addColumn('action', function ($row) use ($canEditCategory, $canDeleteCategory) {
                    if (!$canEditCategory && !$canDeleteCategory) {
                        return '';
                    }

                    $html = '<div class="d-inline-flex gap-1">';

                    if ($canEditCategory) {
                        $html .= '
                            <a href="' . route('warehouse.categories.edit', $row) . '"
                               class="btn btn-sm btn-outline-primary warehouse-action-btn"
                               title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                        ';
                    }

                    if ($canDeleteCategory) {
                        $html .= '
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger warehouse-action-btn delete-category"
                                    data-url="' . route('warehouse.categories.destroy', $row) . '"
                                    data-name="' . e($row->name) . '"
                                    title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        ';
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->rawColumns(['name', 'description', 'status', 'action'])
                ->toJson();
        }

        return view('warehouse.categories.index');
    }

    public function create()
    {
        $this->access('warehouse.categories.create');

        return view('warehouse.categories.create');
    }

    public function store(Request $request)
    {
        $this->access('warehouse.categories.create');

        WarehouseCategory::create($this->validated($request));

        return redirect()
            ->route('warehouse.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(WarehouseCategory $category)
    {
        $this->access('warehouse.categories.edit');

        return view('warehouse.categories.edit', compact('category'));
    }

    public function update(Request $request, WarehouseCategory $category)
    {
        $this->access('warehouse.categories.edit');

        $category->update($this->validated($request, $category->id));

        return redirect()
            ->route('warehouse.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(WarehouseCategory $category)
    {
        $this->access('warehouse.categories.delete');

        $name = $category->name;
        $category->delete();

        if (request()->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Category "' . $name . '" deleted successfully.',
            ]);
        }

        return redirect()
            ->route('warehouse.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    private function canAccess(string $permission): bool
    {
        $user = auth()->user();

        return $user && (
            $user->can($permission)
            || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
        );
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warehouse_categories', 'name')->ignore($ignoreId),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],
        ]);

        $data['status'] = $request->boolean('status');

        return $data;
    }
}