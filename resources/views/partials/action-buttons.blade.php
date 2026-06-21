<div class="d-flex align-items-center gap-2">

    @if(!empty($editUrl) && !empty($canEdit))
        <a href="{{ $editUrl }}"
           class="btn btn-sm btn-primary"
           title="Edit">
            <i class="icon">
                <svg width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13.747 3.41095L20.589 10.2529L7.84302 23H1.00098V16.157L13.747 3.41095Z"
                          stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </i>
        </a>
    @endif

    @if(!empty($deleteUrl) && !empty($canDelete))
        <button type="button"
                class="btn btn-sm btn-danger {{ $deleteClass ?? 'delete-item' }}"
                data-url="{{ $deleteUrl }}"
                data-name="{{ $name ?? '' }}"
                title="Delete">
            <i class="icon">
                <svg width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 6H5H21" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M19 6L18.2 19C18.1 20.1 17.2 21 16.1 21H7.9C6.8 21 5.9 20.1 5.8 19L5 6"
                          stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 11V17" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 11V17" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 6V4C9 3.4 9.4 3 10 3H14C14.6 3 15 3.4 15 4V6"
                          stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </i>
        </button>
    @endif

</div>