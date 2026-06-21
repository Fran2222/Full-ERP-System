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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{

    // WMC_ADJUSTMENT_ADMIN_BOD_ONLY_V2
    private function canUseAdjustment(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRole([
            'Super Admin',
            'Super Administrator',
            'Admin',
            'BOD',
            'Bod',
            'Board of Directors',
            'Board Of Directors',
            'Warehouse Admin',
            'warehouse admin',
            'Warehouse Administrator',
            'warehouse administrator',
        ]);
    }

    private function authorizeAdjustment(): void
    {
        abort_unless($this->canUseAdjustment(), 403, 'Only Warehouse Admin, BOD, or Admin can use Adjustment.');
    }

    private function warehouseAccessLevel($user): ?string
    {
        if (! $user) {
            return null;
        }

        if ($user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])) {
            return 'system_admin';
        }

        $possibleTables = [
            'user_module_assignments',
            'module_assignments',
            'user_modules',
        ];

        foreach ($possibleTables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'user_id')) {
                continue;
            }

            $query = DB::table($table)->where('user_id', $user->id);

            if (Schema::hasColumn($table, 'enabled')) {
                $query->where('enabled', true);
            } elseif (Schema::hasColumn($table, 'is_enabled')) {
                $query->where('is_enabled', true);
            } elseif (Schema::hasColumn($table, 'status')) {
                $query->whereIn('status', ['active', 1, true]);
            }

            $assignments = $query->get();

            foreach ($assignments as $assignment) {
                $haystack = strtolower(collect((array) $assignment)
                    ->filter(fn ($value) => is_scalar($value) && $value !== null)
                    ->implode(' '));

                if (! str_contains($haystack, 'warehouse') && ! str_contains($haystack, 'inventory')) {
                    continue;
                }

                if (str_contains($haystack, 'admin')) {
                    return 'admin';
                }

                if (str_contains($haystack, 'manager')) {
                    return 'manager';
                }

                if (str_contains($haystack, 'staff')) {
                    return 'staff';
                }

                if (str_contains($haystack, 'viewer')) {
                    return 'viewer';
                }
            }
        }

        return null;
    }

    private function access(string $permission = 'warehouse.dashboard.view'): void
    {
        $user = auth()->user();
        $level = $this->warehouseAccessLevel($user);

        $staffPages = [
            'warehouse.dashboard.view',
            'warehouse.inventory.view',
            'warehouse.ledger.view',
        ];

        $managerPages = [
            'warehouse.stock_in.create',
            'warehouse.stock_out.create',
            'warehouse.adjustment.create',
        ];

        $allowedByWarehouseLevel = match (true) {
            in_array($level, ['system_admin', 'admin'], true) => true,
            $level === 'manager' => in_array($permission, array_merge($staffPages, $managerPages), true),
            $level === 'staff' => in_array($permission, $staffPages, true),
            default => false,
        };

        abort_unless(
            $user && (
                $allowedByWarehouseLevel
                || $user->can($permission)
                || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
            ),
            403,
            'Unauthorized warehouse action.'
        );
    }

    public function dashboard(Request $request)
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

        $recentSearch = trim((string) $request->get('recent_search', ''));
        $recentPerPage = 7;
        $recentPage = max(1, (int) $request->get('recent_page', 1));

        $recentBaseQuery = StockMovement::query()
            ->with(['item', 'location.branch'])
            ->orderByDesc('warehouse_stock_movements.transaction_date')
            ->orderByDesc('warehouse_stock_movements.created_at');

        if ($recentSearch !== '') {
            /**
             * Search is intentionally filtered in PHP instead of SQL joins.
             * This avoids 500 errors on deployed databases where some optional
             * reference/location/item columns may be missing or named differently.
             * It still searches all stock movements, including records from page 2+.
             */
            $needle = mb_strtolower($recentSearch);
            $needleNormalized = str_replace([' ', '-'], '_', $needle);

            $recentCollection = $recentBaseQuery
                ->limit(2000)
                ->get()
                ->filter(function ($movement) use ($needle, $needleNormalized) {
                    $item = $movement->item;
                    $location = $movement->location;
                    $branch = $location?->branch;

                    /**
                     * Dashboard search must match only the visible columns requested:
                     * Reference, Item, and Location. Type, qty, balance, remarks, and date
                     * are intentionally excluded so search results match the table UI.
                     */
                    $haystack = collect([
                        // Reference column
                        $movement->reference_no ?? null,
                        $movement->reference_type ?? null,

                        // Item column
                        $item?->name ?? null,
                        $item?->item_name ?? null,
                        $item?->code ?? null,
                        $item?->item_code ?? null,

                        // Location column
                        $location?->name ?? null,
                        $location?->location_name ?? null,
                        $location?->code ?? null,
                        $location?->location_code ?? null,
                        $branch?->name ?? null,
                        $branch?->branch_name ?? null,
                        $branch?->code ?? null,
                    ])
                        ->filter(fn ($value) => $value !== null && $value !== '')
                        ->map(fn ($value) => mb_strtolower((string) $value))
                        ->implode(' ');

                    $haystackNormalized = str_replace([' ', '-'], '_', $haystack);

                    return str_contains($haystack, $needle)
                        || str_contains($haystackNormalized, $needleNormalized);
                })
                ->values();

            $recentMovements = new LengthAwarePaginator(
                $recentCollection->forPage($recentPage, $recentPerPage)->values(),
                $recentCollection->count(),
                $recentPerPage,
                $recentPage,
                [
                    'path' => $request->url(),
                    'pageName' => 'recent_page',
                    'query' => $request->except('warehouse_recent_ajax', '_ts'),
                ]
            );
        } else {
            $recentMovements = $recentBaseQuery
                ->paginate($recentPerPage, ['warehouse_stock_movements.*'], 'recent_page')
                ->appends($request->except('warehouse_recent_ajax', '_ts'));
        }

        if ((string) $request->get('warehouse_recent_ajax') === '1') {
            return view('warehouse.partials.recent-movements-table', compact('recentMovements'));
        }

        $stockCardPerPage = 4;

        $topStocks = Inventory::with(['item', 'branch', 'location'])
            ->orderByDesc('quantity')
            ->paginate($stockCardPerPage, ['*'], 'top_stock_page')
            ->appends($request->except('top_stock_page'));

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
            ->paginate($stockCardPerPage, ['*'], 'low_stock_page')
            ->appends($request->except('low_stock_page'));

        if ((string) $request->get('warehouse_stock_card_ajax') === 'low') {
            return view('warehouse.partials.low-stock-list', compact('lowStockItems'));
        }

        if ((string) $request->get('warehouse_stock_card_ajax') === 'top') {
            return view('warehouse.partials.top-stock-list', compact('topStocks'));
        }

        return view('warehouse.dashboard', compact(
            'cards',
            'recentMovements',
            'topStocks',
            'lowStockItems',
            'recentSearch'
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

                    /*
                     * Transfer ledger rows must display the transfer side used by the
                     * movement, not only the branch attached to the warehouse location.
                     *
                     * transfer_out = source location + source branch
                     * transfer_in  = destination location + destination branch
                     *
                     * This fixes cases where a location relation has a branch value that
                     * does not match the actual transfer header, especially when the
                     * transfer branch fields are optional / central-unassigned.
                     */
                    if (in_array($row->movement_type, ['transfer_in', 'transfer_out'], true)) {
                        $transferQuery = DB::table('warehouse_transfers');

                        if ($row->reference_id) {
                            $transferQuery->where('id', $row->reference_id);
                        } elseif ($row->reference_type && is_string($row->reference_type) && str_starts_with($row->reference_type, 'TRF-')) {
                            $transferQuery->where('transfer_no', $row->reference_type);
                        } else {
                            $transferQuery = null;
                        }

                        $transfer = $transferQuery ? $transferQuery->first() : null;

                        if ($transfer) {
                            $locationId = $row->movement_type === 'transfer_in'
                                ? ($transfer->to_location_id ?? null)
                                : ($transfer->from_location_id ?? null);

                            $branchId = $row->movement_type === 'transfer_in'
                                ? ($transfer->to_branch_id ?? null)
                                : ($transfer->from_branch_id ?? null);

                            if ($locationId) {
                                $location = DB::table('warehouse_locations')
                                    ->where('id', $locationId)
                                    ->first();

                                if ($location) {
                                    $locationName = property_exists($location, 'location_name') && $location->location_name
                                        ? $location->location_name
                                        : (
                                            property_exists($location, 'name') && $location->name
                                                ? $location->name
                                                : '-'
                                        );
                                }
                            }

                            if ($branchId) {
                                $branchName = DB::table('branches')
                                    ->where('id', $branchId)
                                    ->value('name') ?: 'Central / Unassigned';
                            } else {
                                $branchName = 'Central / Unassigned';
                            }
                        }
                    }

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
        $this->authorizeAdjustment();

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