<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\WarehouseSupplier;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
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
            'Unauthorized warehouse supplier action.'
        );
    }

    private function canAccess(string $permission): bool
    {
        $user = auth()->user();

        return $user && (
            $user->can($permission)
            || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
        );
    }

    public function index(Request $request)
    {
        $this->access('warehouse.suppliers.view');

        if ($request->ajax()) {
            $suppliers = WarehouseSupplier::query()
                ->select('warehouse_suppliers.*')
                ->orderByDesc('warehouse_suppliers.id');

            $canEditSupplier = $this->canAccess('warehouse.suppliers.edit');
            $canDeleteSupplier = $this->canAccess('warehouse.suppliers.delete');

            return DataTables::eloquent($suppliers)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('supplier_name', 'ilike', '%' . $search . '%')
                                ->orWhere('contact_person', 'ilike', '%' . $search . '%')
                                ->orWhere('phone', 'ilike', '%' . $search . '%')
                                ->orWhere('email', 'ilike', '%' . $search . '%')
                                ->orWhere('address', 'ilike', '%' . $search . '%');
                        });
                    }
                })
                ->addColumn('supplier_display', function ($row) {
                    $html = '<div class="fw-semibold text-dark">' . e($row->supplier_name) . '</div>';

                    if ($row->address) {
                        $html .= '<div class="text-secondary small">' . e(Str::limit($row->address, 55)) . '</div>';
                    }

                    return $html;
                })
                ->addColumn('contact_display', function ($row) {
                    return '
                        <div class="fw-semibold text-dark">' . e($row->contact_person ?: '-') . '</div>
                        <div class="small text-secondary">' . e($row->phone ?: '-') . '</div>
                    ';
                })
                ->addColumn('email_display', function ($row) {
                    return '<span class="text-secondary">' . e($row->email ?: '-') . '</span>';
                })
                ->addColumn('status_display', function ($row) {
                    if ($row->status) {
                        return '<span class="warehouse-badge warehouse-badge-success">Active</span>';
                    }

                    return '<span class="warehouse-badge warehouse-badge-muted">Inactive</span>';
                })
                ->addColumn('action', function ($row) use ($canEditSupplier, $canDeleteSupplier) {
                    if (!$canEditSupplier && !$canDeleteSupplier) {
                        return '';
                    }

                    $html = '<div class="d-inline-flex gap-1">';

                    if ($canEditSupplier) {
                        $html .= '
                            <a href="' . route('warehouse.suppliers.edit', $row) . '"
                               class="wmc-action-btn wmc-action-edit"
                               title="Edit">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z"
                                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        ';
                    }

                    if ($canDeleteSupplier) {
                        $html .= '
                            <button type="button"
                                    class="wmc-action-btn wmc-action-delete delete-supplier"
                                    data-url="' . route('warehouse.suppliers.destroy', $row) . '"
                                    data-name="' . e($row->supplier_name) . '"
                                    title="Delete">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10 11v5M14 11v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>
                        ';
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->rawColumns([
                    'supplier_display',
                    'contact_display',
                    'email_display',
                    'status_display',
                    'action',
                ])
                ->toJson();
        }

        return view('warehouse.suppliers.index');
    }

    public function create()
    {
        $this->access('warehouse.suppliers.create');

        return view('warehouse.suppliers.create');
    }

    public function store(Request $request)
    {
        $this->access('warehouse.suppliers.create');

        WarehouseSupplier::create($this->validated($request));

        return redirect()
            ->route('warehouse.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function edit(WarehouseSupplier $supplier)
    {
        $this->access('warehouse.suppliers.edit');

        return view('warehouse.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, WarehouseSupplier $supplier)
    {
        $this->access('warehouse.suppliers.edit');

        $supplier->update($this->validated($request, $supplier->id));

        return redirect()
            ->route('warehouse.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(WarehouseSupplier $supplier)
    {
        $this->access('warehouse.suppliers.delete');

        $name = $supplier->supplier_name;

        try {
            $supplier->delete();
        } catch (QueryException $e) {
            if (request()->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Supplier "' . $name . '" cannot be deleted because it is already used in purchasing or warehouse records.',
                ], 422);
            }

            return redirect()
                ->route('warehouse.suppliers.index')
                ->with('error', 'Supplier cannot be deleted because it is already used in purchasing or warehouse records.');
        }

        if (request()->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Supplier "' . $name . '" deleted successfully.',
            ]);
        }

        return redirect()
            ->route('warehouse.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'supplier_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warehouse_suppliers', 'supplier_name')->ignore($ignoreId),
            ],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],
        ]);

        $data['status'] = $request->boolean('status');

        return $data;
    }
}