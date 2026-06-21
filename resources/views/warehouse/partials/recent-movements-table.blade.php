<div class="table-responsive warehouse-recent-table-wrap">
    <table class="table table-hover align-middle mb-0 warehouse-table warehouse-recent-table">
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
                    $referenceLabel = $movement->reference_no ?? $movement->reference_type ?? '-';
                @endphp

                <tr>
                    <td class="text-secondary warehouse-cell-nowrap" title="{{ optional($movement->transaction_date ?? $movement->created_at)->format('M d, Y h:i A') }}">
                        {{ optional($movement->transaction_date ?? $movement->created_at)->format('M d, Y h:i A') }}
                    </td>

                    <td>
                        <span class="fw-semibold text-primary warehouse-cell-truncate" title="{{ $referenceLabel }}">
                            {{ $referenceLabel }}
                        </span>
                    </td>

                    <td>
                        @if($qty >= 0)
                            <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2 warehouse-type-badge" title="{{ $typeLabel }}">
                                {{ $typeLabel }}
                            </span>
                        @else
                            <span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-2 warehouse-type-badge" title="{{ $typeLabel }}">
                                {{ $typeLabel }}
                            </span>
                        @endif
                    </td>

                    <td>
                        <div class="fw-semibold text-dark warehouse-cell-truncate" title="{{ $itemName ?: '-' }}">{{ $itemName ?: '-' }}</div>
                        <div class="small text-secondary warehouse-cell-truncate" title="{{ $itemCode ?: '-' }}">{{ $itemCode ?: '-' }}</div>
                    </td>

                    <td>
                        <div class="fw-semibold text-dark warehouse-cell-truncate" title="{{ $locationName }}">{{ $locationName }}</div>
                        <div class="small text-secondary warehouse-cell-truncate" title="{{ $branchName }}">{{ $branchName }}</div>
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
                            <div class="fw-semibold text-secondary">{{ request('recent_search') ? 'No matching stock movement found.' : 'No stock movements yet.' }}</div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($recentMovements, 'hasPages') && $recentMovements->hasPages())
    @php
        $currentPage = $recentMovements->currentPage();
        $lastPage = $recentMovements->lastPage();
        $startPage = max(1, $currentPage - 1);
        $endPage = min($lastPage, $currentPage + 1);

        if ($currentPage <= 2) {
            $startPage = 1;
            $endPage = min($lastPage, 3);
        }

        if ($currentPage >= $lastPage - 1) {
            $startPage = max(1, $lastPage - 2);
            $endPage = $lastPage;
        }
    @endphp

    <div class="warehouse-dashboard-pagination mt-3 pt-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="small text-secondary warehouse-pagination-summary">
                Showing {{ $recentMovements->firstItem() }} to {{ $recentMovements->lastItem() }} of {{ $recentMovements->total() }} results
            </div>

            <nav aria-label="Recent stock movements pagination">
                <ul class="pagination warehouse-standard-pagination mb-0">
                    <li class="page-item {{ $recentMovements->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $recentMovements->onFirstPage() ? '#' : $recentMovements->previousPageUrl() }}" tabindex="{{ $recentMovements->onFirstPage() ? '-1' : '0' }}" aria-disabled="{{ $recentMovements->onFirstPage() ? 'true' : 'false' }}">Previous</a>
                    </li>

                    @for($page = $startPage; $page <= $endPage; $page++)
                        <li class="page-item {{ $page === $currentPage ? 'active' : '' }}" @if($page === $currentPage) aria-current="page" @endif>
                            <a class="page-link" href="{{ $recentMovements->url($page) }}">{{ $page }}</a>
                        </li>
                    @endfor

                    <li class="page-item {{ $recentMovements->hasMorePages() ? '' : 'disabled' }}">
                        <a class="page-link" href="{{ $recentMovements->hasMorePages() ? $recentMovements->nextPageUrl() : '#' }}" tabindex="{{ $recentMovements->hasMorePages() ? '0' : '-1' }}" aria-disabled="{{ $recentMovements->hasMorePages() ? 'false' : 'true' }}">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
@endif
