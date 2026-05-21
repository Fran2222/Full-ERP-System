<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Warehouse\Inventory;
use App\Models\Warehouse\StockMovement;
use App\Models\Warehouse\WarehouseCategory;
use App\Models\Warehouse\WarehouseItem;
use App\Models\Warehouse\WarehouseLocation;
use App\Models\Warehouse\WarehouseSupplier;
use App\Models\Warehouse\WarehouseUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    private function access(string $permission = 'warehouse.dashboard.view'): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->can($permission)
                || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
            ),
            403,
            'Unauthorized warehouse action.'
        );
    }

    public function dashboard()
    {
        $this->access('warehouse.dashboard.view');

        $cards = [
            'categories' => WarehouseCategory::count(),
            'units' => WarehouseUnit::count(),
            'suppliers' => WarehouseSupplier::count(),
            'locations' => WarehouseLocation::count(),
            'items' => WarehouseItem::count(),
            'inventory_rows' => Inventory::count(),
            'total_stock' => Inventory::sum('quantity'),
            'movements' => StockMovement::count(),
        ];

        $recentMovements = StockMovement::with(['item', 'location.branch'])
            ->latest()
            ->limit(10)
            ->get();

        $topStocks = Inventory::with(['item', 'branch', 'location'])
            ->orderByDesc('quantity')
            ->limit(10)
            ->get();

        $lowStockItems = WarehouseItem::query()
            ->leftJoin('warehouse_inventories', 'warehouse_items.id', '=', 'warehouse_inventories.item_id')
            ->select(
                'warehouse_items.id',
                'warehouse_items.code',
                'warehouse_items.item_code',
                'warehouse_items.name',
                'warehouse_items.item_name',
                'warehouse_items.reorder_level',
                'warehouse_items.minimum_stock',
                DB::raw('COALESCE(SUM(warehouse_inventories.quantity), 0) as total_quantity')
            )
            ->groupBy(
                'warehouse_items.id',
                'warehouse_items.code',
                'warehouse_items.item_code',
                'warehouse_items.name',
                'warehouse_items.item_name',
                'warehouse_items.reorder_level',
                'warehouse_items.minimum_stock'
            )
            ->havingRaw('COALESCE(SUM(warehouse_inventories.quantity), 0) <= COALESCE(warehouse_items.reorder_level, warehouse_items.minimum_stock, 0)')
            ->orderBy('warehouse_items.name')
            ->limit(8)
            ->get();

        return view('warehouse.dashboard', compact(
            'cards',
            'recentMovements',
            'topStocks',
            'lowStockItems'
        ));
    }

    public function inventory(Request $request)
    {
        $this->access('warehouse.inventory.view');

        $inventories = Inventory::with(['item.category', 'item.unit', 'branch', 'location'])
            ->when($request->search, function ($q, $s) {
                $q->whereHas('item', function ($i) use ($s) {
                    $i->where(function ($itemQuery) use ($s) {
                        $itemQuery->where('name', 'ilike', "%{$s}%")
                            ->orWhere('item_name', 'ilike', "%{$s}%")
                            ->orWhere('code', 'ilike', "%{$s}%")
                            ->orWhere('item_code', 'ilike', "%{$s}%");
                    });
                });
            })
            ->when($request->branch_id, fn ($q, $id) => $q->where('branch_id', $id))
            ->when($request->location_id, fn ($q, $id) => $q->where('location_id', $id))
            ->orderByDesc('quantity')
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::orderBy('name')->get();

        $locations = WarehouseLocation::with('branch')
            ->orderBy('location_name')
            ->get();

        return view('warehouse.inventory.index', compact(
            'inventories',
            'branches',
            'locations'
        ));
    }

    public function ledger(Request $request)
    {
        $this->access('warehouse.ledger.view');

        if ($request->ajax()) {
            $movements = StockMovement::with(['item', 'location.branch'])
                ->select('warehouse_stock_movements.*');

            if ($request->filled('type')) {
                $movements->where('movement_type', $request->type);
            }

            return DataTables::eloquent($movements)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('reference_type', 'ilike', '%' . $search . '%')
                                ->orWhere('remarks', 'ilike', '%' . $search . '%')
                                ->orWhere('movement_type', 'ilike', '%' . $search . '%')
                                ->orWhereHas('item', function ($itemQuery) use ($search) {
                                    $itemQuery->where('name', 'ilike', '%' . $search . '%')
                                        ->orWhere('item_name', 'ilike', '%' . $search . '%')
                                        ->orWhere('code', 'ilike', '%' . $search . '%')
                                        ->orWhere('item_code', 'ilike', '%' . $search . '%');
                                })
                                ->orWhereHas('location', function ($locationQuery) use ($search) {
                                    $locationQuery->where('location_name', 'ilike', '%' . $search . '%')
                                        ->orWhere('name', 'ilike', '%' . $search . '%');
                                })
                                ->orWhereHas('location.branch', function ($branchQuery) use ($search) {
                                    $branchQuery->where('name', 'ilike', '%' . $search . '%');
                                });
                        });
                    }
                })
                ->order(function ($query) {
                                $query->orderByDesc('warehouse_stock_movements.transaction_date')
                                    ->orderByDesc('warehouse_stock_movements.id');
                })
                ->addColumn('date_display', function ($row) {
                    $date = $row->transaction_date ?? $row->created_at;

                    return '<span class="text-secondary">' . optional($date)->format('M d, Y h:i A') . '</span>';
                })
                ->addColumn('reference_display', function ($row) {
                    $referenceLabel = $row->reference_type ?: '-';
                    $referenceUrl = null;

                    if ($row->reference_type === 'purchase_order_receiving' && $row->reference_id) {
                        $receivingNo = DB::table('warehouse_receivings')
                            ->where('id', $row->reference_id)
                            ->value('receiving_no');

                        if ($receivingNo) {
                            $referenceLabel = $receivingNo;

                            if (Route::has('purchasing.receiving.show')) {
                                $referenceUrl = route('purchasing.receiving.show', $row->reference_id);
                            }
                        }
                    }

                    if (in_array($row->reference_type, ['sales_receipt', 'sales_receipt_void'], true) && $row->reference_id) {
                        $receiptNo = DB::table('sales_receipts')
                            ->where('id', $row->reference_id)
                            ->value('receipt_no');

                        if ($receiptNo) {
                            $referenceLabel = $receiptNo;

                            if (Route::has('sales.sales-receipts.show')) {
                                $referenceUrl = route('sales.sales-receipts.show', $row->reference_id);
                            }
                        }
                    }

                    if ($row->reference_id && is_string($row->reference_type) && str_starts_with($row->reference_type, 'TRF-')) {
                        $transferNo = DB::table('warehouse_transfers')
                            ->where('id', $row->reference_id)
                            ->value('transfer_no');

                        if ($transferNo) {
                            $referenceLabel = $transferNo;

                            if (Route::has('warehouse.transfer.show')) {
                                $referenceUrl = route('warehouse.transfer.show', $row->reference_id);
                            }
                        }
                    }

                    if (in_array($row->movement_type, ['service_unit_borrow', 'service_unit_return', 'service_unit_return_unavailable'], true) && $row->reference_id) {
                        $borrowNo = DB::table('warehouse_service_unit_borrows')
                            ->where('id', $row->reference_id)
                            ->value('borrow_no');

                        if ($borrowNo) {
                            $referenceLabel = $borrowNo;

                            if (Route::has('warehouse.service-units.show')) {
                                $referenceUrl = route('warehouse.service-units.show', $row->reference_id);
                            }
                        }
                    }

                    if ($referenceUrl) {
                        return '<a href="' . e($referenceUrl) . '" class="fw-semibold text-primary text-decoration-none">' . e($referenceLabel) . '</a>';
                    }

                    return '<span class="fw-semibold text-primary">' . e($referenceLabel) . '</span>';
                })
                ->addColumn('type_display', function ($row) {
                    $typeLabel = ucwords(str_replace('_', ' ', $row->movement_type));
                    $qty = (float) $row->quantity;

                    if ($row->movement_type === 'service_unit_return_unavailable') {
                        return '<span class="badge rounded-pill bg-warning-subtle text-warning px-3 py-2">' . e($typeLabel) . '</span>';
                    }

                    if ($qty >= 0) {
                        return '<span class="badge rounded-pill bg-success-subtle text-success px-3 py-2">' . e($typeLabel) . '</span>';
                    }

                    return '<span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-2">' . e($typeLabel) . '</span>';
                })
                ->addColumn('item_display', function ($row) {
                    $itemCode = $row->item?->code ?: $row->item?->item_code ?: '-';
                    $itemName = $row->item?->name ?: $row->item?->item_name ?: '-';

                    return '
                        <div class="fw-semibold text-dark">' . e($itemName) . '</div>
                        <div class="text-secondary small">' . e($itemCode) . '</div>
                    ';
                })
                ->addColumn('location_display', function ($row) {
                    $locationName = $row->location?->location_name
                        ?? $row->location?->name
                        ?? '-';

                    $branchName = $row->location?->branch?->name ?? 'Central / Unassigned';

                    return '
                        <div class="fw-semibold text-dark">' . e($locationName) . '</div>
                        <div class="text-secondary small">' . e($branchName) . '</div>
                    ';
                })
                ->addColumn('qty_display', function ($row) {
                    $qty = (float) $row->quantity;
                    $class = $qty >= 0 ? 'text-success' : 'text-danger';
                    $sign = $qty >= 0 ? '+' : '';

                    return '<span class="fw-bold ' . $class . '">' . $sign . number_format($qty, 2) . '</span>';
                })
                ->addColumn('balance_display', function ($row) {
                    return '<span class="fw-semibold">' . number_format((float) $row->balance_after, 2) . '</span>';
                })
                ->addColumn('remarks_display', function ($row) {
                    return '<span class="text-secondary">' . e($row->remarks ?: '-') . '</span>';
                })
                ->rawColumns([
                    'date_display',
                    'reference_display',
                    'type_display',
                    'item_display',
                    'location_display',
                    'qty_display',
                    'balance_display',
                    'remarks_display',
                ])
                ->toJson();
        }

        return view('warehouse.inventory.ledger');
    }

    public function stockIn()
    {
        $this->access('warehouse.stock_in.create');

        return view('warehouse.inventory.stock-in', $this->formData());
    }

    public function stockOut()
    {
        $this->access('warehouse.stock_out.create');

        return view('warehouse.inventory.stock-out', $this->formData());
    }

    public function transfer()
    {
        $this->access('warehouse.transfer.create');

        return view('warehouse.inventory.transfer', $this->formData());
    }

    public function adjustment()
    {
        $this->access('warehouse.adjustment.create');

        return view('warehouse.inventory.adjustment', $this->formData());
    }

    private function formData(): array
    {
        return [
            'items' => WarehouseItem::with(['category', 'unit'])
                ->where('status', true)
                ->orderBy('name')
                ->get(),

            'branches' => Branch::orderBy('name')->get(),

            'locations' => WarehouseLocation::with('branch')
                ->where('status', true)
                ->orderBy('location_name')
                ->get(),
        ];
    }
}