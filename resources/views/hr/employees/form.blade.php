<x-app-layout>
    @php
        $isEdit = $isEdit ?? (isset($employee) && !empty($employee->id));
        $profile = $profile ?? optional($employee)->employeeProfile ?? new \App\Models\EmployeeProfile();
        $supervisors = $supervisors ?? collect();
        $suffixes = $suffixes ?? ['N/A', 'Jr.', 'Sr.', 'II', 'III', 'IV', 'V', 'VI'];
        $maritalStatuses = $maritalStatuses ?? ['Single', 'Married', 'Divorced', 'Separated', 'Widowed'];
        $sexes = $sexes ?? ['Male', 'Female'];
        $employmentTypes = $employmentTypes ?? ['Regular', 'Probationary', 'Contractual', 'Project-based', 'Part-time', 'Intern'];
        $employmentStatuses = $employmentStatuses ?? ['Active', 'Inactive', 'Probationary', 'Resigned', 'Terminated'];
        $selectedDepartmentId = (string) old('department_id', $employee->department_id ?? '');
        $selectedPositionId = (string) old('position_id', $employee->position_id ?? $profile->position_id ?? '');
    @endphp

    <style>
        .wmc-contact-emergency-card hr {
            display: none !important;
        }

        .wmc-contact-section-title {
            margin-bottom: 1rem;
        }

        .wmc-emergency-section-title {
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }
    </style>

    <div class="container-fluid content-inner mt-n5 py-0">
        <form method="POST" action="{{ $isEdit ? route('hr.employees.update', $employee) : route('hr.employees.store') }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-0">{{ $isEdit ? 'Edit Employee' : 'New Employee' }}</h4>
                        <p class="mb-0 text-secondary">Manage employee account, personal, contact, and employment details.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('hr.employees.index') }}" class="btn btn-light">Back</a>
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Employee' : 'Save Employee' }}</button>
                    </div>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="card employee-form-card">
                        <div class="card-header employee-section-header">
                            <h5 class="mb-0">Account Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control" value="{{ old('username', $employee->username ?? '') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email ?? '') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card employee-form-card">
                        <div class="card-header employee-section-header">
                            <h5 class="mb-0">Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">First Name <span class="required-mark">*</span></label>
                                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $employee->first_name ?? '') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $employee->middle_name ?? '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Last Name <span class="required-mark">*</span></label>
                                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $employee->last_name ?? '') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Suffix <span class="required-mark">*</span></label>
                                    <select name="suffix" class="form-select" required>
                                        <option value="">Select Suffix</option>
                                        @foreach($suffixes as $suffix)
                                            <option value="{{ $suffix }}" {{ old('suffix', $employee->suffix ?? '') === $suffix ? 'selected' : '' }}>{{ $suffix }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Birthday <span class="required-mark">*</span></label>
                                    <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', optional($profile->birth_date)->format('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Marital Status <span class="required-mark">*</span></label>
                                    <select name="civil_status" class="form-select" required>
                                        <option value="">Select Marital Status</option>
                                        @foreach($maritalStatuses as $status)
                                            <option value="{{ $status }}" {{ old('civil_status', $profile->civil_status ?? '') === $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Sex at Birth <span class="required-mark">*</span></label>
                                    <select name="sex_of_birth" class="form-select" required>
                                        <option value="">Select Sex of Birth</option>
                                        @foreach($sexes as $sex)
                                            <option value="{{ $sex }}" {{ old('sex_of_birth', $profile->sex_of_birth ?? $profile->gender ?? '') === $sex ? 'selected' : '' }}>{{ $sex }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card employee-form-card wmc-contact-emergency-card">
                        <div class="card-header employee-section-header">
                            <h5 class="mb-0">Contact & Emergency Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="wmc-contact-section-title">
                                <h6 class="fw-semibold mb-1">Address Information</h6>
                                <p class="text-secondary small mb-0">Employee current address details for the 201 File.</p>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Province <span class="required-mark">*</span></label>
                                    <select
                                        name="province"
                                        id="province"
                                        class="form-select"
                                        data-selected-value="{{ old('province', $profile->province ?? '') }}"
                                        required
                                    >
                                        <option value="">Select Province</option>
                                        @foreach($provinces as $province)
                                            <option
                                                value="{{ $province->name }}"
                                                data-id="{{ $province->id }}"
                                                {{ old('province', $profile->province ?? '') === $province->name ? 'selected' : '' }}
                                            >
                                                {{ $province->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">City <span class="required-mark">*</span></label>
                                    <select
                                        name="city"
                                        id="city"
                                        class="form-select"
                                        data-selected-value="{{ old('city', $profile->city ?? '') }}"
                                        required
                                    >
                                        <option value="">Select City</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Barangay <span class="required-mark">*</span></label>
                                    <select
                                        name="barangay"
                                        id="barangay"
                                        class="form-select"
                                        data-selected-value="{{ old('barangay', $profile->barangay ?? '') }}"
                                        required
                                    >
                                        <option value="">Select Barangay</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Contact Number</label>
                                    <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $employee->phone_number ?? '') }}">
                                </div>
                            </div>

                            <div class="wmc-emergency-section-title">
                                <h6 class="fw-semibold mb-1">Emergency Contact</h6>
                                <p class="text-secondary small mb-0">Person to contact in case of emergency.</p>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Emergency Contact Name</label>
                                    <input
                                        type="text"
                                        name="emergency_contact_name"
                                        class="form-control"
                                        value="{{ old('emergency_contact_name', $profile->emergency_contact_name ?? '') }}"
                                        placeholder="Enter emergency contact name"
                                    >
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Emergency Contact Number</label>
                                    <input
                                        type="text"
                                        name="emergency_contact_number"
                                        class="form-control"
                                        value="{{ old('emergency_contact_number', $profile->emergency_contact_number ?? '') }}"
                                        placeholder="Enter emergency contact number"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card employee-form-card mb-0">
                        <div class="card-header employee-section-header">
                            <h5 class="mb-0">Employment Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Date Hired <span class="required-mark">*</span></label>
                                    <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date', optional($profile->hire_date)->format('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Branch <span class="required-mark">*</span></label>
                                    <select name="branch_id" class="form-select" required>
                                        <option value="">Select Branch</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ (string) old('branch_id', $employee->branch_id ?? '') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Rate per Day <span class="required-mark">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" step="0.01" min="0" name="employee_rate" class="form-control" value="{{ old('employee_rate', $profile->employee_rate ?? $profile->salary ?? '') }}" required>
                                    </div>
                                    <small class="text-secondary">Used to automatically compute overtime Rate per Hour ÷ 8 hours.</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Department <span class="required-mark">*</span></label>
                                    <select name="department_id" id="department_id" class="form-select" required>
                                        <option value="">Select Department</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ $selectedDepartmentId === (string) $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Designation <span class="required-mark">*</span></label>
                                    <select name="position_id" id="position_id" class="form-select" required>
                                        <option value="">Select Designation</option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->id }}" data-department-id="{{ $position->department_id }}" {{ $selectedPositionId === (string) $position->id ? 'selected' : '' }}>{{ $position->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Immediate Supervisor</label>
                                    <select name="supervisor_id" class="form-select">
                                        <option value="">Select Immediate Supervisor</option>
                                        @foreach($supervisors as $supervisor)
                                            <option value="{{ $supervisor->id }}" {{ (string) old('supervisor_id', $profile->supervisor_id ?? '') === (string) $supervisor->id ? 'selected' : '' }}>{{ $supervisor->full_name ?: $supervisor->username }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">SSS Number</label>
                                    <input type="text" name="sss_number" class="form-control" value="{{ old('sss_number', $profile->sss_number ?? '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Pag-IBIG Number</label>
                                    <input type="text" name="pagibig_number" class="form-control" value="{{ old('pagibig_number', $profile->pagibig_number ?? '') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">PhilHealth Number</label>
                                    <input type="text" name="philhealth_number" class="form-control" value="{{ old('philhealth_number', $profile->philhealth_number ?? '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">TIN Number</label>
                                    <input type="text" name="tax_id_number" class="form-control" value="{{ old('tax_id_number', $profile->tax_id_number ?? '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Employment Type</label>
                                    <select name="employment_type" class="form-select" required>
                                        <option value="">Select Employment Type</option>
                                        @foreach($employmentTypes as $type)
                                            <option value="{{ $type }}" {{ old('employment_type', $profile->employment_type ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Employment Status</label>
                                    <select name="employment_status" class="form-select" required>
                                        <option value="">Select Employment Status</option>
                                        @foreach($employmentStatuses as $status)
                                            <option value="{{ $status }}" {{ old('employment_status', $profile->employment_status ?? $employee->status ?? '') === $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const departmentSelect = document.getElementById('department_id');
            const positionSelect = document.getElementById('position_id');

            if (departmentSelect && positionSelect) {
                const allOptions = Array.from(positionSelect.querySelectorAll('option'));

                function filterPositions() {
                    const selectedDepartmentId = departmentSelect.value;
                    const currentValue = positionSelect.value;

                    allOptions.forEach(function (option) {
                        if (!option.value) {
                            option.hidden = false;
                            return;
                        }

                        const optionDepartmentId = option.getAttribute('data-department-id');
                        option.hidden = selectedDepartmentId !== '' && optionDepartmentId !== selectedDepartmentId;
                    });

                    const currentOption = positionSelect.querySelector('option[value="' + currentValue + '"]');
                    if (currentOption && currentOption.hidden) {
                        positionSelect.value = '';
                    }
                }

                departmentSelect.addEventListener('change', filterPositions);
                filterPositions();
            }

            const provinceSelect = document.getElementById('province');
            const citySelect = document.getElementById('city');
            const barangaySelect = document.getElementById('barangay');

            if (!provinceSelect || !citySelect || !barangaySelect) {
                return;
            }

            const citiesUrl = "{{ route('hr.employees.location.cities') }}";
            const barangaysUrl = "{{ route('hr.employees.location.barangays') }}";

            function resetSelect(select, placeholder) {
                select.innerHTML = '<option value="">' + placeholder + '</option>';
            }

            function fillSelect(select, items, selectedValue) {
                items.forEach(function (item) {
                    const option = document.createElement('option');
                    option.value = item.name;
                    option.textContent = item.name;
                    option.dataset.id = item.id;

                    if (selectedValue && selectedValue === item.name) {
                        option.selected = true;
                    }

                    select.appendChild(option);
                });
            }

            async function loadCities(selectedCityValue = null, selectedBarangayValue = null) {
                resetSelect(citySelect, 'Select City');
                resetSelect(barangaySelect, 'Select Barangay');

                const selectedProvinceOption = provinceSelect.options[provinceSelect.selectedIndex];
                const provinceId = selectedProvinceOption ? selectedProvinceOption.dataset.id : '';

                if (!provinceId) {
                    return;
                }

                try {
                    const response = await fetch(citiesUrl + '?province_id=' + encodeURIComponent(provinceId), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const cities = await response.json();
                    fillSelect(citySelect, cities, selectedCityValue);

                    if (selectedCityValue) {
                        await loadBarangays(selectedBarangayValue);
                    }
                } catch (error) {
                    console.error('Failed to load cities:', error);
                }
            }

            async function loadBarangays(selectedBarangayValue = null) {
                resetSelect(barangaySelect, 'Select Barangay');

                const selectedCityOption = citySelect.options[citySelect.selectedIndex];
                const cityId = selectedCityOption ? selectedCityOption.dataset.id : '';

                if (!cityId) {
                    return;
                }

                try {
                    const response = await fetch(barangaysUrl + '?city_id=' + encodeURIComponent(cityId), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const barangays = await response.json();
                    fillSelect(barangaySelect, barangays, selectedBarangayValue);
                } catch (error) {
                    console.error('Failed to load barangays:', error);
                }
            }

            provinceSelect.addEventListener('change', function () {
                loadCities();
            });

            citySelect.addEventListener('change', function () {
                loadBarangays();
            });

            const selectedProvinceValue = provinceSelect.dataset.selectedValue || '';
            const selectedCityValue = citySelect.dataset.selectedValue || '';
            const selectedBarangayValue = barangaySelect.dataset.selectedValue || '';

            if (selectedProvinceValue) {
                loadCities(selectedCityValue, selectedBarangayValue);
            }
        });
    </script>
</x-app-layout>