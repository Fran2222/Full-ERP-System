<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Main Wizmaster dashboard.
     */
    public function index(Request $request)
    {
        $assets = [];

        $user = auth()->user();

        $usersTotal = $this->safeCount('users');
        $activeUsers = $this->safeCount('users', function ($query) {
            if (Schema::hasColumn('users', 'status')) {
                $query->where('status', 'active');
            }
        });

        $activeEmployees = $this->safeCount('employee_profiles', function ($query) {
            if (Schema::hasColumn('employee_profiles', 'employment_status')) {
                $query->whereIn('employment_status', ['active', 'Active', 'regular', 'Regular', 'probationary', 'Probationary']);
            } elseif (Schema::hasColumn('employee_profiles', 'status')) {
                $query->whereIn('status', ['active', 'Active']);
            }
        });

        $warehouseItems = $this->safeCount('warehouse_items');
        $lowStockItems = $this->safeLowStockCount();

        $purchaseOrdersTotal = $this->safeCount('purchase_orders');
        $pendingPurchaseOrders = $this->safeCount('purchase_orders', function ($query) {
            if (Schema::hasColumn('purchase_orders', 'status')) {
                $query->whereIn(DB::raw('LOWER(status)'), ['pending', 'draft', 'ordered', 'open']);
            }
        });

        $salesReceiptsTotal = $this->safeCount('sales_receipts');
        $salesThisMonth = $this->safeSum('sales_receipts', $this->firstExistingColumn('sales_receipts', ['total_amount', 'amount', 'grand_total', 'total']), function ($query) {
            if (Schema::hasColumn('sales_receipts', 'receipt_date')) {
                $query->whereMonth('receipt_date', now()->month)->whereYear('receipt_date', now()->year);
            } elseif (Schema::hasColumn('sales_receipts', 'created_at')) {
                $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            }
        });

        $customersTotal = $this->safeCount('customers');
        $invoicesTotal = $this->safeCount('invoices');
        $receivables = $this->safeReceivables();

        $pendingLeaves = $this->safeCount('leave_requests', function ($query) {
            if (Schema::hasColumn('leave_requests', 'status')) {
                $query->whereIn(DB::raw('LOWER(status)'), ['pending', 'submitted', 'for review', 'for approval']);
            }
        });

        $travelOrdersTotal = $this->safeCount('travel_orders');
        $pendingTravelOrders = $this->safeCount('travel_orders', function ($query) {
            if (Schema::hasColumn('travel_orders', 'status')) {
                $query->whereIn(DB::raw('LOWER(status)'), ['pending', 'submitted', 'for review', 'for approval']);
            }
        });

        $overtimeRequestsTotal = $this->safeCount('overtime_requests');
        $pendingOvertimeRequests = $this->safeCount('overtime_requests', function ($query) {
            if (Schema::hasColumn('overtime_requests', 'status')) {
                $query->whereIn(DB::raw('LOWER(status)'), ['pending', 'submitted', 'for review', 'for approval']);
            }
        });

        $metrics = [
            [
                'label' => 'Total Users',
                'value' => number_format($usersTotal),
                'sub' => number_format($activeUsers) . ' active accounts',
                'icon' => 'U',
                'tone' => 'primary',
            ],
            [
                'label' => 'Active Employees',
                'value' => number_format($activeEmployees),
                'sub' => number_format($pendingLeaves) . ' pending leave requests',
                'icon' => 'HR',
                'tone' => 'purple',
            ],
            [
                'label' => 'Warehouse Items',
                'value' => number_format($warehouseItems),
                'sub' => number_format($lowStockItems) . ' low stock alerts',
                'icon' => 'WH',
                'tone' => 'success',
            ],
            [
                'label' => 'Pending Purchase Orders',
                'value' => number_format($pendingPurchaseOrders),
                'sub' => number_format($purchaseOrdersTotal) . ' total purchase orders',
                'icon' => 'PO',
                'tone' => 'warning',
            ],
            [
                'label' => 'Sales This Month',
                'value' => '₱ ' . number_format($salesThisMonth, 2),
                'sub' => number_format($salesReceiptsTotal) . ' sales receipts',
                'icon' => '₱',
                'tone' => 'info',
            ],
            [
                'label' => 'Receivables',
                'value' => '₱ ' . number_format($receivables, 2),
                'sub' => number_format($invoicesTotal) . ' total invoices',
                'icon' => 'AR',
                'tone' => 'danger',
            ],
            [
                'label' => 'Travel Orders',
                'value' => number_format($travelOrdersTotal),
                'sub' => number_format($pendingTravelOrders) . ' pending',
                'icon' => 'TO',
                'tone' => 'purple',
            ],
            [
                'label' => 'Overtime Requests',
                'value' => number_format($overtimeRequestsTotal),
                'sub' => number_format($pendingOvertimeRequests) . ' pending',
                'icon' => 'OT',
                'tone' => 'muted',
            ],
        ];

        $moduleCards = [
            [
                'title' => 'Warehouse',
                'description' => 'Items, stock movement, and inventory monitoring',
                'icon' => 'WH',
                'links' => [
                    ['label' => 'Items', 'value' => $warehouseItems, 'route' => $this->routeName('warehouse.items.index')],
                    ['label' => 'Suppliers', 'value' => $this->safeCount('warehouse_suppliers'), 'route' => $this->routeName('warehouse.suppliers.index')],
                    ['label' => 'Locations', 'value' => $this->safeCount('warehouse_locations'), 'route' => $this->routeName('warehouse.locations.index')],
                ],
            ],
            [
                'title' => 'Sales',
                'description' => 'Customers, invoices, receipts, and receivables',
                'icon' => 'S',
                'links' => [
                    ['label' => 'Customers', 'value' => $customersTotal, 'route' => $this->routeName('sales.customers.index')],
                    ['label' => 'Invoices', 'value' => $invoicesTotal, 'route' => $this->routeName('sales.invoices.index')],
                    ['label' => 'Receipts', 'value' => $salesReceiptsTotal, 'route' => $this->routeName('sales.sales-receipts.index')],
                ],
            ],
            [
                'title' => 'Purchasing',
                'description' => 'Purchase orders and receiving workflow',
                'icon' => 'PO',
                'links' => [
                    ['label' => 'POs', 'value' => $purchaseOrdersTotal, 'route' => $this->routeName('purchasing.purchase-orders.index')],
                    ['label' => 'Pending', 'value' => $pendingPurchaseOrders, 'route' => $this->routeName('purchasing.purchase-orders.index')],
                    ['label' => 'Receiving', 'value' => $this->safeCount('receivings'), 'route' => $this->routeName('purchasing.receiving.index')],
                ],
            ],
            [
                'title' => 'Human Resource',
                'description' => 'Employees, leave, attendance, payroll, and requests',
                'icon' => 'HR',
                'links' => [
                    ['label' => 'Employees', 'value' => $activeEmployees, 'route' => $this->routeName('hr.employees.index')],
                    ['label' => 'Leave Pending', 'value' => $pendingLeaves, 'route' => $this->routeName('hr.leave.requests')],
                    ['label' => 'Overtime Pending', 'value' => $pendingOvertimeRequests, 'route' => $this->routeName('hr.overtime-requests.index')],
                ],
            ],
        ];

        $recentInvoices = $this->safeRecentRows('invoices', ['invoice_no', 'invoice_number', 'reference_no', 'status', 'total_amount', 'amount', 'grand_total', 'balance_due', 'due_date', 'created_at'], 5);
        $recentPurchaseOrders = $this->safeRecentRows('purchase_orders', ['po_no', 'po_number', 'reference_no', 'status', 'total_amount', 'amount', 'grand_total', 'created_at'], 5);
        $recentLedger = $this->safeRecentRows($this->firstExistingTable(['warehouse_ledgers', 'warehouse_inventory_ledgers', 'inventory_ledgers']), ['reference_no', 'type', 'movement_type', 'quantity', 'created_at'], 5);
        $announcements = $this->safeRecentRows('announcements', ['title', 'body', 'content', 'message', 'description', 'published_at', 'created_at'], 5, function ($query) {
            if (Schema::hasColumn('announcements', 'is_published')) {
                $query->where('is_published', true);
            }

            if (Schema::hasColumn('announcements', 'expires_at')) {
                $query->where(function ($announcementQuery) {
                    $announcementQuery->whereNull('expires_at')
                        ->orWhere('expires_at', '>=', now());
                });
            }
        });

        $quickActions = [
            ['label' => 'Add Employee', 'route' => $this->routeName('hr.employees.create'), 'permission' => 'hr.employees.create'],
            ['label' => 'Create Sales Receipt', 'route' => $this->routeName('sales.sales-receipts.create'), 'permission' => null],
            ['label' => 'Create Purchase Order', 'route' => $this->routeName('purchasing.purchase-orders.create'), 'permission' => null],
            ['label' => 'Stock In', 'route' => $this->routeName('warehouse.stock-in'), 'permission' => null],
            ['label' => 'Add Customer', 'route' => $this->routeName('sales.customers.create'), 'permission' => null],
        ];

        return view('dashboards.dashboard', compact(
            'assets',
            'user',
            'metrics',
            'moduleCards',
            'recentInvoices',
            'recentPurchaseOrders',
            'recentLedger',
            'announcements',
            'quickActions'
        ));
    }

    private function safeCount(?string $table, ?callable $callback = null): int
    {
        try {
            if (!$table || !Schema::hasTable($table)) {
                return 0;
            }

            $query = DB::table($table);

            if ($callback) {
                $callback($query);
            }

            return (int) $query->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function safeSum(?string $table, ?string $column, ?callable $callback = null): float
    {
        try {
            if (!$table || !$column || !Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                return 0;
            }

            $query = DB::table($table);

            if ($callback) {
                $callback($query);
            }

            return (float) $query->sum($column);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function safeReceivables(): float
    {
        try {
            if (!Schema::hasTable('invoices')) {
                return 0;
            }

            if (Schema::hasColumn('invoices', 'balance_due')) {
                return (float) DB::table('invoices')->sum('balance_due');
            }

            $totalColumn = $this->firstExistingColumn('invoices', ['total_amount', 'amount', 'grand_total', 'total']);
            $paidColumn = $this->firstExistingColumn('invoices', ['paid_amount', 'amount_paid']);

            if ($totalColumn && $paidColumn) {
                return (float) DB::table('invoices')->selectRaw("COALESCE(SUM({$totalColumn} - {$paidColumn}), 0) as total")->value('total');
            }

            return $this->safeSum('invoices', $totalColumn);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function safeLowStockCount(): int
    {
        try {
            if (!Schema::hasTable('warehouse_items')) {
                return 0;
            }

            $stockColumn = $this->firstExistingColumn('warehouse_items', ['stock', 'quantity', 'qty', 'current_stock']);
            $reorderColumn = $this->firstExistingColumn('warehouse_items', ['reorder_level', 'minimum_stock', 'min_stock']);

            if ($stockColumn && $reorderColumn) {
                return (int) DB::table('warehouse_items')->whereColumn($stockColumn, '<=', $reorderColumn)->count();
            }

            return 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function safeRecentRows(?string $table, array $columns = ['*'], int $limit = 5, ?callable $callback = null): array
    {
        try {
            if (!$table || !Schema::hasTable($table)) {
                return [];
            }

            $select = collect($columns)
                ->filter(fn ($column) => $column === '*' || Schema::hasColumn($table, $column))
                ->values()
                ->all();

            if (empty($select)) {
                $select = ['*'];
            }

            $query = DB::table($table)->select($select);

            if ($callback) {
                $callback($query);
            }

            if (Schema::hasColumn($table, 'created_at')) {
                $query->orderByDesc('created_at');
            }

            return $query->limit($limit)->get()->map(fn ($row) => (array) $row)->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        try {
            if (!Schema::hasTable($table)) {
                return null;
            }

            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    return $column;
                }
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    private function firstExistingTable(array $tables): ?string
    {
        foreach ($tables as $table) {
            try {
                if (Schema::hasTable($table)) {
                    return $table;
                }
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    private function routeName(string $name): ?string
    {
        try {
            return app('router')->has($name) ? $name : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /*
     * Menu Style Routs
     */
    public function horizontal(Request $request)
    {
        $assets = ['chart', 'animation'];
        return view('menu-style.horizontal', compact('assets'));
    }

    public function dualhorizontal(Request $request)
    {
        $assets = ['chart', 'animation'];
        return view('menu-style.dual-horizontal', compact('assets'));
    }

    public function dualcompact(Request $request)
    {
        $assets = ['chart', 'animation'];
        return view('menu-style.dual-compact', compact('assets'));
    }

    public function boxed(Request $request)
    {
        $assets = ['chart', 'animation'];
        return view('menu-style.boxed', compact('assets'));
    }

    public function boxedfancy(Request $request)
    {
        $assets = ['chart', 'animation'];
        return view('menu-style.boxed-fancy', compact('assets'));
    }

    /*
     * Pages Routs
     */
    public function billing(Request $request)
    {
        return view('special-pages.billing');
    }

    public function calender(Request $request)
    {
        $assets = ['calender'];
        return view('special-pages.calender', compact('assets'));
    }

    public function kanban(Request $request)
    {
        return view('special-pages.kanban');
    }

    public function pricing(Request $request)
    {
        return view('special-pages.pricing');
    }

    public function rtlsupport(Request $request)
    {
        return view('special-pages.rtl-support');
    }

    public function timeline(Request $request)
    {
        return view('special-pages.timeline');
    }

    /*
     * Widget Routs
     */
    public function widgetbasic(Request $request)
    {
        return view('widget.widget-basic');
    }

    public function widgetchart(Request $request)
    {
        $assets = ['chart'];
        return view('widget.widget-chart', compact('assets'));
    }

    public function widgetcard(Request $request)
    {
        return view('widget.widget-card');
    }

    /*
     * Maps Routs
     */
    public function google(Request $request)
    {
        return view('maps.google');
    }

    public function vector(Request $request)
    {
        return view('maps.vector');
    }

    /*
     * Auth Routs
     */
    public function signin(Request $request)
    {
        return view('auth.login');
    }

    public function signup(Request $request)
    {
        return view('auth.register');
    }

    public function confirmmail(Request $request)
    {
        return view('auth.confirm-mail');
    }

    public function lockscreen(Request $request)
    {
        return view('auth.lockscreen');
    }

    public function recoverpw(Request $request)
    {
        return view('auth.recoverpw');
    }

    public function userprivacysetting(Request $request)
    {
        return view('auth.user-privacy-setting');
    }

    /*
     * Error Page Routs
     */
    public function error404(Request $request)
    {
        return view('errors.error404');
    }

    public function error500(Request $request)
    {
        return view('errors.error500');
    }

    public function maintenance(Request $request)
    {
        return view('errors.maintenance');
    }

    /*
     * uisheet Page Routs
     */
    public function uisheet(Request $request)
    {
        return view('uisheet');
    }

    /*
     * Form Page Routs
     */
    public function element(Request $request)
    {
        return view('forms.element');
    }

    public function wizard(Request $request)
    {
        return view('forms.wizard');
    }

    public function validation(Request $request)
    {
        return view('forms.validation');
    }

    /*
     * Table Page Routs
     */
    public function bootstraptable(Request $request)
    {
        return view('table.bootstraptable');
    }

    public function datatable(Request $request)
    {
        return view('table.datatable');
    }

    /*
     * Icons Page Routs
     */
    public function solid(Request $request)
    {
        return view('icons.solid');
    }

    public function outline(Request $request)
    {
        return view('icons.outline');
    }

    public function dualtone(Request $request)
    {
        return view('icons.dualtone');
    }

    public function colored(Request $request)
    {
        return view('icons.colored');
    }

    /*
     * Extra Page Routs
     */
    public function privacypolicy(Request $request)
    {
        return view('privacy-policy');
    }

    public function termsofuse(Request $request)
    {
        return view('terms-of-use');
    }
}
