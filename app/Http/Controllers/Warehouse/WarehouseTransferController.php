<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Warehouse\Inventory;
use App\Models\Warehouse\StockMovement;
use App\Models\Warehouse\WarehouseItem;
use App\Models\Warehouse\WarehouseItemSerial;
use App\Models\Warehouse\WarehouseLocation;
use App\Models\Warehouse\WarehouseTransfer;
use App\Models\Warehouse\WarehouseTransferItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseTransferController extends Controller
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
            'Unauthorized warehouse transfer action.'
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

    private function canReceive(): bool
    {
        return $this->canAccess('warehouse.transfer.receive')
            || $this->canAccess('warehouse.transfer.create')
            || $this->canAccess('warehouse.stock_in.create');
    }

    public function index(Request $request)
    {
        $this->access('warehouse.transfer.create');

        $query = WarehouseTransfer::with(['fromBranch', 'fromLocation', 'toBranch', 'toLocation', 'creator', 'items'])
            ->withCount('items')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('transfer_no', 'ilike', '%' . $search . '%')
                    ->orWhere('remarks', 'ilike', '%' . $search . '%')
                    ->orWhereHas('fromLocation', function ($location) use ($search) {
                        $location->where('location_name', 'ilike', '%' . $search . '%')
                            ->orWhere('name', 'ilike', '%' . $search . '%');
                    })
                    ->orWhereHas('toLocation', function ($location) use ($search) {
                        $location->where('location_name', 'ilike', '%' . $search . '%')
                            ->orWhere('name', 'ilike', '%' . $search . '%');
                    })
                    ->orWhereHas('fromBranch', function ($branch) use ($search) {
                        $branch->where('name', 'ilike', '%' . $search . '%');
                    })
                    ->orWhereHas('toBranch', function ($branch) use ($search) {
                        $branch->where('name', 'ilike', '%' . $search . '%');
                    });
            });
        }

        $transfers = $query->paginate((int) $request->input('per_page', 10))->withQueryString();

        $cards = [
            'draft' => WarehouseTransfer::where('status', 'draft')->count(),
            'in_transit' => WarehouseTransfer::where('status', 'in_transit')->count(),
            'received' => WarehouseTransfer::where('status', 'received')->count(),
            'cancelled' => WarehouseTransfer::where('status', 'cancelled')->count(),
        ];

        return view('warehouse.transfers.index', compact('transfers', 'cards'));
    }

    public function create()
    {
        $this->access('warehouse.transfer.create');

        return view('warehouse.transfers.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->access('warehouse.transfer.create');

        $data = $request->validate([
            'from_branch_id' => ['nullable', 'exists:branches,id'],
            'from_location_id' => ['required', 'exists:warehouse_locations,id'],
            'to_branch_id' => ['nullable', 'exists:branches,id'],
            'to_location_id' => ['required', 'exists:warehouse_locations,id', 'different:from_location_id'],
            'transfer_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:warehouse_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.remarks' => ['nullable', 'string'],
            'items.*.serial_ids' => ['nullable', 'array'],
            'items.*.serial_ids.*' => ['nullable', 'exists:warehouse_item_serials,id'],
        ]);

        $transfer = DB::transaction(function () use ($data) {
            $transfer = WarehouseTransfer::create([
                'transfer_no' => $this->nextTransferNo(),
                'from_branch_id' => $data['from_branch_id'] ?? null,
                'from_location_id' => $data['from_location_id'],
                'to_branch_id' => $data['to_branch_id'] ?? null,
                'to_location_id' => $data['to_location_id'],
                'status' => 'draft',
                'transfer_date' => $data['transfer_date'] ?? now()->toDateString(),
                'remarks' => $data['remarks'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['items'] as $line) {
                $item = WarehouseItem::findOrFail($line['item_id']);
                $quantity = (float) $line['quantity'];
                $serialIds = collect($line['serial_ids'] ?? [])->filter()->unique()->values();

                if ($item->is_serialized) {
                    abort_unless($serialIds->count() === (int) $quantity, 422, 'Serialized item quantity must match selected serial numbers.');
                    $this->validateAvailableSerials($item->id, $data['from_branch_id'] ?? null, $data['from_location_id'], $serialIds->all());
                }

                $transferItem = WarehouseTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'remarks' => $line['remarks'] ?? null,
                ]);

                if ($serialIds->isNotEmpty()) {
                    $transferItem->serials()->sync($serialIds->all());
                }
            }

            return $transfer;
        });

        return redirect()
            ->route('warehouse.transfer.show', $transfer->id)
            ->with('success', 'Transfer draft created successfully. Dispatch it when the items are ready for delivery.');
    }

    public function show(WarehouseTransfer $transfer)
    {
        $this->access('warehouse.transfer.create');

        $transfer->load([
            'fromBranch', 'fromLocation', 'toBranch', 'toLocation', 'creator', 'dispatcher', 'receiver', 'canceller',
            'items.item', 'items.serials.location.branch',
        ]);

        $canDispatch = $transfer->status === 'draft' && $this->canAccess('warehouse.transfer.create');
        $canCancel = in_array($transfer->status, ['draft', 'in_transit'], true) && $this->canAccess('warehouse.transfer.create');
        $canReceive = $transfer->status === 'in_transit' && $this->canReceive();

        return view('warehouse.transfers.show', compact('transfer', 'canDispatch', 'canCancel', 'canReceive'));
    }

    public function dispatchTransfer(WarehouseTransfer $transfer)
    {
        $this->access('warehouse.transfer.create');

        abort_unless($transfer->status === 'draft', 422, 'Only draft transfers can be dispatched.');

        DB::transaction(function () use ($transfer) {
            $transfer->load(['items.item', 'items.serials']);

            foreach ($transfer->items as $line) {
                $qty = abs((float) $line->quantity);

                $source = $this->inventoryQuery($line->item_id, $transfer->from_branch_id, $transfer->from_location_id)
                    ->lockForUpdate()
                    ->first();

                abort_unless($source && (float) $source->quantity >= $qty, 422, 'Insufficient source stock for ' . ($line->item?->display_name ?? 'selected item') . '.');

                if ($line->item?->is_serialized) {
                    $serialIds = $line->serials->pluck('id')->all();
                    abort_unless(count($serialIds) === (int) $qty, 422, 'Serialized item quantity must match selected serial numbers.');
                    $this->validateAvailableSerials($line->item_id, $transfer->from_branch_id, $transfer->from_location_id, $serialIds);
                }

                $source->quantity = (float) $source->quantity - $qty;
                $source->save();

                $movement = StockMovement::create([
                    'item_id' => $line->item_id,
                    'location_id' => $transfer->from_location_id,
                    'movement_type' => 'transfer_out',
                    'quantity' => -$qty,
                    'balance_after' => $source->quantity,
                    'reference_type' => $transfer->transfer_no,
                    'reference_id' => $transfer->id,
                    'remarks' => 'Dispatched transfer to ' . ($transfer->toLocation?->location_name ?? $transfer->toLocation?->name ?? 'destination') . ($line->remarks ? ' - ' . $line->remarks : ''),
                    'transaction_date' => now(),
                    'created_by' => auth()->id(),
                ]);

                if ($line->item?->is_serialized) {
                    WarehouseItemSerial::whereIn('id', $line->serials->pluck('id')->all())->update([
                        'status' => 'in_transit',
                        'stock_out_movement_id' => $movement->id,
                        'remarks' => 'In transit under ' . $transfer->transfer_no,
                        'updated_at' => now(),
                    ]);
                }
            }

            $transfer->update([
                'status' => 'in_transit',
                'dispatched_at' => now(),
                'dispatched_by' => auth()->id(),
            ]);
        });

        return redirect()->route('warehouse.transfer.show', $transfer->id)->with('success', 'Transfer dispatched successfully. Source inventory has been deducted.');
    }

    public function receive(WarehouseTransfer $transfer)
    {
        abort_unless($this->canReceive(), 403, 'Unauthorized warehouse transfer receive action.');
        abort_unless($transfer->status === 'in_transit', 422, 'Only on-going transfers can be received.');

        DB::transaction(function () use ($transfer) {
            $transfer->load(['items.item', 'items.serials']);

            foreach ($transfer->items as $line) {
                $qty = abs((float) $line->quantity);
                $destination = Inventory::firstOrCreate([
                    'item_id' => $line->item_id,
                    'branch_id' => $transfer->to_branch_id,
                    'location_id' => $transfer->to_location_id,
                ], ['quantity' => 0]);

                $destination->quantity = (float) $destination->quantity + $qty;
                $destination->save();

                $movement = StockMovement::create([
                    'item_id' => $line->item_id,
                    'location_id' => $transfer->to_location_id,
                    'movement_type' => 'transfer_in',
                    'quantity' => $qty,
                    'balance_after' => $destination->quantity,
                    'reference_type' => $transfer->transfer_no,
                    'reference_id' => $transfer->id,
                    'remarks' => 'Received transfer from ' . ($transfer->fromLocation?->location_name ?? $transfer->fromLocation?->name ?? 'source') . ($line->remarks ? ' - ' . $line->remarks : ''),
                    'transaction_date' => now(),
                    'created_by' => auth()->id(),
                ]);

                if ($line->item?->is_serialized) {
                    WarehouseItemSerial::whereIn('id', $line->serials->pluck('id')->all())->update([
                        'branch_id' => $transfer->to_branch_id,
                        'location_id' => $transfer->to_location_id,
                        'status' => 'available',
                        'stock_in_movement_id' => $movement->id,
                        'remarks' => 'Received under ' . $transfer->transfer_no,
                        'updated_at' => now(),
                    ]);
                }
            }

            $transfer->update([
                'status' => 'received',
                'received_at' => now(),
                'received_by' => auth()->id(),
            ]);
        });

        return redirect()->route('warehouse.transfer.show', $transfer->id)->with('success', 'Transfer received successfully. Destination inventory has been added.');
    }

    public function cancel(WarehouseTransfer $transfer)
    {
        $this->access('warehouse.transfer.create');
        abort_unless(in_array($transfer->status, ['draft', 'in_transit'], true), 422, 'This transfer can no longer be cancelled.');

        DB::transaction(function () use ($transfer) {
            $transfer->load(['items.item', 'items.serials']);

            if ($transfer->status === 'in_transit') {
                foreach ($transfer->items as $line) {
                    $qty = abs((float) $line->quantity);
                    $source = Inventory::firstOrCreate([
                        'item_id' => $line->item_id,
                        'branch_id' => $transfer->from_branch_id,
                        'location_id' => $transfer->from_location_id,
                    ], ['quantity' => 0]);

                    $source->quantity = (float) $source->quantity + $qty;
                    $source->save();

                    $movement = StockMovement::create([
                        'item_id' => $line->item_id,
                        'location_id' => $transfer->from_location_id,
                        'movement_type' => 'transfer_cancelled',
                        'quantity' => $qty,
                        'balance_after' => $source->quantity,
                        'reference_type' => $transfer->transfer_no,
                        'reference_id' => $transfer->id,
                        'remarks' => 'Cancelled in-transit transfer; stock returned to source.',
                        'transaction_date' => now(),
                        'created_by' => auth()->id(),
                    ]);

                    if ($line->item?->is_serialized) {
                        WarehouseItemSerial::whereIn('id', $line->serials->pluck('id')->all())->update([
                            'branch_id' => $transfer->from_branch_id,
                            'location_id' => $transfer->from_location_id,
                            'status' => 'available',
                            'stock_in_movement_id' => $movement->id,
                            'remarks' => 'Cancelled transfer ' . $transfer->transfer_no,
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            $transfer->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
            ]);
        });

        return redirect()->route('warehouse.transfer.show', $transfer->id)->with('success', 'Transfer cancelled successfully.');
    }

    public function availableSerials(Request $request)
    {
        $this->access('warehouse.transfer.create');

        $itemId = $request->input('item_id');
        $locationId = $request->input('location_id');
        $branchId = $request->input('branch_id');
        $search = trim((string) $request->input('q'));

        $query = WarehouseItemSerial::query()
            ->where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->where('status', 'available')
            ->orderBy('serial_number');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        } else {
            $query->whereNull('branch_id');
        }

        if ($search !== '') {
            $query->where('serial_number', 'ilike', '%' . $search . '%');
        }

        return response()->json($query->limit(100)->get(['id', 'serial_number'])->map(function ($serial) {
            return ['id' => $serial->id, 'text' => $serial->serial_number];
        }));
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

    private function nextTransferNo(): string
    {
        $date = now()->format('Ymd');
        $count = WarehouseTransfer::where('transfer_no', 'like', 'TRF-' . $date . '-%')->count() + 1;

        do {
            $number = 'TRF-' . $date . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $count++;
        } while (WarehouseTransfer::where('transfer_no', $number)->exists());

        return $number;
    }

    private function inventoryQuery($itemId, $branchId, $locationId)
    {
        $query = Inventory::where('item_id', $itemId)->where('location_id', $locationId);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        } else {
            $query->whereNull('branch_id');
        }

        return $query;
    }

    private function validateAvailableSerials($itemId, $branchId, $locationId, array $serialIds): void
    {
        $query = WarehouseItemSerial::whereIn('id', $serialIds)
            ->where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->where('status', 'available');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        } else {
            $query->whereNull('branch_id');
        }

        abort_unless($query->count() === count($serialIds), 422, 'One or more selected serial numbers are no longer available in the source location.');
    }
}
