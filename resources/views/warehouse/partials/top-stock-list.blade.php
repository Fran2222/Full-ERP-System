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
