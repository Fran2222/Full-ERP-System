<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\WarehouseUnit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class UnitController extends Controller
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
            'Unauthorized warehouse unit action.'
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
        $this->access('warehouse.units.view');

        if ($request->ajax()) {
            $units = WarehouseUnit::query()
                ->select('warehouse_units.*');

            $canEditUnit = $this->canAccess('warehouse.units.edit');
            $canDeleteUnit = $this->canAccess('warehouse.units.delete');

            return DataTables::eloquent($units)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('name', 'ilike', '%' . $search . '%')
                                ->orWhere('abbreviation', 'ilike', '%' . $search . '%');
                        });
                    }
                })
                ->editColumn('name', function ($row) {
                    return '<div class="fw-semibold text-dark">' . e($row->name) . '</div>';
                })
                ->editColumn('abbreviation', function ($row) {
                    return '<span class="text-secondary">' . e($row->abbreviation ?: '-') . '</span>';
                })
                ->addColumn('action', function ($row) use ($canEditUnit, $canDeleteUnit) {
                    if (!$canEditUnit && !$canDeleteUnit) {
                        return '';
                    }

                    $html = '<div class="d-inline-flex gap-1">';

                    if ($canEditUnit) {
                        $html .= '
                            <a href="' . route('warehouse.units.edit', $row) . '"
                               class="btn btn-sm btn-outline-primary warehouse-action-btn"
                               title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                        ';
                    }

                    if ($canDeleteUnit) {
                        $html .= '
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger warehouse-action-btn delete-unit"
                                    data-url="' . route('warehouse.units.destroy', $row) . '"
                                    data-name="' . e($row->name) . '"
                                    title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        ';
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->rawColumns(['name', 'abbreviation', 'action'])
                ->toJson();
        }

        return view('warehouse.units.index');
    }

    public function create()
    {
        $this->access('warehouse.units.create');

        return view('warehouse.units.create');
    }

    public function store(Request $request)
    {
        $this->access('warehouse.units.create');

        $data = $this->validated($request);

        WarehouseUnit::create($data);

        return redirect()
            ->route('warehouse.units.index')
            ->with('success', 'Unit created successfully.');
    }

    public function edit(WarehouseUnit $unit)
    {
        $this->access('warehouse.units.edit');

        return view('warehouse.units.edit', compact('unit'));
    }

    public function update(Request $request, WarehouseUnit $unit)
    {
        $this->access('warehouse.units.edit');

        $data = $this->validated($request, $unit->id);

        $unit->update($data);

        return redirect()
            ->route('warehouse.units.index')
            ->with('success', 'Unit updated successfully.');
    }

    public function destroy(WarehouseUnit $unit)
    {
        $this->access('warehouse.units.delete');

        $name = $unit->name;
        $unit->delete();

        if (request()->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Unit "' . $name . '" deleted successfully.',
            ]);
        }

        return redirect()
            ->route('warehouse.units.index')
            ->with('success', 'Unit deleted successfully.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warehouse_units', 'name')->ignore($ignoreId),
            ],
            'abbreviation' => [
                'required',
                'string',
                'max:30',
                Rule::unique('warehouse_units', 'abbreviation')->ignore($ignoreId),
            ],
        ]);

        $data['abbreviation'] = strtoupper($data['abbreviation']);

        return $data;
    }
}