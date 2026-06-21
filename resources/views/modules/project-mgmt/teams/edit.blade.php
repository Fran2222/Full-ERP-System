<x-app-layout>
        <style>
        .wmc-checkbox-dropdown { position: relative; width: 100%; }
        .wmc-checkbox-dropdown-toggle {
            width: 100%;
            min-height: 44px;
            border: 1px solid #e0e5f2;
            border-radius: 8px;
            background: #fff;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #8a92a6;
        }
        .wmc-checkbox-dropdown-menu {
            display: none;
            position: absolute;
            width: 100%;
            background: #fff;
            border: 1px solid #e0e5f2;
            border-radius: 8px;
            margin-top: 6px;
            z-index: 50;
            box-shadow: 0 12px 30px rgba(0,0,0,.08);
            padding: 10px;
        }
        .wmc-checkbox-dropdown.open .wmc-checkbox-dropdown-menu { display: block; }
        .wmc-checkbox-options {
            max-height: 220px;
            overflow-y: auto;
            margin-top: 8px;
        }
        .wmc-checkbox-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 6px;
            cursor: pointer;
        }
        .wmc-checkbox-option:hover { background: #f5f7fb; }
        #assignUsersText {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 95%;
        }
    </style>
    <div class="container-fluid content-inner mt-n5 py-0">
        <div class="card rounded-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Edit Team</h4>
                <p class="text-secondary mb-0">Update project team information.</p>
            </div>

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger rounded-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('project-teams.update', $team->id) }}" method="POST" class="row g-3 needs-validation" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="col-md-6">
                        <label class="form-label">Team Code</label>
                        <input type="text"
                               name="code"
                               class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code', $team->code) }}"
                               readonly>
                        <div class="invalid-feedback">
                            @error('code') {{ $message }} @else Team code is required. @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Team Name <span class="text-danger">*</span></label>
                        <input type="text"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $team->name) }}"
                               maxlength="100"
                               required>
                        <div class="invalid-feedback">
                            @error('name') {{ $message }} @else Team name is required and must not exceed 100 characters. @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Team Leader <span class="text-danger">*</span></label>
                        <select name="team_leader_id"
                                class="form-select searchable-select @error('team_leader_id') is-invalid @enderror"
                                required>
                            <option value="">Select Team Leader</option>
                            @foreach($users as $user)
                                @php
                                    $displayName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email;
                                @endphp
                                <option value="{{ $user->id }}"
                                    {{ old('team_leader_id', $team->team_leader_id) == $user->id ? 'selected' : '' }}>
                                    {{ $displayName }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">
                            @error('team_leader_id') {{ $message }} @else Please select a team leader. @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status"
                                class="form-select @error('status') is-invalid @enderror"
                                required>
                            <option value="">Select Status</option>
                            <option value="active" {{ old('status', $team->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $team->status ?? 'active') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <div class="invalid-feedback">
                            @error('status') {{ $message }} @else Please select a status. @enderror
                        </div>
                    </div>

                    {{-- Members --}}
                    <div class="col-12">
                        <label class="form-label">Members</label>

                        @php
                            $selectedUserIds = old('members', $team->members->pluck('id')->toArray());
                        @endphp

                        <div class="wmc-checkbox-dropdown" id="teamMembersDropdown">
                            <button type="button" class="wmc-checkbox-dropdown-toggle">
                                <span id="teamMembersText">Select Members</span>
                                <span class="wmc-dropdown-arrow">⌄</span>
                            </button>

                            <div class="wmc-checkbox-dropdown-menu">
                                <input type="text" id="teamMembersSearch" class="form-control" placeholder="Search Users">

                                <div class="wmc-checkbox-options">
                                    @foreach($users as $user)
                                        @php
                                            $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                                            $displayName = $fullName ?: $user->email;
                                            $isSelected = in_array((string) $user->id, array_map('strval', $selectedUserIds));
                                        @endphp

                                        <label class="wmc-checkbox-option" data-name="{{ strtolower($displayName) }}">
                                            <input type="checkbox"
                                                   name="members[]"
                                                   value="{{ $user->id }}"
                                                   data-label="{{ $displayName }}"
                                                   {{ $isSelected ? 'checked' : '' }}>
                                            <span>{{ $displayName }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        @error('members')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks"
                                  class="form-control @error('remarks') is-invalid @enderror"
                                  rows="3"
                                  maxlength="1000">{{ old('remarks', $team->remarks) }}</textarea>
                        <div class="invalid-feedback">
                            @error('remarks') {{ $message }} @else Remarks must not exceed 1000 characters. @enderror
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('project-teams.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Team</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const forms = document.querySelectorAll('.needs-validation');

                forms.forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }

                        form.classList.add('was-validated');
                    }, false);
                });

                if (window.jQuery && $.fn.select2) {
                    $('.searchable-select').select2({
                        width: '100%',
                        allowClear: true
                    });
                }

                const dropdown = document.getElementById('teamMembersDropdown');
                if (!dropdown) return;

                const toggle = dropdown.querySelector('.wmc-checkbox-dropdown-toggle');
                const text = document.getElementById('teamMembersText');
                const search = document.getElementById('teamMembersSearch');
                const options = dropdown.querySelectorAll('.wmc-checkbox-option');
                const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]');

                function updateText() {
                    const selected = Array.from(checkboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.dataset.label);

                    text.textContent = selected.length ? selected.join(', ') : 'Select Members';
                    text.style.color = selected.length ? '#232d42' : '#8a92a6';
                }

                toggle.addEventListener('click', function () {
                    dropdown.classList.toggle('open');

                    if (dropdown.classList.contains('open')) {
                        setTimeout(() => search.focus(), 50);
                    }
                });

                search.addEventListener('input', function () {
                    const term = this.value.toLowerCase();

                    options.forEach(option => {
                        option.style.display = option.dataset.name.includes(term) ? 'flex' : 'none';
                    });
                });

                checkboxes.forEach(cb => cb.addEventListener('change', updateText));

                document.addEventListener('click', function (e) {
                    if (!dropdown.contains(e.target)) {
                        dropdown.classList.remove('open');
                    }
                });

                updateText();
            });
        </script>
    @endpush
</x-app-layout>