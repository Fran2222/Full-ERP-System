<x-app-layout :assets="$assets ?? []">
    <div class="container-fluid content-inner mt-n5 py-0">
        @php
            $id = $id ?? null;
            $profile = $data->userProfile ?? null;

            $moduleOptions = [
                'hr' => 'HR',
                'inventory' => 'Inventory',
                'warehouse' => 'Warehouse',
                'purchasing' => 'Purchasing',
                'sales' => 'Sales',
                'accounting' => 'Accounting',
                'pos' => 'POS',
                'payroll' => 'Payroll',
                'reports' => 'Reports',
                'project_management' => 'Project Management',
                'vehicle_management' => 'Vehicle Management',
            ];

            $accessLevelOptions = [
                'viewer' => [
                    'label' => 'Viewer',
                    'description' => 'Can view records only.',
                ],
                'staff' => [
                    'label' => 'Staff',
                    'description' => 'Can view and create daily transactions.',
                ],
                'manager' => [
                    'label' => 'Manager',
                    'description' => 'Can manage operational workflows.',
                ],
                'admin' => [
                    'label' => 'Admin',
                    'description' => 'Full module access including delete where allowed.',
                ],
            ];

            $moduleAssignmentsMap = [];

            if (isset($data) && method_exists($data, 'moduleAssignments') && $data->relationLoaded('moduleAssignments')) {
                foreach ($data->moduleAssignments as $assignment) {
                    $moduleAssignmentsMap[$assignment->module] = [
                        'enabled' => true,
                        'access_level' => $assignment->access_level,
                        'is_primary' => (bool) $assignment->is_primary,
                    ];
                }
            }

            $oldAssignments = old('module_assignments');
            $oldPrimaryModule = old('primary_module_assignment');

            if (is_array($oldAssignments)) {
                foreach ($oldAssignments as $moduleKey => $row) {
                    $moduleAssignmentsMap[$moduleKey] = [
                        'enabled' => !empty($row['enabled']),
                        'access_level' => $row['access_level'] ?? null,
                        'is_primary' => $oldPrimaryModule === $moduleKey,
                    ];
                }
            }

            $selectedPrimaryModule = $oldPrimaryModule;

            if (!$selectedPrimaryModule && !empty($moduleAssignmentsMap)) {
                foreach ($moduleAssignmentsMap as $moduleKey => $row) {
                    if (!empty($row['is_primary'])) {
                        $selectedPrimaryModule = $moduleKey;
                        break;
                    }
                }
            }

            $currentStatus = old('status', $data->status ?? 'active');
        @endphp

        @if(isset($id))
            {!! Form::model($data, ['route' => ['users.update', $id], 'method' => 'patch', 'enctype' => 'multipart/form-data', 'id' => 'user-management-form']) !!}
        @else
            {!! Form::open(['route' => ['users.store'], 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'user-management-form']) !!}
        @endif

        <div class="user-form-header-card">
            <div>
                <h4 class="mb-1 fw-bold">{{ $id !== null ? 'Update User' : 'Add User' }}</h4>
                <p class="text-secondary mb-0">
                    Manage user profile, organization details, security, and module access levels.
                </p>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary user-soft-btn">
                    Back
                </a>

                <button type="submit" class="btn btn-primary user-soft-btn">
                    {{ $id !== null ? 'Update User' : 'Save User' }}
                </button>
            </div>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-12 col-xl-4 col-xxl-3">
                <div class="card user-panel">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-2">
                        <h5 class="mb-1 fw-bold">Profile Setup</h5>
                        <p class="text-secondary mb-0 small">Photo, role, branch, department, and account status.</p>
                    </div>

                    <div class="card-body px-4 pb-4">
                        <div class="form-group mb-4">
                            <div class="profile-img-edit position-relative text-center">
                                <img src="{{ $profileImage ?? asset('images/avatars/01.png') }}"
                                     alt="User-Profile"
                                     class="profile-pic rounded-circle user-profile-img">

                                <div class="upload-icone bg-primary user-upload-icon">
                                    <svg class="upload-button" width="14" height="14" viewBox="0 0 24 24">
                                        <path fill="#ffffff" d="M14.06,9L15,9.94L5.92,19H5V18.08L14.06,9M17.66,3C17.41,3 17.15,3.1 16.96,3.29L15.13,5.12L18.88,8.87L20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18.17,3.09 17.92,3 17.66,3M14.06,6.19L3,17.25V21H6.75L17.81,9.94L14.06,6.19Z" />
                                    </svg>
                                    <input class="file-upload" type="file" accept="image/*" name="profile_image">
                                </div>
                            </div>

                            <div class="img-extension mt-3 text-center small text-secondary">
                                Only .jpg, .png, .jpeg allowed
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">User Role <span class="text-danger">*</span></label>
                            {{ Form::select(
                                'user_role',
                                $roles ?? [],
                                old('user_role', $data->user_type ?? null),
                                ['class' => 'form-control user-control', 'placeholder' => 'Select User Role', 'required']
                            ) }}
                            @error('user_role')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Branch <span class="text-danger">*</span></label>
                            {{ Form::select(
                                'branch_id',
                                $branches ?? [],
                                old('branch_id', $data->branch_id ?? null),
                                ['class' => 'form-control user-control', 'placeholder' => 'Select Branch', 'required']
                            ) }}
                            @error('branch_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            {{ Form::select(
                                'department_id',
                                $departments ?? [],
                                old('department_id', $data->department_id ?? null),
                                ['class' => 'form-control user-control', 'placeholder' => 'Select Department', 'required']
                            ) }}
                            @error('department_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="user-status-box">
                            <label class="form-label d-block mb-3">Account Status</label>

                            <div class="user-status-grid">
                                @foreach(['active' => 'Active', 'pending' => 'Pending', 'inactive' => 'Inactive', 'banned' => 'Banned'] as $statusKey => $statusLabel)
                                    <label class="user-status-option {{ $currentStatus === $statusKey ? 'selected' : '' }}" for="status-{{ $statusKey }}">
                                        {{ Form::radio('status', $statusKey, $currentStatus === $statusKey, ['class' => 'form-check-input user-status-radio', 'id' => 'status-' . $statusKey]) }}
                                        <span>{{ $statusLabel }}</span>
                                    </label>
                                @endforeach
                            </div>

                            @error('status')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-8 col-xxl-9">
                <div class="card user-panel mb-4">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-2">
                        <h5 class="mb-1 fw-bold">User Information</h5>
                        <p class="text-secondary mb-0 small">Basic profile and contact details.</p>
                    </div>

                    <div class="card-body px-4 pb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="fname">First Name <span class="text-danger">*</span></label>
                                {{ Form::text('first_name', old('first_name', $data->first_name ?? ''), [
                                    'class' => 'form-control user-control',
                                    'id' => 'fname',
                                    'placeholder' => 'First Name',
                                    'required'
                                ]) }}
                                @error('first_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="lname">Last Name <span class="text-danger">*</span></label>
                                {{ Form::text('last_name', old('last_name', $data->last_name ?? ''), [
                                    'class' => 'form-control user-control',
                                    'id' => 'lname',
                                    'placeholder' => 'Last Name',
                                    'required'
                                ]) }}
                                @error('last_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                                {{ Form::email(
                                    'email',
                                    old('email', $data->email ?? ''),
                                    [
                                        'class' => 'form-control user-control',
                                        'id' => 'email',
                                        'placeholder' => 'Enter email address',
                                        'required'
                                    ]
                                ) }}
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="mobno">Mobile Number</label>
                                {{ Form::text(
                                    'phone_number',
                                    old('phone_number', $data->phone_number ?? ''),
                                    [
                                        'class' => 'form-control user-control',
                                        'id' => 'mobno',
                                        'placeholder' => 'Mobile Number'
                                    ]
                                ) }}
                                @error('phone_number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Company Name</label>
                                <input type="text" class="form-control user-control" value="Wizmaster Corporation" readonly>
                                <input type="hidden" name="userProfile[company_name]" value="Wizmaster Corporation">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" class="form-control user-control" value="Philippines" readonly>
                                <input type="hidden" name="userProfile[country]" value="Philippines">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="altconno">Alternate Contact</label>
                                {{ Form::text(
                                    'userProfile[alt_phone_number]',
                                    old('userProfile.alt_phone_number', $profile->alt_phone_number ?? ''),
                                    [
                                        'class' => 'form-control user-control',
                                        'id' => 'altconno',
                                        'placeholder' => 'Alternate Contact'
                                    ]
                                ) }}
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="pin_code">Pin Code</label>
                                {{ Form::number(
                                    'userProfile[pin_code]',
                                    old('userProfile.pin_code', $profile->pin_code ?? ''),
                                    [
                                        'class' => 'form-control user-control',
                                        'id' => 'pin_code',
                                        'step' => 'any',
                                        'placeholder' => 'Pin Code'
                                    ]
                                ) }}
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="full_address">Full Address <span class="text-danger">*</span></label>
                                {{ Form::textarea(
                                    'userProfile[street_addr_1]',
                                    old('userProfile.street_addr_1', $profile->street_addr_1 ?? ''),
                                    [
                                        'class' => 'form-control user-control',
                                        'id' => 'full_address',
                                        'placeholder' => 'Enter Full Address',
                                        'rows' => 2,
                                        'required'
                                    ]
                                ) }}
                            </div>
                        </div>

                        <input type="hidden" name="userProfile[street_addr_2]" value="">
                        <input type="hidden" name="userProfile[state]" value="">
                        <input type="hidden" name="userProfile[city]" value="">
                        <input type="hidden" name="userProfile[facebook_url]" value="">
                        <input type="hidden" name="userProfile[twitter_url]" value="">
                        <input type="hidden" name="userProfile[instagram_url]" value="">
                        <input type="hidden" name="userProfile[linkedin_url]" value="">
                    </div>
                </div>

                <div class="card user-panel mb-4">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-2">
                        <h5 class="mb-1 fw-bold">Module Assignments</h5>
                        <p class="text-secondary mb-0 small">
                            Enable the modules this user can access, choose access level, then select one primary module.
                        </p>
                    </div>

                    <div class="card-body px-4 pb-4">
                        @error('module_assignments')
                            <div class="alert alert-danger rounded-3">{{ $message }}</div>
                        @enderror

                        @error('primary_module_assignment')
                            <div class="alert alert-danger rounded-3">{{ $message }}</div>
                        @enderror

                        <div class="module-grid">
                            @foreach($moduleOptions as $moduleKey => $moduleLabel)
                                @php
                                    $rowData = $moduleAssignmentsMap[$moduleKey] ?? [
                                        'enabled' => false,
                                        'access_level' => null,
                                        'is_primary' => false,
                                    ];

                                    $isEnabled = !empty($rowData['enabled']);
                                    $selectedAccess = $rowData['access_level'] ?? '';
                                    $isPrimary = $selectedPrimaryModule === $moduleKey;
                                @endphp

                                <div class="module-card {{ $isEnabled ? 'enabled' : 'disabled' }}" data-module-card="{{ $moduleKey }}">
                                    <div class="module-card-top">
                                        <div>
                                            <div class="module-title">{{ $moduleLabel }}</div>
                                            <div class="module-subtitle">
                                                {{ $isEnabled ? 'Module enabled' : 'Module disabled' }}
                                            </div>
                                        </div>

                                        <label class="module-switch">
                                            <input
                                                type="checkbox"
                                                name="module_assignments[{{ $moduleKey }}][enabled]"
                                                value="1"
                                                class="module-enable-checkbox"
                                                data-module="{{ $moduleKey }}"
                                                {{ $isEnabled ? 'checked' : '' }}
                                            >
                                            <span></span>
                                        </label>
                                    </div>

                                    <div class="module-control-row">
                                        <label class="form-label small mb-1">Access Level</label>
                                        <select
                                            name="module_assignments[{{ $moduleKey }}][access_level]"
                                            class="form-control user-control module-access-level"
                                            data-module="{{ $moduleKey }}"
                                            {{ !$isEnabled ? 'disabled' : '' }}
                                        >
                                            <option value="">Select Access Level</option>
                                            @foreach($accessLevelOptions as $accessKey => $accessMeta)
                                                <option value="{{ $accessKey }}"
                                                    {{ $selectedAccess === $accessKey ? 'selected' : '' }}>
                                                    {{ $accessMeta['label'] }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <div class="module-access-help" data-access-help="{{ $moduleKey }}">
                                            @if($selectedAccess && isset($accessLevelOptions[$selectedAccess]))
                                                {{ $accessLevelOptions[$selectedAccess]['description'] }}
                                            @else
                                                Select an access level.
                                            @endif
                                        </div>
                                    </div>

                                    <label class="module-primary-option {{ $isPrimary ? 'selected' : '' }}">
                                        <input
                                            type="radio"
                                            name="primary_module_assignment"
                                            value="{{ $moduleKey }}"
                                            class="form-check-input module-primary-radio"
                                            data-module="{{ $moduleKey }}"
                                            {{ $isPrimary ? 'checked' : '' }}
                                            {{ !$isEnabled ? 'disabled' : '' }}
                                        >
                                        <span>Set as primary module</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="module-note mt-3">
                            <strong>Note:</strong> The primary module will be used as the user's main assigned module. Disabled modules are not shown in their sidebar and direct URLs stay protected by permissions.
                        </div>
                    </div>
                </div>

                <div class="card user-panel">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-2">
                        <h5 class="mb-1 fw-bold">Security</h5>
                        <p class="text-secondary mb-0 small">
                            Set username and password. Leave password blank when editing if you do not want to change it.
                        </p>
                    </div>

                    <div class="card-body px-4 pb-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="uname">User Name <span class="text-danger">*</span></label>
                                {{ Form::text(
                                    'username',
                                    old('username', $data->username ?? ''),
                                    [
                                        'class' => 'form-control user-control',
                                        'id' => 'uname',
                                        'required',
                                        'placeholder' => 'Enter Username'
                                    ]
                                ) }}
                                @error('username')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="pass">Password {{ $id === null ? '*' : '' }}</label>
                                {{ Form::password('password', [
                                    'class' => 'form-control user-control',
                                    'id' => 'pass',
                                    'placeholder' => $id === null ? 'Password' : 'Leave blank to keep current password',
                                    $id === null ? 'required' : ''
                                ]) }}
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="rpass">Repeat Password {{ $id === null ? '*' : '' }}</label>
                                {{ Form::password('password_confirmation', [
                                    'class' => 'form-control user-control',
                                    'id' => 'rpass',
                                    'placeholder' => 'Repeat Password',
                                    $id === null ? 'required' : ''
                                ]) }}
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary user-soft-btn">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-primary user-soft-btn">
                                {{ $id !== null ? 'Update User' : 'Save User' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {!! Form::close() !!}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const accessDescriptions = {
                viewer: 'Can view records only.',
                staff: 'Can view and create daily transactions.',
                manager: 'Can manage operational workflows.',
                admin: 'Full module access including delete where allowed.'
            };

            function refreshPrimarySelectionStyle() {
                document.querySelectorAll('.module-primary-option').forEach(function (label) {
                    const radio = label.querySelector('.module-primary-radio');
                    label.classList.toggle('selected', radio && radio.checked);
                });
            }

            function updateStatusCards() {
                document.querySelectorAll('.user-status-option').forEach(function (label) {
                    const input = label.querySelector('.user-status-radio');
                    label.classList.toggle('selected', input && input.checked);
                });
            }

            function syncModuleRow(module) {
                const checkbox = document.querySelector('.module-enable-checkbox[data-module="' + module + '"]');
                const select = document.querySelector('.module-access-level[data-module="' + module + '"]');
                const radio = document.querySelector('.module-primary-radio[data-module="' + module + '"]');
                const card = document.querySelector('[data-module-card="' + module + '"]');
                const help = document.querySelector('[data-access-help="' + module + '"]');

                if (!checkbox || !select || !radio || !card) return;

                const subtitle = card.querySelector('.module-subtitle');

                if (checkbox.checked) {
                    select.removeAttribute('disabled');
                    radio.removeAttribute('disabled');
                    card.classList.add('enabled');
                    card.classList.remove('disabled');

                    if (subtitle) {
                        subtitle.textContent = 'Module enabled';
                    }

                    if (!select.value) {
                        select.value = 'viewer';
                    }

                    if (help) {
                        help.textContent = accessDescriptions[select.value] || 'Select an access level.';
                    }
                } else {
                    select.value = '';
                    select.setAttribute('disabled', 'disabled');

                    radio.checked = false;
                    radio.setAttribute('disabled', 'disabled');

                    card.classList.add('disabled');
                    card.classList.remove('enabled');

                    if (subtitle) {
                        subtitle.textContent = 'Module disabled';
                    }

                    if (help) {
                        help.textContent = 'Select an access level.';
                    }
                }

                refreshPrimarySelectionStyle();
            }

            document.querySelectorAll('.module-enable-checkbox').forEach(function (checkbox) {
                syncModuleRow(checkbox.dataset.module);

                checkbox.addEventListener('change', function () {
                    syncModuleRow(this.dataset.module);

                    const enabledRadios = Array.from(document.querySelectorAll('.module-primary-radio:not(:disabled)'));
                    const hasPrimary = enabledRadios.some(function (radio) {
                        return radio.checked;
                    });

                    if (!hasPrimary && enabledRadios.length > 0) {
                        enabledRadios[0].checked = true;
                    }

                    refreshPrimarySelectionStyle();
                });
            });

            document.querySelectorAll('.module-access-level').forEach(function (select) {
                select.addEventListener('change', function () {
                    const help = document.querySelector('[data-access-help="' + this.dataset.module + '"]');

                    if (help) {
                        help.textContent = accessDescriptions[this.value] || 'Select an access level.';
                    }
                });
            });

            document.querySelectorAll('.module-primary-radio').forEach(function (radio) {
                radio.addEventListener('change', refreshPrimarySelectionStyle);
            });

            document.querySelectorAll('.user-status-radio').forEach(function (radio) {
                radio.addEventListener('change', updateStatusCards);
            });

            const form = document.getElementById('user-management-form');

            if (form) {
                form.addEventListener('submit', function (event) {
                    const enabledCards = Array.from(document.querySelectorAll('.module-enable-checkbox:checked'));

                    if (enabledCards.length > 0) {
                        const primary = document.querySelector('.module-primary-radio:checked');

                        if (!primary) {
                            event.preventDefault();

                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Primary module required',
                                    text: 'Please select one primary module for this user.'
                                });
                            } else {
                                alert('Please select one primary module for this user.');
                            }
                        }
                    }
                });
            }

            refreshPrimarySelectionStyle();
            updateStatusCards();
        });
    </script>

    <style>
        .user-form-header-card,
        .user-panel {
            background: #ffffff;
            border-radius: 18px !important;
            border: 1px solid #edf0f5 !important;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055) !important;
            overflow: hidden;
        }

        .user-form-header-card {
            padding: 22px 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
        }

        .user-soft-btn {
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 700;
        }

        .user-control {
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            min-height: 42px;
        }

        .user-control:focus {
            border-color: #3f5cff;
            box-shadow: 0 0 0 0.15rem rgba(63, 92, 255, 0.12);
        }

        .user-profile-img {
            width: 104px;
            height: 104px;
            object-fit: cover;
            border: 4px solid #f1f5f9;
        }

        .user-upload-icon {
            position: absolute;
            right: calc(50% - 52px);
            bottom: 4px;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            cursor: pointer;
        }

        .user-upload-icon input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .user-status-box {
            background: #f8fafc;
            border: 1px solid #edf0f5;
            border-radius: 16px;
            padding: 16px;
        }

        .user-status-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .user-status-option {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #ffffff;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-weight: 700;
            color: #475569;
            transition: all .18s ease-in-out;
        }

        .user-status-option.selected {
            border-color: #3f5cff;
            background: #eef4ff;
            color: #2448e8;
        }

        .module-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .module-card {
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 16px;
            background: #ffffff;
            transition: all .18s ease-in-out;
        }

        .module-card.enabled {
            border-color: rgba(63, 92, 255, 0.28);
            background: linear-gradient(180deg, #f8faff 0%, #ffffff 70%);
            box-shadow: 0 8px 18px rgba(63, 92, 255, 0.06);
        }

        .module-card.disabled {
            background: #fbfcfe;
            opacity: .82;
        }

        .module-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .module-title {
            color: #111827;
            font-size: 15px;
            font-weight: 900;
            line-height: 1.2;
        }

        .module-subtitle {
            color: #8a94a6;
            font-size: 12px;
            margin-top: 4px;
        }

        .module-control-row {
            margin-bottom: 12px;
        }

        .module-access-help {
            color: #8a94a6;
            font-size: 11px;
            margin-top: 6px;
            min-height: 28px;
            line-height: 1.3;
        }

        .module-primary-option {
            border: 1px dashed #d5dae5;
            border-radius: 12px;
            padding: 9px 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            transition: all .18s ease-in-out;
        }

        .module-primary-option.selected {
            border-style: solid;
            border-color: #3f5cff;
            background: #eef4ff;
            color: #2448e8;
        }

        .module-switch {
            position: relative;
            width: 44px;
            height: 24px;
            flex: 0 0 auto;
        }

        .module-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .module-switch span {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background: #cbd5e1;
            border-radius: 999px;
            transition: .2s;
        }

        .module-switch span:before {
            content: "";
            position: absolute;
            width: 18px;
            height: 18px;
            left: 3px;
            top: 3px;
            background: #ffffff;
            border-radius: 50%;
            transition: .2s;
            box-shadow: 0 2px 6px rgba(15, 23, 42, .18);
        }

        .module-switch input:checked + span {
            background: #3f5cff;
        }

        .module-switch input:checked + span:before {
            transform: translateX(20px);
        }

        .module-note {
            background: #f8fafc;
            border: 1px solid #edf0f5;
            border-radius: 14px;
            padding: 12px 14px;
            color: #64748b;
            font-size: 13px;
        }

        @media (max-width: 1399px) {
            .module-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 991px) {
            .module-grid {
                grid-template-columns: 1fr;
            }

            .user-form-header-card {
                padding: 18px;
            }
        }

        @media (max-width: 575px) {
            .user-status-grid {
                grid-template-columns: 1fr;
            }

            .user-form-header-card .d-flex {
                width: 100%;
            }

            .user-form-header-card .btn {
                flex: 1;
            }
        }
    </style>
</x-app-layout>