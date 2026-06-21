<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WarehouseItemPickerController extends Controller
{
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');
        $branchId = $request->query('branch_id');
        $locationId = $request->query('location_id');
        $serialized = $request->query('serialized');
        $availableOnly = $request->query('available_only', '1');

        if (!Schema::hasTable('warehouse_inventories') || !Schema::hasTable('warehouse_items')) {
            return response()->json([
                'data' => [],
                'message' => 'Warehouse inventory or items table not found.',
            ]);
        }

        $invCols = Schema::getColumnListing('warehouse_inventories');
        $itemCols = Schema::getColumnListing('warehouse_items');

        $invItemCol = $this->pickCol('warehouse_inventories', ['warehouse_item_id', 'item_id']);
        if (!$invItemCol) {
            return response()->json(['data' => []]);
        }

        $invLocationCol = $this->pickCol('warehouse_inventories', ['warehouse_location_id', 'location_id']);
        $invBranchCol = $this->pickCol('warehouse_inventories', ['branch_id']);
        $invOnHandCol = $this->pickCol('warehouse_inventories', ['on_hand', 'quantity', 'qty']);
        $invBorrowedCol = $this->pickCol('warehouse_inventories', ['borrowed', 'borrowed_qty']);
        $invAvailableCol = $this->pickCol('warehouse_inventories', ['available', 'available_qty']);

        $itemCodeCol = $this->pickCol('warehouse_items', ['code', 'item_code', 'sku']);
        $itemNameCol = $this->pickCol('warehouse_items', ['name', 'item_name']);
        $itemDescCol = $this->pickCol('warehouse_items', ['description', 'remarks']);
        $itemSpecsCol = $this->pickCol('warehouse_items', ['specs', 'specification', 'specifications']);
        $itemCategoryCol = $this->pickCol('warehouse_items', ['category_id', 'warehouse_category_id']);
        $itemUnitCol = $this->pickCol('warehouse_items', ['unit_id', 'warehouse_unit_id']);
        $itemCostCol = $this->pickCol('warehouse_items', ['cost_price', 'cost', 'purchase_price']);
        $itemPriceCol = $this->pickCol('warehouse_items', ['selling_price', 'price', 'srp']);
        $itemSerializedCol = $this->pickCol('warehouse_items', ['is_serialized', 'serialized']);
        $itemImageCol = $this->pickCol('warehouse_items', ['image', 'image_path', 'photo', 'photo_path']);

        $categoryNameExpr = "'-'";
        if ($itemCategoryCol && Schema::hasTable('warehouse_categories')) {
            $catNameCol = $this->pickCol('warehouse_categories', ['name', 'category_name']);
            if ($catNameCol) {
                $categoryNameExpr = "wc.{$catNameCol}";
            }
        }

        $unitNameExpr = "'-'";
        if ($itemUnitCol && Schema::hasTable('warehouse_units')) {
            $unitNameCol = $this->pickCol('warehouse_units', ['name', 'unit_name', 'symbol']);
            if ($unitNameCol) {
                $unitNameExpr = "wu.{$unitNameCol}";
            }
        }

        $branchNameExpr = "'Central / Unassigned'";
        if ($invBranchCol && Schema::hasTable('branches')) {
            $branchNameCol = $this->pickCol('branches', ['name', 'branch_name']);
            if ($branchNameCol) {
                $branchNameExpr = "br.{$branchNameCol}";
            }
        }

        $locationNameExpr = "'-'";
        if ($invLocationCol && Schema::hasTable('warehouse_locations')) {
            $locNameCol = $this->pickCol('warehouse_locations', ['name', 'location_name']);
            if ($locNameCol) {
                $locationNameExpr = "wl.{$locNameCol}";
            }
        }

        $select = [
            'wi.id as item_id',
            'inv.id as inventory_id',
            DB::raw($invLocationCol ? "inv.{$invLocationCol} as location_id" : "NULL as location_id"),
            DB::raw($invBranchCol ? "inv.{$invBranchCol} as branch_id" : "NULL as branch_id"),
            DB::raw($itemCodeCol ? "wi.{$itemCodeCol} as item_code" : "CAST(wi.id AS TEXT) as item_code"),
            DB::raw($itemNameCol ? "wi.{$itemNameCol} as item_name" : "CAST(wi.id AS TEXT) as item_name"),
            DB::raw($itemDescCol ? "COALESCE(wi.{$itemDescCol}, '-') as description" : "'-' as description"),
            DB::raw($itemSpecsCol ? "COALESCE(wi.{$itemSpecsCol}, '-') as specs" : "'-' as specs"),
            DB::raw($itemCostCol ? "COALESCE(wi.{$itemCostCol}, 0) as cost_price" : "0 as cost_price"),
            DB::raw($itemPriceCol ? "COALESCE(wi.{$itemPriceCol}, 0) as selling_price" : "0 as selling_price"),
            DB::raw($itemSerializedCol ? "COALESCE(wi.{$itemSerializedCol}, false) as is_serialized" : "false as is_serialized"),
            DB::raw($itemImageCol ? "wi.{$itemImageCol} as image_path" : "NULL as image_path"),
            DB::raw($categoryNameExpr . ' as category_name'),
            DB::raw($unitNameExpr . ' as unit_name'),
            DB::raw($branchNameExpr . ' as branch_name'),
            DB::raw($locationNameExpr . ' as location_name'),
            DB::raw($invOnHandCol ? "COALESCE(inv.{$invOnHandCol}, 0) as on_hand" : "0 as on_hand"),
            DB::raw($invBorrowedCol ? "COALESCE(inv.{$invBorrowedCol}, 0) as borrowed" : "0 as borrowed"),
        ];

        if ($invAvailableCol) {
            $select[] = DB::raw("COALESCE(inv.{$invAvailableCol}, 0) as available");
        } elseif ($invOnHandCol && $invBorrowedCol) {
            $select[] = DB::raw("(COALESCE(inv.{$invOnHandCol}, 0) - COALESCE(inv.{$invBorrowedCol}, 0)) as available");
        } elseif ($invOnHandCol) {
            $select[] = DB::raw("COALESCE(inv.{$invOnHandCol}, 0) as available");
        } else {
            $select[] = DB::raw("0 as available");
        }

        $query = DB::table('warehouse_inventories as inv')
            ->join('warehouse_items as wi', 'wi.id', '=', "inv.{$invItemCol}")
            ->select($select);

        if ($itemCategoryCol && Schema::hasTable('warehouse_categories')) {
            $query->leftJoin('warehouse_categories as wc', "wc.id", '=', "wi.{$itemCategoryCol}");
        }

        if ($itemUnitCol && Schema::hasTable('warehouse_units')) {
            $query->leftJoin('warehouse_units as wu', "wu.id", '=', "wi.{$itemUnitCol}");
        }

        if ($invBranchCol && Schema::hasTable('branches')) {
            $query->leftJoin('branches as br', "br.id", '=', "inv.{$invBranchCol}");
        }

        if ($invLocationCol && Schema::hasTable('warehouse_locations')) {
            $query->leftJoin('warehouse_locations as wl', "wl.id", '=', "inv.{$invLocationCol}");
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q, $itemCodeCol, $itemNameCol, $itemDescCol, $itemSpecsCol) {
                if ($itemCodeCol) {
                    $sub->orWhere("wi.{$itemCodeCol}", 'like', "%{$q}%");
                }

                if ($itemNameCol) {
                    $sub->orWhere("wi.{$itemNameCol}", 'like', "%{$q}%");
                }

                if ($itemDescCol) {
                    $sub->orWhere("wi.{$itemDescCol}", 'like', "%{$q}%");
                }

                if ($itemSpecsCol) {
                    $sub->orWhere("wi.{$itemSpecsCol}", 'like', "%{$q}%");
                }
            });
        }

        if ($itemCategoryCol && $categoryId) {
            $query->where("wi.{$itemCategoryCol}", $categoryId);
        }

        if ($invBranchCol && $branchId) {
            $query->where("inv.{$invBranchCol}", $branchId);
        }

        if ($invLocationCol && $locationId) {
            $query->where("inv.{$invLocationCol}", $locationId);
        }

        if ($itemSerializedCol && $serialized !== null && $serialized !== '') {
            $query->where("wi.{$itemSerializedCol}", filter_var($serialized, FILTER_VALIDATE_BOOLEAN));
        }

        if ($availableOnly === '1' || $availableOnly === 'true') {
            if ($invAvailableCol) {
                $query->where("inv.{$invAvailableCol}", '>', 0);
            } elseif ($invOnHandCol && $invBorrowedCol) {
                $query->whereRaw("COALESCE(inv.{$invOnHandCol}, 0) - COALESCE(inv.{$invBorrowedCol}, 0) > 0");
            } elseif ($invOnHandCol) {
                $query->where("inv.{$invOnHandCol}", '>', 0);
            }
        }

        $canViewCostPrice = $this->canViewCostPrice();

        $rows = $query
            ->orderBy($itemNameCol ? "wi.{$itemNameCol}" : 'wi.id')
            ->limit(50)
            ->get()
            ->map(function ($row) use ($canViewCostPrice) {
                $image = $row->image_path ?? null;
                if ($image && !preg_match('/^https?:\/\//', $image)) {
                    $image = asset('storage/' . ltrim($image, '/'));
                }

                $isSerialized = filter_var($row->is_serialized, FILTER_VALIDATE_BOOLEAN);
                $serialAvailableCount = null;

                if ($isSerialized) {
                    $serialAvailableCount = $this->serializedAvailableCount(
                        $row->item_id,
                        $row->location_id ?? null,
                        $row->branch_id ?? null
                    );
                }

                $available = $isSerialized && $serialAvailableCount !== null
                    ? (float) $serialAvailableCount
                    : (float) $row->available;

                $onHand = $isSerialized && $serialAvailableCount !== null
                    ? (float) $serialAvailableCount
                    : (float) $row->on_hand;

                return [
                    'inventory_id' => $row->inventory_id,
                    'item_id' => $row->item_id,
                    'location_id' => $row->location_id ?? null,
                    'branch_id' => $row->branch_id ?? null,
                    'item_code' => $row->item_code,
                    'item_name' => $row->item_name,
                    'category_name' => $row->category_name,
                    'unit_name' => $row->unit_name,
                    'branch_name' => $row->branch_name,
                    'location_name' => $row->location_name,
                    'description' => $row->description,
                    'specs' => $row->specs,
                    'cost_price' => $canViewCostPrice ? (float) $row->cost_price : null,
                    'can_view_cost_price' => $canViewCostPrice,
                    'selling_price' => (float) $row->selling_price,
                    'is_serialized' => $isSerialized,
                    'on_hand' => $onHand,
                    'borrowed' => (float) $row->borrowed,
                    'available' => $available,
                    'serialized_available_count' => $serialAvailableCount,
                    'image_url' => $image,
                ];
            })
            ->when($availableOnly === '1' || $availableOnly === 'true', function ($rows) {
                return $rows->filter(function ($row) {
                    return (float) ($row['available'] ?? 0) > 0;
                })->values();
            });

        return response()->json(['data' => $rows]);
    }

    public function serials(Request $request)
    {
        $itemId = $request->query('item_id');
        $inventoryId = $request->query('inventory_id');
        $locationId = $request->query('location_id');
        $branchId = $request->query('branch_id');
        $q = trim((string) $request->query('q', ''));

        if (!Schema::hasTable('warehouse_item_serials')) {
            return response()->json(['data' => []]);
        }

        $serialNoCol = $this->pickCol('warehouse_item_serials', ['serial_no', 'serial_number', 'serial']);
        $itemCol = $this->pickCol('warehouse_item_serials', ['warehouse_item_id', 'item_id']);
        $inventoryCol = $this->pickCol('warehouse_item_serials', ['warehouse_inventory_id', 'inventory_id']);
        $statusCol = $this->pickCol('warehouse_item_serials', ['status']);
        $locationCol = $this->pickCol('warehouse_item_serials', ['warehouse_location_id', 'location_id']);
        $branchCol = $this->pickCol('warehouse_item_serials', ['branch_id']);
        $remarksCol = $this->pickCol('warehouse_item_serials', ['remarks', 'notes']);

        if ($inventoryId && Schema::hasTable('warehouse_inventories')) {
            $invItemCol = $this->pickCol('warehouse_inventories', ['warehouse_item_id', 'item_id']);
            $invLocationCol = $this->pickCol('warehouse_inventories', ['warehouse_location_id', 'location_id']);
            $invBranchCol = $this->pickCol('warehouse_inventories', ['branch_id']);

            $inventory = DB::table('warehouse_inventories')->where('id', $inventoryId)->first();

            if ($inventory) {
                if (! $itemId && $invItemCol && isset($inventory->{$invItemCol})) {
                    $itemId = $inventory->{$invItemCol};
                }

                if (! $locationId && $invLocationCol && isset($inventory->{$invLocationCol})) {
                    $locationId = $inventory->{$invLocationCol};
                }

                if ($branchId === null && $invBranchCol && isset($inventory->{$invBranchCol})) {
                    $branchId = $inventory->{$invBranchCol};
                }
            }
        }

        $select = [
            'id',
            DB::raw($serialNoCol ? "{$serialNoCol} as serial_no" : "CAST(id AS TEXT) as serial_no"),
            DB::raw($statusCol ? "COALESCE({$statusCol}, '-') as status" : "'-' as status"),
            DB::raw($remarksCol ? "COALESCE({$remarksCol}, '-') as remarks" : "'-' as remarks"),
        ];

        $query = DB::table('warehouse_item_serials')->select($select);

        if ($itemCol && $itemId) {
            $query->where($itemCol, $itemId);
        }

        if ($inventoryCol && $inventoryId) {
            $query->where($inventoryCol, $inventoryId);
        }

        if ($locationCol && $locationId) {
            $query->where($locationCol, $locationId);
        }

        if ($branchCol) {
            if ($branchId !== null && $branchId !== '') {
                $query->where($branchCol, $branchId);
            } else {
                $query->whereNull($branchCol);
            }
        }

        if ($statusCol) {
            $query->where(function ($sub) use ($statusCol) {
                $sub->whereNull($statusCol)
                    ->orWhere($statusCol, 'available')
                    ->orWhere($statusCol, 'Available');
            });
        }

        if ($q !== '' && $serialNoCol) {
            $query->where($serialNoCol, 'like', "%{$q}%");
        }

        $canViewCostPrice = $this->canViewCostPrice();

        $rows = $query
            ->orderBy($serialNoCol ?: 'id')
            ->limit(1000)
            ->get()
            ->map(function ($row) use ($canViewCostPrice) {
                return [
                    'id' => $row->id,
                    'serial_no' => $row->serial_no,
                    'status' => $row->status,
                    'remarks' => $row->remarks,
                ];
            });

        return response()->json(['data' => $rows]);
    }

    private function serializedAvailableCount($itemId, $locationId = null, $branchId = null): ?int
    {
        if (!Schema::hasTable('warehouse_item_serials')) {
            return null;
        }

        $itemCol = $this->pickCol('warehouse_item_serials', ['warehouse_item_id', 'item_id']);
        $locationCol = $this->pickCol('warehouse_item_serials', ['warehouse_location_id', 'location_id']);
        $branchCol = $this->pickCol('warehouse_item_serials', ['branch_id']);
        $statusCol = $this->pickCol('warehouse_item_serials', ['status']);

        if (! $itemCol || ! $itemId) {
            return null;
        }

        $query = DB::table('warehouse_item_serials')->where($itemCol, $itemId);

        if ($locationCol && $locationId !== null && $locationId !== '') {
            $query->where($locationCol, $locationId);
        }

        if ($branchCol) {
            if ($branchId !== null && $branchId !== '') {
                $query->where($branchCol, $branchId);
            } else {
                $query->whereNull($branchCol);
            }
        }

        if ($statusCol) {
            $query->where(function ($sub) use ($statusCol) {
                $sub->whereNull($statusCol)
                    ->orWhere($statusCol, 'available')
                    ->orWhere($statusCol, 'Available');
            });
        }

        return (int) $query->count();
    }
    private function canViewCostPrice(): bool
    {
        $user = auth()->user();

        return $user && (
            (method_exists($user, 'canViewCostPrice') && $user->canViewCostPrice())
            || $user->hasAnyRole([
                'Super Admin',
                'Super Administrator',
                'Admin',
                'BOD',
                'Bod',
                'Board of Directors',
                'Board Of Directors',
            ])
            || $user->can('warehouse.cost_price.view')
            || $user->can('view cost price')
        );
    }

    private function pickCol(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }
}