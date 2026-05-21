<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\AccountingBankAccount;
use App\Models\Branch;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesReceipt;
use App\Models\Warehouse\Inventory;
use App\Models\Warehouse\StockMovement;
use App\Models\Warehouse\WarehouseItem;
use App\Models\Warehouse\WarehouseLocation;
use App\Services\Accounting\SalesAccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesReceiptController extends Controller
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
            'Unauthorized sales receipt action.'
        );
    }

    public function index(Request $request)
    {
        $this->access('sales.receipts.view');

        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $search = trim((string) $request->input('search', ''));

        $salesReceipts = SalesReceipt::with(['customer', 'branch', 'location', 'accountingBankAccount'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('receipt_no', 'ilike', '%' . $search . '%')
                        ->orWhere('reference_no', 'ilike', '%' . $search . '%')
                        ->orWhere('payment_method', 'ilike', '%' . $search . '%')
                        ->orWhere('status', 'ilike', '%' . $search . '%')
                        ->orWhereHas('customer', function ($customer) use ($search) {
                            $customer->where('customer_code', 'ilike', '%' . $search . '%')
                                ->orWhere('customer_name', 'ilike', '%' . $search . '%');
                        })
                        ->orWhereHas('accountingBankAccount', function ($bank) use ($search) {
                            $bank->where('name', 'ilike', '%' . $search . '%')
                                ->orWhere('type', 'ilike', '%' . $search . '%')
                                ->orWhere('bank_name', 'ilike', '%' . $search . '%')
                                ->orWhere('account_number', 'ilike', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate($perPage)
            ->appends([
                'search' => $search,
                'per_page' => $perPage,
            ]);

        if ($request->ajax()) {
            return response()->json([
                'tbody' => view('sales.sales-receipts.index', [
                    'salesReceipts' => $salesReceipts,
                    'ajaxTableOnly' => true,
                ])->render(),
                'pagination' => $salesReceipts->withQueryString()->links()->render(),
                'showing' => $this->paginationShowingText($salesReceipts),
                'total' => $salesReceipts->total(),
            ]);
        }

        return view('sales.sales-receipts.index', compact('salesReceipts', 'perPage', 'search'));
    }

    public function create()
    {
        $this->access('sales.receipts.create');

        $stockMap = Inventory::query()
            ->select('item_id', 'branch_id', 'location_id', 'quantity')
            ->get()
            ->map(function ($inventory) {
                return [
                    'item_id' => (int) $inventory->item_id,
                    'branch_id' => (int) $inventory->branch_id,
                    'location_id' => (int) $inventory->location_id,
                    'quantity' => (float) $inventory->quantity,
                ];
            })
            ->values();

        return view('sales.sales-receipts.create', [
            'customers' => Customer::where('status', true)->orderBy('customer_name')->get(),
            'branches' => Branch::orderBy('name')->get(),
            'locations' => WarehouseLocation::where('status', true)
                ->orderBy('location_name')
                ->orderBy('name')
                ->get(),
            'items' => WarehouseItem::where('status', true)
                ->orderBy('name')
                ->orderBy('item_name')
                ->get(),
            'accountingBankAccounts' => AccountingBankAccount::with('accountingAccount')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'stockMap' => $stockMap,
        ]);
    }

    public function store(Request $request)
    {
        $this->access('sales.receipts.create');

        $data = $this->validated($request);

        DB::transaction(function () use ($data, &$salesReceipt) {
            $receiptNo = $this->generateReceiptNo();
            $totals = $this->computeTotals($data['items']);

            $salesReceipt = SalesReceipt::create([
                'receipt_no' => $receiptNo,
                'customer_id' => $data['customer_id'],
                'branch_id' => $data['branch_id'],
                'location_id' => $data['location_id'],
                'receipt_date' => $data['receipt_date'],
                'payment_method' => $data['payment_method'],
                'accounting_bank_account_id' => $data['accounting_bank_account_id'],
                'reference_no' => $data['reference_no'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $totals['total'],
                'paid_amount' => $totals['total'],
                'status' => 'paid',
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['items'] as $line) {
                $warehouseItem = WarehouseItem::findOrFail($line['item_id']);

                $itemCode = $warehouseItem->code ?: $warehouseItem->item_code;
                $itemName = $warehouseItem->name ?: $warehouseItem->item_name;

                $quantity = (float) $line['quantity'];
                $unitPrice = (float) $line['unit_price'];
                $lineTotal = $quantity * $unitPrice;

                $inventory = Inventory::where('item_id', $warehouseItem->id)
                    ->where('branch_id', $data['branch_id'])
                    ->where('location_id', $data['location_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $inventory || (float) $inventory->quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => 'Insufficient stock for item: ' . $itemCode . ' - ' . $itemName,
                    ]);
                }

                $inventory->quantity = (float) $inventory->quantity - $quantity;
                $inventory->save();

                $salesReceipt->items()->create([
                    'item_id' => $warehouseItem->id,
                    'item_code' => $itemCode,
                    'item_name' => $itemName,
                    'description' => $line['description'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'line_total' => $lineTotal,
                ]);

                StockMovement::create([
                    'item_id' => $warehouseItem->id,
                    'location_id' => $data['location_id'],
                    'movement_type' => 'stock_out',
                    'quantity' => -abs($quantity),
                    'balance_after' => $inventory->quantity,
                    'reference_type' => 'sales_receipt',
                    'reference_id' => $salesReceipt->id,
                    'remarks' => 'Sales Receipt #' . $salesReceipt->receipt_no,
                    'transaction_date' => $data['receipt_date'],
                    'created_by' => auth()->id(),
                ]);
            }

            app(SalesAccountingService::class)->postSalesReceipt(
                $salesReceipt->fresh(['customer', 'accountingBankAccount.accountingAccount'])
            );
        });

        return redirect()
            ->route('sales.sales-receipts.show', $salesReceipt)
            ->with('success', 'Sales receipt created, warehouse stock deducted, and accounting journal entry posted successfully.');
    }

    public function show(SalesReceipt $salesReceipt)
    {
        $this->access('sales.receipts.view');

        $salesReceipt->load([
            'customer',
            'branch',
            'location',
            'items',
            'accountingBankAccount.accountingAccount',
            'accountingJournalEntry',
        ]);

        return view('sales.sales-receipts.show', compact('salesReceipt'));
    }

    public function edit(SalesReceipt $salesReceipt)
    {
        $this->access('sales.receipts.edit');

        return redirect()
            ->route('sales.sales-receipts.show', $salesReceipt)
            ->with('error', 'Editing sales receipts will be added later.');
    }

    public function update(Request $request, SalesReceipt $salesReceipt)
    {
        $this->access('sales.receipts.edit');

        return redirect()
            ->route('sales.sales-receipts.show', $salesReceipt)
            ->with('error', 'Updating sales receipts will be added later.');
    }

    public function destroy(Request $request, SalesReceipt $salesReceipt)
    {
        $this->access('sales.receipts.delete');

        DB::transaction(function () use ($salesReceipt) {
            $salesReceipt->load(['items', 'customer', 'accountingBankAccount.accountingAccount']);

            app(SalesAccountingService::class)->reverseSalesReceipt($salesReceipt->fresh(['customer']));

            if ($salesReceipt->branch_id && $salesReceipt->location_id) {
                foreach ($salesReceipt->items as $line) {
                    $inventory = Inventory::firstOrCreate(
                        [
                            'item_id' => $line->item_id,
                            'branch_id' => $salesReceipt->branch_id,
                            'location_id' => $salesReceipt->location_id,
                        ],
                        [
                            'quantity' => 0,
                        ]
                    );

                    $inventory->quantity = (float) $inventory->quantity + (float) $line->quantity;
                    $inventory->save();

                    StockMovement::create([
                        'item_id' => $line->item_id,
                        'location_id' => $salesReceipt->location_id,
                        'movement_type' => 'sales_receipt_void',
                        'quantity' => abs((float) $line->quantity),
                        'balance_after' => $inventory->quantity,
                        'reference_type' => 'sales_receipt_deleted',
                        'reference_id' => $salesReceipt->id,
                        'remarks' => 'Deleted Sales Receipt #' . $salesReceipt->receipt_no,
                        'transaction_date' => now(),
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            $salesReceipt->delete();
        });

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Sales receipt deleted, stock returned, and accounting entry reversed successfully.',
            ]);
        }

        return redirect()
            ->route('sales.sales-receipts.index')
            ->with('success', 'Sales receipt deleted, stock returned, and accounting entry reversed successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'location_id' => ['required', 'exists:warehouse_locations,id'],
            'receipt_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:255'],
            'accounting_bank_account_id' => ['required', 'exists:accounting_bank_accounts,id'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:warehouse_items,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function computeTotals(array $items): array
    {
        $subtotal = 0;

        foreach ($items as $line) {
            $subtotal += (float) $line['quantity'] * (float) $line['unit_price'];
        }

        return [
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ];
    }

    private function generateReceiptNo(): string
    {
        $prefix = 'SR-' . now()->format('Ymd') . '-';
        $countToday = SalesReceipt::whereDate('created_at', today())->count() + 1;

        return $prefix . str_pad($countToday, 4, '0', STR_PAD_LEFT);
    }

    private function paginationShowingText($paginator): string
    {
        if ($paginator->total() <= 0) {
            return 'Showing 0 entries';
        }

        return 'Showing ' . $paginator->firstItem()
            . ' to ' . $paginator->lastItem()
            . ' of ' . $paginator->total()
            . ' entries';
    }
}