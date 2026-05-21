<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\WarehouseCategory;
use App\Models\Warehouse\WarehouseItem;
use App\Models\Warehouse\WarehouseLocation;
use App\Models\Warehouse\WarehouseSupplier;
use App\Models\Warehouse\WarehouseUnit;

class WarehouseDashboardController extends Controller
{
    public function index()
    {
        abort_unless(
            auth()->user()?->can('warehouse.dashboard.view')
            || auth()->user()?->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin']),
            403
        );

        $stats = [
            'categories' => WarehouseCategory::count(),
            'units' => WarehouseUnit::count(),
            'suppliers' => WarehouseSupplier::count(),
            'locations' => WarehouseLocation::count(),
            'items' => WarehouseItem::count(),
        ];

        $recentItems = WarehouseItem::with(['category', 'unit'])->latest()->take(5)->get();

        return view('warehouse.index', compact('stats', 'recentItems'));
    }
}
