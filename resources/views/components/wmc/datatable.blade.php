@props([
    'tableId',
    'columns' => [],
    'hasFilter' => false,
    'filterId' => null
])

<div class="card rounded-4 wmc-table-card">
    <div class="card-body">

        @if($hasFilter)
            <div id="{{ $filterId }}Source" class="d-none">
                {{ $filter ?? '' }}
            </div>
        @endif

        <div class="table-responsive">
            <table id="{{ $tableId }}" class="table table-hover align-middle w-100 wmc-table">
                <thead>
                    <tr>
                        @foreach($columns as $col)
                            <th>{{ $col['title'] }}</th>
                        @endforeach
                    </tr>
                </thead>
            </table>
        </div>

    </div>
</div>

<style>
    .filter-holder {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
    }

    .filter-holder .wmc-filter-wrap {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
    }

    .filter-holder label {
        color: #8a92a6 !important;
        margin: 0 !important;
        white-space: nowrap !important;
    }

    .filter-holder .filter-select-wrap {
        position: relative !important;
        width: 220px !important;
    }

    .filter-holder select {
        width: 220px !important;
        height: 38px !important;
        padding: 6px 35px 6px 12px !important;
        border: 1px solid #e0e5f2 !important;
        border-radius: 4px !important;
        color: #8a92a6 !important;
        background-color: #fff !important;
        font-size: 14px !important;
        line-height: 1.5 !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
    }

    .filter-holder .filter-select-wrap::after {
        content: "⌄" !important;
        position: absolute !important;
        right: 12px !important;
        top: 50% !important;
        transform: translateY(-55%) !important;
        color: #8a92a6 !important;
        pointer-events: none !important;
        font-size: 18px !important;
    }
    
</style>