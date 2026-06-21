<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesReceipt;
use App\Models\Warehouse\Inventory;
use App\Models\Warehouse\StockMovement;
use App\Models\Warehouse\WarehouseItem;
use App\Models\Warehouse\WarehouseItemSerial;
use App\Models\Warehouse\WarehouseLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class SalesReceiptController extends Controller
{
    private function canAccess(string $permission): bool
    {
        $user = auth()->user();

        return $user && (
            $user->can($permission)
            || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
        );
    }

    private function access(string $permission): void
    {
        abort_unless(
            $this->canAccess($permission),
            403,
            'Unauthorized sales receipt action.'
        );
    }

    private function accessAny(array $permissions): void
    {
        foreach ($permissions as $permission) {
            if ($this->canAccess($permission)) {
                return;
            }
        }

        abort(403, 'Unauthorized sales receipt action.');
    }

    public function index(Request $request)
    {
        $this->access('sales.receipts.view');

        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $search = trim((string) $request->input('search', ''));

        $salesReceipts = SalesReceipt::with(['customer', 'branch', 'location'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('receipt_no', 'ilike', '%' . $search . '%')
                        ->orWhere('reference_no', 'ilike', '%' . $search . '%')
                        ->orWhere('payment_method', 'ilike', '%' . $search . '%')
                        ->orWhere('status', 'ilike', '%' . $search . '%')
                        ->orWhereHas('customer', function ($customer) use ($search) {
                            $customer->where('customer_code', 'ilike', '%' . $search . '%')
                                ->orWhere('customer_name', 'ilike', '%' . $search . '%');
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
            'stockMap' => $stockMap,
        ]);
    }


    public function availableSerials(Request $request)
    {
        $this->access('sales.receipts.create');

        $data = $request->validate([
            'item_id' => ['required', 'exists:warehouse_items,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'location_id' => ['required', 'exists:warehouse_locations,id'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $item = WarehouseItem::findOrFail($data['item_id']);

        if (! (bool) $item->is_serialized) {
            return response()->json(['results' => []]);
        }

        $search = trim((string) ($data['q'] ?? ''));

        $serials = WarehouseItemSerial::query()
            ->where('item_id', $item->id)
            ->where('branch_id', $data['branch_id'])
            ->where('location_id', $data['location_id'])
            ->where('status', 'available')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('serial_number', 'ilike', '%' . $search . '%');
            })
            ->orderBy('serial_number')
            ->limit(50)
            ->get(['id', 'serial_number']);

        return response()->json([
            'results' => $serials->map(function ($serial) {
                return [
                    'id' => (string) $serial->id,
                    'text' => $serial->serial_number,
                    'serial_number' => $serial->serial_number,
                ];
            })->values(),
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

                $serialIds = array_values(array_filter($line['serial_ids'] ?? []));
                $quantity = (float) $line['quantity'];

                if ((bool) $warehouseItem->is_serialized) {
                    $quantity = count($serialIds);
                }

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

                $receiptItem = $salesReceipt->items()->create([
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

                $movement = StockMovement::create([
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

                if ((bool) $warehouseItem->is_serialized) {
                    $serials = WarehouseItemSerial::query()
                        ->whereIn('id', $serialIds)
                        ->where('item_id', $warehouseItem->id)
                        ->where('branch_id', $data['branch_id'])
                        ->where('location_id', $data['location_id'])
                        ->where('status', 'available')
                        ->lockForUpdate()
                        ->get();

                    if ($serials->count() !== count($serialIds)) {
                        throw ValidationException::withMessages([
                            'items' => 'One or more selected serial numbers are no longer available for item: ' . $itemCode . ' - ' . $itemName,
                        ]);
                    }

                    foreach ($serials as $serial) {
                        $serial->forceFill([
                            'status' => 'sold',
                            'stock_out_movement_id' => $movement->id,
                            'issued_at' => now(),
                            'remarks' => 'Sold under Sales Receipt ' . $salesReceipt->receipt_no,
                        ])->save();
                    }

                    if (Schema::hasTable('sales_receipt_item_serials')) {
                        $now = now();
                        $rows = $serials->map(function ($serial) use ($receiptItem, $now) {
                            return [
                                'sales_receipt_item_id' => $receiptItem->id,
                                'warehouse_item_serial_id' => $serial->id,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        })->all();

                        DB::table('sales_receipt_item_serials')->insertOrIgnore($rows);
                    }
                }
            }
        });

        \App\Services\SystemNotificationService::notifySalesReceiptCreated($salesReceipt->fresh(['customer']), auth()->id());

        return redirect()
            ->route('sales.sales-receipts.show', $salesReceipt)
            ->with('success', 'Sales receipt created successfully.');
    }

    public function show(SalesReceipt $salesReceipt)
    {
        $this->access('sales.receipts.view');

        $salesReceipt->load(['customer', 'branch', 'location', 'items.serials', 'createdBy', 'voidedBy']);

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

    public function void(Request $request, SalesReceipt $salesReceipt)
    {
        $this->access('sales.receipts.void');

        $data = $request->validate([
            'void_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($salesReceipt, $data) {
            $this->performVoid(
                $salesReceipt,
                trim((string) ($data['void_reason'] ?? '')) ?: 'Voided by authorized user.'
            );
        });

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Sales receipt voided, stock returned, and reversal entry posted successfully.',
            ]);
        }

        return redirect()
            ->route('sales.sales-receipts.show', $salesReceipt)
            ->with('success', 'Sales receipt voided, stock returned, and reversal entry posted successfully.');
    }

    public function destroy(Request $request, SalesReceipt $salesReceipt)
    {
        // Backward compatibility for old Delete buttons/routes.
        // This no longer deletes the record; it performs a proper void.
        $this->accessAny(['sales.receipts.void', 'sales.receipts.delete']);

        DB::transaction(function () use ($salesReceipt, $request) {
            $this->performVoid(
                $salesReceipt,
                trim((string) $request->input('void_reason', '')) ?: 'Voided from old delete action.'
            );
        });

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Sales receipt voided and stock returned successfully.',
            ]);
        }

        return redirect()
            ->route('sales.sales-receipts.index')
            ->with('success', 'Sales receipt voided and stock returned successfully.');
    }

    private function performVoid(SalesReceipt $salesReceipt, string $reason): void
    {
        $salesReceipt->refresh();

        if (strtolower((string) $salesReceipt->status) === 'void') {
            throw ValidationException::withMessages([
                'sales_receipt' => 'This sales receipt is already voided.',
            ]);
        }

        $salesReceipt->load(['items', 'customer']);

        // If the accounting service exists in the deployed build, post the reversal journal.
        $accountingServiceClass = 'App\\Services\\Accounting\\SalesAccountingService';
        if (class_exists($accountingServiceClass)) {
            $service = app($accountingServiceClass);

            if (method_exists($service, 'reverseSalesReceipt')) {
                $service->reverseSalesReceipt($salesReceipt->fresh(['customer']));
            }
        }

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
                    'reference_type' => 'sales_receipt_void',
                    'reference_id' => $salesReceipt->id,
                    'remarks' => 'Voided Sales Receipt #' . $salesReceipt->receipt_no . ($reason ? ' - ' . $reason : ''),
                    'transaction_date' => now(),
                    'created_by' => auth()->id(),
                ]);
            }
        }

        $this->releaseSerializedItemsIfLinked($salesReceipt);

        $updates = [
            'status' => 'void',
        ];

        if (Schema::hasColumn('sales_receipts', 'voided_at')) {
            $updates['voided_at'] = now();
        }

        if (Schema::hasColumn('sales_receipts', 'voided_by')) {
            $updates['voided_by'] = auth()->id();
        }

        if (Schema::hasColumn('sales_receipts', 'void_reason')) {
            $updates['void_reason'] = $reason;
        }

        $salesReceipt->forceFill($updates)->save();
    }

    private function releaseSerializedItemsIfLinked(SalesReceipt $salesReceipt): void
    {
        if (! Schema::hasTable('warehouse_item_serials')) {
            return;
        }

        if (! Schema::hasColumn('warehouse_item_serials', 'stock_out_movement_id')) {
            return;
        }

        $movementIds = StockMovement::query()
            ->where('reference_type', 'sales_receipt')
            ->where('reference_id', $salesReceipt->id)
            ->where('movement_type', 'stock_out')
            ->pluck('id')
            ->all();

        if (empty($movementIds)) {
            return;
        }

        $updates = [
            'status' => 'available',
            'stock_out_movement_id' => null,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('warehouse_item_serials', 'issued_at')) {
            $updates['issued_at'] = null;
        }

        DB::table('warehouse_item_serials')
            ->whereIn('stock_out_movement_id', $movementIds)
            ->update($updates);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'location_id' => ['required', 'exists:warehouse_locations,id'],
            'receipt_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:warehouse_items,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.serial_ids' => ['nullable', 'array'],
            'items.*.serial_ids.*' => ['integer', 'exists:warehouse_item_serials,id'],
        ]);

        return $this->normalizeSerializedLines($data);
    }

    private function normalizeSerializedLines(array $data): array
    {
        $usedSerialIds = [];

        foreach ($data['items'] as $index => $line) {
            $item = WarehouseItem::findOrFail($line['item_id']);
            $serialIds = array_values(array_unique(array_filter($line['serial_ids'] ?? [])));

            if ((bool) $item->is_serialized) {
                if (empty($serialIds)) {
                    throw ValidationException::withMessages([
                        'items.' . $index . '.serial_ids' => 'Serial number is required for serialized item: ' . ($item->code ?: $item->item_code) . ' - ' . ($item->name ?: $item->item_name),
                    ]);
                }

                $duplicateSerials = array_intersect($usedSerialIds, $serialIds);

                if (! empty($duplicateSerials)) {
                    throw ValidationException::withMessages([
                        'items.' . $index . '.serial_ids' => 'The same serial number cannot be sold more than once in one sales receipt.',
                    ]);
                }

                $availableCount = WarehouseItemSerial::query()
                    ->whereIn('id', $serialIds)
                    ->where('item_id', $item->id)
                    ->where('branch_id', $data['branch_id'])
                    ->where('location_id', $data['location_id'])
                    ->where('status', 'available')
                    ->count();

                if ($availableCount !== count($serialIds)) {
                    throw ValidationException::withMessages([
                        'items.' . $index . '.serial_ids' => 'One or more selected serial numbers are not available at the selected warehouse location.',
                    ]);
                }

                $data['items'][$index]['serial_ids'] = $serialIds;
                $data['items'][$index]['quantity'] = count($serialIds);
                $usedSerialIds = array_merge($usedSerialIds, $serialIds);
            } else {
                $data['items'][$index]['serial_ids'] = [];
            }
        }

        return $data;
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

