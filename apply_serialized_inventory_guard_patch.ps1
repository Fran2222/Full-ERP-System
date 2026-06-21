
# WMC Serialized Inventory Guard Patch
# Run from: C:\xampp\htdocs\wizhopeui
# This creates a helper and injects sync calls after serialized stock movements.

$ErrorActionPreference = "Stop"

function Backup-File($path) {
    if (Test-Path $path) {
        Copy-Item $path "$path.bak-serialized-guard-$(Get-Date -Format yyyyMMddHHmmss)" -Force
    }
}

function Insert-After($content, $needle, $insert) {
    if ($content -notlike "*$insert*") {
        return $content.Replace($needle, $needle + "`r`n" + $insert)
    }
    return $content
}

function Ensure-Use($content, $useLine) {
    if ($content -match [regex]::Escape($useLine)) { return $content }
    $namespacePos = $content.IndexOf("namespace ")
    if ($namespacePos -lt 0) { return $content }
    $semi = $content.IndexOf(";", $namespacePos)
    if ($semi -lt 0) { return $content }
    return $content.Insert($semi + 1, "`r`n`r`n$useLine")
}

$helperDir = "app\Support"
$helperPath = "$helperDir\WarehouseSerializedInventorySync.php"
New-Item -ItemType Directory -Force -Path $helperDir | Out-Null

@'
<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WarehouseSerializedInventorySync
{
    /**
     * Recompute warehouse_inventories.quantity from actual available serial rows.
     * For serialized items only, warehouse_item_serials is the source of truth.
     */
    public static function syncItem(int $itemId): void
    {
        if ($itemId <= 0 || ! self::tablesReady()) {
            return;
        }

        $isSerialized = (bool) DB::table('warehouse_items')
            ->where('id', $itemId)
            ->value('is_serialized');

        if (! $isSerialized) {
            return;
        }

        $now = now();

        $serialRows = DB::table('warehouse_item_serials')
            ->where('item_id', $itemId)
            ->where('status', 'available')
            ->select('branch_id', 'location_id', DB::raw('COUNT(*) as total'))
            ->groupBy('branch_id', 'location_id')
            ->get();

        foreach ($serialRows as $row) {
            DB::table('warehouse_inventories')->updateOrInsert(
                [
                    'item_id' => $itemId,
                    'branch_id' => $row->branch_id,
                    'location_id' => $row->location_id,
                ],
                [
                    'quantity' => (float) $row->total,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $activeKeys = $serialRows->map(function ($row) {
            return ((string) ($row->branch_id ?? 'NULL')) . ':' . ((string) ($row->location_id ?? 'NULL'));
        })->all();

        $inventoryRows = DB::table('warehouse_inventories')
            ->where('item_id', $itemId)
            ->get(['id', 'branch_id', 'location_id']);

        foreach ($inventoryRows as $inventory) {
            $key = ((string) ($inventory->branch_id ?? 'NULL')) . ':' . ((string) ($inventory->location_id ?? 'NULL'));

            if (! in_array($key, $activeKeys, true)) {
                DB::table('warehouse_inventories')
                    ->where('id', $inventory->id)
                    ->update([
                        'quantity' => 0,
                        'updated_at' => $now,
                    ]);
            }
        }
    }

    public static function syncLocation(int $itemId, $branchId, $locationId): float
    {
        if ($itemId <= 0 || ! self::tablesReady()) {
            return 0;
        }

        $isSerialized = (bool) DB::table('warehouse_items')
            ->where('id', $itemId)
            ->value('is_serialized');

        if (! $isSerialized) {
            return (float) DB::table('warehouse_inventories')
                ->where('item_id', $itemId)
                ->when($branchId === null || $branchId === '', fn ($q) => $q->whereNull('branch_id'), fn ($q) => $q->where('branch_id', $branchId))
                ->where('location_id', $locationId)
                ->value('quantity');
        }

        $query = DB::table('warehouse_item_serials')
            ->where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->where('status', 'available');

        if ($branchId === null || $branchId === '') {
            $query->whereNull('branch_id');
            $cleanBranchId = null;
        } else {
            $query->where('branch_id', $branchId);
            $cleanBranchId = $branchId;
        }

        $count = (float) $query->count();
        $now = now();

        DB::table('warehouse_inventories')->updateOrInsert(
            [
                'item_id' => $itemId,
                'branch_id' => $cleanBranchId,
                'location_id' => $locationId,
            ],
            [
                'quantity' => $count,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        return $count;
    }

    private static function tablesReady(): bool
    {
        return Schema::hasTable('warehouse_items')
            && Schema::hasTable('warehouse_item_serials')
            && Schema::hasTable('warehouse_inventories')
            && Schema::hasColumn('warehouse_items', 'is_serialized')
            && Schema::hasColumn('warehouse_item_serials', 'item_id')
            && Schema::hasColumn('warehouse_item_serials', 'status');
    }
}
'@ | Set-Content $helperPath -Encoding UTF8

# 1) POSController
$path = "app\Http\Controllers\POS\POSController.php"
if (Test-Path $path) {
    Backup-File $path
    $c = Get-Content $path -Raw
    $c = Ensure-Use $c "use App\Support\WarehouseSerializedInventorySync;"

    $needle = @'
            $remaining -= $deduct;
        }

        if ($remaining > 0.00001) {
'@
    $insert = @'
        WarehouseSerializedInventorySync::syncItem((int) $line['item_id']);
'@
    $c = Insert-After $c $needle $insert
    Set-Content $path $c -Encoding UTF8
}

# 2) Warehouse InventoryController
$path = "app\Http\Controllers\Warehouse\InventoryController.php"
if (Test-Path $path) {
    Backup-File $path
    $c = Get-Content $path -Raw
    $c = Ensure-Use $c "use App\Support\WarehouseSerializedInventorySync;"

    $c = $c.Replace(
@'
            $this->createStockInSerialRows($acceptedSerials, $data, (int) $branchId, (int) $movement->id, $referenceNo);

            if ($isFromPurchaseOrder && $purchaseOrder && $purchaseOrderItem) {
'@,
@'
            $this->createStockInSerialRows($acceptedSerials, $data, (int) $branchId, (int) $movement->id, $referenceNo);

            if ($isSerializedItem) {
                $inventory->quantity = WarehouseSerializedInventorySync::syncLocation((int) $data['item_id'], $branchId ?: null, (int) $data['location_id']);
                $inventory->save();
                $movement->balance_after = $inventory->quantity;
                $movement->save();
            }

            if ($isFromPurchaseOrder && $purchaseOrder && $purchaseOrderItem) {
'@)

    $c = $c.Replace(
@'
                WarehouseItemSerial::whereIn('id', $serialIds)->update([
                    'status' => 'stock_out',
                    'stock_out_movement_id' => $movement->id,
                    'issued_at' => now(),
                    'remarks' => $data['remarks'] ?? null,
                    'updated_at' => now(),
                ]);
            }
        });
'@,
@'
                WarehouseItemSerial::whereIn('id', $serialIds)->update([
                    'status' => 'stock_out',
                    'stock_out_movement_id' => $movement->id,
                    'issued_at' => now(),
                    'remarks' => $data['remarks'] ?? null,
                    'updated_at' => now(),
                ]);

                $inventory->quantity = WarehouseSerializedInventorySync::syncLocation((int) $data['item_id'], $branchId ?: null, (int) $data['location_id']);
                $inventory->save();
                $movement->balance_after = $inventory->quantity;
                $movement->save();
            }
        });
'@)

    $c = $c.Replace(
@'
            if ($isSerialized && $data['adjustment_type'] === 'add') {
                $this->createStockInSerialRows($acceptedSerials, $data, (int) ($branchId ?: 0), (int) $movement->id, $referenceNo);
            }

            if ($isSerialized && $data['adjustment_type'] === 'deduct') {
'@,
@'
            if ($isSerialized && $data['adjustment_type'] === 'add') {
                $this->createStockInSerialRows($acceptedSerials, $data, (int) ($branchId ?: 0), (int) $movement->id, $referenceNo);

                $inventory->quantity = WarehouseSerializedInventorySync::syncLocation((int) $data['item_id'], $branchId ?: null, (int) $data['location_id']);
                $inventory->save();
                $movement->balance_after = $inventory->quantity;
                $movement->save();
            }

            if ($isSerialized && $data['adjustment_type'] === 'deduct') {
'@)

    $c = $c.Replace(
@'
                    'remarks' => $data['remarks'] ?? 'Deducted via stock adjustment ' . $referenceNo,
                    'updated_at' => now(),
                ]);
'@,
@'
                    'remarks' => $data['remarks'] ?? 'Deducted via stock adjustment ' . $referenceNo,
                    'updated_at' => now(),
                ]);

                $inventory->quantity = WarehouseSerializedInventorySync::syncLocation((int) $data['item_id'], $branchId ?: null, (int) $data['location_id']);
                $inventory->save();
                $movement->balance_after = $inventory->quantity;
                $movement->save();
'@)

    Set-Content $path $c -Encoding UTF8
}

# 3) WarehouseTransferController
$path = "app\Http\Controllers\Warehouse\WarehouseTransferController.php"
if (Test-Path $path) {
    Backup-File $path
    $c = Get-Content $path -Raw
    $c = Ensure-Use $c "use App\Support\WarehouseSerializedInventorySync;"

    $c = $c.Replace(
@'
                    WarehouseItemSerial::whereIn('id', $line->serials->pluck('id')->all())->update([
                        'status' => 'in_transit',
                        'stock_out_movement_id' => $movement->id,
                        'remarks' => 'In transit under ' . $transfer->transfer_no,
                        'updated_at' => now(),
                    ]);
'@,
@'
                    WarehouseItemSerial::whereIn('id', $line->serials->pluck('id')->all())->update([
                        'status' => 'in_transit',
                        'stock_out_movement_id' => $movement->id,
                        'remarks' => 'In transit under ' . $transfer->transfer_no,
                        'updated_at' => now(),
                    ]);

                    $source->quantity = WarehouseSerializedInventorySync::syncLocation((int) $line->item_id, $transfer->from_branch_id, (int) $transfer->from_location_id);
                    $source->save();
                    $movement->balance_after = $source->quantity;
                    $movement->save();
'@)

    $c = $c.Replace(
@'
                    WarehouseItemSerial::whereIn('id', $line->serials->pluck('id')->all())->update([
                        'branch_id' => $transfer->to_branch_id,
                        'location_id' => $transfer->to_location_id,
                        'status' => 'available',
                        'stock_in_movement_id' => $movement->id,
                        'remarks' => 'Received under ' . $transfer->transfer_no,
                        'updated_at' => now(),
                    ]);
'@,
@'
                    WarehouseItemSerial::whereIn('id', $line->serials->pluck('id')->all())->update([
                        'branch_id' => $transfer->to_branch_id,
                        'location_id' => $transfer->to_location_id,
                        'status' => 'available',
                        'stock_in_movement_id' => $movement->id,
                        'remarks' => 'Received under ' . $transfer->transfer_no,
                        'updated_at' => now(),
                    ]);

                    $destination->quantity = WarehouseSerializedInventorySync::syncLocation((int) $line->item_id, $transfer->to_branch_id, (int) $transfer->to_location_id);
                    $destination->save();
                    $movement->balance_after = $destination->quantity;
                    $movement->save();
'@)

    $c = $c.Replace(
@'
                    WarehouseItemSerial::whereIn('id', $line->serials->pluck('id')->all())->update([
                            'branch_id' => $transfer->from_branch_id,
                            'location_id' => $transfer->from_location_id,
                            'status' => 'available',
                            'stock_in_movement_id' => $movement->id,
                            'remarks' => 'Cancelled transfer ' . $transfer->transfer_no,
                            'updated_at' => now(),
                        ]);
'@,
@'
                    WarehouseItemSerial::whereIn('id', $line->serials->pluck('id')->all())->update([
                            'branch_id' => $transfer->from_branch_id,
                            'location_id' => $transfer->from_location_id,
                            'status' => 'available',
                            'stock_in_movement_id' => $movement->id,
                            'remarks' => 'Cancelled transfer ' . $transfer->transfer_no,
                            'updated_at' => now(),
                        ]);

                        $source->quantity = WarehouseSerializedInventorySync::syncLocation((int) $line->item_id, $transfer->from_branch_id, (int) $transfer->from_location_id);
                        $source->save();
                        $movement->balance_after = $source->quantity;
                        $movement->save();
'@)

    Set-Content $path $c -Encoding UTF8
}

# 4) ServiceUnitController
$path = "app\Http\Controllers\Warehouse\ServiceUnitController.php"
if (Test-Path $path) {
    Backup-File $path
    $c = Get-Content $path -Raw
    $c = Ensure-Use $c "use App\Support\WarehouseSerializedInventorySync;"

    $c = $c.Replace(
@'
            $serial->update([
                'status' => 'borrowed',
                'remarks' => trim(($serial->remarks ? $serial->remarks . "\n" : '') . 'Borrowed under ' . $borrow->borrow_no),
            ]);
'@,
@'
            $serial->update([
                'status' => 'borrowed',
                'remarks' => trim(($serial->remarks ? $serial->remarks . "\n" : '') . 'Borrowed under ' . $borrow->borrow_no),
            ]);

            $inventory->quantity = WarehouseSerializedInventorySync::syncLocation((int) $item->id, $branchId ?: null, (int) $data['location_id']);
            $inventory->save();
'@)

    $c = $c.Replace(
@'
            $serviceUnit->serial?->update([
                'status' => $returnStatus,
                'remarks' => trim(($serviceUnit->serial?->remarks ? $serviceUnit->serial->remarks . "\n" : '') . 'Returned from ' . $serviceUnit->borrow_no),
            ]);
'@,
@'
            $serviceUnit->serial?->update([
                'status' => $returnStatus,
                'remarks' => trim(($serviceUnit->serial?->remarks ? $serviceUnit->serial->remarks . "\n" : '') . 'Returned from ' . $serviceUnit->borrow_no),
            ]);

            $inventory->quantity = WarehouseSerializedInventorySync::syncLocation((int) $serviceUnit->item_id, $branchId ?: null, (int) $serviceUnit->location_id);
            $inventory->save();
'@)

    Set-Content $path $c -Encoding UTF8
}

# 5) SalesReceiptController
$path = "app\Http\Controllers\Sales\SalesReceiptController.php"
if (Test-Path $path) {
    Backup-File $path
    $c = Get-Content $path -Raw
    $c = Ensure-Use $c "use App\Support\WarehouseSerializedInventorySync;"

    $c = $c.Replace(
@'
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
'@,
@'
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

                    $inventory->quantity = WarehouseSerializedInventorySync::syncLocation((int) $warehouseItem->id, $data['branch_id'], (int) $data['location_id']);
                    $inventory->save();
                    $movement->balance_after = $inventory->quantity;
                    $movement->save();
                }
            }
'@)

    $c = $c.Replace(
@'
        $this->releaseSerializedItemsIfLinked($salesReceipt);
'@,
@'
        $this->releaseSerializedItemsIfLinked($salesReceipt);

        foreach ($salesReceipt->items as $line) {
            WarehouseSerializedInventorySync::syncLocation((int) $line->item_id, $salesReceipt->branch_id, (int) $salesReceipt->location_id);
        }
'@)

    Set-Content $path $c -Encoding UTF8
}

php artisan optimize:clear

Write-Host ""
Write-Host "WMC serialized inventory guard patch applied."
Write-Host "Backups created beside each modified file with .bak-serialized-guard timestamp."
Write-Host ""
Write-Host "Recommended verify:"
Write-Host "php artisan tinker --execute=""echo 'SERIAL AVAILABLE PER LOCATION'.PHP_EOL; dump(DB::table('warehouse_item_serials')->where('item_id',4)->where('status','available')->select('branch_id','location_id',DB::raw('count(*) as total'))->groupBy('branch_id','location_id')->orderBy('location_id')->get()); echo PHP_EOL.'INVENTORY'.PHP_EOL; dump(DB::table('warehouse_inventories')->where('item_id',4)->orderBy('location_id')->get());"""
