<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('warehouse_service_unit_borrows')
            || ! Schema::hasTable('warehouse_inventories')
            || ! Schema::hasTable('warehouse_stock_movements')
        ) {
            return;
        }

        $borrows = DB::table('warehouse_service_unit_borrows')
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        foreach ($borrows as $borrow) {
            $alreadyLogged = DB::table('warehouse_stock_movements')
                ->where('movement_type', 'service_unit_borrow')
                ->where('reference_id', $borrow->id)
                ->exists();

            if ($alreadyLogged) {
                continue;
            }

            $branchId = $borrow->branch_id;

            if (! $branchId && $borrow->location_id && Schema::hasTable('warehouse_locations')) {
                $branchId = DB::table('warehouse_locations')
                    ->where('id', $borrow->location_id)
                    ->value('branch_id');
            }

            $inventory = DB::table('warehouse_inventories')
                ->where('item_id', $borrow->item_id)
                ->where('location_id', $borrow->location_id)
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->first();

            if (! $inventory) {
                continue;
            }

            $newQty = max(0, (float) $inventory->quantity - 1);

            DB::table('warehouse_inventories')
                ->where('id', $inventory->id)
                ->update([
                    'quantity' => $newQty,
                    'updated_at' => now(),
                ]);

            DB::table('warehouse_stock_movements')->insert([
                'item_id' => $borrow->item_id,
                'location_id' => $borrow->location_id,
                'movement_type' => 'service_unit_borrow',
                'quantity' => -1,
                'balance_after' => $newQty,
                'reference_type' => $borrow->borrow_no ?? ('SUB-' . $borrow->id),
                'reference_id' => $borrow->id,
                'remarks' => 'Backfilled service unit borrow movement.',
                'transaction_date' => $borrow->borrowed_at ?? now(),
                'created_by' => $borrow->released_by ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('warehouse_stock_movements')) {
            return;
        }

        DB::table('warehouse_stock_movements')
            ->where('movement_type', 'service_unit_borrow')
            ->where('remarks', 'Backfilled service unit borrow movement.')
            ->delete();
    }
};
