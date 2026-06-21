<x-app-layout>
    <style>

        .wmc-crm-filterbar { background:#fff; border:0; border-radius: 8px; box-shadow: 0 10px 30px rgba(8,15,52,.05); }
        .wmc-crm-filterbar .card-body { min-height: 72px; padding: 14px 22px; }
        .wmc-drive-search { min-width: 280px; flex: 1 1 420px; display:flex; align-items:center; gap:14px; }
        .wmc-drive-search svg { color:#6f7a87; flex:0 0 auto; }
        .wmc-drive-search input { border:0 !important; box-shadow:none !important; background:transparent; padding-left:0; min-height:42px; font-size:15px; }
        .wmc-drive-search input:focus { box-shadow:none; }
        .wmc-filter-select { display:flex; align-items:center; gap:7px; white-space:nowrap; color:#6c757d; font-size:15px; }
        .wmc-filter-select .wmc-filter-label { color:#6c757d; font-weight:500; }
        .wmc-filter-select select { width: 145px; }
        .wmc-filter-select .select2-container { min-width: 145px; }
        .wmc-filter-select .select2-container .select2-selection--single { border:0 !important; background:transparent !important; min-height:36px; height:36px; display:flex; align-items:center; box-shadow:none !important; }
        .wmc-filter-select .select2-container--default .select2-selection--single .select2-selection__rendered { color:#6c757d; padding-left:0; padding-right:20px; line-height:36px; }
        .wmc-filter-select .select2-container--default .select2-selection--single .select2-selection__arrow { height:36px; right:0; }
        .wmc-filter-select .select2-container--default .select2-selection--single .select2-selection__clear { margin-right:18px; }
        .wmc-view-pill { display:inline-flex; align-items:center; overflow:hidden; border:1px solid #9aa3ad; border-radius:999px; background:#fff; height:38px; }
        .wmc-view-pill a { width:48px; height:38px; display:inline-flex; align-items:center; justify-content:center; color:#232d42; text-decoration:none; border-right:1px solid #9aa3ad; }
        .wmc-view-pill a:last-child { border-right:0; }
        .wmc-view-pill a.active, .wmc-view-pill a:hover { background:#dff3ff; color:#111827; }
        .wmc-view-pill svg { width:19px; height:19px; }
        .wmc-clear-filter-link { font-size:13px; color:#6c757d; text-decoration:none; }
        .wmc-clear-filter-link:hover { color:#0d6efd; }
        @media (max-width: 991.98px) { .wmc-drive-search { min-width:100%; } .wmc-filter-select { width:100%; justify-content:space-between; } .wmc-filter-select .select2-container { min-width: 180px; } }
        .wmc-files-toolbar .form-control,
        .wmc-files-toolbar .form-select { min-height: 42px; border-radius: 10px; border-color: #e0e5f2; }
        .wmc-files-toolbar .select2-container .select2-selection--single { height: 42px; border-radius: 10px; border-color: #e0e5f2; display: flex; align-items: center; }
        .wmc-files-toolbar .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px; }
        .wmc-files-toolbar .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
        .wmc-view-toggle { width: 42px; height: 42px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e0e5f2; background: #fff; color: #6c757d; text-decoration: none; }
        .wmc-view-toggle.active, .wmc-view-toggle:hover { background: #edf8ff; color: #0d6efd; border-color: #91d9ff; }
        .wmc-folder-grid { --bs-gutter-x: 1.15rem; --bs-gutter-y: 1.15rem; }
        .wmc-folder-tile { position: relative; width: 184px; max-width: 100%; min-height: 164px; border: 1px solid transparent; border-radius: 8px; padding: 16px 16px 10px; transition: .16s ease; text-align: center; margin: 0 auto; }
        .wmc-folder-tile:hover { border-color: rgba(47, 183, 255, .25); background: rgba(47, 183, 255, .04); }
        .wmc-folder-link { display: flex; min-height: 136px; padding-right: 20px; padding-left: 6px; flex-direction: column; align-items: center; justify-content: center; text-decoration: none; color: inherit; }
        .wmc-folder-action-wrap { position: absolute; top: 12px; right: 10px; z-index: 20; padding: 4px; border-radius: 9px; background: #fff; box-shadow: 0 6px 18px rgba(8, 15, 52, .12); opacity: 1; visibility: visible; }
        .wmc-folder-action-btn { width: 28px; height: 28px; border: 0; border-radius: 6px; background: #fff; color: #232d42; display: inline-flex; align-items: center; justify-content: center; padding: 0; line-height: 1; }
        .wmc-folder-action-btn:hover, .wmc-folder-action-btn:focus { background: #f4f7fb; color: #0d6efd; }
        .wmc-folder-icon { width: 96px; height: 70px; position: relative; border-radius: 8px 8px 9px 9px; background: linear-gradient(180deg, #72dcff 0%, #2cbcff 100%); box-shadow: inset 0 -10px 16px rgba(0, 85, 140, .14), 0 5px 10px rgba(0, 0, 0, .14); }
        .wmc-folder-icon::before { content: ""; position: absolute; top: -12px; left: 0; width: 44px; height: 22px; border-radius: 8px 8px 0 0; background: linear-gradient(180deg, #a8edff 0%, #55d0ff 100%); }
        .wmc-folder-icon::after { content: ""; position: absolute; top: 10px; left: 0; right: 0; height: 14px; border-radius: 8px 8px 0 0; background: rgba(255, 255, 255, .24); }
        .wmc-folder-icon.blue { background: linear-gradient(180deg, #7aa1ff 0%, #3155e7 100%); } .wmc-folder-icon.blue::before { background: linear-gradient(180deg, #adc3ff 0%, #5d8dff 100%); }
        .wmc-folder-icon.green { background: linear-gradient(180deg, #78e4a5 0%, #1aa96b 100%); } .wmc-folder-icon.green::before { background: linear-gradient(180deg, #a6f1c2 0%, #58d68d 100%); }
        .wmc-folder-icon.orange { background: linear-gradient(180deg, #ffe18a 0%, #ffb52e 100%); } .wmc-folder-icon.orange::before { background: linear-gradient(180deg, #ffd45b 0%, #ffb300 100%); }
        .wmc-folder-icon.purple { background: linear-gradient(180deg, #baa7ff 0%, #6f42c1 100%); } .wmc-folder-icon.purple::before { background: linear-gradient(180deg, #d4c9ff 0%, #a88cff 100%); }
        .wmc-folder-icon.pink { background: linear-gradient(180deg, #ffabc7 0%, #f14184 100%); } .wmc-folder-icon.pink::before { background: linear-gradient(180deg, #ffc4d8 0%, #ff8ab3 100%); }
        .wmc-folder-icon.gray { background: linear-gradient(180deg, #d4dce6 0%, #7b8794 100%); } .wmc-folder-icon.gray::before { background: linear-gradient(180deg, #edf1f6 0%, #c8d0dc 100%); }
        .wmc-folder-title { margin-top: 12px; color: #232d42; font-weight: 700; line-height: 1.2; font-size: 13px; width: 100%; max-width: 154px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; word-break: break-word; }
        .wmc-folder-meta { color: #8a92a6; font-size: 12px; margin-top: 5px; }
        .wmc-folder-grid .dropdown-menu, .wmc-folder-list .dropdown-menu { border: 0; border-radius: 12px; box-shadow: 0 14px 35px rgba(8, 15, 52, .14); padding: 8px; z-index: 5000; min-width: 185px; }
        .wmc-folder-grid .dropdown-item, .wmc-folder-list .dropdown-item { border-radius: 8px; font-size: 13px; padding: 8px 10px; }
        .wmc-folder-list { overflow: visible; } .wmc-folder-list .table-responsive { overflow-x: auto; overflow-y: visible; max-width: 100%; } .wmc-folder-list table { table-layout: auto; min-width: 980px; width: 100%; } .wmc-folder-list th, .wmc-folder-list td { white-space: nowrap; vertical-align: middle; } .wmc-folder-list .wmc-table-truncate { display:inline-block; max-width: 260px; overflow:hidden; text-overflow:ellipsis; vertical-align:bottom; }
        .wmc-mini-folder { width: 38px; height: 28px; border-radius: 5px 5px 6px 6px; background: linear-gradient(180deg, #72dcff 0%, #2cbcff 100%); position: relative; display: inline-block; }
        .wmc-mini-folder::before { content: ""; position: absolute; top: -6px; left: 0; width: 17px; height: 10px; border-radius: 5px 5px 0 0; background: #8ee6ff; }
        .wmc-topbar-icon { width:22px; height:22px; color:#6f7a87; flex:0 0 auto; }
        .wmc-three-dot-svg { width:18px; height:18px; display:block; }

        /* v9 searchable placeholder dropdowns + client-side realtime filters */
        .wmc-filter-select { position: relative; min-width: 168px; }
        .wmc-filter-select .form-select.wmc-searchable-select { display:none !important; }
        .wmc-filter-control { width:168px; min-height:42px; border:1px solid #e0e5f2; border-radius:10px; background:#fff; color:#6c757d; display:flex; align-items:center; justify-content:space-between; gap:10px; padding:0 14px; cursor:pointer; font-size:15px; box-shadow:none; }
        .wmc-filter-control:hover, .wmc-filter-control.active { border-color:#3f63ff; color:#232d42; }
        .wmc-filter-control .wmc-filter-text { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .wmc-filter-caret { width:16px; height:16px; flex:0 0 auto; }
        .wmc-search-dropdown { position:fixed; z-index:6000; min-width:260px; max-width:420px; background:#fff; border:1px solid #dfe6f5; border-radius:0 0 10px 10px; box-shadow:0 16px 40px rgba(8,15,52,.14); padding:10px; display:none; }
        .wmc-search-dropdown.show { display:block; }
        .wmc-search-dropdown-input { width:100%; height:42px; border:1px solid #5b70ff; border-radius:6px; padding:0 12px; outline:none; box-shadow:0 4px 10px rgba(63,99,255,.12); color:#232d42; }
        .wmc-search-dropdown-list { margin-top:8px; max-height:230px; overflow-y:auto; }
        .wmc-search-dropdown-item { padding:10px 12px; border-radius:8px; cursor:pointer; color:#344767; line-height:1.35; }
        .wmc-search-dropdown-item:hover, .wmc-search-dropdown-item.active { background:#edf3ff; color:#0d6efd; }
        .wmc-search-dropdown-empty { padding:10px 12px; color:#8a92a6; }
        .wmc-filter-empty-state { display:none; }
        @media (max-width: 991.98px) { .wmc-filter-control { width:100%; } }
    </style>

    @php
        $hasDropdownFilters = request()->filled('client_id') || request()->filled('project_type_id') || request()->filled('modified') || request('sort', 'modified_desc') !== 'modified_desc';
        $queryWithoutView = request()->except('view');
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card rounded-4">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h4 class="mb-1 fw-bold">Files</h4>
                        <p class="text-muted mb-0">Filing for project documents and attachments.</p>
                    </div>

                    @can('projects_mgmt.create')
                        <a href="{{ route('project-files.create') }}" class="btn btn-primary">
                            <i class="ri-upload-cloud-2-line me-1"></i> Upload Files
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-12">
            <form method="GET" action="{{ route('project-files.index') }}" class="card wmc-crm-filterbar" id="projectFilesFilterForm">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <div class="card-body d-flex align-items-center gap-3 gap-xl-4 flex-wrap">
                    <div class="wmc-drive-search">
                        <svg width="23" height="23" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 5H20L14 12.1V18.2L10 20V12.1L4 5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        </svg>
                        <input type="text" name="search" class="form-control wmc-live-search"
                               value="{{ request('search') }}"
                               placeholder="Search by Files">
                    </div>

                    <div class="ms-xl-auto d-flex align-items-center flex-wrap gap-3 gap-xl-4">
                        <div class="wmc-filter-select">
                            <svg class="wmc-topbar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7H20M7 12H17M10 17H14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            <select name="project_type_id" class="form-select wmc-searchable-select wmc-auto-submit" data-placeholder="Type">
                                <option value="">Type</option>
                                @foreach($projectTypes as $type)
                                    <option value="{{ $type->id }}" @selected((string) request('project_type_id') === (string) $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="wmc-filter-select">
                            <select name="client_id" class="form-select wmc-searchable-select wmc-auto-submit" data-placeholder="Clients">
                                <option value="">Clients</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" @selected((string) request('client_id') === (string) $client->id)>{{ $client->code }} - {{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="wmc-filter-select">
                            <select name="modified" class="form-select wmc-searchable-select wmc-auto-submit" data-placeholder="Modified">
                                <option value="">Modified</option>
                                <option value="today" @selected(request('modified') === 'today')>Today</option>
                                <option value="7days" @selected(request('modified') === '7days')>Last 7 Days</option>
                                <option value="30days" @selected(request('modified') === '30days')>Last 30 Days</option>
                                <option value="year" @selected(request('modified') === 'year')>This Year</option>
                            </select>
                        </div>

                        <div class="wmc-view-pill" title="Switch view">
                            <a class="{{ $viewMode === 'list' ? 'active' : '' }}" title="List View" href="{{ route('project-files.index', array_merge($queryWithoutView, ['view' => 'list'])) }}">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M9 6H20M9 12H20M9 18H20M4 6H5M4 12H5M4 18H5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </a>
                            <a class="{{ $viewMode === 'folder' ? 'active' : '' }}" title="Folder View" href="{{ route('project-files.index', array_merge($queryWithoutView, ['view' => 'folder'])) }}">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M4 7.5C4 6.67 4.67 6 5.5 6H9L11 8H18.5C19.33 8 20 8.67 20 9.5V17.5C20 18.33 19.33 19 18.5 19H5.5C4.67 19 4 18.33 4 17.5V7.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M4 10H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            </a>
                        </div>

                        @if($hasDropdownFilters)
                            <a href="{{ route('project-files.index', ['search' => request('search'), 'view' => $viewMode]) }}" class="wmc-clear-filter-link">Clear Filter</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="col-12">
            @if($viewMode === 'list')
                <div class="card rounded-4 wmc-folder-list">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Project Folder</th>
                                        <th>Client</th>
                                        <th>Type</th>
                                        <th>Files</th>
                                        <th>Modified</th>
                                        <th class="text-center pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($projects as $project)
                                        @php
                                            $folderColor = optional($project->fileFolder)->color ?: 'sky';
                                            $folderTitle = trim(($project->code ?: 'PROJECT') . ' - ' . $project->name);
                                        @endphp
                                        <tr class="wmc-filter-item"
                                            data-search="{{ Str::lower($folderTitle . ' ' . (optional($project->client)->name ?: '') . ' ' . (optional($project->type)->name ?: ($project->project_type ?: '')) . ' ' . (optional($project->projectStatus)->name ?: ($project->status ?: '')) . ' ' . $project->files->pluck('file_name')->implode(' ') . ' ' . $project->files->pluck('original_name')->implode(' ') . ' ' . $project->files->pluck('extension')->implode(' ')) }}"
                                            data-client-id="{{ $project->client_id }}"
                                            data-type-id="{{ $project->project_type_id }}"
                                            data-modified="{{ $project->files_max_updated_at ? \Carbon\Carbon::parse($project->files_max_updated_at)->format('Y-m-d') : '' }}">
                                            <td class="ps-4">
                                                <a href="{{ route('project-files.folder', $project->id) }}" class="text-decoration-none d-flex align-items-center gap-3">
                                                    <span class="wmc-mini-folder"></span>
                                                    <span>
                                                        <span class="d-block fw-bold text-dark wmc-table-truncate" title="{{ $folderTitle }}">{{ $folderTitle }}</span>
                                                        <span class="small text-muted">{{ optional($project->projectStatus)->name ?: ($project->status ?: 'No Status') }}</span>
                                                    </span>
                                                </a>
                                            </td>
                                            <td><span class="wmc-table-truncate" title="{{ optional($project->client)->name ?: 'No Client' }}">{{ optional($project->client)->name ?: 'No Client' }}</span></td>
                                            <td><span class="wmc-table-truncate" title="{{ optional($project->type)->name ?: ($project->project_type ?: 'No Type') }}">{{ optional($project->type)->name ?: ($project->project_type ?: 'No Type') }}</span></td>
                                            <td>{{ $project->files_count }}</td>
                                            <td>{{ $project->files_max_updated_at ? \Carbon\Carbon::parse($project->files_max_updated_at)->format('M d, Y h:i A') : 'No files yet' }}</td>
                                            <td class="text-center pe-4">
                                                <div class="dropdown d-inline-block">
                                                    <button class="wmc-folder-action-btn" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-display="dynamic" aria-expanded="false" title="Folder actions"><svg class="wmc-three-dot-svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg></button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="{{ route('project-files.folder', $project->id) }}"><i class="ri-folder-open-line me-2"></i>Open</a></li>
                                                        @can('projects_mgmt.create')<li><a class="dropdown-item" href="{{ route('project-files.create', ['project_id' => $project->id]) }}"><i class="ri-upload-cloud-2-line me-2"></i>Upload Files</a></li>@endcan
                                                        @can('projects_mgmt.edit')<li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#folderColorModal" data-project-id="{{ $project->id }}" data-color="{{ $folderColor }}"><i class="ri-palette-line me-2"></i>Folder Color</button></li>@endcan
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center py-5">No project folders found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="row g-4 wmc-folder-grid">
                    @forelse($projects as $project)
                        @php
                            $folderColor = optional($project->fileFolder)->color ?: 'sky';
                            $folderTitle = trim(($project->code ?: 'PROJECT') . ' - ' . $project->name);
                        @endphp

                        <div class="col-6 col-sm-4 col-md-3 col-lg-2 col-xl-2 col-xxl-2 wmc-filter-item"
                             data-search="{{ Str::lower($folderTitle . ' ' . (optional($project->client)->name ?: '') . ' ' . (optional($project->type)->name ?: ($project->project_type ?: '')) . ' ' . (optional($project->projectStatus)->name ?: ($project->status ?: '')) . ' ' . $project->files->pluck('file_name')->implode(' ') . ' ' . $project->files->pluck('original_name')->implode(' ') . ' ' . $project->files->pluck('extension')->implode(' ')) }}"
                             data-client-id="{{ $project->client_id }}"
                             data-type-id="{{ $project->project_type_id }}"
                             data-modified="{{ $project->files_max_updated_at ? \Carbon\Carbon::parse($project->files_max_updated_at)->format('Y-m-d') : '' }}">
                            <div class="wmc-folder-tile">
                                <div class="dropdown wmc-folder-action-wrap">
                                    <button class="wmc-folder-action-btn" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-display="dynamic" aria-expanded="false" title="Folder actions"><svg class="wmc-three-dot-svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg></button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('project-files.folder', $project->id) }}"><i class="ri-folder-open-line me-2"></i>Open</a></li>
                                        @can('projects_mgmt.create')<li><a class="dropdown-item" href="{{ route('project-files.create', ['project_id' => $project->id]) }}"><i class="ri-upload-cloud-2-line me-2"></i>Upload Files</a></li>@endcan
                                        @can('projects_mgmt.edit')<li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#folderColorModal" data-project-id="{{ $project->id }}" data-color="{{ $folderColor }}"><i class="ri-palette-line me-2"></i>Folder Color</button></li>@endcan
                                    </ul>
                                </div>

                                <a href="{{ route('project-files.folder', $project->id) }}" class="wmc-folder-link">
                                    <div class="wmc-folder-icon {{ $folderColor }}"></div>
                                    <div class="wmc-folder-title" title="{{ $folderTitle }}">{{ $folderTitle }}</div>
                                    <div class="wmc-folder-meta">{{ $project->files_count }} {{ Str::plural('file', $project->files_count) }}</div>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card rounded-4"><div class="card-body text-center py-5"><h5 class="mb-1">No project folders found</h5><p class="text-muted mb-0">Try clearing the filters or upload files to a project folder.</p></div></div>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>

    @can('projects_mgmt.edit')
        <div class="modal fade" id="folderColorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="#" class="modal-content wmc-swal-form" id="folderColorForm" data-swal-title="Change folder color?" data-swal-text="This will update the selected project folder color.">
                    @csrf
                    @method('PUT')
                    <div class="modal-header"><h5 class="modal-title">Change Folder Color</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <div class="row g-2">
                            @foreach(['sky' => 'Sky Blue', 'blue' => 'Blue', 'green' => 'Green', 'orange' => 'Yellow', 'purple' => 'Purple', 'pink' => 'Pink', 'gray' => 'Gray'] as $color => $label)
                                <div class="col-6"><label class="border rounded-3 px-3 py-2 w-100 d-flex align-items-center gap-2 color-choice"><input type="radio" name="color" value="{{ $color }}"> <span>{{ $label }}</span></label></div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Color</button></div>
                </form>
            </div>
        </div>
    @endcan

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            function enableDropdownPortals(scope) {
                (scope || document).querySelectorAll('.dropdown').forEach(function (dropdown) {
                    const toggle = dropdown.querySelector('[data-bs-toggle="dropdown"]');
                    const menu = dropdown.querySelector('.dropdown-menu');
                    if (!toggle || !menu || menu.dataset.portalReady === '1') return;
                    menu.dataset.portalReady = '1';
                    const originalParent = menu.parentNode;
                    let placeholder = null;

                    dropdown.addEventListener('show.bs.dropdown', function () {
                        placeholder = document.createComment('dropdown-placeholder');
                        originalParent.insertBefore(placeholder, menu);
                        document.body.appendChild(menu);
                        menu.style.position = 'fixed';
                        menu.style.zIndex = '5000';
                        menu.style.display = 'block';

                        const rect = toggle.getBoundingClientRect();
                        const width = Math.max(menu.offsetWidth || 185, 185);
                        let left = rect.right - width;
                        let top = rect.bottom + 6;

                        if (left < 8) left = 8;
                        if (left + width > window.innerWidth - 8) left = window.innerWidth - width - 8;
                        if (top + menu.offsetHeight > window.innerHeight - 8) top = Math.max(8, rect.top - menu.offsetHeight - 6);

                        menu.style.left = left + 'px';
                        menu.style.top = top + 'px';
                    });

                    dropdown.addEventListener('hidden.bs.dropdown', function () {
                        menu.style.position = '';
                        menu.style.zIndex = '';
                        menu.style.display = '';
                        menu.style.left = '';
                        menu.style.top = '';
                        if (placeholder && placeholder.parentNode) {
                            placeholder.parentNode.insertBefore(menu, placeholder);
                            placeholder.remove();
                            placeholder = null;
                        } else if (!originalParent.contains(menu)) {
                            originalParent.appendChild(menu);
                        }
                    });
                });
            }
            enableDropdownPortals(document);

            const form = document.getElementById('projectFilesFilterForm');
            const searchInput = form ? form.querySelector('input[name="search"]') : null;
            const typeSelect = form ? form.querySelector('select[name="project_type_id"]') : null;
            const clientSelect = form ? form.querySelector('select[name="client_id"]') : null;
            const modifiedSelect = form ? form.querySelector('select[name="modified"]') : null;

            function dateMatchesFilter(dateText, filterValue) {
                if (!filterValue) return true;
                if (!dateText) return false;
                const itemDate = new Date(dateText + 'T00:00:00');
                const today = new Date();
                today.setHours(0,0,0,0);
                if (filterValue === 'today') return itemDate.getTime() === today.getTime();
                if (filterValue === '7days') {
                    const d = new Date(today); d.setDate(d.getDate() - 7);
                    return itemDate >= d && itemDate <= today;
                }
                if (filterValue === '30days') {
                    const d = new Date(today); d.setDate(d.getDate() - 30);
                    return itemDate >= d && itemDate <= today;
                }
                if (filterValue === 'year') return itemDate.getFullYear() === today.getFullYear();
                return true;
            }

            function applyFileFilters() {
                const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
                const type = typeSelect ? typeSelect.value : '';
                const client = clientSelect ? clientSelect.value : '';
                const modified = modifiedSelect ? modifiedSelect.value : '';
                let visible = 0;

                document.querySelectorAll('.wmc-filter-item').forEach(function (item) {
                    const haystack = (item.dataset.search || '').toLowerCase();
                    const okSearch = !q || haystack.includes(q);
                    const okType = !type || item.dataset.typeId === type;
                    const okClient = !client || item.dataset.clientId === client;
                    const okModified = dateMatchesFilter(item.dataset.modified || '', modified);
                    const show = okSearch && okType && okClient && okModified;
                    item.style.display = show ? '' : 'none';
                    if (show) visible++;
                });
            }

            function buildSearchableSelect(select) {
                if (!select || select.dataset.customReady === '1') return;
                select.dataset.customReady = '1';
                select.style.display = 'none';

                const wrap = document.createElement('div');
                wrap.className = 'wmc-search-select-wrap';
                select.parentNode.insertBefore(wrap, select.nextSibling);

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'wmc-filter-control';
                button.innerHTML = '<span class="wmc-filter-text"></span><svg class="wmc-filter-caret" viewBox="0 0 24 24" fill="none"><path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                wrap.appendChild(button);

                const menu = document.createElement('div');
                menu.className = 'wmc-search-dropdown';
                menu.innerHTML = '<input type="text" class="wmc-search-dropdown-input" placeholder="Search..."><div class="wmc-search-dropdown-list"></div>';
                document.body.appendChild(menu);

                const text = button.querySelector('.wmc-filter-text');
                const input = menu.querySelector('input');
                const list = menu.querySelector('.wmc-search-dropdown-list');

                function cleanOptionText(option) {
                    return (option ? option.textContent : '').replace(/^id\)>/i, '').trim();
                }

                function selectedText() {
                    const option = select.options[select.selectedIndex];
                    return cleanOptionText(option) || select.dataset.placeholder || 'Select';
                }

                function syncButton() { text.textContent = selectedText(); }

                function render(term) {
                    const filter = (term || '').toLowerCase();
                    list.innerHTML = '';
                    let count = 0;
                    Array.from(select.options).forEach(function(option) {
                        const label = cleanOptionText(option);
                        if (!label) return;
                        if (filter && !label.toLowerCase().includes(filter)) return;
                        const item = document.createElement('div');
                        item.className = 'wmc-search-dropdown-item' + (option.selected ? ' active' : '');
                        item.textContent = label;
                        item.addEventListener('click', function() {
                            select.value = option.value;
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                            closeMenu();
                        });
                        list.appendChild(item);
                        count++;
                    });
                    if (!count) list.innerHTML = '<div class="wmc-search-dropdown-empty">No results found</div>';
                }

                function positionMenu() {
                    const rect = button.getBoundingClientRect();
                    const width = Math.max(rect.width, 260);
                    let left = rect.left;
                    if (left + width > window.innerWidth - 12) left = window.innerWidth - width - 12;
                    menu.style.width = width + 'px';
                    menu.style.left = Math.max(12, left) + 'px';
                    menu.style.top = (rect.bottom + 6) + 'px';
                }

                function openMenu() {
                    document.querySelectorAll('.wmc-search-dropdown.show').forEach(function(opened) { if (opened !== menu) opened.classList.remove('show'); });
                    positionMenu();
                    render('');
                    menu.classList.add('show');
                    button.classList.add('active');
                    input.value = '';
                    setTimeout(function(){ input.focus(); }, 30);
                }

                function closeMenu() { menu.classList.remove('show'); button.classList.remove('active'); }

                button.addEventListener('click', function(e) { e.preventDefault(); menu.classList.contains('show') ? closeMenu() : openMenu(); });
                input.addEventListener('input', function() { render(input.value); });
                select.addEventListener('change', function() { syncButton(); applyFileFilters(); });
                window.addEventListener('resize', function(){ if (menu.classList.contains('show')) positionMenu(); });
                window.addEventListener('scroll', function(){ if (menu.classList.contains('show')) positionMenu(); }, true);
                document.addEventListener('click', function(e) { if (!wrap.contains(e.target) && !menu.contains(e.target)) closeMenu(); });
                syncButton();
            }

            document.querySelectorAll('.wmc-searchable-select').forEach(buildSearchableSelect);
            if (searchInput) searchInput.addEventListener('input', applyFileFilters);
            applyFileFilters();

            const folderColorModal = document.getElementById('folderColorModal');
            if (folderColorModal) {
                folderColorModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const projectId = button ? button.getAttribute('data-project-id') : '';
                    const color = button ? button.getAttribute('data-color') : 'sky';
                    const colorForm = document.getElementById('folderColorForm');
                    colorForm.action = "{{ route('project-files.folder-color', '__ID__') }}".replace('__ID__', projectId);
                    colorForm.querySelectorAll('input[name="color"]').forEach(function (input) { input.checked = input.value === color; });
                });
            }

            document.querySelectorAll('.wmc-swal-form').forEach(function (swalForm) {
                swalForm.addEventListener('submit', function (event) {
                    if (swalForm.dataset.confirmed === '1') return;
                    event.preventDefault();
                    const submit = function () { swalForm.dataset.confirmed = '1'; swalForm.submit(); };
                    if (window.Swal) {
                        Swal.fire({ title: swalForm.dataset.swalTitle || 'Are you sure?', text: swalForm.dataset.swalText || 'Please confirm this action.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, continue', cancelButtonText: 'Cancel' }).then(function (result) { if (result.isConfirmed) submit(); });
                    } else if (confirm(swalForm.dataset.swalTitle || 'Are you sure?')) { submit(); }
                });
            });
        });
    </script>
</x-app-layout>
