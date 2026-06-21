<x-app-layout :assets="$assets ?? []">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h4 class="mb-1">CRM Pipeline</h4>
                            <p class="text-secondary mb-0">
                                WMC CRM. Track leads from inquiry up to won or lost deals.
                            </p>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('crm.dashboard') }}" class="btn btn-primary btn-sm px-3">
                                <svg width="16" viewBox="0 0 24 24" fill="none" class="me-1">
                                    <path opacity="0.4" d="M10.0833 3H5.41667C4.07998 3 3 4.08999 3 5.42668V10.0933C3 11.43 4.07998 12.52 5.41667 12.52H10.0833C11.42 12.52 12.5 11.43 12.5 10.0933V5.42668C12.5 4.08999 11.42 3 10.0833 3Z" fill="currentColor"/>
                                    <path d="M18.5833 3H15.9167C14.58 3 13.5 4.08999 13.5 5.42668V8.09332C13.5 9.43 14.58 10.52 15.9167 10.52H18.5833C19.92 10.52 21 9.43 21 8.09332V5.42668C21 4.08999 19.92 3 18.5833 3Z" fill="currentColor"/>
                                    <path d="M18.5833 11.48H15.9167C14.58 11.48 13.5 12.57 13.5 13.9067V18.5733C13.5 19.91 14.58 21 15.9167 21H18.5833C19.92 21 21 19.91 21 18.5733V13.9067C21 12.57 19.92 11.48 18.5833 11.48Z" fill="currentColor"/>
                                    <path opacity="0.4" d="M10.0833 13.48H5.41667C4.07998 13.48 3 14.57 3 15.9067V18.5733C3 19.91 4.07998 21 5.41667 21H10.0833C11.42 21 12.5 19.91 12.5 18.5733V15.9067C12.5 14.57 11.42 13.48 10.0833 13.48Z" fill="currentColor"/>
                                </svg>
                                View Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter / Sort Bar --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card rounded-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center flex-grow-1">
                            <svg width="22" viewBox="0 0 24 24" fill="none"
                                 xmlns="http://www.w3.org/2000/svg"
                                 class="me-3 text-secondary">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                      d="M4.56517 3C3.70108 3 3 3.71286 3 4.5904V5.52644C3 6.17647 3.24719 6.80158 3.68936 7.27177L8.5351 12.4243C9.47271 13.3788 9.99905 14.6734 9.99905 16.0233V20.5952C9.99905 20.9007 10.3187 21.0957 10.584 20.9516L13.3436 19.4479C13.7602 19.2204 14.0201 18.7784 14.0201 18.2984V16.0114C14.0201 14.6691 14.539 13.3799 15.466 12.4243L20.3117 7.27177C20.7528 6.80158 21 6.17647 21 5.52644V4.5904C21 3.71286 20.3 3 19.4359 3H4.56517Z"
                                      stroke="currentColor"
                                      stroke-width="1.5"
                                      stroke-linecap="round"
                                      stroke-linejoin="round"></path>
                            </svg>

                            <input type="text"
                                   id="crmPipelineSearch"
                                   class="form-control border-0 shadow-none p-0"
                                   placeholder="Filter by lead name...">
                        </div>

                        <div class="d-flex align-items-center flex-wrap gap-4 text-secondary">
                            <div class="dropdown">
                                <span class="dropdown-toggle d-flex align-items-center"
                                      role="button"
                                      data-bs-toggle="dropdown"
                                      aria-expanded="false">
                                    Sort By:
                                </span>

                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Lead Name</a>
                                    <a class="dropdown-item" href="#">Priority</a>
                                    <a class="dropdown-item" href="#">Next Follow-up</a>
                                    <a class="dropdown-item" href="#">Estimated Value</a>
                                </div>
                            </div>

                            <div class="dropdown">
                                <span class="dropdown-toggle d-flex align-items-center"
                                      role="button"
                                      data-bs-toggle="dropdown"
                                      aria-expanded="false">
                                    Group By: Status
                                </span>

                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Status</a>
                                    <a class="dropdown-item" href="#">Assigned Staff</a>
                                    <a class="dropdown-item" href="#">Priority</a>
                                    <a class="dropdown-item" href="#">Source</a>
                                </div>
                            </div>

                            <div class="dropdown">
                                <span class="dropdown-toggle d-flex align-items-center"
                                      role="button"
                                      data-bs-toggle="dropdown"
                                      aria-expanded="false">
                                    <svg width="22" viewBox="0 0 24 24" fill="none" class="me-2">
                                        <path d="M20.5 3.5L3.5 10.5L10.5 13.5L13.5 20.5L20.5 3.5Z"
                                              stroke="currentColor"
                                              stroke-width="1.5"
                                              stroke-linecap="round"
                                              stroke-linejoin="round"/>
                                    </svg>
                                    Share
                                </span>

                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Copy Pipeline Link</a>
                                    <a class="dropdown-item" href="#">Export Pipeline</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Horizontal Pipeline: one row, scroll left/right by touchpad --}}
    <div class="overflow-auto pb-3">
        <div id="crmPipelineStageRow" class="row flex-nowrap g-3">
            @forelse($stages as $stage)
                @include('modules.project-crm.pipeline._stage', [
                    'stage' => $stage,
                ])
            @empty
                <div class="col-12 flex-shrink-0">
                    <div class="card rounded-4">
                        <div class="card-body text-center py-5">
                            <h5 class="mb-2">No CRM pipeline stages found</h5>
                            <p class="text-secondary mb-0">
                                Please run the CRM pipeline stage seeder first.
                            </p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    @include('modules.project-crm.pipeline._new-lead-modal')

    {{-- Rename Stage Modal --}}
    <div class="modal fade" id="crmRenameStageModal" tabindex="-1" aria-labelledby="crmRenameStageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="crmRenameStageForm" class="modal-content rounded-4">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title" id="crmRenameStageModalLabel">Rename Stage</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <label class="form-label">Stage Name</label>
                    <input type="text"
                           name="name"
                           id="crmRenameStageName"
                           class="form-control"
                           required
                           maxlength="100">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Change Stage Color Modal --}}
    <div class="modal fade" id="crmChangeStageColorModal" tabindex="-1" aria-labelledby="crmChangeStageColorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="crmChangeStageColorForm" class="modal-content rounded-4">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="crmChangeStageColorModalLabel">Change Stage Color</h5>
                        <p class="text-secondary mb-0 small" id="crmChangeStageColorStageName">
                            Select a color for this stage.
                        </p>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <label class="form-label">Color</label>

                    <div class="d-flex flex-wrap gap-2">
                        @foreach([
                            'primary' => 'Blue',
                            'info' => 'Cyan',
                            'success' => 'Green',
                            'warning' => 'Yellow',
                            'danger' => 'Red',
                            'secondary' => 'Gray',
                            'dark' => 'Dark',
                        ] as $color => $label)
                            <label class="border rounded-3 px-3 py-2 d-flex align-items-center gap-2" style="cursor: pointer;">
                                <input type="radio"
                                    name="color"
                                    value="{{ $color }}"
                                    class="form-check-input m-0 crm-stage-color-option"
                                    required>

                                <span class="rounded-circle bg-{{ $color }}"
                                    style="width: 14px; height: 14px; display: inline-block;"></span>

                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Save Color
                    </button>
                </div>
            </form>
        </div>
    </div>

    @include('modules.project-crm.pipeline._scripts')
</x-app-layout>