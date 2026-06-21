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
