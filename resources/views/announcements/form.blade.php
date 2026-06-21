<x-app-layout :assets="$assets ?? []">
    <div class="row">
        <div class="col-sm-12">
            <div class="card rounded-4 border-0 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap bg-white border-0 pb-0">
                    <div class="header-title">
                        <h4 class="card-title mb-1">
                            {{ isset($announcement) ? 'Edit Announcement' : 'New Announcement' }}
                        </h4>
                        <p class="mb-0 text-secondary">Create a memorandum-style announcement for the HR dashboard.</p>
                    </div>

                    <a href="{{ route('announcements.index') }}" class="btn btn-light mt-2 mt-md-0">
                        Back
                    </a>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ isset($announcement) ? route('announcements.update', $announcement->id) : route('announcements.store') }}"
                          method="POST">
                        @csrf
                        @if(isset($announcement))
                            @method('PUT')
                        @endif

                        <div class="row g-3">
                            @php
                                $oldRecipientIds = old('memo_to_user_ids');

                                if (is_array($oldRecipientIds)) {
                                    $selectedRecipientIds = collect($oldRecipientIds)->filter()->map(fn ($id) => (string) $id)->values();
                                    $recipientMode = old('memo_to_mode', $selectedRecipientIds->isNotEmpty() ? 'selected' : 'all');
                                } elseif (isset($announcement)) {
                                    $announcement->loadMissing('recipients');
                                    $selectedRecipientIds = $announcement->recipients->pluck('id')->map(fn ($id) => (string) $id)->values();

                                    if ($selectedRecipientIds->isEmpty() && $announcement->memo_to_user_id) {
                                        $selectedRecipientIds = collect([(string) $announcement->memo_to_user_id]);
                                    }

                                    $recipientMode = $selectedRecipientIds->isNotEmpty() ? 'selected' : 'all';
                                } else {
                                    $selectedRecipientIds = collect();
                                    $recipientMode = 'all';
                                }

                                $selectedMemoTo = old('memo_to', $announcement->memo_to ?? 'All Employees');
                            @endphp

                            <div class="col-md-4">
                                <label class="form-label">To</label>

                                <div class="announcement-recipient-picker" id="announcement_recipient_picker">
                                    <button type="button"
                                            class="announcement-recipient-button"
                                            id="announcement_recipient_toggle"
                                            aria-expanded="false">
                                        <span id="announcement_recipient_button_text">All Employees</span>
                                        <span class="announcement-recipient-caret">⌄</span>
                                    </button>

                                    <div class="announcement-recipient-menu" id="announcement_recipient_menu">
                                        <div class="announcement-recipient-search-wrap">
                                            <input type="text"
                                                   class="form-control form-control-sm"
                                                   id="announcement_recipient_search"
                                                   placeholder="Search employee...">
                                        </div>

                                        <label class="announcement-recipient-option announcement-recipient-all-option">
                                            <input type="checkbox"
                                                   id="announcement_recipient_all"
                                                   value="all"
                                                   {{ $recipientMode === 'all' ? 'checked' : '' }}>
                                            <span>All Employees</span>
                                        </label>

                                        <div class="announcement-recipient-options" id="announcement_recipient_options">
                                            @foreach(($recipientEmployees ?? collect()) as $recipient)
                                                @php
                                                    $middleInitial = trim((string) ($recipient->middle_name ?? ''));
                                                    $middleInitial = $middleInitial !== '' ? strtoupper(substr($middleInitial, 0, 1)) . '.' : '';

                                                    $recipientName = collect([
                                                        trim((string) ($recipient->last_name ?? '')),
                                                        trim(collect([
                                                            trim((string) ($recipient->first_name ?? '')),
                                                            $middleInitial,
                                                        ])->filter()->implode(' ')),
                                                    ])->filter()->implode(', ');

                                                    $recipientName = $recipientName ?: trim($recipient->full_name ?: $recipient->email);
                                                    $isSelectedRecipient = $selectedRecipientIds->contains((string) $recipient->id);
                                                @endphp
                                                <label class="announcement-recipient-option" data-search="{{ strtolower($recipientName) }}">
                                                    <input type="checkbox"
                                                           name="memo_to_user_ids[]"
                                                           value="{{ $recipient->id }}"
                                                           class="announcement-recipient-checkbox"
                                                           data-name="{{ $recipientName }}"
                                                           {{ $isSelectedRecipient ? 'checked' : '' }}>
                                                    <span>{{ $recipientName }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden"
                                       name="memo_to_mode"
                                       id="announcement_memo_to_mode"
                                       value="{{ $recipientMode === 'selected' ? 'selected' : 'all' }}">
                                <input type="hidden"
                                       name="memo_to"
                                       id="announcement_memo_to"
                                       value="{{ $selectedMemoTo ?: 'All Employees' }}">
                                <small class="text-secondary">Choose All Employees or select multiple employees.</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">From</label>
                                <input type="text"
                                       name="memo_from"
                                       class="form-control"
                                       placeholder="Management"
                                       value="{{ old('memo_from', $announcement->memo_from ?? 'Management') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Date</label>
                                <input type="date"
                                       name="memo_date"
                                       class="form-control"
                                       value="{{ old('memo_date', isset($announcement) && $announcement->memo_date ? $announcement->memo_date->format('Y-m-d') : now()->toDateString()) }}">
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">Subject</label>
                                <input type="text"
                                       name="title"
                                       class="form-control"
                                       placeholder="Example: Declaration of Holidays – Holy Week Schedule"
                                       value="{{ old('title', $announcement->title ?? '') }}"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Show for how many days?</label>
                                <input type="number"
                                       name="display_days"
                                       class="form-control"
                                       min="1"
                                       max="365"
                                       placeholder="Example: 5"
                                       value="{{ old('display_days', $announcement->display_days ?? '') }}">
                                <small class="text-secondary">
                                    Leave blank if this announcement should stay visible until manually unpublished.
                                </small>
                            </div>

                            @if(isset($announcement) && $announcement->expires_at)
                                <div class="col-md-12">
                                    <div class="alert alert-info rounded-3 mb-0">
                                        This announcement is visible until
                                        <strong>{{ $announcement->expires_at->format('M d, Y h:i A') }}</strong>.
                                    </div>
                                </div>
                            @endif

                            @php
                                $announcementEditorContent = old('content', $announcement->content ?? '');
                                $announcementAllowedTags = '<p><br><strong><b><em><i><u><s><strike><ul><ol><li><div><span><blockquote>';
                                $announcementEditorContent = strip_tags((string) $announcementEditorContent, $announcementAllowedTags);
                            @endphp

                            <div class="col-md-12">
                                <label class="form-label">Content</label>

                                <div class="announcement-editor-wrap">
                                    <div class="announcement-editor-toolbar" role="toolbar" aria-label="Announcement content toolbar">
                                        <button type="button" class="announcement-editor-btn" data-command="bold" title="Bold">
                                            <strong>B</strong>
                                        </button>
                                        <button type="button" class="announcement-editor-btn" data-command="italic" title="Italic">
                                            <em>I</em>
                                        </button>
                                        <button type="button" class="announcement-editor-btn" data-command="underline" title="Underline">
                                            <u>U</u>
                                        </button>
                                        <button type="button" class="announcement-editor-btn" data-command="strikeThrough" title="Strikethrough">
                                            <s>ab</s>
                                        </button>
                                        <span class="announcement-editor-separator"></span>
                                        <button type="button" class="announcement-editor-btn" data-command="insertUnorderedList" title="Bullet list">
                                            • List
                                        </button>
                                        <button type="button" class="announcement-editor-btn" data-command="insertOrderedList" title="Numbered list">
                                            1. List
                                        </button>
                                        <span class="announcement-editor-separator"></span>
                                        <button type="button" class="announcement-editor-btn" data-command="outdent" title="Outdent">
                                            ◀
                                        </button>
                                        <button type="button" class="announcement-editor-btn" data-command="indent" title="Indent">
                                            ▶
                                        </button>
                                        <span class="announcement-editor-separator"></span>
                                        <button type="button" class="announcement-editor-btn" data-command="removeFormat" title="Clear formatting">
                                            Clear
                                        </button>
                                    </div>

                                    <div id="announcement_content_editor"
                                         class="announcement-content-editor"
                                         contenteditable="true"
                                         data-placeholder="Type the full announcement content here...">{!! $announcementEditorContent !!}</div>
                                </div>

                                <textarea name="content"
                                          id="announcement_content_input"
                                          class="d-none"
                                          required>{{ $announcementEditorContent }}</textarea>

                                <small class="text-secondary">Tip: You may use bold, italic, underline, lists, and indentation. It will display cleanly inside the dashboard pop-up.</small>
                            </div>

                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="is_published"
                                           value="1"
                                           id="is_published"
                                           {{ old('is_published', isset($announcement) && $announcement->is_published ? 1 : 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_published">
                                        Publish this announcement
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-12 d-flex gap-2 flex-wrap">
                                <button type="submit" class="btn btn-primary px-4">
                                    {{ isset($announcement) ? 'Update Announcement' : 'Create Announcement' }}
                                </button>
                                <a href="{{ route('announcements.index') }}" class="btn btn-light px-4">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .announcement-recipient-picker {
            position: relative;
        }

        .announcement-recipient-button {
            width: 100%;
            min-height: 44px;
            padding: 0 14px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: #fff;
            color: #5f6b7a;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            text-align: left;
        }

        .announcement-recipient-button:focus,
        .announcement-recipient-button.is-open {
            border-color: #3a57e8;
            box-shadow: 0 0 0 .2rem rgba(58, 87, 232, .12);
            outline: none;
        }

        #announcement_recipient_button_text {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .announcement-recipient-caret {
            flex: 0 0 auto;
            color: #344054;
            font-size: 18px;
        }

        .announcement-recipient-menu {
            display: none;
            position: absolute;
            z-index: 1055;
            top: calc(100% + 6px);
            left: 0;
            width: 100%;
            max-height: 320px;
            overflow: hidden;
            border: 1px solid #d9dee8;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .16);
        }

        .announcement-recipient-menu.is-open {
            display: block;
        }

        .announcement-recipient-search-wrap {
            padding: 10px;
            border-bottom: 1px solid #edf0f5;
        }

        .announcement-recipient-options {
            max-height: 220px;
            overflow-y: auto;
            padding: 4px 0;
        }

        .announcement-recipient-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            margin: 0;
            color: #344054;
            cursor: pointer;
            font-size: 14px;
            line-height: 1.35;
        }

        .announcement-recipient-option:hover {
            background: #f5f7fb;
        }

        .announcement-recipient-option input {
            flex: 0 0 auto;
            width: 16px;
            height: 16px;
            accent-color: #3a57e8;
        }

        .announcement-recipient-all-option {
            border-bottom: 1px solid #edf0f5;
            font-weight: 600;
        }

        .announcement-editor-wrap {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            background: #fff;
            overflow: hidden;
        }

        .announcement-editor-toolbar {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            padding: 8px 10px;
            background: #f8f9fb;
            border-bottom: 1px solid #e9ecef;
        }

        .announcement-editor-btn {
            min-width: 36px;
            height: 34px;
            padding: 0 10px;
            border: 1px solid #d9dee8;
            border-radius: 8px;
            background: #fff;
            color: #344054;
            font-size: 13px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            transition: .15s ease;
        }

        .announcement-editor-btn:hover,
        .announcement-editor-btn.is-active {
            color: #3a57e8;
            border-color: #3a57e8;
            background: rgba(58, 87, 232, .08);
        }

        .announcement-editor-separator {
            width: 1px;
            height: 24px;
            background: #dde3ee;
            margin: 0 2px;
        }

        .announcement-content-editor {
            min-height: 260px;
            max-height: 420px;
            overflow-y: auto;
            padding: 16px 18px;
            outline: none;
            color: #1f2937;
            font-size: 15px;
            line-height: 1.7;
            white-space: normal;
        }

        .announcement-content-editor:empty::before {
            content: attr(data-placeholder);
            color: #98a2b3;
            pointer-events: none;
        }

        .announcement-content-editor p,
        .announcement-content-editor div {
            margin-bottom: .75rem;
        }

        .announcement-content-editor ul,
        .announcement-content-editor ol {
            padding-left: 1.5rem;
            margin-bottom: .75rem;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var recipientPicker = document.getElementById('announcement_recipient_picker');
            var recipientToggle = document.getElementById('announcement_recipient_toggle');
            var recipientMenu = document.getElementById('announcement_recipient_menu');
            var recipientSearch = document.getElementById('announcement_recipient_search');
            var allRecipientCheckbox = document.getElementById('announcement_recipient_all');
            var recipientCheckboxes = document.querySelectorAll('.announcement-recipient-checkbox');
            var memoToInput = document.getElementById('announcement_memo_to');
            var memoToModeInput = document.getElementById('announcement_memo_to_mode');
            var recipientButtonText = document.getElementById('announcement_recipient_button_text');

            function selectedRecipientNames() {
                return Array.prototype.slice.call(recipientCheckboxes)
                    .filter(function (checkbox) {
                        return checkbox.checked;
                    })
                    .map(function (checkbox) {
                        return checkbox.dataset.name || checkbox.value;
                    });
            }

            function syncRecipientDisplay() {
                if (!allRecipientCheckbox || !memoToInput || !memoToModeInput || !recipientButtonText) {
                    return;
                }

                var names = selectedRecipientNames();

                if (allRecipientCheckbox.checked || names.length === 0) {
                    allRecipientCheckbox.checked = true;
                    recipientCheckboxes.forEach(function (checkbox) {
                        checkbox.checked = false;
                    });
                    memoToModeInput.value = 'all';
                    memoToInput.value = 'All Employees';
                    recipientButtonText.textContent = 'All Employees';
                    return;
                }

                memoToModeInput.value = 'selected';

                if (names.length === 1) {
                    memoToInput.value = names[0];
                    recipientButtonText.textContent = names[0];
                } else if (names.length <= 3) {
                    memoToInput.value = names.join(', ');
                    recipientButtonText.textContent = names.join(', ');
                } else {
                    memoToInput.value = names.length + ' Employees';
                    recipientButtonText.textContent = names.length + ' employees selected';
                }
            }

            if (recipientPicker && recipientToggle && recipientMenu) {
                recipientToggle.addEventListener('click', function () {
                    var shouldOpen = !recipientMenu.classList.contains('is-open');
                    recipientMenu.classList.toggle('is-open', shouldOpen);
                    recipientToggle.classList.toggle('is-open', shouldOpen);
                    recipientToggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');

                    if (shouldOpen && recipientSearch) {
                        setTimeout(function () {
                            recipientSearch.focus();
                        }, 50);
                    }
                });

                document.addEventListener('click', function (event) {
                    if (!recipientPicker.contains(event.target)) {
                        recipientMenu.classList.remove('is-open');
                        recipientToggle.classList.remove('is-open');
                        recipientToggle.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            if (allRecipientCheckbox) {
                allRecipientCheckbox.addEventListener('change', function () {
                    if (this.checked) {
                        recipientCheckboxes.forEach(function (checkbox) {
                            checkbox.checked = false;
                        });
                    }
                    syncRecipientDisplay();
                });
            }

            recipientCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    if (this.checked && allRecipientCheckbox) {
                        allRecipientCheckbox.checked = false;
                    }
                    syncRecipientDisplay();
                });
            });

            if (recipientSearch) {
                recipientSearch.addEventListener('input', function () {
                    var keyword = this.value.trim().toLowerCase();
                    document.querySelectorAll('#announcement_recipient_options .announcement-recipient-option').forEach(function (option) {
                        var haystack = option.dataset.search || option.textContent.toLowerCase();
                        option.style.display = haystack.indexOf(keyword) !== -1 ? 'flex' : 'none';
                    });
                });
            }

            syncRecipientDisplay();

            var editor = document.getElementById('announcement_content_editor');
            var contentInput = document.getElementById('announcement_content_input');
            var form = editor ? editor.closest('form') : null;
            var toolbarButtons = document.querySelectorAll('.announcement-editor-btn[data-command]');

            if (editor && contentInput && form) {
                function cleanEditorHtml(html) {
                    return (html || '')
                        .replace(/<script[\s\S]*?>[\s\S]*?<\/script>/gi, '')
                        .replace(/<style[\s\S]*?>[\s\S]*?<\/style>/gi, '')
                        .replace(/\son[a-z]+="[^"]*"/gi, '')
                        .replace(/\son[a-z]+='[^']*'/gi, '')
                        .replace(/javascript:/gi, '');
                }

                function syncContentInput() {
                    contentInput.value = cleanEditorHtml(editor.innerHTML).trim();
                }

                function updateToolbarState() {
                    toolbarButtons.forEach(function (button) {
                        var command = button.dataset.command;
                        if (!command) {
                            return;
                        }

                        try {
                            button.classList.toggle('is-active', document.queryCommandState(command));
                        } catch (error) {
                            button.classList.remove('is-active');
                        }
                    });
                }

                toolbarButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        var command = this.dataset.command;
                        editor.focus();
                        document.execCommand(command, false, null);
                        syncContentInput();
                        updateToolbarState();
                    });
                });

                editor.addEventListener('input', syncContentInput);
                editor.addEventListener('keyup', updateToolbarState);
                editor.addEventListener('mouseup', updateToolbarState);

                editor.addEventListener('paste', function (event) {
                    event.preventDefault();
                    var text = (event.clipboardData || window.clipboardData).getData('text/plain');
                    document.execCommand('insertText', false, text);
                    syncContentInput();
                });

                form.addEventListener('submit', function () {
                    syncContentInput();
                });

                syncContentInput();
            }
        });
    </script>

</x-app-layout>
