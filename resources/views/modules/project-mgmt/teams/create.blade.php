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
                <h4 class="card-title mb-0">Create Team</h4>
                <p class="text-secondary mb-0">Add new project team.</p>
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

                <form action="{{ route('project-teams.store') }}" method="POST" class="row g-3 needs-validation" novalidate>
                    @csrf

                    <div class="col-md-6">
                        <label class="form-label">Team Code</label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $code) }}" readonly>
                        <div class="invalid-feedback">
                            @error('code') {{ $message }} @else Team code is required. @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Team Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" maxlength="100" required>
                        <div class="invalid-feedback">
                            @error('name') {{ $message }} @else Team name is required and must not exceed 100 characters. @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Team Leader <span class="text-danger">*</span></label>
                        <select name="team_leader_id" class="form-select searchable-select @error('team_leader_id') is-invalid @enderror" required>
                            <option value="">Select Team Leader</option>
                            @foreach($users as $user)
                                @php
                                    $displayName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email;
                                @endphp
                                <option value="{{ $user->id }}" {{ old('team_leader_id') == $user->id ? 'selected' : '' }}>
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
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="">Select Status</option>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', 'active') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <div class="invalid-feedback">
                            @error('status') {{ $message }} @else Please select a status. @enderror
                        </div>
                    </div>

                    {{-- Members --}}
                        <div class="col-12">
                            <label class="form-label">Members</label>

                            @php
                                $selectedUserIds = old('members', []);
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
                        <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3" maxlength="1000">{{ old('remarks') }}</textarea>
                        <div class="invalid-feedback">
                            @error('remarks') {{ $message }} @else Remarks must not exceed 1000 characters. @enderror
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('project-teams.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Team</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('modules.project-mgmt.teams.partials.team-form-script')
</x-app-layout>