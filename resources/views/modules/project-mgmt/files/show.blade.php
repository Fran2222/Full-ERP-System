<x-app-layout>
    <style>

        .wmc-crm-filterbar { background:#fff; border:0; border-radius: 8px; box-shadow: 0 10px 30px rgba(8,15,52,.05); }
        .wmc-crm-filterbar .card-body { min-height: 72px; padding: 14px 22px; }
        .wmc-drive-search { min-width: 280px; flex: 1 1 430px; display:flex; align-items:center; gap:14px; }
        .wmc-drive-search svg { color:#6f7a87; flex:0 0 auto; }
        .wmc-drive-search input { border:0 !important; box-shadow:none !important; background:transparent; padding-left:0; min-height:42px; font-size:15px; }
        .wmc-drive-search input:focus { box-shadow:none; }
        .wmc-filter-select { display:flex; align-items:center; gap:7px; white-space:nowrap; color:#6c757d; font-size:15px; }
        .wmc-filter-select .wmc-filter-label { color:#6c757d; font-weight:500; }
        .wmc-filter-select select { width: 150px; }
        .wmc-filter-select .select2-container { min-width: 150px; }
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
        .wmc-icon-soft-btn { width:38px; height:38px; border-radius:999px; border:0; background:transparent; color:#6c757d; display:inline-flex; align-items:center; justify-content:center; }
        .wmc-icon-soft-btn:hover { background:#f4f7fb; color:#0d6efd; }
        @media (max-width: 991.98px) { .wmc-drive-search { min-width:100%; } .wmc-filter-select { width:100%; justify-content:space-between; } .wmc-filter-select .select2-container { min-width: 180px; } }
        .wmc-drive-header { border-radius: 18px; overflow: hidden; background: linear-gradient(135deg, #f1fbff, #ffffff); border: 1px solid #e7f7ff; }
        .wmc-folder-icon-lg { width: 84px; height: 62px; position: relative; border-radius: 14px 14px 16px 16px; background: linear-gradient(135deg, #66d9ff, #2fb7ff); box-shadow: inset 0 -8px 16px rgba(0, 93, 135, .12); }
        .wmc-folder-icon-lg::before { content: ""; position: absolute; top: -11px; left: 10px; width: 38px; height: 17px; border-radius: 9px 9px 0 0; background: linear-gradient(135deg, #9be9ff, #5ecfff); }
        .wmc-folder-icon-lg.blue { background: linear-gradient(135deg, #5d8dff, #3155e7); } .wmc-folder-icon-lg.green { background: linear-gradient(135deg, #58d68d, #1aa96b); } .wmc-folder-icon-lg.orange { background: linear-gradient(135deg, #ffbd59, #ff8a00); } .wmc-folder-icon-lg.purple { background: linear-gradient(135deg, #a88cff, #6f42c1); } .wmc-folder-icon-lg.pink { background: linear-gradient(135deg, #ff8ab3, #f14184); } .wmc-folder-icon-lg.gray { background: linear-gradient(135deg, #c8d0dc, #7b8794); }
        .wmc-file-table-wrap { overflow: visible; }
        .wmc-file-table-wrap .table-responsive { overflow-x:auto; overflow-y: visible; max-width:100%; }
        .wmc-file-table { table-layout: auto; min-width: 900px; width: 100%; }
        .wmc-file-table th, .wmc-file-table td { white-space: nowrap; }
        .wmc-file-table .wmc-table-truncate { display:inline-block; max-width: clamp(180px, 28vw, 360px); overflow:hidden; text-overflow:ellipsis; vertical-align:bottom; }
        .min-w-0 { min-width: 0 !important; }
        .wmc-file-grid-row { --bs-gutter-x: 1.25rem; --bs-gutter-y: 1.25rem; }
        .wmc-file-grid-col { max-width: 360px; }
        .wmc-file-table td, .wmc-file-table th { vertical-align: middle; border-color: #f2f4f8; }
        .wmc-file-icon { width: 38px; height: 38px; border-radius: 11px; background: #eef9ff; color: #1286c8; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; text-transform: uppercase; font-size: 11px; flex: 0 0 auto; }
        .wmc-file-grid-card { border: 1px solid #edf2f7; border-radius: 16px; height: 100%; box-shadow: 0 10px 26px rgba(17, 38, 146, .04); overflow: visible; }
        .wmc-file-grid-card .card-body { min-height: 152px; padding: 22px 22px; }
        .wmc-file-main { min-width: 0; flex: 1 1 auto; overflow: hidden; }
        .wmc-file-name-line { display: -webkit-box !important; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; white-space: normal; line-height: 1.25; word-break: break-word; }
        .wmc-file-meta-line { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .wmc-file-card-head { min-width: 0; align-items: flex-start; }
        .wmc-file-card-head .dropdown { flex: 0 0 auto; }
        .wmc-action-dot { width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; border: 0; background: transparent; color: #232d42; }
        .wmc-action-dot:hover { background: #f4f7fb; }
        .dropdown-menu { z-index: 5000; border: 0; border-radius: 12px; box-shadow: 0 14px 35px rgba(8, 15, 52, .14); padding: 8px; min-width: 185px; }
        .dropdown-item { border-radius: 8px; font-size: 14px; padding: 9px 12px; }
        .form-control, .form-select { min-height: 42px; border-radius: 10px; border-color: #e0e5f2; }
        .select2-container .select2-selection--single { height: 42px; border-radius: 10px; border-color: #e0e5f2; display: flex; align-items: center; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
        .wmc-view-toggle { width: 42px; height: 42px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e0e5f2; background: #fff; color: #6c757d; text-decoration: none; }
        .wmc-view-toggle.active, .wmc-view-toggle:hover { background: #edf8ff; color: #0d6efd; border-color: #91d9ff; }
        .wmc-color-dot { width: 22px; height: 22px; border-radius: 999px; display: inline-block; border: 2px solid #fff; box-shadow: 0 0 0 1px #dbe3ef; }
        .wmc-color-dot.sky { background: #2fb7ff; } .wmc-color-dot.blue { background: #3155e7; } .wmc-color-dot.green { background: #1aa96b; } .wmc-color-dot.orange { background: #ff8a00; } .wmc-color-dot.purple { background: #6f42c1; } .wmc-color-dot.pink { background: #f14184; } .wmc-color-dot.gray { background: #7b8794; }
        .wmc-topbar-icon { width:22px; height:22px; color:#6f7a87; flex:0 0 auto; }
        .wmc-preview-frame { width:100%; height:72vh; border:0; border-radius:14px; background:#f8fafc; }
        .wmc-preview-image { max-width:100%; max-height:72vh; object-fit:contain; margin:0 auto; display:block; border-radius:14px; background:#f8fafc; }
        .wmc-preview-unavailable { min-height:260px; border:1px dashed #d9e2ef; border-radius:14px; display:flex; align-items:center; justify-content:center; text-align:center; padding:24px; background:#fbfdff; }
        .wmc-activity-timeline { position: relative; padding-left: 26px; }
        .wmc-activity-timeline::before { content: ""; position: absolute; top: 8px; bottom: 8px; left: 8px; width: 2px; background: #e7eef7; }
        .wmc-activity-item { position: relative; padding-bottom: 18px; }
        .wmc-activity-item::before { content: ""; position: absolute; left: -24px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: #2fb7ff; box-shadow: 0 0 0 4px #eef9ff; }

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
        @media (max-width: 991.98px) { .wmc-filter-control { width:100%; } }
    </style>

    @php
        $folderColor = optional($project->fileFolder)->color ?: 'sky';
        $folderTitle = trim(($project->code ?: 'PROJECT') . ' - ' . $project->name);
        $hasDropdownFilters = request()->filled('modified') || request('sort', 'modified_desc') !== 'modified_desc';
        $queryWithoutView = request()->except('view');
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card wmc-drive-header mb-4">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-4">
                    <div class="d-flex align-items-center gap-4">
                        <div class="wmc-folder-icon-lg {{ $folderColor }}"></div>
                        <div>
                            <h4 class="fw-bold mb-1">{{ $folderTitle }}</h4>
                            <p class="text-muted mb-0">{{ optional($project->client)->name ?: 'No Client' }} • {{ optional($project->type)->name ?: ($project->project_type ?: 'No Type') }} • {{ optional($project->projectStatus)->name ?: ($project->status ?: 'No Status') }}</p>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('project-files.index') }}" class="btn btn-light">All Folders</a>
                        @can('projects_mgmt.create')
                            <a href="{{ route('project-files.create', ['project_id' => $project->id]) }}" class="btn btn-primary"><i class="ri-upload-cloud-2-line me-1"></i> Upload Files</a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <form method="GET" action="{{ route('project-files.folder', $project->id) }}" class="card wmc-crm-filterbar" id="folderFilesFilterForm">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                <div class="card-body d-flex align-items-center gap-3 gap-xl-4 flex-wrap">
                    <div class="wmc-drive-search">
                        <svg width="23" height="23" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 5H20L14 12.1V18.2L10 20V12.1L4 5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        </svg>
                        <input type="text" name="search" class="form-control wmc-live-search"
                               value="{{ request('search') }}"
                               placeholder="Search files inside {{ $project->code }}">
                    </div>

                    <div class="ms-xl-auto d-flex align-items-center flex-wrap gap-3 gap-xl-4">
                        <div class="wmc-filter-select">
                            <svg class="wmc-topbar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7H20M7 12H17M10 17H14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            <select name="sort" class="form-select wmc-searchable-select wmc-auto-submit" data-placeholder="Sort">
                                <option value="modified_desc" @selected($sort === 'modified_desc')>Date Modified</option>
                                <option value="name" @selected($sort === 'name')>File Name</option>
                                <option value="size_desc" @selected($sort === 'size_desc')>Largest</option>
                                <option value="size_asc" @selected($sort === 'size_asc')>Smallest</option>
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


                        <button type="button" class="wmc-icon-soft-btn" data-bs-toggle="modal" data-bs-target="#activityModal" title="Files Activity" aria-label="Files Activity">
                            <svg class="wmc-topbar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 8V12L14.5 14.5M5.6 7.2C7 5.25 9.32 4 12 4C16.42 4 20 7.58 20 12C20 16.42 16.42 20 12 20C8.05 20 4.77 17.14 4.12 13.38M4 7V3M4 7H8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        <div class="wmc-view-pill" title="Switch view">
                            <a class="{{ $viewMode === 'list' ? 'active' : '' }}" title="List View" href="{{ route('project-files.folder', array_merge(['project' => $project->id], $queryWithoutView, ['view' => 'list'])) }}">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M9 6H20M9 12H20M9 18H20M4 6H5M4 12H5M4 18H5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </a>
                            <a class="{{ $viewMode === 'grid' ? 'active' : '' }}" title="Grid View" href="{{ route('project-files.folder', array_merge(['project' => $project->id], $queryWithoutView, ['view' => 'grid'])) }}">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M5 5H10V10H5V5ZM14 5H19V10H14V5ZM5 14H10V19H5V14ZM14 14H19V19H14V14Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                            </a>
                        </div>

                        @if($hasDropdownFilters)
                            <a href="{{ route('project-files.folder', ['project' => $project->id, 'search' => request('search'), 'view' => $viewMode]) }}" class="wmc-clear-filter-link">Clear Filter</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="col-12">
            @if($viewMode === 'grid')
                <div class="row wmc-file-grid-row">
                    @forelse($files as $file)
                        <div class="col-xl-3 col-lg-4 col-md-6 wmc-file-grid-col wmc-file-filter-item"
                             data-search="{{ Str::lower($file->file_name . ' ' . $file->original_name . ' ' . $file->extension . ' ' . $file->owner_name) }}"
                             data-name="{{ Str::lower($file->file_name) }}"
                             data-size="{{ $file->size }}"
                             data-modified="{{ optional($file->updated_at)->format('Y-m-d') }}"
                             data-modified-ts="{{ optional($file->updated_at)->timestamp ?: 0 }}">
                            <div class="card wmc-file-grid-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between gap-3 wmc-file-card-head">
                                        <a href="{{ route('project-files.download', $file->id) }}" class="text-decoration-none d-flex align-items-start gap-3 wmc-file-main">
                                            <span class="wmc-file-icon">{{ $file->extension ?: 'FILE' }}</span>
                                            <span class="wmc-file-main">
                                                <span class="d-block fw-bold text-dark wmc-file-name-line" title="{{ $file->file_name }}">{{ $file->file_name }}</span>
                                                <span class="d-block small text-muted wmc-file-meta-line">{{ $file->formatted_size }}</span>
                                            </span>
                                        </a>
                                        @include('modules.project-mgmt.files.partials.file-actions', ['file' => $file, 'projects' => $projects])
                                    </div>
                                    <div class="small text-muted mt-3"><div>Owner: {{ $file->owner_name }}</div><div>Modified: {{ optional($file->updated_at)->format('M d, Y h:i A') }}</div></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="card rounded-4"><div class="card-body text-center py-5"><h5 class="mb-1">No files found</h5><p class="text-muted mb-0">Upload files or adjust your search.</p></div></div></div>
                    @endforelse
                </div>
            @else
                <div class="card rounded-4 wmc-file-table-wrap">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 wmc-file-table">
                                <thead><tr><th class="ps-4">File Name</th><th>Owner</th><th>Date Modified</th><th>File Size</th><th class="text-center pe-4">Action</th></tr></thead>
                                <tbody>
                                    @forelse($files as $file)
                                        <tr class="wmc-file-filter-item"
                                            data-search="{{ Str::lower($file->file_name . ' ' . $file->original_name . ' ' . $file->extension . ' ' . $file->owner_name) }}"
                                            data-name="{{ Str::lower($file->file_name) }}"
                                            data-size="{{ $file->size }}"
                                            data-modified="{{ optional($file->updated_at)->format('Y-m-d') }}"
                                            data-modified-ts="{{ optional($file->updated_at)->timestamp ?: 0 }}">
                                            <td class="ps-4">
                                                <a href="{{ route('project-files.download', $file->id) }}" class="text-decoration-none d-flex align-items-center gap-3">
                                                    <span class="wmc-file-icon">{{ $file->extension ?: 'FILE' }}</span>
                                                    <span class="min-w-0"><span class="d-block fw-bold text-dark wmc-table-truncate" title="{{ $file->file_name }}">{{ $file->file_name }}</span><span class="d-block small text-muted wmc-table-truncate" title="{{ $file->original_name }}">{{ $file->original_name }}</span></span>
                                                </a>
                                            </td>
                                            <td>{{ $file->owner_name }}</td>
                                            <td>{{ optional($file->updated_at)->format('M d, Y h:i A') }}</td>
                                            <td>{{ $file->formatted_size }}</td>
                                            <td class="text-center pe-4">@include('modules.project-mgmt.files.partials.file-actions', ['file' => $file, 'projects' => $projects])</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center py-5"><h5 class="mb-1">No files found</h5><p class="text-muted mb-0">Upload files or adjust your search.</p></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-3">{{ $files->links() }}</div>
        </div>
    </div>

    <div class="modal fade" id="activityModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="ri-history-line me-2"></i>Files Activity Timeline</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    @forelse($activities as $activity)
                        <div class="wmc-activity-timeline">
                            <div class="wmc-activity-item">
                                <div class="fw-semibold">{{ $activity->description }}</div>
                                <div class="small text-muted">{{ $activity->user_name }} • {{ optional($activity->created_at)->format('M d, Y h:i A') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4"><h6 class="mb-1">No activity yet</h6><p class="text-muted mb-0">Uploads, rename, move, delete, and folder color changes will appear here.</p></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="previewFileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i class="ri-eye-line"></i>
                        <span id="previewFileTitle">Preview Attachment</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img src="" alt="Attachment preview" class="wmc-preview-image d-none" id="previewFileImage">
                    <iframe src="about:blank" class="wmc-preview-frame d-none" id="previewFileFrame"></iframe>
                    <div class="wmc-preview-unavailable d-none" id="previewFileFallback">
                        <div>
                            <h6 class="mb-2">Preview may not be supported for this attachment type.</h6>
                            <p class="text-muted mb-3">You can still open the file in a new tab or download it.</p>
                            <a href="#" target="_blank" class="btn btn-outline-primary me-2" id="previewFileOpenLink">Open Preview</a>
                            <a href="#" class="btn btn-primary" id="previewFileDownloadLink">Download</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" target="_blank" class="btn btn-outline-primary" id="previewFileOpenFooter">Open in New Tab</a>
                    <a href="#" class="btn btn-primary" id="previewFileDownloadFooter"><i class="ri-download-2-line me-1"></i>Download</a>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="renameFileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" class="modal-content wmc-swal-form" id="renameFileForm" data-swal-title="Rename this file?" data-swal-text="The file display name will be updated.">
                @csrf @method('PUT')
                <div class="modal-header"><h5 class="modal-title">Rename File</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body"><label class="form-label">File Name</label><input type="text" name="file_name" id="renameFileName" class="form-control" required maxlength="255"></div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Rename</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="moveFileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" class="modal-content wmc-swal-form" id="moveFileForm" data-swal-title="Move this file?" data-swal-text="This file will be organized into the selected project folder.">
                @csrf @method('PUT')
                <div class="modal-header"><h5 class="modal-title">Move File</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <label class="form-label">Move to Project Folder</label>
                    <select name="project_id" class="form-select wmc-searchable-select" required data-placeholder="Search target folder...">
                        <option value="">Select target folder</option>
                        @foreach($projects as $targetProject)<option value="{{ $targetProject->id }}">{{ $targetProject->code }} - {{ $targetProject->name }}</option>@endforeach
                    </select>
                    @if($projects->isEmpty())<small class="text-muted">No other project folder is available.</small>@endif
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary" {{ $projects->isEmpty() ? "disabled" : "" }}>Move File</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="folderColorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('project-files.folder-color', $project->id) }}" class="modal-content wmc-swal-form" data-swal-title="Change folder color?" data-swal-text="This will update the selected folder color.">
                @csrf @method('PUT')
                <div class="modal-header"><h5 class="modal-title">Folder Color</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    @foreach(['sky', 'blue', 'green', 'orange', 'purple', 'pink', 'gray'] as $color)
                        <label class="d-flex align-items-center gap-3 mb-2"><input type="radio" name="color" value="{{ $color }}" @checked($folderColor === $color)><span class="wmc-color-dot {{ $color }}"></span><span class="text-capitalize">{{ $color }}</span></label>
                    @endforeach
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Color</button></div>
            </form>
        </div>
    </div>

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

            const form = document.getElementById('folderFilesFilterForm');
            const searchInput = form ? form.querySelector('input[name="search"]') : null;
            const sortSelect = form ? form.querySelector('select[name="sort"]') : null;
            const modifiedSelect = form ? form.querySelector('select[name="modified"]') : null;

            function dateMatchesFilter(dateText, filterValue) {
                if (!filterValue) return true;
                if (!dateText) return false;
                const itemDate = new Date(dateText + 'T00:00:00');
                const today = new Date();
                today.setHours(0,0,0,0);
                if (filterValue === 'today') return itemDate.getTime() === today.getTime();
                if (filterValue === '7days') { const d = new Date(today); d.setDate(d.getDate() - 7); return itemDate >= d && itemDate <= today; }
                if (filterValue === '30days') { const d = new Date(today); d.setDate(d.getDate() - 30); return itemDate >= d && itemDate <= today; }
                if (filterValue === 'year') return itemDate.getFullYear() === today.getFullYear();
                return true;
            }
            function sortCurrentFileItems() {
                const sort = sortSelect ? sortSelect.value : 'modified_desc';
                const items = Array.from(document.querySelectorAll('.wmc-file-filter-item'));
                if (!items.length) return;
                const parent = items[0].parentNode;
                items.sort(function(a,b) {
                    if (sort === 'name') return (a.dataset.name || '').localeCompare(b.dataset.name || '');
                    if (sort === 'size_desc') return (parseInt(b.dataset.size || '0', 10) - parseInt(a.dataset.size || '0', 10));
                    if (sort === 'size_asc') return (parseInt(a.dataset.size || '0', 10) - parseInt(b.dataset.size || '0', 10));
                    return (parseInt(b.dataset.modifiedTs || '0', 10) - parseInt(a.dataset.modifiedTs || '0', 10));
                });
                items.forEach(function(item){ parent.appendChild(item); });
            }
            function applyFolderFileFilters() {
                const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
                const modified = modifiedSelect ? modifiedSelect.value : '';
                sortCurrentFileItems();
                document.querySelectorAll('.wmc-file-filter-item').forEach(function(item) {
                    const haystack = (item.dataset.search || '').toLowerCase();
                    const show = (!q || haystack.includes(q)) && dateMatchesFilter(item.dataset.modified || '', modified);
                    item.style.display = show ? '' : 'none';
                });
            }
            function buildSearchableSelect(select) {
                if (!select || select.dataset.customReady === '1') return;
                select.dataset.customReady = '1'; select.style.display = 'none';
                const wrap = document.createElement('div'); wrap.className = 'wmc-search-select-wrap'; select.parentNode.insertBefore(wrap, select.nextSibling);
                const button = document.createElement('button'); button.type = 'button'; button.className = 'wmc-filter-control';
                button.innerHTML = '<span class="wmc-filter-text"></span><svg class="wmc-filter-caret" viewBox="0 0 24 24" fill="none"><path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                wrap.appendChild(button);
                const menu = document.createElement('div'); menu.className = 'wmc-search-dropdown'; menu.innerHTML = '<input type="text" class="wmc-search-dropdown-input" placeholder="Search..."><div class="wmc-search-dropdown-list"></div>'; document.body.appendChild(menu);
                const text = button.querySelector('.wmc-filter-text'), input = menu.querySelector('input'), list = menu.querySelector('.wmc-search-dropdown-list');
                function cleanOptionText(option) { return (option ? option.textContent : '').replace(/^id\)>/i, '').trim(); }
                function syncButton() { const option = select.options[select.selectedIndex]; text.textContent = cleanOptionText(option) || select.dataset.placeholder || 'Select'; }
                function render(term) { const filter=(term||'').toLowerCase(); list.innerHTML=''; let count=0; Array.from(select.options).forEach(function(option){ const label=cleanOptionText(option); if(!label || (filter && !label.toLowerCase().includes(filter))) return; const item=document.createElement('div'); item.className='wmc-search-dropdown-item'+(option.selected?' active':''); item.textContent=label; item.addEventListener('click', function(){ select.value=option.value; select.dispatchEvent(new Event('change',{bubbles:true})); closeMenu(); }); list.appendChild(item); count++; }); if(!count) list.innerHTML='<div class="wmc-search-dropdown-empty">No results found</div>'; }
                function positionMenu() { const rect=button.getBoundingClientRect(); const width=Math.max(rect.width,260); let left=rect.left; if(left+width>window.innerWidth-12) left=window.innerWidth-width-12; menu.style.width=width+'px'; menu.style.left=Math.max(12,left)+'px'; menu.style.top=(rect.bottom+6)+'px'; }
                function openMenu() { document.querySelectorAll('.wmc-search-dropdown.show').forEach(function(opened){ if(opened!==menu) opened.classList.remove('show'); }); positionMenu(); render(''); menu.classList.add('show'); button.classList.add('active'); input.value=''; setTimeout(function(){ input.focus(); },30); }
                function closeMenu() { menu.classList.remove('show'); button.classList.remove('active'); }
                button.addEventListener('click', function(e){ e.preventDefault(); menu.classList.contains('show') ? closeMenu() : openMenu(); }); input.addEventListener('input', function(){ render(input.value); }); select.addEventListener('change', function(){ syncButton(); applyFolderFileFilters(); });
                window.addEventListener('resize', function(){ if(menu.classList.contains('show')) positionMenu(); }); window.addEventListener('scroll', function(){ if(menu.classList.contains('show')) positionMenu(); }, true); document.addEventListener('click', function(e){ if(!wrap.contains(e.target) && !menu.contains(e.target)) closeMenu(); }); syncButton();
            }
            document.querySelectorAll('#folderFilesFilterForm .wmc-searchable-select').forEach(buildSearchableSelect);
            if (searchInput) searchInput.addEventListener('input', applyFolderFileFilters);
            applyFolderFileFilters();

            const previewModal = document.getElementById('previewFileModal');
            if (previewModal) {
                const title = document.getElementById('previewFileTitle');
                const frame = document.getElementById('previewFileFrame');
                const image = document.getElementById('previewFileImage');
                const fallback = document.getElementById('previewFileFallback');
                const openLink = document.getElementById('previewFileOpenLink');
                const downloadLink = document.getElementById('previewFileDownloadLink');
                const openFooter = document.getElementById('previewFileOpenFooter');
                const downloadFooter = document.getElementById('previewFileDownloadFooter');

                previewModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const previewUrl = button.getAttribute('data-preview-url');
                    const downloadUrl = button.getAttribute('data-download-url');
                    const fileName = button.getAttribute('data-file-name') || 'Preview Attachment';
                    const mime = (button.getAttribute('data-file-mime') || '').toLowerCase();

                    title.textContent = fileName;
                    openLink.href = previewUrl;
                    openFooter.href = previewUrl;
                    downloadLink.href = downloadUrl;
                    downloadFooter.href = downloadUrl;

                    frame.classList.add('d-none');
                    image.classList.add('d-none');
                    fallback.classList.add('d-none');
                    frame.src = 'about:blank';
                    image.removeAttribute('src');

                    if (mime.startsWith('image/')) {
                        image.src = previewUrl;
                        image.classList.remove('d-none');
                    } else if (mime === 'application/pdf' || mime.startsWith('text/') || mime.includes('html')) {
                        frame.src = previewUrl;
                        frame.classList.remove('d-none');
                    } else {
                        frame.src = previewUrl;
                        frame.classList.remove('d-none');
                    }
                });

                previewModal.addEventListener('hidden.bs.modal', function () {
                    frame.src = 'about:blank';
                    image.removeAttribute('src');
                });
            }

            const renameModal = document.getElementById('renameFileModal');
            const moveModal = document.getElementById('moveFileModal');
            if (renameModal) {
                renameModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const fileId = button.getAttribute('data-file-id');
                    const fileName = button.getAttribute('data-file-name');
                    document.getElementById('renameFileName').value = fileName;
                    document.getElementById('renameFileForm').action = "{{ route('project-files.rename', '__ID__') }}".replace('__ID__', fileId);
                });
            }
            if (moveModal) {
                moveModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const fileId = button.getAttribute('data-file-id');
                    document.getElementById('moveFileForm').action = "{{ route('project-files.move', '__ID__') }}".replace('__ID__', fileId);
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
