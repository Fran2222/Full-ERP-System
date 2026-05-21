<x-app-layout :assets="$assets ?? []">

@php
    $canEditMatrix = auth()->user()->can('role.permission.edit');
    $canCreateRole = auth()->user()->can('roles.create');
    $canEditRole = auth()->user()->can('roles.edit');
    $canDeleteRole = auth()->user()->can('roles.delete');
    $canCreatePermission = auth()->user()->can('permissions.create');

    $groupedPermissions = collect($permissions)->groupBy(function ($permission) {
        $name = $permission->name ?? '';
        $module = explode('.', $name)[0] ?? 'general';
        return strtoupper(str_replace(['-', '_', '.'], ' ', $module));
    });

    $formatPermissionLabel = function ($permissionName) {
        return collect(explode('.', $permissionName))
            ->map(fn ($part) => ucfirst(str_replace(['-', '_'], ' ', $part)))
            ->implode(' ');
    };
@endphp

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <div class="header-title">
                    <h4 class="card-title mb-0">Role & Permission</h4>
                </div>

                <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
                    @if($canCreatePermission)
                        <a href="javascript:void(0);" class="btn btn-primary new-permission">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" class="me-1" viewBox="0 0 24 24" fill="none">
                                <path d="M12 5V19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            New Permission
                        </a>
                    @endif

                    @if($canCreateRole)
                        <a href="javascript:void(0);" class="btn btn-primary new-role">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" class="me-1" viewBox="0 0 24 24" fill="none">
                                <path d="M12 5V19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            New Role
                        </a>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <form action="{{ route('role.permission.store') }}" method="POST">
                        @csrf

                        <table class="table table-striped table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 260px;">PERMISSION</th>

                                    @foreach ($roles as $role)
                                        @php
                                            $roleName = strtolower($role->name ?? '');
                                            $isProtected = in_array($roleName, ['super admin', 'super-admin', 'superadmin']);
                                        @endphp

                                        <th class="text-center" style="min-width: 180px;">
                                            <div class="fw-bold text-uppercase mb-2">
                                                {{ $role->title ?? $role->name }}
                                            </div>

                                            <div class="d-flex flex-column align-items-center gap-2">
                                                @if(($role->status ?? 0) == 1)
                                                    <span class="badge bg-success">ACTIVE</span>
                                                @endif

                                                @if($isProtected)
                                                    <span class="badge bg-dark">PROTECTED</span>
                                                @else
                                                    <div class="d-flex justify-content-center gap-2">
                                                        @if($canEditRole)
                                                            <button type="button"
                                                                    class="btn btn-sm btn-primary edit-role"
                                                                    data-id="{{ $role->id }}"
                                                                    title="Edit Role">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" viewBox="0 0 24 24" fill="none">
                                                                    <path d="M13.7476 20.4428H21" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.0786 3.89261C12.8166 3.00261 14.1536 2.87861 15.0436 3.61661C15.0926 3.65761 16.6246 4.84961 16.6246 4.84961C17.6026 5.44061 17.9056 6.69861 17.3026 7.66761C17.2706 7.71961 8.33757 18.8936 8.33757 18.8936C8.03957 19.2656 7.58857 19.4846 7.11157 19.4896L3.47157 19.5356L2.65157 16.0566C2.54457 15.6016 2.65157 15.1236 2.94757 14.7526L12.0786 3.89261Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    <path d="M10.3721 6.03613L15.9341 10.3661" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                </svg>
                                                            </button>
                                                        @endif

                                                        @if($canDeleteRole)
                                                            <button type="button"
                                                                    class="btn btn-sm btn-danger delete-role"
                                                                    data-id="{{ $role->id }}"
                                                                    data-title="{{ $role->title ?? $role->name }}"
                                                                    title="Delete Role">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" viewBox="0 0 24 24" fill="none">
                                                                    <path d="M19.3248 9.46826C19.3248 9.46826 18.7368 16.7553 18.3958 19.8243C18.2338 21.2913 17.3258 22.1503 15.8408 22.1773C13.0148 22.2283 10.1848 22.2313 7.35977 22.1723C5.93077 22.1423 5.03977 21.2723 4.88077 19.8313C4.53777 16.7353 3.95277 9.46826 3.95277 9.46826" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    <path d="M20.823 5.98975H2.453" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    <path d="M17.2926 5.98975C16.4446 5.98975 15.7146 5.39075 15.5486 4.55875L15.2866 3.24775C15.1246 2.64175 14.5766 2.22175 13.9486 2.22175H9.3266C8.6986 2.22175 8.1506 2.64175 7.9886 3.24775L7.7266 4.55875C7.5606 5.39075 6.8306 5.98975 5.9826 5.98975" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($groupedPermissions as $module => $modulePermissions)
                                    <tr class="table-secondary">
                                        <td colspan="{{ count($roles) + 1 }}" class="fw-bold text-uppercase">
                                            {{ $module }}
                                        </td>
                                    </tr>

                                    @foreach ($modulePermissions as $permission)
                                        <tr>
                                            <td>
                                                {{ $formatPermissionLabel($permission->name) }}
                                            </td>

                                            @foreach ($roles as $role)
                                                @php
                                                    $roleName = strtolower($role->name ?? '');
                                                    $isProtected = in_array($roleName, ['super admin', 'super-admin', 'superadmin']);
                                                @endphp

                                                <td class="text-center">
                                                    <div class="form-check d-inline-flex justify-content-center">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="permissions[{{ $role->id }}][]"
                                                            value="{{ $permission->name }}"
                                                            {{ \App\Helpers\AuthHelper::checkRolePermission($role, $permission->name) ? 'checked' : '' }}
                                                            {{ (!$canEditMatrix || $isProtected) ? 'disabled' : '' }}
                                                        >
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>

                        @if($canEditMatrix)
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary">
                                    Save Changes
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</x-app-layout>