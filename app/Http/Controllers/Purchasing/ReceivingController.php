<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingJournalEntry;
use App\Models\AccountingJournalLine;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Warehouse\Inventory;
use App\Models\Warehouse\WarehouseLocation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class ReceivingController extends Controller
{
    private function authorizePurchasing(string $permission): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->can($permission) ||
                $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
            ),
            403,
            'Unauthorized purchasing action.'
        );
    }

    private function canAccess(string $permission): bool
    {
        $user = auth()->user();

        return $user && (
            $user->can($permission) ||
            $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
        );
    }

    public function index(Request $request)
    {
        $this->authorizePurchasing('purchasing.receiving.view');

        if ($request->ajax()) {
            $receivings = DB::table('warehouse_receivings as wr')
                ->leftJoin('warehouse_suppliers as s', 's.id', '=', 'wr.supplier_id')
                ->leftJoin('warehouse_locations as l', 'l.id', '=', 'wr.location_id')
                ->select(
                    'wr.*',
                    's.supplier_name',
                    's.contact_person',
                    'l.location_name',
                    'l.name as location_alt_name'
                );

            return DataTables::queryBuilder($receivings)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('wr.receiving_no', 'ilike', '%' . $search . '%')
                                ->orWhere('wr.reference_no', 'ilike', '%' . $search . '%')
                                ->orWhere('wr.status', 'ilike', '%' . $search . '%')
                                ->orWhere('s.supplier_name', 'ilike', '%' . $search . '%')
                                ->orWhere('s.contact_person', 'ilike', '%' . $search . '%')
                                ->orWhere('l.location_name', 'ilike', '%' . $search . '%')
                                ->orWhere('l.name', 'ilike', '%' . $search . '%');
                        });
                    }
                })
                ->order(function ($query) {
                    $query->orderByDesc('wr.id');
                })
                ->addColumn('receiving_no_display', function ($row) {
                    return '<a href="' . route('purchasing.receiving.show', $row->id) . '"
                               class="fw-semibold text-primary text-decoration-none">'
                        . e($row->receiving_no) .
                    '</a>';
                })
                ->addColumn('supplier_display', function ($row) {
                    return '
                        <div class="fw-semibold text-dark">' . e($row->supplier_name ?? '-') . '</div>
                        <div class="small text-secondary">' . e($row->contact_person ?? '-') . '</div>
                    ';
                })
                ->addColumn('received_date_display', function ($row) {
                    return '<span class="text-secondary">'
                        . Carbon::parse($row->received_date)->format('M d, Y') .
                    '</span>';
                })
                ->addColumn('reference_display', function ($row) {
                    return '<span class="text-secondary">' . e($row->reference_no ?: '-') . '</span>';
                })
                ->addColumn('location_display', function ($row) {
                    $locationName = $row->location_name ?? $row->location_alt_name ?? '-';

                    return '<div class="fw-semibold text-dark">' . e($locationName) . '</div>';
                })
                ->addColumn('status_display', function ($row) {
                    $status = strtolower((string) $row->status);

                    $class = match ($status) {
                        'posted', 'received' => 'purchasing-badge purchasing-badge-success',
                        'draft' => 'purchasing-badge purchasing-badge-warning',
                        'cancelled', 'void' => 'purchasing-badge purchasing-badge-muted',
                        default => 'purchasing-badge purchasing-badge-info',
                    };

                    return '<span class="' . $class . '">' . e(ucwords(str_replace('_', ' ', $row->status))) . '</span>';
                })
                ->rawColumns([
                    'receiving_no_display',
                    'supplier_display',
                    'received_date_display',
                    'reference_display',
                    'location_display',
                    'status_display',
                ])
                ->toJson();
        }

        $canPostReceiving = $this->canAccess('purchasing.receiving.post');

        return view('purchasing.receiving.index', compact('canPostReceiving'));
    }

    public function create(Request $request)
    {
        $this->authorizePurchasing('purchasing.receiving.post');

        $purchaseOrders = PurchaseOrder::with(['supplier', 'location', 'items'])
            ->whereIn('status', ['ordered', 'partially_received'])
            ->orderByDesc('id')
            ->get();

        $selectedPO = null;

        if ($request->filled('po_id')) {
            $selectedPO = PurchaseOrder::with(['supplier', 'location', 'items'])
                ->whereIn('status', ['ordered', 'partially_received'])
                ->find($request->po_id);
        }

        return view('purchasing.receiving.create', compact('purchaseOrders', 'selectedPO'));
    }

    public function store(Request $request)
    {
        $this->authorizePurchasing('purchasing.receiving.post');

        $validated = $request->validate([
            'purchase_order_id' => ['required', 'exists:purchase_orders,id'],
            'received_date' => ['required', 'date'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.receive_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string'],
        ]);

        $po = PurchaseOrder::with(['items', 'location', 'supplier'])
            ->whereIn('status', ['ordered', 'partially_received'])
            ->findOrFail($validated['purchase_order_id']);

        if (! $po->location_id) {
            return back()
                ->withInput()
                ->with('error', 'Purchase order has no receiving location.');
        }

        $location = WarehouseLocation::findOrFail($po->location_id);

        if (! $location->branch_id) {
            return back()
                ->withInput()
                ->with('error', 'The receiving location has no assigned branch. Please assign a branch to this warehouse location first.');
        }

        $existingPurchaseBillIds = \Illuminate\Support\Facades\Schema::hasTable('purchase_bills')
            ? DB::table('purchase_bills')->where('purchase_order_id', $po->id)->pluck('id')->all()
            : [];

        $receivingId = null;
        $poStatusAfterReceiving = null;

        DB::transaction(function () use ($validated, $po, $location, &$receivingId, &$poStatusAfterReceiving) {
            $receivingNo = $this->generateReceivingNo();

            $receivingId = DB::table('warehouse_receivings')->insertGetId([
                'receiving_no' => $receivingNo,
                'supplier_id' => $po->supplier_id,
                'location_id' => $po->location_id,
                'reference_no' => $validated['reference_no'] ?: $po->po_no,
                'received_date' => $validated['received_date'],
                'remarks' => $validated['remarks'] ?? null,
                'received_by' => Auth::id(),
                'status' => 'received',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $receivedAny = false;
            $totalReceivedCost = 0;

            foreach ($validated['items'] as $line) {
                $poItem = $po->items->firstWhere('id', (int) $line['purchase_order_item_id']);

                if (! $poItem) {
                    continue;
                }

                $orderedQty = (float) $poItem->quantity;
                $alreadyReceived = (float) $poItem->received_quantity;
                $remainingQty = max(0, $orderedQty - $alreadyReceived);
                $receiveQty = (float) $line['receive_quantity'];

                if ($receiveQty <= 0) {
                    continue;
                }

                if ($receiveQty > $remainingQty) {
                    throw ValidationException::withMessages([
                        'items' => 'Receive quantity for ' . $poItem->item_name . ' cannot exceed remaining quantity.',
                    ]);
                }

                $unitCost = (float) $line['unit_cost'];
                $totalCost = $receiveQty * $unitCost;
                $totalReceivedCost += $totalCost;

                DB::table('warehouse_receiving_items')->insert([
                    'receiving_id' => $receivingId,
                    'item_id' => $poItem->item_id,
                    'quantity' => $receiveQty,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'remarks' => $line['remarks'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('purchase_order_items')
                    ->where('id', $poItem->id)
                    ->update([
                        'received_quantity' => $alreadyReceived + $receiveQty,
                        'updated_at' => now(),
                    ]);

                $inventory = Inventory::firstOrCreate(
                    [
                        'item_id' => $poItem->item_id,
                        'branch_id' => $location->branch_id,
                        'location_id' => $po->location_id,
                    ],
                    [
                        'quantity' => 0,
                    ]
                );

                $newBalance = (float) $inventory->quantity + $receiveQty;

                $inventory->update([
                    'quantity' => $newBalance,
                ]);

                DB::table('warehouse_stock_movements')->insert([
                    'item_id' => $poItem->item_id,
                    'location_id' => $po->location_id,
                    'movement_type' => 'Receiving',
                    'quantity' => $receiveQty,
                    'balance_after' => $newBalance,
                    'reference_type' => 'purchase_order_receiving',
                    'reference_id' => $receivingId,
                    'remarks' => 'Received from PO #' . $po->po_no,
                    'transaction_date' => Carbon::parse($validated['received_date'])->startOfDay(),
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $receivedAny = true;
            }

            if (! $receivedAny) {
                throw ValidationException::withMessages([
                    'items' => 'Please enter at least one item quantity to receive.',
                ]);
            }

            $freshItems = DB::table('purchase_order_items')
                ->where('purchase_order_id', $po->id)
                ->get();

            $allReceived = $freshItems->every(function ($item) {
                return (float) $item->received_quantity >= (float) $item->quantity;
            });

            $poStatusAfterReceiving = $allReceived ? 'received' : 'partially_received';

            DB::table('purchase_orders')
                ->where('id', $po->id)
                ->update([
                    'status' => $poStatusAfterReceiving,
                    'updated_at' => now(),
                ]);

            $this->postReceivingJournalEntry(
                po: $po,
                receivingNo: $receivingNo,
                receivingDate: $validated['received_date'],
                amount: $totalReceivedCost
            );
        });

        $freshPo = $po->fresh();
        \App\Services\SystemNotificationService::notifyPurchaseOrderReceived($freshPo, $receivingId, $poStatusAfterReceiving, Auth::id());

        if ($poStatusAfterReceiving === 'received' && class_exists(\App\Services\Purchasing\PurchaseBillAutoService::class)) {
            app(\App\Services\Purchasing\PurchaseBillAutoService::class)->createMissingBillsForFullyReceivedPurchaseOrders();

            if (\Illuminate\Support\Facades\Schema::hasTable('purchase_bills')) {
                $newBills = DB::table('purchase_bills')
                    ->where('purchase_order_id', $po->id)
                    ->when(! empty($existingPurchaseBillIds), fn ($query) => $query->whereNotIn('id', $existingPurchaseBillIds))
                    ->get();

                foreach ($newBills as $newBill) {
                    \App\Services\SystemNotificationService::notifyPurchaseBillCreated($newBill, Auth::id());
                }
            }
        }

        return redirect()
            ->route('purchasing.receiving.index')
            ->with('success', 'Items received, warehouse inventory updated, and accounting journal entry posted successfully.');
    }
public function print($receiving)
{
    $this->authorizePurchasing('purchasing.receiving.view');

    $receiving = DB::table('warehouse_receivings as wr')
        ->leftJoin('warehouse_suppliers as s', 's.id', '=', 'wr.supplier_id')
        ->leftJoin('warehouse_locations as l', 'l.id', '=', 'wr.location_id')
        ->leftJoin('users as u', 'u.id', '=', 'wr.received_by')
        ->select(
            'wr.*',
            's.supplier_name',
            's.contact_person',
            's.phone',
            's.email',
            's.address as supplier_address',
            'l.location_name',
            'l.name as location_alt_name',
            DB::raw("NULLIF(TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))), '') as received_by_name"),
            'u.email as received_by_email'
        )
        ->where('wr.id', $receiving)
        ->first();

    abort_if(! $receiving, 404);

    $items = DB::table('warehouse_receiving_items as ri')
        ->leftJoin('warehouse_items as wi', 'wi.id', '=', 'ri.item_id')
        ->leftJoin('warehouse_units as wu', 'wu.id', '=', 'wi.unit_id')
        ->select(
            'ri.*',
            'wi.item_code',
            'wi.code',
            'wi.item_name',
            'wi.name',
            'wu.name as unit_name',
            'wu.abbreviation as unit_abbreviation'
        )
        ->where('ri.receiving_id', $receiving->id)
        ->orderBy('ri.id')
        ->get();

    $subtotal = $items->sum(function ($item) {
        return (float) $item->total_cost;
    });

    return view('purchasing.print.receiving', compact(
        'receiving',
        'items',
        'subtotal'
    ));
}
    public function show($receiving)
    {
        $this->authorizePurchasing('purchasing.receiving.view');

        $receiving = DB::table('warehouse_receivings as wr')
            ->leftJoin('warehouse_suppliers as s', 's.id', '=', 'wr.supplier_id')
            ->leftJoin('warehouse_locations as l', 'l.id', '=', 'wr.location_id')
            ->leftJoin('users as u', 'u.id', '=', 'wr.received_by')
            ->select(
                'wr.*',
                's.supplier_name',
                's.contact_person',
                's.phone',
                's.email',
                's.address as supplier_address',
                'l.location_name',
                'l.name as location_alt_name',
                DB::raw("TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))) as received_by_name"),
                'u.email as received_by_email'
            )
            ->where('wr.id', $receiving)
            ->first();

        abort_if(! $receiving, 404);

        $items = DB::table('warehouse_receiving_items as ri')
            ->leftJoin('warehouse_items as wi', 'wi.id', '=', 'ri.item_id')
            ->leftJoin('warehouse_units as wu', 'wu.id', '=', 'wi.unit_id')
            ->select(
                'ri.*',
                'wi.item_code',
                'wi.code',
                'wi.item_name',
                'wi.name',
                'wu.name as unit_name',
                'wu.abbreviation as unit_abbreviation'
            )
            ->where('ri.receiving_id', $receiving->id)
            ->orderBy('ri.id')
            ->get();

        $subtotal = $items->sum(function ($item) {
            return (float) $item->total_cost;
        });

        return view('purchasing.receiving.show', compact(
            'receiving',
            'items',
            'subtotal'
        ));
    }

    private function postReceivingJournalEntry(
        PurchaseOrder $po,
        string $receivingNo,
        string $receivingDate,
        float $amount
    ): void {
        $amount = round($amount, 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'items' => 'Receiving amount must be greater than zero before posting to accounting.',
            ]);
        }

        $inventoryAccount = $this->findAccountingAccountByCode('1200', 'Inventory Asset');
        $grniAccount = $this->findOrCreateGrniAccount();

        if (! $inventoryAccount || ! $grniAccount) {
            throw ValidationException::withMessages([
                'accounting' => 'Accounting setup is incomplete. Please make sure Chart of Accounts has active accounts: 1200 - Inventory Asset and 2100 - Goods Received Not Invoiced.',
            ]);
        }

        $supplierName = $po->supplier?->supplier_name ?? 'Supplier';
        $memo = 'Purchase Receiving ' . $receivingNo . ' from PO ' . $po->po_no . ' - ' . $supplierName;

        $journalEntry = AccountingJournalEntry::create([
            'entry_no' => $this->generateJournalEntryNo($receivingDate),
            'entry_date' => $receivingDate,
            'description' => $memo,
            'status' => 'posted',
            'total_debit' => $amount,
            'total_credit' => $amount,
            'created_by' => auth()->id(),
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);

        AccountingJournalLine::create([
            'accounting_journal_entry_id' => $journalEntry->id,
            'accounting_account_id' => $inventoryAccount->id,
            'line_no' => 1,
            'description' => $memo,
            'debit' => $amount,
            'credit' => 0,
        ]);

        AccountingJournalLine::create([
            'accounting_journal_entry_id' => $journalEntry->id,
            'accounting_account_id' => $grniAccount->id,
            'line_no' => 2,
            'description' => $memo,
            'debit' => 0,
            'credit' => $amount,
        ]);
    }

    private function findAccountingAccountByCode(string $code, string $name)
    {
        return AccountingAccount::query()
            ->where('is_active', true)
            ->where(function ($query) use ($code, $name) {
                $query->where('code', $code)
                    ->orWhere('name', 'ilike', $name);
            })
            ->orderByRaw("CASE WHEN code = ? THEN 0 ELSE 1 END", [$code])
            ->first();
    }

    
    private function findOrCreateGrniAccount(): AccountingAccount
    {
        $account = AccountingAccount::where('code', '2100')
            ->where('is_active', true)
            ->first();

        if ($account) {
            return $account;
        }

        return AccountingAccount::create([
            'code' => '2100',
            'name' => 'Goods Received Not Invoiced',
            'type' => 'liability',
            'normal_balance' => 'credit',
            'description' => 'Auto-created clearing account for purchase receiving before supplier billing.',
            'is_active' => true,
        ]);
    }

    private function generateReceivingNo(): string
    {
        $prefix = 'RCV-' . now()->format('Ymd') . '-';

        $last = DB::table('warehouse_receivings')
            ->where('receiving_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('receiving_no');

        $next = 1;

        if ($last) {
            $next = ((int) substr($last, -4)) + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function generateJournalEntryNo(string $date): string
    {
        $prefix = 'JE-' . date('Ymd', strtotime($date)) . '-';

        $last = AccountingJournalEntry::query()
            ->where('entry_no', 'like', $prefix . '%')
            ->orderByDesc('entry_no')
            ->value('entry_no');

        $next = 1;

        if ($last) {
            $next = ((int) substr($last, -4)) + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}