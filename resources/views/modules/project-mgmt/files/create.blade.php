<x-app-layout>
    <style>
        .wmc-upload-row { border: 1px solid #edf2f7; border-radius: 14px; padding: 14px; background: #fbfdff; position: relative; }
        .wmc-upload-row + .wmc-upload-row { margin-top: 12px; }
        .wmc-file-drop-note { border: 1px dashed #8ddcff; background: #f1fbff; border-radius: 16px; padding: 18px; color: #31556f; }
        .form-control, .form-select { min-height: 43px; border-radius: 10px; border-color: #e0e5f2; }
        .select2-container .select2-selection--single { height: 43px; border-radius: 10px; border-color: #e0e5f2; display: flex; align-items: center; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 41px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 41px; }
        .wmc-remove-row-btn { width: 32px; height: 32px; border-radius: 999px; border: 0; background: #fff1f1; color: #d92d20; display: inline-flex; align-items: center; justify-content: center; padding: 0; font-size: 24px; line-height: 1; font-weight: 800; box-shadow: inset 0 0 0 1px rgba(217,45,32,.08); }
        .wmc-remove-row-btn:disabled { opacity: 1; cursor: not-allowed; color:#d92d20; background:#fff1f1; }
        .wmc-upload-action-col { display: flex; align-items: end; justify-content: center; padding-bottom: 5px; }

        /* v9 project folder searchable dropdown, patterned after project create client dropdown */
        .wmc-create-project-select { position:relative; }
        .wmc-create-project-select select.wmc-searchable-select { display:none !important; }
        .wmc-create-select-control { width:100%; min-height:43px; border:1px solid #e0e5f2; border-radius:10px; background:#fff; color:#8a92a6; display:flex; align-items:center; justify-content:space-between; gap:10px; padding:0 16px; cursor:pointer; font-size:16px; }
        .wmc-create-select-control.active, .wmc-create-select-control:hover { border-color:#3f63ff; color:#232d42; }
        .wmc-create-select-control .wmc-create-select-text { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .wmc-create-search-menu { position:fixed; z-index:6000; background:#fff; border:1px solid #dfe6f5; border-radius:0 0 10px 10px; box-shadow:0 16px 40px rgba(8,15,52,.14); padding:10px; display:none; }
        .wmc-create-search-menu.show { display:block; }
        .wmc-create-search-menu input { width:100%; height:42px; border:1px solid #5b70ff; border-radius:6px; padding:0 12px; outline:none; box-shadow:0 4px 10px rgba(63,99,255,.12); color:#232d42; }
        .wmc-create-option-list { margin-top:8px; max-height:230px; overflow-y:auto; }
        .wmc-create-option { padding:10px 12px; border-radius:8px; cursor:pointer; color:#344767; line-height:1.35; }
        .wmc-create-option:hover, .wmc-create-option.active { background:#edf3ff; color:#0d6efd; }
        .wmc-create-empty { padding:10px 12px; color:#8a92a6; }
    </style>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card rounded-4">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h4 class="mb-1 fw-bold">Upload Project Files</h4>
                        <p class="text-muted mb-0">Add one or more attachments to a project folder.</p>
                    </div>
                    <a href="{{ $selectedProject ? route('project-files.folder', $selectedProject->id) : route('project-files.index') }}" class="btn btn-light">Back</a>
                </div>
            </div>

            <form method="POST" action="{{ route('project-files.store') }}" enctype="multipart/form-data" class="card rounded-4">
                @csrf

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Please check the form.</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-12 wmc-create-project-select">
                            <label class="form-label">Project Folder <span class="text-danger">*</span></label>
                            @if($selectedProject)
                                <input type="hidden" name="project_id" value="{{ $selectedProject->id }}">
                                <input type="text" class="form-control bg-light" value="{{ $selectedProject->code }} - {{ $selectedProject->name }}" readonly>
                                <small class="text-muted">You opened this upload form from inside the folder, so the project is fixed.</small>
                            @else
                                <select name="project_id" class="form-select wmc-searchable-select" required data-placeholder="Search project...">
                                    <option value="">Select Project Folder</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" @selected((string) old('project_id', $selectedProjectId) === (string) $project->id)>{{ $project->code }} - {{ $project->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Folder title follows Project Code - Project Name.</small>
                            @endif
                        </div>
                    </div>

                    <div class="wmc-file-drop-note mt-4">
                        Accepted attachments: documents, images, PDFs, spreadsheets, ZIP files, and other file types. Maximum size per file is 50MB.
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-4 mb-2">
                        <h6 class="mb-0 fw-bold">Files to Upload</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addFileRowBtn"><i class="ri-add-line me-1"></i>Add Row</button>
                    </div>

                    <div id="fileRows">
                        <div class="wmc-upload-row">
                            <div class="row g-3 align-items-end">
                                <div class="col-lg-5 col-md-5">
                                    <label class="form-label">Display File Name</label>
                                    <input type="text" name="file_names[]" class="form-control" placeholder="Optional custom name">
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <label class="form-label">Attachment <span class="text-danger">*</span></label>
                                    <input type="file" name="attachments[]" class="form-control" required>
                                </div>
                                <div class="col-lg-1 col-md-1 wmc-upload-action-col">
                                    <button type="button" class="wmc-remove-row-btn remove-file-row" disabled title="Remove row" aria-label="Remove row"><span aria-hidden="true">&times;</span></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ $selectedProject ? route('project-files.folder', $selectedProject->id) : route('project-files.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Upload Files</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rows = document.getElementById('fileRows');
            const addBtn = document.getElementById('addFileRowBtn');

            function buildProjectFolderDropdown() {
                const select = document.querySelector('.wmc-create-project-select select.wmc-searchable-select');
                if (!select || select.dataset.customReady === '1') return;
                select.dataset.customReady = '1';
                select.style.display = 'none';

                const wrap = document.createElement('div');
                wrap.className = 'wmc-create-search-wrap';
                select.parentNode.insertBefore(wrap, select.nextSibling);

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'wmc-create-select-control';
                button.innerHTML = '<span class="wmc-create-select-text"></span><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                wrap.appendChild(button);

                const menu = document.createElement('div');
                menu.className = 'wmc-create-search-menu';
                menu.innerHTML = '<input type="text" placeholder="Search project..."><div class="wmc-create-option-list"></div>';
                document.body.appendChild(menu);

                const text = button.querySelector('.wmc-create-select-text');
                const input = menu.querySelector('input');
                const list = menu.querySelector('.wmc-create-option-list');

                function cleanOptionText(option) {
                    return (option ? option.textContent : '').replace(/^id\)>/i, '').trim();
                }

                function syncButton() {
                    const option = select.options[select.selectedIndex];
                    const label = cleanOptionText(option);
                    text.textContent = option && option.value ? label : 'Select Project Folder';
                }

                function render(term) {
                    const filter = (term || '').toLowerCase();
                    list.innerHTML = '';
                    let count = 0;
                    Array.from(select.options).forEach(function(option) {
                        const label = cleanOptionText(option);
                        if (!option.value || !label) return;
                        if (filter && !label.toLowerCase().includes(filter)) return;
                        const item = document.createElement('div');
                        item.className = 'wmc-create-option' + (option.selected ? ' active' : '');
                        item.textContent = label;
                        item.addEventListener('click', function() {
                            select.value = option.value;
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                            syncButton();
                            closeMenu();
                        });
                        list.appendChild(item);
                        count++;
                    });
                    if (!count) list.innerHTML = '<div class="wmc-create-empty">No project found</div>';
                }

                function positionMenu() {
                    const rect = button.getBoundingClientRect();
                    let left = rect.left;
                    let width = rect.width;
                    if (left + width > window.innerWidth - 12) left = window.innerWidth - width - 12;
                    menu.style.left = Math.max(12, left) + 'px';
                    menu.style.top = (rect.bottom + 6) + 'px';
                    menu.style.width = width + 'px';
                }

                function openMenu() {
                    positionMenu();
                    render('');
                    menu.classList.add('show');
                    button.classList.add('active');
                    input.value = '';
                    setTimeout(function(){ input.focus(); }, 30);
                }

                function closeMenu() {
                    menu.classList.remove('show');
                    button.classList.remove('active');
                }

                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    menu.classList.contains('show') ? closeMenu() : openMenu();
                });
                input.addEventListener('input', function(){ render(input.value); });
                window.addEventListener('resize', function(){ if (menu.classList.contains('show')) positionMenu(); });
                window.addEventListener('scroll', function(){ if (menu.classList.contains('show')) positionMenu(); }, true);
                document.addEventListener('click', function(event) {
                    if (!wrap.contains(event.target) && !menu.contains(event.target)) closeMenu();
                });
                syncButton();
            }

            buildProjectFolderDropdown();

            function refreshRemoveButtons() {
                const buttons = rows.querySelectorAll('.remove-file-row');
                buttons.forEach(function (btn) { btn.disabled = buttons.length === 1; });
            }

            addBtn.addEventListener('click', function () {
                const firstRow = rows.querySelector('.wmc-upload-row');
                const clone = firstRow.cloneNode(true);
                clone.querySelectorAll('input').forEach(function (input) { input.value = ''; });
                rows.appendChild(clone);
                refreshRemoveButtons();
            });

            rows.addEventListener('click', function (event) {
                const btn = event.target.closest('.remove-file-row');
                if (! btn || btn.disabled) return;
                btn.closest('.wmc-upload-row').remove();
                refreshRemoveButtons();
            });

            refreshRemoveButtons();
        });
    </script>
</x-app-layout>
