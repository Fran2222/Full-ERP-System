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

        <div class="row g-4 warehouse-dashboard-recent-row">
            @if($canAccess('warehouse.ledger.view'))
                <div class="col-12 d-flex">
                    <div class="card rounded-4 border-0 shadow-sm h-100 w-100 warehouse-recent-movements-card">
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

                                <div class="warehouse-recent-actions">
                                    <div class="warehouse-recent-search">
                                        <span class="warehouse-recent-search-icon">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                                                <path d="M21 21L16.65 16.65M19 11C19 15.4183 15.4183 19 11 19C6.58172 19 3 15.4183 3 11C3 6.58172 6.58172 3 11 3C15.4183 3 19 6.58172 19 11Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <input type="search"
                                               class="form-control warehouse-recent-search-input"
                                               placeholder="Search movements..."
                                               value="{{ $recentSearch ?? request('recent_search') }}"
                                               autocomplete="off"
                                               data-recent-movement-search>
                                    </div>

                                    <a href="{{ route('warehouse.ledger') }}" class="btn btn-outline-primary warehouse-soft-btn warehouse-soft-btn-sm">
                                        View Ledger
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-4 pb-4 pt-3 d-flex flex-column">
                            <div id="warehouseRecentMovementsBox" data-recent-movements-box data-recent-url="{{ request()->url() }}">
                                @include('warehouse.partials.recent-movements-table', ['recentMovements' => $recentMovements])
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        @if($canAccess('warehouse.inventory.view') || $canAccess('warehouse.items.view'))
            <div class="row g-4 mb-4 warehouse-dashboard-alert-row">
                @if($canAccess('warehouse.items.view') || $canAccess('warehouse.inventory.view'))
                    <div class="col-xl-6 d-flex">
                        <div class="card rounded-4 border-0 shadow-sm warehouse-dashboard-info-card warehouse-low-stock-card h-100 w-100">
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

                                <div class="warehouse-stock-list-row border-bottom">
                                    <div class="warehouse-stock-list-info">
                                        <div class="fw-semibold text-dark warehouse-stock-title" title="{{ $itemName ?: '-' }}">{{ $itemName ?: '-' }}</div>
                                        <div class="small text-secondary warehouse-stock-meta" title="{{ $itemCode ?: '-' }}">{{ $itemCode ?: '-' }}</div>
                                    </div>

                                    <div class="text-end flex-shrink-0">
                                        <div class="fw-bold text-danger warehouse-stock-qty">
                                            {{ number_format((float) ($item->total_quantity ?? 0), 2) }}
                                        </div>
                                        <div class="small text-secondary warehouse-stock-meta">
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

                            @if(method_exists($lowStockItems, 'hasPages') && $lowStockItems->hasPages())
                                <nav class="warehouse-card-pagination mt-3" aria-label="Low stock alerts pagination">
                                    <ul class="pagination pagination-sm mb-0 justify-content-end">
                                        <li class="page-item {{ $lowStockItems->onFirstPage() ? 'disabled' : '' }}">
                                            <a class="page-link" href="{{ $lowStockItems->previousPageUrl() ?: '#' }}">Previous</a>
                                        </li>

                                        @for($page = 1; $page <= $lowStockItems->lastPage(); $page++)
                                            <li class="page-item {{ $lowStockItems->currentPage() === $page ? 'active' : '' }}" @if($lowStockItems->currentPage() === $page) aria-current="page" @endif>
                                                <a class="page-link" href="{{ $lowStockItems->url($page) }}">{{ $page }}</a>
                                            </li>
                                        @endfor

                                        <li class="page-item {{ $lowStockItems->hasMorePages() ? '' : 'disabled' }}">
                                            <a class="page-link" href="{{ $lowStockItems->nextPageUrl() ?: '#' }}">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            @endif
                        </div>
                    </div>

                    </div>
                @endif

                @if($canAccess('warehouse.inventory.view'))
                    <div class="col-xl-6 d-flex">
                        <div class="card rounded-4 border-0 shadow-sm warehouse-dashboard-info-card warehouse-top-stock-card h-100 w-100">
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

                                <div class="warehouse-stock-list-row border-bottom">
                                    <div class="warehouse-stock-list-info">
                                        <div class="fw-semibold text-dark warehouse-stock-title" title="{{ $itemName ?: '-' }}">{{ $itemName ?: '-' }}</div>
                                        <div class="small text-secondary warehouse-stock-meta" title="{{ ($stock->branch?->name ?? '-') . ' / ' . $locationName }}">
                                            {{ $stock->branch?->name ?? '-' }} / {{ $locationName }}
                                        </div>
                                        <div class="small text-secondary warehouse-stock-meta" title="{{ $itemCode ?: '-' }}">{{ $itemCode ?: '-' }}</div>
                                    </div>

                                    <div class="fw-bold text-success warehouse-stock-qty flex-shrink-0">
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

                            @if(method_exists($topStocks, 'hasPages') && $topStocks->hasPages())
                                <nav class="warehouse-card-pagination mt-3" aria-label="Top stock pagination">
                                    <ul class="pagination pagination-sm mb-0 justify-content-end">
                                        <li class="page-item {{ $topStocks->onFirstPage() ? 'disabled' : '' }}">
                                            <a class="page-link" href="{{ $topStocks->previousPageUrl() ?: '#' }}">Previous</a>
                                        </li>

                                        @for($page = 1; $page <= $topStocks->lastPage(); $page++)
                                            <li class="page-item {{ $topStocks->currentPage() === $page ? 'active' : '' }}" @if($topStocks->currentPage() === $page) aria-current="page" @endif>
                                                <a class="page-link" href="{{ $topStocks->url($page) }}">{{ $page }}</a>
                                            </li>
                                        @endfor

                                        <li class="page-item {{ $topStocks->hasMorePages() ? '' : 'disabled' }}">
                                            <a class="page-link" href="{{ $topStocks->nextPageUrl() ?: '#' }}">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            @endif
                        </div>
                    </div>

                    </div>
                @endif
            </div>
        @endif

    </div>

    <style>
        .warehouse-dashboard-alert-row {
            margin-bottom: 1.5rem;
        }

        .warehouse-dashboard-recent-row {
            margin-bottom: 2rem;
        }

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

        .warehouse-soft-btn-sm {
            min-width: 112px;
            height: 38px;
            padding: 0.45rem 1rem !important;
            border-radius: 0.65rem !important;
            font-size: 0.92rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .warehouse-recent-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }

        .warehouse-recent-search {
            position: relative;
            width: 260px;
            max-width: 100%;
        }

        .warehouse-recent-search-icon {
            position: absolute;
            top: 50%;
            left: 13px;
            transform: translateY(-50%);
            z-index: 2;
            color: #8a94a6;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .warehouse-recent-search-input {
            height: 38px;
            border-radius: 0.65rem;
            border: 1px solid #e4e8f1;
            padding: 0.45rem 0.85rem 0.45rem 2.45rem;
            font-size: 0.92rem;
            font-weight: 600;
            color: #26334d;
            box-shadow: none !important;
        }

        .warehouse-recent-search-input:focus {
            border-color: #3f5cff;
        }


        .warehouse-stock-list-row {
            min-height: 62px;
            padding: 0.55rem 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .warehouse-stock-list-info {
            min-width: 0;
            flex: 1 1 auto;
        }

        .warehouse-stock-title,
        .warehouse-stock-meta {
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .warehouse-stock-title {
            font-size: 0.95rem;
            line-height: 1.2;
        }

        .warehouse-stock-meta {
            line-height: 1.2;
        }

        .warehouse-stock-qty {
            min-width: 72px;
            text-align: right;
            font-size: 0.95rem;
        }

        .warehouse-card-pagination {
            padding-top: 0.75rem;
            border-top: 1px solid #edf0f5;
        }

        .warehouse-card-pagination .page-link {
            min-width: 34px;
            height: 32px;
            padding: 0 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-color: #e4e8f1;
            color: #3f5cff;
            font-size: 12px;
            font-weight: 700;
            box-shadow: none !important;
        }

        .warehouse-card-pagination .page-item.active .page-link {
            background: #3f5cff;
            border-color: #3f5cff;
            color: #fff;
        }

        .warehouse-card-pagination .page-item.disabled .page-link {
            color: #a0a8b8;
            background: #f8fafc;
            pointer-events: none;
        }

        .warehouse-dashboard-info-card {
            min-height: 250px;
            display: flex;
            flex-direction: column;
        }

        .warehouse-dashboard-info-card .card-body {
            flex: 1 1 auto;
            overflow: hidden;
        }

        .warehouse-dashboard-info-card .border-bottom:last-child {
            border-bottom: 0 !important;
        }

        .warehouse-recent-movements-card {
            min-height: auto;
        }

        .warehouse-recent-movements-card .card-header {
            padding-bottom: 0.75rem !important;
        }

        .warehouse-recent-table-wrap {
            width: 100%;
            overflow-x: hidden;
            overflow-y: visible;
            flex: 1 1 auto;
        }

        .warehouse-table {
            width: 100%;
            min-width: 0;
            table-layout: fixed;
        }

        .warehouse-table thead th {
            background: #f4f6fb;
            color: #8a94a6;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 0;
            padding: 12px 14px;
            white-space: nowrap;
            vertical-align: middle;
        }

        .warehouse-table tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid #edf0f5;
            vertical-align: middle;
            font-size: 13px;
            line-height: 1.25;
            overflow: hidden;
        }

        .warehouse-table tbody tr {
            transition: all 0.18s ease-in-out;
        }

        .warehouse-table tbody tr:hover {
            background: #f8faff;
        }

        .warehouse-recent-table th:nth-child(1),
        .warehouse-recent-table td:nth-child(1) {
            width: 16%;
        }

        .warehouse-recent-table th:nth-child(2),
        .warehouse-recent-table td:nth-child(2) {
            width: 18%;
        }

        .warehouse-recent-table th:nth-child(3),
        .warehouse-recent-table td:nth-child(3) {
            width: 12%;
        }

        .warehouse-recent-table th:nth-child(4),
        .warehouse-recent-table td:nth-child(4) {
            width: 19%;
        }

        .warehouse-recent-table th:nth-child(5),
        .warehouse-recent-table td:nth-child(5) {
            width: 20%;
        }

        .warehouse-recent-table th:nth-child(6),
        .warehouse-recent-table td:nth-child(6) {
            width: 7.5%;
        }

        .warehouse-recent-table th:nth-child(7),
        .warehouse-recent-table td:nth-child(7) {
            width: 7.5%;
        }

        .warehouse-cell-nowrap {
            white-space: nowrap;
        }

        .warehouse-cell-truncate {
            display: block;
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .warehouse-recent-table .badge {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding: 0.35rem 0.6rem !important;
            font-size: 11px;
            border-radius: 999px;
        }

        .warehouse-dashboard-pagination {
            border-top: 1px solid #edf0f5;
        }

        .warehouse-recent-loading {
            opacity: 0.78;
            pointer-events: none;
            transition: opacity 0.12s ease-in-out;
        }

        .warehouse-page-btn,
        .warehouse-page-indicator {
            min-width: 78px;
            height: 34px;
            padding: 0 12px;
            border: 1px solid #e4e8f1;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            background: #fff;
        }

        .warehouse-page-btn {
            color: #3f5cff;
        }

        .warehouse-page-btn:hover {
            color: #fff;
            background: #3f5cff;
            border-color: #3f5cff;
        }

        .warehouse-page-btn.disabled {
            color: #a0a8b8;
            background: #f8fafc;
            pointer-events: none;
        }

        .warehouse-page-indicator {
            min-width: 58px;
            color: #606b80;
            background: #f8fafc;
        }

        .warehouse-empty-td {
            height: 150px;
            padding: 0 !important;
        }

        .warehouse-empty-state {
            min-height: 150px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 10px;
            text-align: center;
            background: #f8fafc;
        }

        .warehouse-empty-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            color: #94a3b8;
            background: #fff;
        }

        .warehouse-side-stack {
            height: 100%;
            min-height: 100%;
            display: grid;
            grid-template-rows: minmax(0, 1fr) minmax(0, 1fr);
            gap: 1.5rem;
        }

        .warehouse-side-card {
            min-height: 0 !important;
            height: 100% !important;
            margin-bottom: 0 !important;
            display: flex;
            flex-direction: column;
        }

        .warehouse-side-card .card-body {
            flex: 1 1 auto;
            overflow: hidden;
        }

        .warehouse-side-empty {
            min-height: 96px;
            height: 100%;
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


        .warehouse-cell-date,
        .warehouse-cell-truncate,
        .warehouse-type-badge {
            display: block;
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .warehouse-standard-pagination .page-link {
            min-width: 42px;
            height: 36px;
            padding: 0 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-color: #e4e8f1;
            color: #3f5cff;
            font-size: 13px;
            font-weight: 700;
            box-shadow: none !important;
        }

        .warehouse-standard-pagination .page-item:first-child .page-link {
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
        }

        .warehouse-standard-pagination .page-item:last-child .page-link {
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        .warehouse-standard-pagination .page-item.active .page-link {
            background: #3f5cff;
            border-color: #3f5cff;
            color: #fff;
        }

        .warehouse-standard-pagination .page-item.disabled .page-link {
            color: #a0a8b8;
            background: #f8fafc;
            pointer-events: none;
        }

        .warehouse-standard-pagination .page-link:hover {
            background: #eef2ff;
            border-color: #cfd7ff;
            color: #3f5cff;
        }

        .warehouse-standard-pagination .page-item.active .page-link:hover {
            background: #3f5cff;
            color: #fff;
        }

        @media (max-width: 1399.98px) {
            .warehouse-table thead th,
            .warehouse-table tbody td {
                padding-left: 8px;
                padding-right: 8px;
                font-size: 12px;
            }

        }

        @media (max-width: 1199.98px) {
            .warehouse-side-stack {
                grid-template-rows: auto auto;
            }

            .warehouse-side-card {
                min-height: 190px !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const recentBox = document.querySelector('[data-recent-movements-box]');
            const recentSearchInput = document.querySelector('[data-recent-movement-search]');

            if (!recentBox) {
                return;
            }

            let searchTimer = null;
            let activeController = null;

            function getBaseRecentUrl() {
                // Important for deployments inside a subfolder like /wizhopeui.
                // Do not use route() here because it may generate /warehouse instead of /wizhopeui/warehouse.
                const url = new URL(recentBox.dataset.recentUrl || window.location.href, window.location.origin);
                url.search = '';
                return url;
            }

            function buildRecentUrl(pageUrl = null) {
                const url = pageUrl
                    ? new URL(pageUrl, window.location.origin)
                    : getBaseRecentUrl();

                url.searchParams.set('warehouse_recent_ajax', '1');
                url.searchParams.set('_ts', Date.now().toString());

                const searchValue = recentSearchInput ? recentSearchInput.value.trim() : '';
                if (searchValue.length > 0) {
                    url.searchParams.set('recent_search', searchValue);

                    // Only reset to page 1 when typing a new search.
                    // When clicking pagination, keep the clicked page number.
                    if (!pageUrl) {
                        url.searchParams.set('recent_page', '1');
                    }
                } else {
                    url.searchParams.delete('recent_search');
                }

                return url;
            }

            function loadRecentMovements(pageUrl = null) {
                const url = buildRecentUrl(pageUrl);

                if (activeController) {
                    activeController.abort();
                }

                activeController = new AbortController();
                recentBox.classList.add('warehouse-recent-loading');

                fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                        'Cache-Control': 'no-cache',
                    },
                    credentials: 'same-origin',
                    signal: activeController.signal,
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Recent movements request failed. HTTP ' + response.status);
                        }

                        return response.text();
                    })
                    .then(function (html) {
                        recentBox.innerHTML = html;
                    })
                    .catch(function (error) {
                        if (error.name !== 'AbortError') {
                            console.error(error);
                            recentBox.innerHTML = '<div class="alert alert-danger mb-0">Unable to load recent stock movements. Please refresh the page.</div>';
                        }
                    })
                    .finally(function () {
                        recentBox.classList.remove('warehouse-recent-loading');
                    });
            }

            recentBox.addEventListener('click', function (event) {
                const link = event.target.closest('.warehouse-standard-pagination a.page-link');

                if (!link || link.getAttribute('href') === '#' || link.closest('.page-item.disabled')) {
                    return;
                }

                event.preventDefault();
                loadRecentMovements(link.href);
            });

            if (recentSearchInput) {
                recentSearchInput.addEventListener('input', function () {
                    window.clearTimeout(searchTimer);

                    searchTimer = window.setTimeout(function () {
                        loadRecentMovements(null);
                    }, 180);
                });

                recentSearchInput.addEventListener('search', function () {
                    loadRecentMovements(null);
                });

                recentSearchInput.addEventListener('keyup', function () {
                    window.clearTimeout(searchTimer);

                    searchTimer = window.setTimeout(function () {
                        loadRecentMovements(null);
                    }, 180);
                });
            }
        });
    </script>


</x-app-layout>
