<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Warehouse\WarehouseLocation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class LocationController extends Controller
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
            'Unauthorized warehouse location action.'
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
        $this->access('warehouse.locations.view');

        if ($request->ajax()) {
            $locations = WarehouseLocation::with('branch')
                ->select('warehouse_locations.*');

            $canEditLocation = $this->canAccess('warehouse.locations.edit');
            $canDeleteLocation = $this->canAccess('warehouse.locations.delete');

            return DataTables::eloquent($locations)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('location_code', 'ilike', '%' . $search . '%')
                                ->orWhere('location_name', 'ilike', '%' . $search . '%')
                                ->orWhere('name', 'ilike', '%' . $search . '%')
                                ->orWhere('location_type', 'ilike', '%' . $search . '%')
                                ->orWhere('address', 'ilike', '%' . $search . '%')
                                ->orWhereHas('branch', function ($branchQuery) use ($search) {
                                    $branchQuery->where('name', 'ilike', '%' . $search . '%')
                                        ->orWhere('code', 'ilike', '%' . $search . '%');
                                });
                        });
                    }
                })
                ->editColumn('location_code', function ($row) {
                    return '<span class="fw-semibold text-primary">' . e($row->location_code ?: '-') . '</span>';
                })
                ->editColumn('location_name', function ($row) {
                    $locationName = $row->location_name ?: $row->name ?: '-';

                    $html = '<div class="fw-semibold text-dark">' . e($locationName) . '</div>';

                    if ($row->address) {
                        $html .= '<div class="text-secondary small">' . e(\Illuminate\Support\Str::limit($row->address, 45)) . '</div>';
                    }

                    return $html;
                })
                ->editColumn('location_type', function ($row) {
                    return '<span class="text-secondary">' . e($row->location_type ?: '-') . '</span>';
                })
                ->addColumn('branch_name', function ($row) {
                    return '<span class="text-secondary">' . e($row->branch?->name ?? '-') . '</span>';
                })
                ->editColumn('status', function ($row) {
                    if ($row->status) {
                        return '<span class="badge rounded-pill bg-success-subtle text-success px-3 py-2">Active</span>';
                    }

                    return '<span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-2">Inactive</span>';
                })
                ->addColumn('action', function ($row) use ($canEditLocation, $canDeleteLocation) {
                    if (!$canEditLocation && !$canDeleteLocation) {
                        return '';
                    }

                    $html = '<div class="d-inline-flex gap-1">';

                    if ($canEditLocation) {
                        $html .= '
                            <a href="' . route('warehouse.locations.edit', $row) . '"
                               class="btn btn-sm btn-outline-primary warehouse-action-btn"
                               title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                        ';
                    }

                    if ($canDeleteLocation) {
                        $locationName = $row->location_name ?: $row->name ?: $row->location_code;

                        $html .= '
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger warehouse-action-btn delete-location"
                                    data-url="' . route('warehouse.locations.destroy', $row) . '"
                                    data-name="' . e($locationName) . '"
                                    title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        ';
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->rawColumns([
                    'location_code',
                    'location_name',
                    'location_type',
                    'branch_name',
                    'status',
                    'action',
                ])
                ->toJson();
        }

        return view('warehouse.locations.index');
    }

    public function create()
    {
        $this->access('warehouse.locations.create');

        $branches = Branch::orderBy('name')->get();

        return view('warehouse.locations.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $this->access('warehouse.locations.create');

        WarehouseLocation::create($this->validated($request));

        return redirect()
            ->route('warehouse.locations.index')
            ->with('success', 'Location created successfully.');
    }

    public function edit(WarehouseLocation $location)
    {
        $this->access('warehouse.locations.edit');

        $branches = Branch::orderBy('name')->get();

        return view('warehouse.locations.edit', compact('location', 'branches'));
    }

    public function update(Request $request, WarehouseLocation $location)
    {
        $this->access('warehouse.locations.edit');

        $location->update($this->validated($request, $location->id));

        return redirect()
            ->route('warehouse.locations.index')
            ->with('success', 'Location updated successfully.');
    }

    public function destroy(WarehouseLocation $location)
    {
        $this->access('warehouse.locations.delete');

        $locationName = $location->location_name ?: $location->name ?: $location->location_code;

        $location->delete();

        if (request()->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Location "' . $locationName . '" deleted successfully.',
            ]);
        }

        return redirect()
            ->route('warehouse.locations.index')
            ->with('success', 'Location deleted successfully.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'location_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warehouse_locations', 'location_code')->ignore($ignoreId),
            ],
            'location_name' => ['required', 'string', 'max:255'],
            'location_type' => ['required', 'string', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],
        ]);

        $data['location_code'] = strtoupper($data['location_code']);
        $data['name'] = $data['location_name'];
        $data['status'] = $request->boolean('status');

        return $data;
    }
}