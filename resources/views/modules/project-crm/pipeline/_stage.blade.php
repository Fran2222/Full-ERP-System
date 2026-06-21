@php
    $stageColor = $stage->color ?? 'primary';
    $leadCount = $stage->leads->count();
    $stageGroupId = 'crm-stage-group-' . $stage->id;
@endphp

<div class="flex-shrink-0"
     data-stage-column-id="{{ $stage->id }}"
     style="width: 350px; min-width: 350px; max-width: 350px;">
    <div class="card shadow-none rounded-4 bg-white border border-light"
         style="border-top: 4px solid var(--bs-{{ $stageColor }}) !important;">
        <div class="card-body p-2">

            {{-- Stage Header --}}
            <div class="d-flex align-items-center justify-content-between mb-2 px-1"
                data-stage-drag-handle
                style="cursor: grab;">
                <div class="d-flex align-items-center gap-2">
                    <button type="button"
                            class="btn p-0 border-0 bg-transparent js-change-stage-color"
                            data-bs-toggle="modal"
                            data-bs-target="#crmChangeStageColorModal"
                            data-stage-name="{{ $stage->name }}"
                            data-current-color="{{ $stageColor }}"
                            data-action="{{ route('crm.pipeline.stages.color', $stage->id) }}"
                            title="Change Stage Color">
                        <span class="badge rounded-pill bg-{{ $stageColor }}">&nbsp;</span>
                    </button>

                    <h6 class="mb-0 text-dark fw-bold text-uppercase">
                        {{ strtoupper($stage->name) }}
                        <span class="crm-stage-count" data-stage-count="{{ $stage->id }}">
                            {{ str_pad($leadCount, 2, '0', STR_PAD_LEFT) }}
                        </span>
                    </h6>
                </div>

                <div class="dropdown">
                    <span class="h5 mb-0 text-secondary" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <circle cx="7" cy="12" r="1" fill="currentColor"/>
                            <circle cx="12" cy="12" r="1" fill="currentColor"/>
                            <circle cx="17" cy="12" r="1" fill="currentColor"/>
                        </svg>
                    </span>

                    <div class="dropdown-menu dropdown-menu-end">
                        <form action="{{ route('crm.pipeline.stages.duplicate', $stage->id) }}" method="POST">
                            @csrf

                            <button type="submit" class="dropdown-item">
                                <svg width="20" viewBox="0 0 24 24" fill="none" class="me-2">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M14.7366 2.76175H8.08455C6.00455 2.75375 4.29955 4.41075 4.25055 6.49075V17.3397C4.21555 19.3897 5.84855 21.0807 7.89955 21.1167C7.96055 21.1167 8.02255 21.1167 8.08455 21.1147H16.0726C18.1416 21.0937 19.8056 19.4087 19.8026 17.3397V8.03975L14.7366 2.76175Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14.4741 2.75V5.659C14.4741 7.079 15.6231 8.23 17.0431 8.234H19.7971" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14.2936 12.9141H9.39355" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M11.8442 15.3639V10.4639" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Duplicate Stage
                            </button>
                        </form>

                        <button type="button"
                                class="dropdown-item js-rename-stage"
                                data-bs-toggle="modal"
                                data-bs-target="#crmRenameStageModal"
                                data-stage-id="{{ $stage->id }}"
                                data-stage-name="{{ $stage->name }}"
                                data-action="{{ route('crm.pipeline.stages.rename', $stage->id) }}">
                            <svg width="20" viewBox="0 0 24 24" fill="none" class="me-2">
                                <path d="M13.7476 20.4428H21.0002" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.78 3.79479C13.5557 2.86779 14.95 2.73186 15.8962 3.49173C15.9485 3.53296 17.6295 4.83879 17.6295 4.83879C18.669 5.46719 18.992 6.80311 18.3494 7.82259C18.3153 7.87718 8.81195 19.7645 8.81195 19.7645C8.49578 20.1589 8.01583 20.3918 7.50291 20.3973L3.86353 20.443L3.04353 16.9723C2.92866 16.4843 3.04353 15.9718 3.3597 15.5773L12.78 3.79479Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M11.021 6.00098L16.4732 10.1881" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Rename Stage
                        </button>

                        @if(!$stage->is_locked)
                            <form action="{{ route('crm.pipeline.stages.archive', $stage->id) }}"
                                method="POST"
                                class="js-archive-stage-form">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="dropdown-item text-danger">
                                    <svg width="20" viewBox="0 0 24 24" fill="none" class="me-2">
                                        <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Archive Stage
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- New item button --}}
            <a href="#"
               class="iq-dashed-border text-decoration-none d-flex align-items-center mb-2 px-3 py-2 js-crm-new-lead js-open-new-lead-modal"
               style="min-height: 44px;"
               data-bs-toggle="modal"
               data-bs-target="#crmNewLeadModal"
               data-stage-id="{{ $stage->id }}"
               data-stage-name="{{ $stage->name }}">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <h6 class="text-secondary mb-0 small fw-semibold">New item</h6>

                    <svg xmlns="http://www.w3.org/2000/svg"
                         width="16"
                         viewBox="0 0 24 24"
                         fill="none"
                         class="text-secondary">
                        <path d="M12.0711 18.9706V4.82847M19.1421 11.8995H5"
                              stroke="currentColor"
                              stroke-linecap="round"/>
                    </svg>
                </div>
            </a>

            {{-- Lead cards only --}}
            <div class="group crm-sortable-group overflow-auto pe-1"
                 id="{{ $stageGroupId }}"
                 data-stage-id="{{ $stage->id }}"
                 data-stage-name="{{ $stage->name }}"
                 style="max-height: 1000px; min-height: 10px;">
                @forelse($stage->leads as $lead)
                    @include('modules.project-crm.pipeline._lead-card', [
                        'lead' => $lead,
                        'stage' => $stage,
                    ])
                @empty
                    {{-- No empty text needed. Keep column clean. --}}
                @endforelse
            </div>

        </div>
    </div>
</div>