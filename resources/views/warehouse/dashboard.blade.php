<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">

        @include('warehouse.partials.nav')
        @include('warehouse.inventory._alerts')

        @php
            $user = auth()->user();

            $canAccess = function ($permission) use ($user) {
                return $user && (
                    $user->can($permission)
                    || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
                );
            };

            $safeCards = $cards ?? [
                'categories' => 0,
                'units' => 0,
                'suppliers' => 0,
                'locations' => 0,
                'items' => 0,
                'inventory_rows' => 0,
                'total_stock' => 0,
                'movements' => 0,
            ];

            $recentMovements = $recentMovements ?? collect();
            $topStocks = $topStocks ?? collect();
            $lowStockItems = $lowStockItems ?? collect();

            $summaryCards = [
                [
                    'title' => 'Categories',
                    'value' => $safeCards['categories'] ?? 0,
                    'subtitle' => 'Warehouse item groups',
                    'route' => 'warehouse.categories.index',
                    'permission' => 'warehouse.categories.view',
                    'format' => 'number',
                    'theme' => 'blue',
                    'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M4 7.2C4 6.08 4 5.52 4.218 5.092C4.41 4.716 4.716 4.41 5.092 4.218C5.52 4 6.08 4 7.2 4H8.8C9.92 4 10.48 4 10.908 4.218C11.284 4.41 11.59 4.716 11.782 5.092C12 5.52 12 6.08 12 7.2V8.8C12 9.92 12 10.48 11.782 10.908C11.59 11.284 11.284 11.59 10.908 11.782C10.48 12 9.92 12 8.8 12H7.2C6.08 12 5.52 12 5.092 11.782C4.716 11.59 4.41 11.284 4.218 10.908C4 10.48 4 9.92 4 8.8V7.2Z" stroke="currentColor" stroke-width="1.8"/><path d="M14 7.2C14 6.08 14 5.52 14.218 5.092C14.41 4.716 14.716 4.41 15.092 4.218C15.52 4 16.08 4 17.2 4H18.8C19.92 4 20.48 4 20.908 4.218C21.284 4.41 21.59 4.716 21.782 5.092C22 5.52 22 6.08 22 7.2V8.8C22 9.92 22 10.48 21.782 10.908C21.59 11.284 21.284 11.59 20.908 11.782C20.48 12 19.92 12 18.8 12H17.2C16.08 12 15.52 12 15.092 11.782C14.716 11.59 14.41 11.284 14.218 10.908C14 10.48 14 9.92 14 8.8V7.2Z" stroke="currentColor" stroke-width="1.8"/><path d="M4 17.2C4 16.08 4 15.52 4.218 15.092C4.41 14.716 4.716 14.41 5.092 14.218C5.52 14 6.08 14 7.2 14H8.8C9.92 14 10.48 14 10.908 14.218C11.284 14.41 11.59 14.716 11.782 15.092C12 15.52 12 16.08 12 17.2V18.8C12 19.92 12 20.48 11.782 20.908C11.59 21.284 11.284 21.59 10.908 21.782C10.48 22 9.92 22 8.8 22H7.2C6.08 22 5.52 22 5.092 21.782C4.716 21.59 4.41 21.284 4.218 20.908C4 20.48 4 19.92 4 18.8V17.2Z" stroke="currentColor" stroke-width="1.8"/><path d="M14 17.2C14 16.08 14 15.52 14.218 15.092C14.41 14.716 14.716 14.41 15.092 14.218C15.52 14 16.08 14 17.2 14H18.8C19.92 14 20.48 14 20.908 14.218C21.284 14.41 21.59 14.716 21.782 15.092C22 15.52 22 16.08 22 17.2V18.8C22 19.92 22 20.48 21.782 20.908C21.59 21.284 21.284 21.59 20.908 21.782C20.48 22 19.92 22 18.8 22H17.2C16.08 22 15.52 22 15.092 21.782C14.716 21.59 14.41 21.284 14.218 20.908C14 20.48 14 19.92 14 18.8V17.2Z" stroke="currentColor" stroke-width="1.8"/></svg>',
                ],
                [
                    'title' => 'Items',
                    'value' => $safeCards['items'] ?? 0,
                    'subtitle' => 'Item master records',
                    'route' => 'warehouse.items.index',
                    'permission' => 'warehouse.items.view',
                    'format' => 'number',
                    'theme' => 'green',
                    'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 3L20 7.5V16.5L12 21L4 16.5V7.5L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M4.5 8L12 12.25L19.5 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 12.25V20.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
                ],
                [
                    'title' => 'Total Stock',
                    'value' => $safeCards['total_stock'] ?? 0,
                    'subtitle' => ($safeCards['inventory_rows'] ?? 0) . ' inventory rows',
                    'route' => 'warehouse.inventory',
                    'permission' => 'warehouse.inventory.view',
                    'format' => 'decimal',
                    'theme' => 'purple',
                    'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M4 19V10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M12 19V5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M20 19V13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M3 19H21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M4 10L12 5L20 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                ],
                [
                    'title' => 'Movements',
                    'value' => $safeCards['movements'] ?? 0,
                    'subtitle' => 'Stock movement history',
                    'route' => 'warehouse.ledger',
                    'permission' => 'warehouse.ledger.view',
                    'format' => 'number',
                    'theme' => 'orange',
                    'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M7 7H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M17 4L20 7L17 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 17H4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M7 14L4 17L7 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                ],
            ];

            $visibleSummaryCards = collect($summaryCards)
                ->filter(fn ($card) => $canAccess($card['permission']) && Route::has($card['route']))
                ->values();
        @endphp

        <div class="row g-3 mb-4">
            @foreach($visibleSummaryCards as $card)
                <div class="col-xl-3 col-md-6">
                    <a href="{{ route($card['route']) }}" class="text-decoration-none">
                        <div class="card rounded-4 border-0 shadow-sm warehouse-stat-card h-100 warehouse-stat-{{ $card['theme'] }}">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="text-secondary mb-2">{{ $card['title'] }}</div>

                                        <div class="fs-2 fw-bold text-dark">
                                            @if($card['format'] === 'decimal')
                                                {{ number_format((float) $card['value'], 2) }}
                                            @else
                                                {{ number_format((float) $card['value'], 0) }}
                                            @endif
                                        </div>

                                        <div class="small text-secondary mt-2">{{ $card['subtitle'] }}</div>
                                    </div>

                                    <span class="warehouse-card-icon">
                                        {!! $card['icon'] !!}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="row g-4">
            @if($canAccess('warehouse.ledger.view'))
                <div class="col-xl-8">
                    <div class="card rounded-4 border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="warehouse-section-icon warehouse-section-icon-blue">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                            <path d="M5 5H19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M5 12H19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M5 19H13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M17 17L19 19L22 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <h4 class="card-title mb-1 fw-bold">Recent Stock Movements</h4>
                                        <p class="text-secondary mb-0">Latest warehouse transactions.</p>
                                    </div>
                                </div>

                                <a href="{{ route('warehouse.ledger') }}" class="btn btn-outline-primary">
                                    View Ledger
                                </a>
                            </div>
                        </div>

                        <div class="card-body px-4 pb-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 warehouse-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Reference</th>
                                            <th>Type</th>
                                            <th>Item</th>
                                            <th>Location</th>
                                            <th class="text-end">Qty</th>
                                            <th class="text-end">Balance</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($recentMovements as $movement)
                                            @php
                                                $itemCode = $movement->item?->code ?: $movement->item?->item_code;
                                                $itemName = $movement->item?->name ?: $movement->item?->item_name;

                                                $locationName = $movement->location?->location_name
                                                    ?? $movement->location?->name
                                                    ?? '-';

                                                $branchName = $movement->location?->branch?->name ?? '-';

                                                $qty = (float) $movement->quantity;
                                                $typeLabel = ucwords(str_replace('_', ' ', $movement->movement_type));
                                            @endphp

                                            <tr>
                                                <td class="text-secondary">
                                                    {{ optional($movement->transaction_date ?? $movement->created_at)->format('M d, Y h:i A') }}
                                                </td>

                                                <td>
                                                    <span class="fw-semibold text-primary">
                                                        {{ $movement->reference_type ?: '-' }}
                                                    </span>
                                                </td>

                                                <td>
                                                    @if($qty >= 0)
                                                        <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2">
                                                            {{ $typeLabel }}
                                                        </span>
                                                    @else
                                                        <span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-2">
                                                            {{ $typeLabel }}
                                                        </span>
                                                    @endif
                                                </td>

                                                <td>
                                                    <div class="fw-semibold text-dark">{{ $itemName ?: '-' }}</div>
                                                    <div class="small text-secondary">{{ $itemCode ?: '-' }}</div>
                                                </td>

                                                <td>
                                                    <div class="fw-semibold text-dark">{{ $locationName }}</div>
                                                    <div class="small text-secondary">{{ $branchName }}</div>
                                                </td>

                                                <td class="text-end">
                                                    <span class="fw-bold {{ $qty >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $qty >= 0 ? '+' : '' }}{{ number_format($qty, 2) }}
                                                    </span>
                                                </td>

                                                <td class="text-end fw-semibold">
                                                    {{ number_format((float) $movement->balance_after, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="warehouse-empty-td">
                                                    <div class="warehouse-empty-state">
                                                        <span class="warehouse-empty-icon">
                                                            <svg width="30" height="30" viewBox="0 0 24 24" fill="none">
                                                                <path d="M5 5H19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                                <path d="M5 12H19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                                <path d="M5 19H13" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                            </svg>
                                                        </span>
                                                        <div class="fw-semibold text-secondary">No stock movements yet.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($canAccess('warehouse.inventory.view') || $canAccess('warehouse.items.view'))
                <div class="{{ $canAccess('warehouse.ledger.view') ? 'col-xl-4' : 'col-xl-12' }}">
                    @if($canAccess('warehouse.items.view') || $canAccess('warehouse.inventory.view'))
                        <div class="card rounded-4 border-0 shadow-sm mb-4 warehouse-side-card">
                            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="warehouse-section-icon warehouse-section-icon-red">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 9V13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M12 17H12.01" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                            <path d="M10.29 3.86L2.82 16.34C2.41 17.03 2.2 17.37 2.23 17.66C2.25 17.92 2.39 18.15 2.6 18.3C2.84 18.47 3.24 18.47 4.04 18.47H19.96C20.76 18.47 21.16 18.47 21.4 18.3C21.61 18.15 21.75 17.92 21.77 17.66C21.8 17.37 21.59 17.03 21.18 16.34L13.71 3.86C13.31 3.2 13.12 2.87 12.86 2.76C12.63 2.66 12.37 2.66 12.14 2.76C11.88 2.87 11.69 3.2 11.29 3.86Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <h4 class="card-title mb-1 fw-bold">Low Stock Alerts</h4>
                                        <p class="text-secondary mb-0">Items at or below reorder level.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body px-4 pb-4">
                                @forelse($lowStockItems as $item)
                                    @php
                                        $itemCode = $item->code ?: $item->item_code;
                                        $itemName = $item->name ?: $item->item_name;
                                        $reorderLevel = $item->reorder_level ?? $item->minimum_stock ?? 0;
                                    @endphp

                                    <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $itemName ?: '-' }}</div>
                                            <div class="small text-secondary">{{ $itemCode ?: '-' }}</div>
                                        </div>

                                        <div class="text-end">
                                            <div class="fw-bold text-danger">
                                                {{ number_format((float) ($item->total_quantity ?? 0), 2) }}
                                            </div>
                                            <div class="small text-secondary">
                                                Reorder: {{ number_format((float) $reorderLevel, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="warehouse-side-empty">
                                        <span class="warehouse-mini-empty-icon text-danger">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                <path d="M12 9V13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <path d="M12 17H12.01" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                                <path d="M10.29 3.86L2.82 16.34C2.41 17.03 2.2 17.37 2.23 17.66C2.25 17.92 2.39 18.15 2.6 18.3C2.84 18.47 3.24 18.47 4.04 18.47H19.96C20.76 18.47 21.16 18.47 21.4 18.3C21.61 18.15 21.75 17.92 21.77 17.66C21.8 17.37 21.59 17.03 21.18 16.34L13.71 3.86C13.31 3.2 13.12 2.87 12.86 2.76C12.63 2.66 12.37 2.66 12.14 2.76C11.88 2.87 11.69 3.2 11.29 3.86Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <div class="text-secondary">No low stock item.</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    @if($canAccess('warehouse.inventory.view'))
                        <div class="card rounded-4 border-0 shadow-sm warehouse-side-card">
                            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="warehouse-section-icon warehouse-section-icon-green">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 3L14.78 8.63L21 9.54L16.5 13.92L17.56 20.11L12 17.19L6.44 20.11L7.5 13.92L3 9.54L9.22 8.63L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <h4 class="card-title mb-1 fw-bold">Top Stock</h4>
                                        <p class="text-secondary mb-0">Highest available quantities.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body px-4 pb-4">
                                @forelse($topStocks as $stock)
                                    @php
                                        $itemCode = $stock->item?->code ?: $stock->item?->item_code;
                                        $itemName = $stock->item?->name ?: $stock->item?->item_name;

                                        $locationName = $stock->location?->location_name
                                            ?? $stock->location?->name
                                            ?? '-';
                                    @endphp

                                    <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $itemName ?: '-' }}</div>
                                            <div class="small text-secondary">
                                                {{ $stock->branch?->name ?? '-' }} / {{ $locationName }}
                                            </div>
                                            <div class="small text-secondary">{{ $itemCode ?: '-' }}</div>
                                        </div>

                                        <div class="fw-bold text-success">
                                            {{ number_format((float) ($stock->quantity ?? 0), 2) }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="warehouse-side-empty">
                                        <span class="warehouse-mini-empty-icon text-success">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                <path d="M12 3L14.78 8.63L21 9.54L16.5 13.92L17.56 20.11L12 17.19L6.44 20.11L7.5 13.92L3 9.54L9.22 8.63L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <div class="text-secondary">No stock yet.</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <style>
        .warehouse-stat-card {
            transition: all 0.18s ease-in-out;
            overflow: hidden;
            position: relative;
        }

        .warehouse-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(8, 23, 53, 0.08) !important;
        }

        .warehouse-card-icon,
        .warehouse-section-icon,
        .warehouse-empty-icon,
        .warehouse-mini-empty-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .warehouse-card-icon {
            width: 46px;
            height: 46px;
            border-radius: 16px;
        }

        .warehouse-section-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
        }

        .warehouse-stat-blue .warehouse-card-icon,
        .warehouse-section-icon-blue {
            color: #3f5cff;
            background: #eef2ff;
        }

        .warehouse-stat-green .warehouse-card-icon,
        .warehouse-section-icon-green {
            color: #16a34a;
            background: #ecfdf3;
        }

        .warehouse-stat-purple .warehouse-card-icon {
            color: #7c3aed;
            background: #f3e8ff;
        }

        .warehouse-stat-orange .warehouse-card-icon {
            color: #f97316;
            background: #fff7ed;
        }

        .warehouse-section-icon-red {
            color: #dc2626;
            background: #fef2f2;
        }

        .warehouse-side-card {
            min-height: 190px;
        }

        .warehouse-table thead th {
            background: #f4f6fb;
            color: #8a94a6;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 0;
            padding: 14px 16px;
            white-space: nowrap;
        }

        .warehouse-table tbody td {
            padding: 16px;
            border-bottom: 1px solid #edf0f5;
            vertical-align: middle;
        }

        .warehouse-table tbody tr {
            transition: all 0.18s ease-in-out;
        }

        .warehouse-table tbody tr:hover {
            background: #f8faff;
        }

        .warehouse-empty-td {
            height: 190px;
            padding: 0 !important;
        }

        .warehouse-empty-state {
            min-height: 190px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }

        .warehouse-empty-icon {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            color: #94a3b8;
            background: #f8fafc;
        }

        .warehouse-side-empty {
            min-height: 96px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 8px;
            text-align: center;
        }

        .warehouse-mini-empty-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: #f8fafc;
        }
    </style>
</x-app-layout>
