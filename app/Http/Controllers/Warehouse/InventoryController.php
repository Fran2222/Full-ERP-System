<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Warehouse\Inventory;
use App\Models\Warehouse\StockMovement;
use App\Models\Warehouse\WarehouseLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InventoryController extends Controller
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
            'Unauthorized warehouse inventory action.'
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


    private function inventoryNumberHtml(float $value, string $class = 'text-dark'): string
    {
        return '<span class="fw-bold d-block text-end ' . $class . '">' . number_format($value, 2) . '</span>';
    }

    private function serialCountForInventory($row, array $statuses): float
    {
        if (! DB::getSchemaBuilder()->hasTable('warehouse_item_serials')) {
            return 0;
        }

        $query = DB::table('warehouse_item_serials')
            ->where('item_id', $row->item_id)
            ->where('location_id', $row->location_id)
            ->whereIn('status', $statuses);

        if (is_null($row->branch_id)) {
            $query->whereNull('branch_id');
        } else {
            $query->where('branch_id', $row->branch_id);
        }

        return (float) $query->count();
    }

    public function index(Request $request)
    {
        $this->access('warehouse.inventory.view');

        if ($request->ajax()) {
            $inventories = Inventory::with([
                    'item.category',
                    'item.unit',
                    'branch',
                    'location',
                ])
                ->select('warehouse_inventories.*')
                ->orderByDesc('warehouse_inventories.id');

            if ($request->filled('branch_id')) {
                $inventories->where('branch_id', $request->branch_id);
            }

            if ($request->filled('location_id')) {
                $inventories->where('location_id', $request->location_id);
            }

            return DataTables::eloquent($inventories)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('item', function ($itemQuery) use ($search) {
                                $itemQuery->where('code', 'ilike', '%' . $search . '%')
                                    ->orWhere('item_code', 'ilike', '%' . $search . '%')
                                    ->orWhere('name', 'ilike', '%' . $search . '%')
                                    ->orWhere('item_name', 'ilike', '%' . $search . '%')
                                    ->orWhere('description', 'ilike', '%' . $search . '%');
                            })
                            ->orWhereHas('item.category', function ($categoryQuery) use ($search) {
                                $categoryQuery->where('name', 'ilike', '%' . $search . '%');
                            })
                            ->orWhereHas('item.unit', function ($unitQuery) use ($search) {
                                $unitQuery->where('name', 'ilike', '%' . $search . '%')
                                    ->orWhere('abbreviation', 'ilike', '%' . $search . '%');
                            })
                            ->orWhereHas('branch', function ($branchQuery) use ($search) {
                                $branchQuery->where('name', 'ilike', '%' . $search . '%');
                            })
                            ->orWhereHas('location', function ($locationQuery) use ($search) {
                                $locationQuery->where('location_name', 'ilike', '%' . $search . '%')
                                    ->orWhere('name', 'ilike', '%' . $search . '%');
                            });
                        });
                    }
                })
                ->addColumn('item_code', function ($row) {
                    $code = $row->item?->code ?: $row->item?->item_code ?: '-';

                    if (! $row->item_id || $code === '-') {
                        return '<span class="fw-semibold text-muted">' . e($code) . '</span>';
                    }

                    $url = route('warehouse.items.show', $row->item_id) . '?return_url=' . urlencode(request()->fullUrl());

                    return '<a href="' . e($url) . '" class="fw-semibold text-primary text-decoration-none">' . e($code) . '</a>';
                })
                ->addColumn('item_name', function ($row) {
                    $name = $row->item?->name ?: $row->item?->item_name ?: '-';

                    return '<div class="fw-semibold text-dark">' . e($name) . '</div>';
                })
                ->addColumn('category_name', function ($row) {
                    return '<span class="text-secondary">' . e($row->item?->category?->name ?? '-') . '</span>';
                })
                ->addColumn('branch_name', function ($row) {
                    return '<span class="text-secondary">' . e($row->branch?->name ?? 'Central / Unassigned') . '</span>';
                })
                ->addColumn('location_name', function ($row) {
                    $locationName = $row->location?->location_name
                        ?? $row->location?->name
                        ?? '-';

                    return '<span class="text-secondary">' . e($locationName) . '</span>';
                })
                ->addColumn('unit_name', function ($row) {
                    $unitName = $row->item?->unit?->name
                        ?? $row->item?->unit?->abbreviation
                        ?? '-';

                    return '<span class="text-secondary">' . e($unitName) . '</span>';
                })
                ->addColumn('on_hand_quantity', function ($row) {
                    $available = (float) $row->quantity;
                    $borrowed = $this->serialCountForInventory($row, ['borrowed']);
                    $unavailable = $this->serialCountForInventory($row, ['for_repair', 'damaged', 'lost']);

                    return $this->inventoryNumberHtml($available + $borrowed + $unavailable, 'text-dark');
                })
                ->addColumn('borrowed_quantity', function ($row) {
                    $borrowed = $this->serialCountForInventory($row, ['borrowed']);

                    return $this->inventoryNumberHtml($borrowed, $borrowed > 0 ? 'text-warning' : 'text-secondary');
                })
                ->addColumn('available_quantity', function ($row) {
                    $item = $row->item;
                    $reorderLevel = $item?->reorder_level ?? $item?->minimum_stock ?? 0;
                    $lowStock = (float) $row->quantity <= (float) $reorderLevel;

                    return $this->inventoryNumberHtml((float) $row->quantity, $lowStock ? 'text-danger' : 'text-success');
                })
                ->editColumn('quantity', function ($row) {
                    $item = $row->item;
                    $reorderLevel = $item?->reorder_level ?? $item?->minimum_stock ?? 0;
                    $lowStock = (float) $row->quantity <= (float) $reorderLevel;

                    return '<span class="fw-bold d-block text-end ' . ($lowStock ? 'text-danger' : 'text-success') . '">'
                        . number_format((float) $row->quantity, 2)
                        . '</span>';
                })
                ->addColumn('stock_status', function ($row) {
                    $item = $row->item;
                    $reorderLevel = $item?->reorder_level ?? $item?->minimum_stock ?? 0;
                    $lowStock = (float) $row->quantity <= (float) $reorderLevel;

                    if ($lowStock) {
                        return '<span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-2">Low Stock</span>';
                    }

                    return '<span class="badge rounded-pill bg-success-subtle text-success px-3 py-2">OK</span>';
                })
                ->rawColumns([
                    'item_code',
                    'item_name',
                    'category_name',
                    'branch_name',
                    'location_name',
                    'unit_name',
                    'on_hand_quantity',
                    'borrowed_quantity',
                    'available_quantity',
                    'quantity',
                    'stock_status',
                ])
                ->toJson();
        }

        $branches = Branch::orderBy('name')->get();
        $locations = WarehouseLocation::with('branch')->orderBy('location_name')->get();

        $canStockIn = $this->canAccess('warehouse.stock_in.create');
        $canStockOut = $this->canAccess('warehouse.stock_out.create');
        $canViewLedger = $this->canAccess('warehouse.ledger.view');

        return view('warehouse.inventory.index', compact(
            'branches',
            'locations',
            'canStockIn',
            'canStockOut',
            'canViewLedger'
        ));
    }

    public function stockIn(Request $request)
    {
        $this->access('warehouse.stock_in.create');

        $data = $request->validate([
            'item_id' => ['required', 'exists:warehouse_items,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'location_id' => ['required', 'exists:warehouse_locations,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            $location = WarehouseLocation::findOrFail($data['location_id']);
            $branchId = $data['branch_id'] ?? $location->branch_id;

            abort_unless($branchId, 422, 'Selected location has no assigned branch.');

            $inventory = Inventory::firstOrCreate(
                [
                    'item_id' => $data['item_id'],
                    'branch_id' => $branchId,
                    'location_id' => $data['location_id'],
                ],
                [
                    'quantity' => 0,
                ]
            );

            $inventory->quantity = (float) $inventory->quantity + (float) $data['quantity'];
            $inventory->save();

            StockMovement::create([
                'item_id' => $data['item_id'],
                'location_id' => $data['location_id'],
                'movement_type' => 'stock_in',
                'quantity' => (float) $data['quantity'],
                'balance_after' => $inventory->quantity,
                'reference_type' => $data['reference_no'] ?: 'STOCK-IN-' . now()->format('YmdHis'),
                'reference_id' => null,
                'remarks' => $data['remarks'] ?? null,
                'transaction_date' => now(),
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('warehouse.inventory')
            ->with('success', 'Stock in saved successfully.');
    }

    public function stockOut(Request $request)
    {
        $this->access('warehouse.stock_out.create');

        $data = $request->validate([
            'item_id' => ['required', 'exists:warehouse_items,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'location_id' => ['required', 'exists:warehouse_locations,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            $location = WarehouseLocation::findOrFail($data['location_id']);
            $branchId = $data['branch_id'] ?? $location->branch_id;

            abort_unless($branchId, 422, 'Selected location has no assigned branch.');

            $inventory = Inventory::where('item_id', $data['item_id'])
                ->where('branch_id', $branchId)
                ->where('location_id', $data['location_id'])
                ->lockForUpdate()
                ->first();

            if (!$inventory || (float) $inventory->quantity < (float) $data['quantity']) {
                abort(422, 'Insufficient stock for this item/location.');
            }

            $inventory->quantity = (float) $inventory->quantity - (float) $data['quantity'];
            $inventory->save();

            StockMovement::create([
                'item_id' => $data['item_id'],
                'location_id' => $data['location_id'],
                'movement_type' => 'stock_out',
                'quantity' => -abs((float) $data['quantity']),
                'balance_after' => $inventory->quantity,
                'reference_type' => $data['reference_no'] ?: 'STOCK-OUT-' . now()->format('YmdHis'),
                'reference_id' => null,
                'remarks' => $data['remarks'] ?? null,
                'transaction_date' => now(),
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('warehouse.inventory')
            ->with('success', 'Stock out saved successfully.');
    }

    public function transfer(Request $request)
    {
        $this->access('warehouse.transfer.create');

        $data = $request->validate([
            'item_id' => ['required', 'exists:warehouse_items,id'],
            'from_branch_id' => ['nullable', 'exists:branches,id'],
            'from_location_id' => ['required', 'exists:warehouse_locations,id'],
            'to_branch_id' => ['nullable', 'exists:branches,id'],
            'to_location_id' => ['required', 'exists:warehouse_locations,id', 'different:from_location_id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $data['from_branch_id'] = $data['from_branch_id'] ?? null;
        $data['to_branch_id'] = $data['to_branch_id'] ?? null;
        $data['reference_no'] = $data['reference_no'] ?? null;
        $data['remarks'] = $data['remarks'] ?? null;

        DB::transaction(function () use ($data) {
            WarehouseLocation::findOrFail($data['from_location_id']);
            WarehouseLocation::findOrFail($data['to_location_id']);

            $fromInventoryQuery = Inventory::where('item_id', $data['item_id'])
                ->where('location_id', $data['from_location_id'])
                ->lockForUpdate();

            if ($data['from_branch_id']) {
                $fromInventoryQuery->where('branch_id', $data['from_branch_id']);
            } else {
                $fromInventoryQuery->whereNull('branch_id');
            }

            $fromInventory = $fromInventoryQuery->first();

            if (! $fromInventory || (float) $fromInventory->quantity < (float) $data['quantity']) {
                abort(422, 'Insufficient stock in source location.');
            }

            $toInventory = Inventory::firstOrCreate(
                [
                    'item_id' => $data['item_id'],
                    'branch_id' => $data['to_branch_id'],
                    'location_id' => $data['to_location_id'],
                ],
                [
                    'quantity' => 0,
                ]
            );

            $qty = abs((float) $data['quantity']);
            $ref = $data['reference_no'] ?: 'TRANSFER-' . now()->format('YmdHis');

            $fromInventory->quantity = (float) $fromInventory->quantity - $qty;
            $fromInventory->save();

            $toInventory->quantity = (float) $toInventory->quantity + $qty;
            $toInventory->save();

            StockMovement::create([
                'item_id' => $data['item_id'],
                'location_id' => $data['from_location_id'],
                'movement_type' => 'transfer_out',
                'quantity' => -$qty,
                'balance_after' => $fromInventory->quantity,
                'reference_type' => $ref,
                'reference_id' => null,
                'remarks' => $data['remarks'],
                'transaction_date' => now(),
                'created_by' => auth()->id(),
            ]);

            StockMovement::create([
                'item_id' => $data['item_id'],
                'location_id' => $data['to_location_id'],
                'movement_type' => 'transfer_in',
                'quantity' => $qty,
                'balance_after' => $toInventory->quantity,
                'reference_type' => $ref,
                'reference_id' => null,
                'remarks' => $data['remarks'],
                'transaction_date' => now(),
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('warehouse.inventory')
            ->with('success', 'Stock transfer saved successfully.');
    }

    public function adjustment(Request $request)
    {
        $this->access('warehouse.adjustment.create');

        $data = $request->validate([
            'item_id' => ['required', 'exists:warehouse_items,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'location_id' => ['required', 'exists:warehouse_locations,id'],
            'adjustment_type' => ['required', 'in:add,deduct'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            WarehouseLocation::findOrFail($data['location_id']);

            $inventory = Inventory::firstOrCreate(
                [
                    'item_id' => $data['item_id'],
                    'branch_id' => $data['branch_id'],
                    'location_id' => $data['location_id'],
                ],
                [
                    'quantity' => 0,
                ]
            );

            $qty = (float) $data['quantity'];

            if ($data['adjustment_type'] === 'deduct') {
                if ((float) $inventory->quantity < $qty) {
                    abort(422, 'Insufficient stock for deduction adjustment.');
                }

                $qty = -abs($qty);
            }

            $inventory->quantity = (float) $inventory->quantity + $qty;
            $inventory->save();

            StockMovement::create([
                'item_id' => $data['item_id'],
                'location_id' => $data['location_id'],
                'movement_type' => $data['adjustment_type'] === 'add' ? 'adjustment_add' : 'adjustment_deduct',
                'quantity' => $qty,
                'balance_after' => $inventory->quantity,
                'reference_type' => $data['reference_no'] ?: 'ADJ-' . now()->format('YmdHis'),
                'reference_id' => null,
                'remarks' => $data['remarks'] ?? null,
                'transaction_date' => now(),
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('warehouse.inventory')
            ->with('success', 'Stock adjustment saved successfully.');
    }
}