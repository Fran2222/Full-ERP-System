<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\Inventory;
use App\Models\Warehouse\StockMovement;
use App\Models\Warehouse\WarehouseCategory;
use App\Models\Warehouse\WarehouseItem;
use App\Models\Warehouse\WarehouseLocation;
use App\Models\Warehouse\WarehouseSupplier;
use App\Models\Warehouse\WarehouseUnit;

class DashboardController extends Controller
{
    public function index()
    {
        abort_unless(
            auth()->user()?->can('warehouse.dashboard.view')
            || auth()->user()?->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin']),
            403
        );

        return view('warehouse.dashboard', [
            'categoryCount' => WarehouseCategory::count(),
            'unitCount' => WarehouseUnit::count(),
            'supplierCount' => WarehouseSupplier::count(),
            'locationCount' => WarehouseLocation::count(),
            'itemCount' => WarehouseItem::count(),
            'inventoryCount' => Inventory::count(),
            'totalStock' => Inventory::sum('quantity'),
            'movementCount' => StockMovement::count(),
            'recentItems' => WarehouseItem::with(['category', 'unit'])->latest()->take(5)->get(),
            'recentMovements' => StockMovement::with(['item', 'fromBranch', 'fromLocation', 'toBranch', 'toLocation'])->latest()->take(5)->get(),
        ]);
    }
}
