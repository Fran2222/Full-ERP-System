<x-app-layout :assets="$assets ?? []">
    @include('vehicle.partials.nav', ['active' => 'setup'])

    <style>
        .vm-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(31,45,61,.06);
        }

        .vm-control {
            min-height: 42px;
            border-radius: 10px;
        }

        .vm-table th {
            font-size: 12px;
            text-transform: uppercase;
            color: #64748b;
            background: #f5f7fb;
            border-bottom: 0;
            white-space: nowrap;
        }

        .vm-table td {
            vertical-align: middle;
            border-color: #eef2f7;
        }

        .vm-action-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
        }

        .vm-action-btn svg {
            width: 14px;
            height: 14px;
        }

        .vm-muted {
            color: #7b8794;
            font-size: 12px;
        }

        .vm-tabs {
            background: #fff;
            border-radius: 14px;
            padding: 10px;
            box-shadow: 0 6px 18px rgba(31,45,61,.04);
        }

        .vm-tabs .nav-link {
            border-radius: 999px;
            border: 1px solid #2f4dfd;
            color: #2f4dfd;
            padding: 7px 14px;
            margin-right: 6px;
            font-weight: 600;
            background: #fff;
        }

        .vm-tabs .nav-link.active {
            background: #2f4dfd;
            color: #fff;
        }

        .vm-inline-edit-row {
            background: #fbfcff;
        }

        .vm-setup-search {
            max-width: 360px;
        }
    </style>

    <div class="mb-3">
        <h3 class="mb-1">Vehicle Setup</h3>
        <p class="text-muted mb-0">Manage dropdown values used by Vehicle Management.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please check the form:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $activeSetupTab = session('vehicle_setup_tab', request('tab', 'vehicle-types'));
    @endphp

    <ul class="nav vm-tabs mb-3" id="vehicleSetupTabs" role="tablist">
        <li class="nav-item"><button class="nav-link {{ $activeSetupTab === 'vehicle-types' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#vehicleTypes" type="button" data-vm-tab="vehicle-types">Vehicle Types</button></li>
        <li class="nav-item"><button class="nav-link {{ $activeSetupTab === 'vehicle-statuses' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#vehicleStatuses" type="button" data-vm-tab="vehicle-statuses">Vehicle Statuses</button></li>
        <li class="nav-item"><button class="nav-link {{ $activeSetupTab === 'maintenance-types' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#maintenanceTypes" type="button" data-vm-tab="maintenance-types">Maintenance Types</button></li>
        <li class="nav-item"><button class="nav-link {{ $activeSetupTab === 'document-types' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#documentTypes" type="button" data-vm-tab="document-types">Document Types</button></li>
        <li class="nav-item"><button class="nav-link {{ $activeSetupTab === 'fuel-types' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#fuelTypes" type="button" data-vm-tab="fuel-types">Fuel Types</button></li>
    </ul>

    <div class="tab-content">
        @include('vehicle.setup.partials.setup-table', [
            'tabId' => 'vehicleTypes',
            'active' => $activeSetupTab === 'vehicle-types',
            'title' => 'Vehicle Types',
            'subtitle' => 'Examples: L300, Motorcycle, Truck, Sedan.',
            'group' => 'vehicle-types',
            'rows' => $vehicleTypes,
        ])

        @include('vehicle.setup.partials.setup-table', [
            'tabId' => 'vehicleStatuses',
            'active' => $activeSetupTab === 'vehicle-statuses',
            'title' => 'Vehicle Statuses',
            'subtitle' => 'Examples: Available, Assigned, In Use, Under Maintenance, Retired.',
            'group' => 'vehicle-statuses',
            'rows' => $vehicleStatuses,
        ])

        @include('vehicle.setup.partials.setup-table', [
            'tabId' => 'maintenanceTypes',
            'active' => $activeSetupTab === 'maintenance-types',
            'title' => 'Maintenance Types',
            'subtitle' => 'Examples: PMS, Repair, Change Oil, Tire Replacement.',
            'group' => 'maintenance-types',
            'rows' => $maintenanceTypes,
        ])

        @include('vehicle.setup.partials.setup-table', [
            'tabId' => 'documentTypes',
            'active' => $activeSetupTab === 'document-types',
            'title' => 'Document Types',
            'subtitle' => 'Examples: OR/CR, Registration, Insurance, Emission Test.',
            'group' => 'document-types',
            'rows' => $documentTypes,
        ])

        @include('vehicle.setup.partials.setup-table', [
            'tabId' => 'fuelTypes',
            'active' => $activeSetupTab === 'fuel-types',
            'title' => 'Fuel Types',
            'subtitle' => 'Examples: Diesel, Gasoline, Electric, Hybrid.',
            'group' => 'fuel-types',
            'rows' => $fuelTypes,
        ])
    </div>

    <script>
        (function () {
            document.querySelectorAll('[data-vm-setup-edit]').forEach(button => {
                button.addEventListener('click', function () {
                    const wrapper = this.closest('[data-vm-row]');
                    if (!wrapper) return;

                    wrapper.classList.add('vm-inline-edit-row');
                    wrapper.querySelectorAll('[data-vm-display]').forEach(el => el.classList.add('d-none'));
                    wrapper.querySelectorAll('[data-vm-edit]').forEach(el => el.classList.remove('d-none'));
                });
            });

            document.querySelectorAll('[data-vm-setup-cancel]').forEach(button => {
                button.addEventListener('click', function () {
                    const wrapper = this.closest('[data-vm-row]');
                    if (!wrapper) return;

                    wrapper.classList.remove('vm-inline-edit-row');
                    wrapper.querySelectorAll('[data-vm-display]').forEach(el => el.classList.remove('d-none'));
                    wrapper.querySelectorAll('[data-vm-edit]').forEach(el => el.classList.add('d-none'));
                });
            });

            document.querySelectorAll('[data-vm-setup-search]').forEach(input => {
                input.addEventListener('input', function () {
                    const target = document.querySelector(this.dataset.vmSetupSearch);
                    if (!target) return;

                    const keyword = this.value.trim().toLowerCase();

                    target.querySelectorAll('tbody tr[data-vm-row]').forEach(row => {
                        const text = row.innerText.toLowerCase();
                        row.style.display = !keyword || text.includes(keyword) ? '' : 'none';
                    });
                });
            });

            document.querySelectorAll('[data-vm-tab]').forEach(button => {
                button.addEventListener('shown.bs.tab', function () {
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', this.dataset.vmTab);
                    window.history.replaceState({}, '', url.toString());
                });
            });
        })();
    </script>
</x-app-layout>
