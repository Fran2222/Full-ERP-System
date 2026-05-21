@php
    use Illuminate\Support\Facades\Route;

    $user = auth()->user();
    $primaryModule = strtolower((string) ($user->primary_module ?? ''));
    $isAccountingPrimary = $primaryModule === 'accounting';
    /*
    |--------------------------------------------------------------------------
    | Access Separation
    |--------------------------------------------------------------------------
    | Merged from the HR module sidebar, while keeping the deployed/main system
    | module links intact. This separates Super Admin/Admin, HR Management,
    | Department Head/Supervisor, and Employee Self-Service sidebars.
    */
    $isSuperAdmin = $user && $user->hasAnyRole([
        'Super Admin',
        'Super Administrator',
        'super admin',
        'super-admin',
        'superadmin',
    ]);

    $isAdminAccess = $user && $user->hasAnyRole([
        'Super Admin',
        'Super Administrator',
        'Admin',
        'super admin',
        'super-admin',
        'superadmin',
        'admin',
    ]);

    $isDepartmentHeadAccess = $user
        && ! $isAdminAccess
        && (
            $user->hasAnyRole([
                'Department Head',
                'department head',
                'department-head',
                'department_head',
                'departmenthead',
                'Supervisor',
                'supervisor',
                'Head',
                'head',
            ])
            || (method_exists($user, 'supervisedEmployees') && $user->supervisedEmployees()->exists())
        );

    $isHrAccess = $user && (
        $user->hasAnyRole(['HR', 'Hr', 'hr', 'Human Resource', 'Human Resources'])
        || strtolower((string) ($user->primary_module ?? '')) === 'hr'
        || $user->can('hr.view')
        || $user->can('hr.dashboard.view')
        || $user->can('hr.employees.view')
        || $user->can('hr.leave.view')
        || $user->can('hr.leave.own.view')
        || $user->can('hr.attendance.view')
        || $user->can('hr.attendance.own.view')
        || $user->can('hr.payroll.view')
        || $user->can('hr.payroll.own.payslip.view')
        || $user->can('hr.evaluation.view')
        || $user->can('hr.evaluation.own.result.view')
        || $isDepartmentHeadAccess
    );

    $useHrAsMainDashboard = $user
        && ! $isAccountingPrimary
        && (
            $user->can('hr.view')
            || $user->can('hr.dashboard.view')
            || strtolower((string) ($user->primary_module ?? '')) === 'hr'
            || $user->hasAnyRole(['HR', 'Hr', 'hr', 'employee', 'user', 'Department Head', 'department head', 'department-head', 'supervisor'])
            || $isDepartmentHeadAccess
        );

    $isEmployeeSelfService = $user
        && ! $isAdminAccess
        && ! $user->can('hr.employees.view')
        && (! $user->can('hr.leave.requests.view') || $isDepartmentHeadAccess)
        && ! $user->can('hr.attendance.view')
        && ! $user->can('hr.payroll.view')
        && ! $user->can('hr.evaluation.view')
        && ! $isAccountingPrimary
        && (
            $isDepartmentHeadAccess
            || $user->hasAnyRole(['employee', 'user', 'Department Head', 'department head', 'department-head', 'department_head', 'departmenthead', 'supervisor', 'head'])
            || $user->can('hr.leave.own.view')
            || $user->can('hr.attendance.own.view')
            || $user->can('hr.payroll.own.payslip.view')
            || $user->can('hr.evaluation.own.result.view')
        );

    $isHrManagementAccess = $user
        && ! $isAdminAccess
        && ! $isEmployeeSelfService
        && ! $isDepartmentHeadAccess
        && (
            $user->hasAnyRole(['HR', 'Hr', 'hr', 'Human Resource', 'Human Resources'])
            || $user->can('hr.employees.view')
            || $user->can('hr.leave.requests.view')
            || $user->can('hr.attendance.view')
            || $user->can('hr.payroll.view')
            || $user->can('hr.evaluation.view')
        );

    $isHrFocusedSidebar = $isHrManagementAccess || $isEmployeeSelfService || $isDepartmentHeadAccess;
    $isHrAdminSidebar = $isHrManagementAccess;
    $isHrEmployeeSidebar = $isEmployeeSelfService || $isDepartmentHeadAccess;

    $canAny = function (array $permissions = []) use ($user, $isAdminAccess) {
        if (! $user) {
            return false;
        }

        if ($isAdminAccess) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    };

    $safeRoute = function (string $name, array $parameters = [], string $fallback = '#') {
        return Route::has($name) ? route($name, $parameters) : $fallback;
    };

    $dashboardRoute = $useHrAsMainDashboard
        ? $safeRoute('hr.dashboard', [], $safeRoute('dashboard'))
        : $safeRoute('dashboard');

    $dashboardActive = $useHrAsMainDashboard
        ? (
            request()->routeIs('hr.dashboard')
            || request()->routeIs('hr.overview')
            || request()->routeIs('module.hr')
            || request()->routeIs('module.hr.overview')
            || request()->is('hr')
            || request()->is('hr/dashboard')
            || request()->is('module/hr')
        )
        : request()->routeIs('dashboard');

    $showDashboard = auth()->check() && ($isAdminAccess || $isHrFocusedSidebar || $canAny(['dashboard.view', 'hr.dashboard.view', 'hr.view']));

    $showBranches = $isAdminAccess || $canAny([
        'branches.view', 'branches.create', 'branches.edit', 'branches.delete',
        'hr.branches.view', 'hr.branches.create', 'hr.branches.edit', 'hr.branches.delete',
    ]);

    $showDepartments = $isAdminAccess || $canAny([
        'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
        'hr.departments.view', 'hr.departments.create', 'hr.departments.edit', 'hr.departments.delete',
    ]);

    $showDesignations = $isAdminAccess || $canAny([
        'designations.view', 'designations.create', 'designations.edit', 'designations.delete',
        'hr.designations.view', 'hr.designations.create', 'hr.designations.edit', 'hr.designations.delete',
    ]);

    $showEmployeeList = $isAdminAccess || $canAny([
        'hr.employees.view', 'hr.employees.create', 'hr.employees.edit', 'hr.employees.delete',
        'hr.employees.documents.view', 'hr.employees.documents.upload', 'hr.employees.documents.delete',
        'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
    ]);
    $showHrEmployees = $showEmployeeList;
    $showEmployees = $showEmployeeList;

    $showRolesPermissions = $isAdminAccess || $canAny(['roles.view', 'permissions.view', 'role.permission.view']);

    $showOrganization = $showBranches
        || $showDepartments
        || $showDesignations
        || $showEmployeeList
        || $showRolesPermissions;

    $showAnnouncements = $isAdminAccess || $isHrFocusedSidebar || $canAny([
        'announcements.view', 'announcements.create', 'announcements.edit', 'announcements.delete',
    ]);

    $showHrLeave = $isAdminAccess || $isHrFocusedSidebar || $isDepartmentHeadAccess || $canAny([
        'hr.leave.view',
        'hr.leave.apply',
        'hr.leave.own.view',
        'hr.leave.credits.view',
        'hr.leave.requests.view',
        'hr.leave-types.view',
        'hr.leave.approve',
        'hr.leave.reject',
    ]);

    if ($isSuperAdmin) {
        // Super Admin is system administration only: hide employee self-service leave menus.
        $canApplyLeaveMenu = false;
        $canViewOwnLeaveMenu = false;
        $canViewLeaveCreditsMenu = false;
        $canViewLeaveRequestsMenu = false;
        $canViewLeaveTypesMenu = $canAny(['hr.leave-types.view']);
        $canViewLeaveCreditManagementMenu = $canAny(['hr.leave.requests.view', 'hr.leave.credits.view', 'hr.leave-types.view']);
    } else {
        $canApplyLeaveMenu = $canAny(['hr.leave.apply']);
        $canViewOwnLeaveMenu = $canAny(['hr.leave.view', 'hr.leave.own.view']);
        $canViewLeaveCreditsMenu = $canAny(['hr.leave.view', 'hr.leave.own.view', 'hr.leave.credits.view']);
        $canViewLeaveRequestsMenu = $isDepartmentHeadAccess || $canAny(['hr.leave.requests.view']);
        $canViewLeaveTypesMenu = $canAny(['hr.leave-types.view']);
        $canViewLeaveCreditManagementMenu = $isHrManagementAccess && $canAny(['hr.leave.requests.view', 'hr.leave.credits.view', 'hr.leave-types.view']);
    }

    $showHrEvaluation = $isAdminAccess || $isHrFocusedSidebar || $canAny([
        'hr.evaluation.view',
        'hr.evaluation.create',
        'hr.evaluation.edit',
        'hr.evaluation.delete',
        'hr.evaluation.result.view',
        'hr.evaluation.own.result.view',
    ]);

    $showHrAttendance = $isAdminAccess || $isHrFocusedSidebar || $canAny(['hr.attendance.view', 'hr.attendance.own.view']);
    $showHrPayroll = $isAdminAccess || $isHrFocusedSidebar || $canAny(['hr.payroll.view', 'hr.payroll.payslip.view', 'hr.payroll.own.payslip.view']);
    $showHrTravelOrders = $isAdminAccess || $isHrFocusedSidebar || $canAny(['hr.view', 'accounting.view']);
    $showHrOvertimeRequests = $isAdminAccess || $isHrFocusedSidebar || $canAny(['hr.view', 'accounting.view']);

    $showHr = ($isAdminAccess || $isHrAccess || $isHrFocusedSidebar || $canAny(['hr.view', 'accounting.view'])) && (
        $showAnnouncements
        || $showHrEmployees
        || $showHrAttendance
        || $showHrLeave
        || $showHrEvaluation
        || $showHrPayroll
        || $showHrTravelOrders
        || $showHrOvertimeRequests
    );

    if ($isHrManagementAccess || $isEmployeeSelfService || $isDepartmentHeadAccess) {
        $showInventory = false;
        $showWarehouse = false;
        $showPurchasing = false;
        $showSales = false;
        $showAccounting = $isAdminAccess || $canAny([
            'accounting.view',
            'accounting.create',
            'accounting.edit',
            'accounting.delete',
        ]);
        $showProjectManagement = false;
    } else {
        $showInventory = $isAdminAccess || $canAny([
            'warehouse.inventory.view',
            'warehouse.stock_in.create',
            'warehouse.stock_out.create',
            'warehouse.transfer.create',
            'warehouse.adjustment.create',
            'warehouse.ledger.view',
        ]);

        $showWarehouse = $isAdminAccess || $canAny([
            'warehouse.dashboard.view',
            'warehouse.categories.view',
            'warehouse.units.view',
            'warehouse.suppliers.view',
            'warehouse.locations.view',
            'warehouse.items.view',
            'warehouse.inventory.view',
            'warehouse.stock_in.create',
            'warehouse.stock_out.create',
            'warehouse.transfer.create',
            'warehouse.adjustment.create',
            'warehouse.ledger.view',
        ]);

        $showPurchasing = $isAdminAccess || $canAny([
            'purchasing.dashboard.view',
            'purchasing.po.view',
            'purchasing.receiving.view',
        ]);

        $showSales = $isAdminAccess || $canAny([
            'sales.dashboard.view',
            'sales.customers.view',
            'sales.invoices.view',
            'sales.receipts.view',
            'sales.payments.view',
        ]);

        $showAccounting = $isAdminAccess || $canAny([
            'accounting.view',
            'accounting.dashboard.view',
            'accounting.accounts.view',
            'accounting.journal_entries.view',
            'accounting.general_ledger.view',
            'accounting.bank_accounts.view',
            'accounting.collections.view',
            'accounting.expenses.view',
            'accounting.reports.view',
        ]);

        $showProjectManagement = $isAdminAccess || $canAny([
            'project_management.view',
            'project-management.view',
            'projects.view',
            'project.dashboard.view',
            'project_management.dashboard.view',
        ]);
    }

    $showUsersMenu = $isAdminAccess || $canAny(['users.view', 'users.create', 'users.edit', 'users.delete']);
    $showUserProfile = $isAdminAccess || auth()->check();
    $showAddUser = $isAdminAccess || $canAny(['users.create']);
    $showUserList = $isAdminAccess || $canAny(['users.view']);

    $organizationMenuOpen = request()->is('branches')
        || request()->is('branches/*')
        || request()->is('departments')
        || request()->is('departments/*')
        || request()->is('designations')
        || request()->is('designations/*')
        || request()->is('hr/employees')
        || request()->is('hr/employees/*')
        || request()->routeIs('hr.employees.*')
        || request()->routeIs('role.permission.index')
        || request()->routeIs('role.*')
        || request()->routeIs('permission.*');

    $hrMenuOpen = request()->is('hr')
        || request()->is('hr/*')
        || request()->is('announcements')
        || request()->is('announcements/*');

    $hrLeaveMenuOpen = request()->is('hr/leave')
        || request()->is('hr/leave/*')
        || request()->is('hr/leave-types')
        || request()->is('hr/leave-types/*')
        || request()->routeIs('hr.leave.*')
        || request()->routeIs('hr.leave-types.*');

    $hrEvaluationMenuOpen = request()->is('hr/evaluation')
        || request()->is('hr/evaluation/*')
        || request()->routeIs('hr.evaluation.*');

    $warehouseMenuOpen = request()->is('warehouse') || request()->is('warehouse/*');
    $salesMenuOpen = request()->is('sales') || request()->is('sales/*');
    $purchasingMenuOpen = request()->is('purchasing') || request()->is('purchasing/*');
    $accountingMenuOpen = request()->is('accounting') || request()->is('accounting/*') || request()->routeIs('accounting.*');
    $usersMenuOpen = request()->is('users') || request()->is('users/*');
@endphp

<ul class="navbar-nav iq-main-menu" id="sidebar">

@if($isHrEmployeeSidebar)
    {{-- =========================================================
       HR EMPLOYEE SELF-SERVICE SIDEBAR
       Matches employee dashboard layout: self-service only.
       ========================================================= --}}

    <li class="nav-item static-item">
        <a class="nav-link static-item disabled" href="#" tabindex="-1">
            <span class="default-icon">Home</span>
            <span class="mini-icon">-</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ $dashboardActive ? 'active' : '' }}" href="{{ $dashboardRoute }}">
            <i class="icon">
                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.4" d="M16.0756 2H19.4616C20.8639 2 22.0001 3.14585 22.0001 4.55996V7.97452C22.0001 9.38864 20.8639 10.5345 19.4616 10.5345H16.0756C14.6734 10.5345 13.5371 9.38864 13.5371 7.97452V4.55996C13.5371 3.14585 14.6734 2 16.0756 2Z" fill="currentColor"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.53852 2H7.92449C9.32676 2 10.463 3.14585 10.463 4.55996V7.97452C10.463 9.38864 9.32676 10.5345 7.92449 10.5345H4.53852C3.13626 10.5345 2 9.38864 2 7.97452V4.55996C2 3.14585 3.13626 2 4.53852 2ZM4.53852 13.4655H7.92449C9.32676 13.4655 10.463 14.6114 10.463 16.0255V19.44C10.463 20.8532 9.32676 22 7.92449 22H4.53852C3.13626 22 2 20.8532 2 19.44V16.0255C2 14.6114 3.13626 13.4655 4.53852 13.4655ZM19.4615 13.4655H16.0755C14.6732 13.4655 13.537 14.6114 13.537 16.0255V19.44C13.537 20.8532 14.6732 22 16.0755 22H19.4615C20.8637 22 22 20.8532 22 19.44V16.0255C22 14.6114 20.8637 13.4655 19.4615 13.4655Z" fill="currentColor"/>
                </svg>
            </i>
            <span class="item-name">Dashboard</span>
        </a>
    </li>

    <li class="nav-item static-item">
        <a class="nav-link static-item disabled" href="#" tabindex="-1">
            <span class="default-icon">General</span>
            <span class="mini-icon">-</span>
        </a>
    </li>

    @if($showHrEvaluation)
    <li class="nav-item">
        <a class="nav-link {{ $hrEvaluationMenuOpen ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#employeeEvaluationMenu" role="button" aria-expanded="{{ $hrEvaluationMenuOpen ? 'true' : 'false' }}" aria-controls="employeeEvaluationMenu">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 19V5C4 3.9 4.9 3 6 3H18C19.1 3 20 3.9 20 5V19C20 20.1 19.1 21 18 21H6C4.9 21 4 20.1 4 19Z" stroke="currentColor" stroke-width="1.7"/><path d="M8 8H16M8 12H16M8 16H12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i>
            <span class="item-name">My Evaluations</span>
            <i class="right-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></i>
        </a>
        <ul class="sub-nav collapse {{ $hrEvaluationMenuOpen ? 'show' : '' }}" id="employeeEvaluationMenu" data-bs-parent="#sidebar">
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.evaluation.index') || request()->routeIs('hr.evaluation.my.evaluate') || request()->routeIs('hr.evaluation.my.submit') ? 'active' : '' }}" href="{{ $safeRoute('hr.evaluation.index') }}"><i class="icon"><svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 12L11 15L16 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 4H19V20H5V4Z" stroke="currentColor" stroke-width="1.8"/></svg></i><span class="item-name">Assigned to Me</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.evaluation.my.results') || request()->routeIs('hr.evaluation.my.results.show') ? 'active' : '' }}" href="{{ $safeRoute('hr.evaluation.my.results') }}"><i class="icon"><svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 19V5M5 19H19M8 16L11 12L14 14L18 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></i><span class="item-name">My Results</span></a></li>
        </ul>
    </li>
    @endif

    <li class="nav-item">
        <a class="nav-link {{ $hrLeaveMenuOpen ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#employeeLeaveMenu" role="button" aria-expanded="{{ $hrLeaveMenuOpen ? 'true' : 'false' }}" aria-controls="employeeLeaveMenu">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 3V6M17 3V6M4 9H20" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M5 5H19C20.1 5 21 5.9 21 7V19C21 20.1 20.1 21 19 21H5C3.9 21 3 20.1 3 19V7C3 5.9 3.9 5 5 5Z" stroke="currentColor" stroke-width="1.7"/></svg></i>
            <span class="item-name">Leave</span>
            <i class="right-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></i>
        </a>
        <ul class="sub-nav collapse {{ $hrLeaveMenuOpen ? 'show' : '' }}" id="employeeLeaveMenu" data-bs-parent="#sidebar">
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.leave.file') ? 'active' : '' }}" href="{{ $safeRoute('hr.leave.file', [], $safeRoute('hr.leave.index')) }}"><i class="icon"><svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></i><span class="item-name">File A Leave</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.leave.history') ? 'active' : '' }}" href="{{ $safeRoute('hr.leave.history', [], $safeRoute('hr.leave.index')) }}"><i class="icon"><svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 8V12L15 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/></svg></i><span class="item-name">My Leave History</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.leave.credits') ? 'active' : '' }}" href="{{ $safeRoute('hr.leave.credits', [], $safeRoute('hr.leave.index')) }}"><i class="icon"><svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 7H20V17H4V7Z" stroke="currentColor" stroke-width="1.8"/><path d="M8 12H16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></i><span class="item-name">My Leave Credits</span></a></li>
            @if($canViewLeaveRequestsMenu)
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.leave.requests') ? 'active' : '' }}" href="{{ $safeRoute('hr.leave.requests') }}"><i class="icon"><svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 4H18V20H6V4Z" stroke="currentColor" stroke-width="1.8"/><path d="M9 9H15M9 13H15M9 17H12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></i><span class="item-name">Leave Requests</span></a></li>
            @endif
        </ul>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('hr.attendance.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.attendance.index') }}">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.7"/><path d="M12 8V12L14.5 14.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i>
            <span class="item-name">My Attendance</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('hr.travel-orders.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.travel-orders.index') }}">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21C12 21 19 14.5 19 9C19 5.1 15.9 2 12 2C8.1 2 5 5.1 5 9C5 14.5 12 21 12 21Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="1.7"/></svg></i>
            <span class="item-name">Travel Order</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('hr.overtime-requests.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.overtime-requests.index') }}">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.7"/><path d="M12 7V12H16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i>
            <span class="item-name">Overtime Request</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('hr.payroll.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.payroll.index') }}">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.7"/><path d="M12 7V17M9 10C9 8.8 10.2 8 12 8C13.8 8 15 8.8 15 10C15 11.4 13.8 12 12 12C10.2 12 9 12.6 9 14C9 15.2 10.2 16 12 16C13.8 16 15 15.2 15 14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i>
            <span class="item-name">My Payslip</span>
        </a>
    </li>

@elseif($isHrAdminSidebar)
    {{-- =========================================================
       HR ROLE SIDEBAR
       This layout matches the original HR module sidebar.
       ========================================================= --}}

    <li class="nav-item static-item">
        <a class="nav-link static-item disabled" href="#" tabindex="-1">
            <span class="default-icon">Overview</span>
            <span class="mini-icon">-</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ $dashboardActive ? 'active' : '' }}" href="{{ $dashboardRoute }}">
            <i class="icon">
                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.4" d="M16.0756 2H19.4616C20.8639 2 22.0001 3.14585 22.0001 4.55996V7.97452C22.0001 9.38864 20.8639 10.5345 19.4616 10.5345H16.0756C14.6734 10.5345 13.5371 9.38864 13.5371 7.97452V4.55996C13.5371 3.14585 14.6734 2 16.0756 2Z" fill="currentColor"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.53852 2H7.92449C9.32676 2 10.463 3.14585 10.463 4.55996V7.97452C10.463 9.38864 9.32676 10.5345 7.92449 10.5345H4.53852C3.13626 10.5345 2 9.38864 2 7.97452V4.55996C2 3.14585 3.13626 2 4.53852 2ZM4.53852 13.4655H7.92449C9.32676 13.4655 10.463 14.6114 10.463 16.0255V19.44C10.463 20.8532 9.32676 22 7.92449 22H4.53852C3.13626 22 2 20.8532 2 19.44V16.0255C2 14.6114 3.13626 13.4655 4.53852 13.4655ZM19.4615 13.4655H16.0755C14.6732 13.4655 13.537 14.6114 13.537 16.0255V19.44C13.537 20.8532 14.6732 22 16.0755 22H19.4615C20.8637 22 22 20.8532 22 19.44V16.0255C22 14.6114 20.8637 13.4655 19.4615 13.4655Z" fill="currentColor"/>
                </svg>
            </i>
            <span class="item-name">Dashboard</span>
        </a>
    </li>

    <li class="nav-item static-item">
        <a class="nav-link static-item disabled" href="#" tabindex="-1">
            <span class="default-icon">Organization</span>
            <span class="mini-icon">-</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->is('branches') || request()->is('branches/*') ? 'active' : '' }}" href="{{ $safeRoute('branches.index') }}">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 11H5C3.9 11 3 11.9 3 13V20H9V13C9 11.9 8.1 11 7 11Z" stroke="currentColor" stroke-width="1.7"/><path d="M19 8H17C15.9 8 15 8.9 15 10V20H21V10C21 8.9 20.1 8 19 8Z" stroke="currentColor" stroke-width="1.7"/><path d="M13 4H11C9.9 4 9 4.9 9 6V20H15V6C15 4.9 14.1 4 13 4Z" stroke="currentColor" stroke-width="1.7"/></svg></i>
            <span class="item-name">Branches</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->is('departments') || request()->is('departments/*') ? 'active' : '' }}" href="{{ $safeRoute('departments.index') }}">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 21V6C4 4.9 4.9 4 6 4H14C15.1 4 16 4.9 16 6V21" stroke="currentColor" stroke-width="1.7"/><path d="M16 10H19C20.1 10 21 10.9 21 12V21" stroke="currentColor" stroke-width="1.7"/><path d="M8 8H12M8 12H12M8 16H12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i>
            <span class="item-name">Departments</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->is('designations') || request()->is('designations/*') ? 'active' : '' }}" href="{{ $safeRoute('designations.index') }}">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 7V5C8 3.9 8.9 3 10 3H14C15.1 3 16 3.9 16 5V7" stroke="currentColor" stroke-width="1.7"/><path d="M5 7H19C20.1 7 21 7.9 21 9V19C21 20.1 20.1 21 19 21H5C3.9 21 3 20.1 3 19V9C3 7.9 3.9 7 5 7Z" stroke="currentColor" stroke-width="1.7"/><path d="M9 13H15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i>
            <span class="item-name">Designations</span>
        </a>
    </li>

    <li class="nav-item static-item">
        <a class="nav-link static-item disabled" href="#" tabindex="-1">
            <span class="default-icon">Employee Management</span>
            <span class="mini-icon">-</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('hr.employees.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.employees.index') }}">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="0.4" d="M17.5 21C17.5 18.5 15 16.5 12 16.5C9 16.5 6.5 18.5 6.5 21" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M12 13C14.2 13 16 11.2 16 9C16 6.8 14.2 5 12 5C9.8 5 8 6.8 8 9C8 11.2 9.8 13 12 13Z" stroke="currentColor" stroke-width="1.7"/></svg></i>
            <span class="item-name">Employee List</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('hr.attendance.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.attendance.index') }}">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.7"/><path d="M12 8V12L14.5 14.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i>
            <span class="item-name">Attendance</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('hr.travel-orders.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.travel-orders.index') }}">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21C12 21 19 14.5 19 9C19 5.1 15.9 2 12 2C8.1 2 5 5.1 5 9C5 14.5 12 21 12 21Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="1.7"/></svg></i>
            <span class="item-name">Travel Order</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('hr.overtime-requests.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.overtime-requests.index') }}">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.7"/><path d="M12 7V12H16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i>
            <span class="item-name">Overtime Request</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ $hrLeaveMenuOpen ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#hrLeaveMenu" role="button" aria-expanded="{{ $hrLeaveMenuOpen ? 'true' : 'false' }}" aria-controls="hrLeaveMenu">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 3V6M17 3V6M4 9H20" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M5 5H19C20.1 5 21 5.9 21 7V19C21 20.1 20.1 21 19 21H5C3.9 21 3 20.1 3 19V7C3 5.9 3.9 5 5 5Z" stroke="currentColor" stroke-width="1.7"/></svg></i>
            <span class="item-name">Leave</span>
            <i class="right-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></i>
        </a>
        <ul class="sub-nav collapse {{ $hrLeaveMenuOpen ? 'show' : '' }}" id="hrLeaveMenu" data-bs-parent="#sidebar">
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.leave.file') ? 'active' : '' }}" href="{{ $safeRoute('hr.leave.file', [], $safeRoute('hr.leave.index')) }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 3V6M17 3V6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M4 9H20" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M6 5H18C19.1 5 20 5.9 20 7V19C20 20.1 19.1 21 18 21H6C4.9 21 4 20.1 4 19V7C4 5.9 4.9 5 6 5Z" stroke="currentColor" stroke-width="1.7"/><path d="M12 12V18M9 15H15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i><span class="item-name">File A Leave</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.leave.history') ? 'active' : '' }}" href="{{ $safeRoute('hr.leave.history', [], $safeRoute('hr.leave.index')) }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 3V6M17 3V6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M4 9H20" stroke="currentColor" stroke-width="1.7"/><path d="M6 5H18C19.1 5 20 5.9 20 7V19C20 20.1 19.1 21 18 21H6C4.9 21 4 20.1 4 19V7C4 5.9 4.9 5 6 5Z" stroke="currentColor" stroke-width="1.7"/><path d="M9 15L11 17L15 13" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></i><span class="item-name">My Leave History</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.leave.credits') ? 'active' : '' }}" href="{{ $safeRoute('hr.leave.credits', [], $safeRoute('hr.leave.index')) }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 7H19C20.1 7 21 7.9 21 9V17C21 18.1 20.1 19 19 19H5C3.9 19 3 18.1 3 17V9C3 7.9 3.9 7 5 7Z" stroke="currentColor" stroke-width="1.7"/><path d="M7 12H13M7 15H10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M17 12V16M15 14H19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i><span class="item-name">My Leave Credits</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.leave.requests') ? 'active' : '' }}" href="{{ $safeRoute('hr.leave.requests') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 4H16C17.1 4 18 4.9 18 6V20H6V6C6 4.9 6.9 4 8 4Z" stroke="currentColor" stroke-width="1.7"/><path d="M9 8H15M9 12H15M9 16H12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i><span class="item-name">Leave Requests</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.leave-types.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.leave-types.index') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 12L12 4H20V12L12 20L4 12Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="16" cy="8" r="1.5" fill="currentColor"/></svg></i><span class="item-name">Leave Types</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.leave.credit-management') || request()->routeIs('hr.leave.credit-management.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.leave.credit-management', [], $safeRoute('hr.leave.credits', [], $safeRoute('hr.leave.index'))) }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 7H19C20.1 7 21 7.9 21 9V17C21 18.1 20.1 19 19 19H5C3.9 19 3 18.1 3 17V9C3 7.9 3.9 7 5 7Z" stroke="currentColor" stroke-width="1.7"/><path d="M7 12H13M7 15H10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M16 13L17.3 14.3L20 11.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></i><span class="item-name">Leave Credits</span></a></li>
        </ul>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ $hrEvaluationMenuOpen ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#hrEvaluationMenu" role="button" aria-expanded="{{ $hrEvaluationMenuOpen ? 'true' : 'false' }}" aria-controls="hrEvaluationMenu">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 19V5C4 3.9 4.9 3 6 3H18C19.1 3 20 3.9 20 5V19C20 20.1 19.1 21 18 21H6C4.9 21 4 20.1 4 19Z" stroke="currentColor" stroke-width="1.7"/><path d="M8 8H16M8 12H16M8 16H12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i>
            <span class="item-name">Evaluation</span>
            <i class="right-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></i>
        </a>
        <ul class="sub-nav collapse {{ $hrEvaluationMenuOpen ? 'show' : '' }}" id="hrEvaluationMenu" data-bs-parent="#sidebar">
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.evaluation.forms.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.evaluation.forms.index') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 3H14L19 8V21H7C5.9 21 5 20.1 5 19V5C5 3.9 5.9 3 7 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M14 3V8H19" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 12H15M9 16H15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i><span class="item-name">Forms</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.evaluation.center.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.evaluation.center.index') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.7"/><path d="M8.5 13.5L11 16L16 9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></i><span class="item-name">Evaluation Center</span></a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.evaluation.performance-summary.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.evaluation.performance-summary.index') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 19V5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M5 19H19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M8 16L11 12L14 14L18 8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></i><span class="item-name">Results Report</span></a></li>
            @if(!$isSuperAdmin)
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.evaluation.index') || request()->routeIs('hr.evaluation.my.evaluate') || request()->routeIs('hr.evaluation.my.submit') ? 'active' : '' }}" href="{{ $safeRoute('hr.evaluation.index') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 12L11 15L16 9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 4H19V20H5V4Z" stroke="currentColor" stroke-width="1.7"/></svg></i><span class="item-name">Assigned to Me</span></a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.evaluation.my.results') || request()->routeIs('hr.evaluation.my.results.show') ? 'active' : '' }}" href="{{ $safeRoute('hr.evaluation.my.results') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 19V5M5 19H19M8 16L11 12L14 14L18 8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></i><span class="item-name">My Results</span></a></li>
            @endif
        </ul>
    </li>

    <li class="nav-item static-item">
        <a class="nav-link static-item disabled" href="#" tabindex="-1">
            <span class="default-icon">Communication</span>
            <span class="mini-icon">-</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->is('announcements') || request()->is('announcements/*') ? 'active' : '' }}" href="{{ $safeRoute('announcements.index') }}">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 14V10L15 5V19L4 14Z" stroke="currentColor" stroke-width="1.7"/><path d="M4 14L6 20H9L7 15" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M18 9C19 10 19 14 18 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i>
            <span class="item-name">Announcements</span>
        </a>
    </li>

    <li class="nav-item static-item">
        <a class="nav-link static-item disabled" href="#" tabindex="-1">
            <span class="default-icon">Compensation</span>
            <span class="mini-icon">-</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('hr.payroll.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.payroll.index') }}">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.7"/><path d="M12 7V17M9 10C9 8.8 10.2 8 12 8C13.8 8 15 8.8 15 10C15 11.4 13.8 12 12 12C10.2 12 9 12.6 9 14C9 15.2 10.2 16 12 16C13.8 16 15 15.2 15 14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i>
            <span class="item-name">Payroll</span>
        </a>
    </li>

@else
    {{-- =========================================================
       ADMIN / SUPER ADMIN / GENERAL SIDEBAR
       ========================================================= --}}

    <li class="nav-item static-item">
        <a class="nav-link static-item disabled" href="#" tabindex="-1">
            <span class="default-icon">Home</span>
            <span class="mini-icon">-</span>
        </a>
    </li>

    @if($showDashboard)
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ $safeRoute('dashboard') }}">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="0.4" d="M16.0756 2H19.4616C20.8639 2 22.0001 3.14585 22.0001 4.55996V7.97452C22.0001 9.38864 20.8639 10.5345 19.4616 10.5345H16.0756C14.6734 10.5345 13.5371 9.38864 13.5371 7.97452V4.55996C13.5371 3.14585 14.6734 2 16.0756 2Z" fill="currentColor"/><path fill-rule="evenodd" clip-rule="evenodd" d="M4.53852 2H7.92449C9.32676 2 10.463 3.14585 10.463 4.55996V7.97452C10.463 9.38864 9.32676 10.5345 7.92449 10.5345H4.53852C3.13626 10.5345 2 9.38864 2 7.97452V4.55996C2 3.14585 3.13626 2 4.53852 2ZM4.53852 13.4655H7.92449C9.32676 13.4655 10.463 14.6114 10.463 16.0255V19.44C10.463 20.8532 9.32676 22 7.92449 22H4.53852C3.13626 22 2 20.8532 2 19.44V16.0255C2 14.6114 3.13626 13.4655 4.53852 13.4655ZM19.4615 13.4655H16.0755C14.6732 13.4655 13.537 14.6114 13.537 16.0255V19.44C13.537 20.8532 14.6732 22 16.0755 22H19.4615C20.8637 22 22 20.8532 22 19.44V16.0255C22 14.6114 20.8637 13.4655 19.4615 13.4655Z" fill="currentColor"/></svg></i>
            <span class="item-name">Dashboard</span>
        </a>
    </li>
    @endif

    @if($showOrganization)
    <li class="nav-item">
        <a class="nav-link {{ $organizationMenuOpen ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#sidebar-organization" role="button" aria-expanded="{{ $organizationMenuOpen ? 'true' : 'false' }}" aria-controls="sidebar-organization">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 21V6C4 4.9 4.9 4 6 4H14C15.1 4 16 4.9 16 6V21" stroke="currentColor" stroke-width="1.7"/><path d="M16 10H19C20.1 10 21 10.9 21 12V21" stroke="currentColor" stroke-width="1.7"/><path d="M8 8H12M8 12H12M8 16H12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i>
            <span class="item-name">Organization</span>
            <i class="right-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></i>
        </a>
<ul class="sub-nav collapse {{ $organizationMenuOpen ? 'show' : '' }}" id="sidebar-organization" data-bs-parent="#sidebar">
    @if($showBranches)
        <li class="nav-item">
            <a class="nav-link {{ request()->is('branches') || request()->is('branches/*') ? 'active' : '' }}" href="{{ $safeRoute('branches.index') }}">
                <i class="icon">
                    <svg width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 21V10M18 21V10M12 21V4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        <path d="M4 10H8M16 10H20M10 4H14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        <circle cx="6" cy="7" r="2" stroke="currentColor" stroke-width="1.7"/>
                        <circle cx="18" cy="7" r="2" stroke="currentColor" stroke-width="1.7"/>
                        <circle cx="12" cy="3" r="1.5" stroke="currentColor" stroke-width="1.7"/>
                    </svg>
                </i>
                <span class="item-name">Branches</span>
            </a>
        </li>
    @endif

    @if($showDepartments)
        <li class="nav-item">
            <a class="nav-link {{ request()->is('departments') || request()->is('departments/*') ? 'active' : '' }}" href="{{ $safeRoute('departments.index') }}">
                <i class="icon">
                    <svg width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 21V6C4 4.9 4.9 4 6 4H14C15.1 4 16 4.9 16 6V21" stroke="currentColor" stroke-width="1.7"/>
                        <path d="M16 10H19C20.1 10 21 10.9 21 12V21" stroke="currentColor" stroke-width="1.7"/>
                        <path d="M8 8H12M8 12H12M8 16H12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                </i>
                <span class="item-name">Departments</span>
            </a>
        </li>
    @endif

    @if($showDesignations)
        <li class="nav-item">
            <a class="nav-link {{ request()->is('designations') || request()->is('designations/*') ? 'active' : '' }}" href="{{ $safeRoute('designations.index') }}">
                <i class="icon">
                    <svg width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 7V5C8 3.9 8.9 3 10 3H14C15.1 3 16 3.9 16 5V7" stroke="currentColor" stroke-width="1.7"/>
                        <path d="M5 7H19C20.1 7 21 7.9 21 9V19C21 20.1 20.1 21 19 21H5C3.9 21 3 20.1 3 19V9C3 7.9 3.9 7 5 7Z" stroke="currentColor" stroke-width="1.7"/>
                        <path d="M9 13H15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                </i>
                <span class="item-name">Designations</span>
            </a>
        </li>
    @endif

    @if($showEmployeeList)
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('hr.employees.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.employees.index') }}">
                <i class="icon">
                    <svg width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.4" d="M17.5 21C17.5 18.5 15 16.5 12 16.5C9 16.5 6.5 18.5 6.5 21" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        <path d="M12 13C14.2 13 16 11.2 16 9C16 6.8 14.2 5 12 5C9.8 5 8 6.8 8 9C8 11.2 9.8 13 12 13Z" stroke="currentColor" stroke-width="1.7"/>
                        <path d="M19 12.5C20.4 13 21.5 14.3 22 16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        <path d="M17.5 5.5C19 5.8 20 7 20 8.5C20 10 19 11.2 17.5 11.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                </i>
                <span class="item-name">Employee List</span>
            </a>
        </li>
    @endif

    @if($showRolesPermissions)
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('role.permission.index') ? 'active' : '' }}" href="{{ $safeRoute('role.permission.index') }}">
                <i class="icon">
                    <svg width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 3L20 6V11C20 16 16.6 20 12 21C7.4 20 4 16 4 11V6L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="M9.5 12L11.2 13.7L15 9.8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </i>
                <span class="item-name">Role &amp; Permission</span>
            </a>
        </li>
    @endif
</ul>
    </li>
    @endif

    <li class="nav-item static-item">
        <a class="nav-link static-item disabled" href="#" tabindex="-1">
            <span class="default-icon">Modules</span>
            <span class="mini-icon">-</span>
        </a>
    </li>

    @if($showHr)
    <li class="nav-item">
        <a class="nav-link {{ $hrMenuOpen ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#hrModuleMenu" role="button" aria-expanded="{{ $hrMenuOpen ? 'true' : 'false' }}" aria-controls="hrModuleMenu">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 12C14.2 12 16 10.2 16 8C16 5.8 14.2 4 12 4C9.8 4 8 5.8 8 8C8 10.2 9.8 12 12 12Z" fill="currentColor"/><path opacity="0.4" d="M4 20C4.5 16.6 7.7 14 12 14C16.3 14 19.5 16.6 20 20" fill="currentColor"/></svg></i>
            <span class="item-name">Human Resource</span>
            <i class="right-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></i>
        </a>
        <ul class="sub-nav collapse {{ $hrMenuOpen ? 'show' : '' }}" id="hrModuleMenu" data-bs-parent="#sidebar">
            @if($showAnnouncements)<li class="nav-item"><a class="nav-link {{ request()->is('announcements') || request()->is('announcements/*') ? 'active' : '' }}" href="{{ $safeRoute('announcements.index') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 11V13C4 14.1 4.9 15 6 15H8L13 19V5L8 9H6C4.9 9 4 9.9 4 11Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 9.5C16.8 10.2 17.2 11.1 17.2 12C17.2 12.9 16.8 13.8 16 14.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i><span class="item-name">Announcements</span></a></li>@endif

            @if($showHrEvaluation)
            <li class="nav-item">
                <a class="nav-link {{ $hrEvaluationMenuOpen ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#adminHrEvaluationMenu" role="button" aria-expanded="{{ $hrEvaluationMenuOpen ? 'true' : 'false' }}" aria-controls="adminHrEvaluationMenu">
                    <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 19V5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M4 19H20" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M7 15L11 11L14 14L19 8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></i><span class="item-name">Evaluation</span>
                    <i class="right-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></i>
                </a>
                <ul class="sub-nav collapse {{ $hrEvaluationMenuOpen ? 'show' : '' }}" id="adminHrEvaluationMenu">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.evaluation.forms.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.evaluation.forms.index') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 3H14L19 8V21H7C5.9 21 5 20.1 5 19V5C5 3.9 5.9 3 7 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M14 3V8H19" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 12H15M9 16H15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i><span class="item-name">Forms</span></a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.evaluation.center.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.evaluation.center.index') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.7"/><path d="M8.5 13.5L11 16L16 9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></i><span class="item-name">Evaluation Center</span></a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.evaluation.performance-summary.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.evaluation.performance-summary.index') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 19V5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M5 19H19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M8 16L11 12L14 14L18 8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></i><span class="item-name">Results Report</span></a></li>
                </ul>
            </li>
            @endif

            @if($showHrLeave)
            <li class="nav-item">
                <a class="nav-link {{ $hrLeaveMenuOpen ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#adminHrLeaveMenu" role="button" aria-expanded="{{ $hrLeaveMenuOpen ? 'true' : 'false' }}" aria-controls="adminHrLeaveMenu">
                    <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 3V6M17 3V6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M4 9H20" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M6 5H18C19.1 5 20 5.9 20 7V19C20 20.1 19.1 21 18 21H6C4.9 21 4 20.1 4 19V7C4 5.9 4.9 5 6 5Z" stroke="currentColor" stroke-width="1.7"/><path d="M8 13H10M12 13H14M16 13H18M8 17H10M12 17H14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i><span class="item-name">Leave</span>
                    <i class="right-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></i>
                </a>
                <ul class="sub-nav collapse {{ $hrLeaveMenuOpen ? 'show' : '' }}" id="adminHrLeaveMenu">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.leave-types.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.leave-types.index') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 12L12 4H20V12L12 20L4 12Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="16" cy="8" r="1.5" fill="currentColor"/></svg></i><span class="item-name">Leave Types</span></a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.leave.credit-management') || request()->routeIs('hr.leave.credit-management.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.leave.credit-management', [], $safeRoute('hr.leave.credits', [], $safeRoute('hr.leave.index'))) }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 7H19C20.1 7 21 7.9 21 9V17C21 18.1 20.1 19 19 19H5C3.9 19 3 18.1 3 17V9C3 7.9 3.9 7 5 7Z" stroke="currentColor" stroke-width="1.7"/><path d="M7 12H13M7 15H10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M16 13L17.3 14.3L20 11.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></i><span class="item-name">Leave Credits</span></a></li>
                </ul>
            </li>
            @endif

            @if($showHrAttendance)<li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.attendance.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.attendance.index') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.7"/><path d="M12 8V12L15 14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></i><span class="item-name">Attendance</span></a></li>@endif
            @if($showHrTravelOrders)<li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.travel-orders.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.travel-orders.index') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 18L4 20V6L9 4L15 6L20 4V18L15 20L9 18Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 4V18M15 6V20" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i><span class="item-name">Travel Order</span></a></li>@endif
            @if($showHrOvertimeRequests)<li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.overtime-requests.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.overtime-requests.index') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.7"/><path d="M12 7V12H16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M17.5 17.5L20 20" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i><span class="item-name">Overtime Request</span></a></li>@endif
            @if($showHrPayroll)<li class="nav-item"><a class="nav-link {{ request()->routeIs('hr.payroll.*') ? 'active' : '' }}" href="{{ $safeRoute('hr.payroll.index') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.7"/><path d="M14.5 9.5C14.1 8.8 13.2 8.4 12.1 8.4C10.8 8.4 9.8 9 9.8 10C9.8 12.5 14.5 11.2 14.5 14C14.5 15.1 13.5 15.7 12.1 15.7C11 15.7 10.1 15.3 9.5 14.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M12 7V17" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i><span class="item-name">Payroll</span></a></li>@endif
        </ul>
    </li>
    @endif

    @if($showProjectManagement)
    <li class="nav-item"><a class="nav-link" href="{{ $safeRoute('module.project-management') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 4H19C20.1 4 21 4.9 21 6V18C21 19.1 20.1 20 19 20H5C3.9 20 3 19.1 3 18V6C3 4.9 3.9 4 5 4Z" stroke="currentColor" stroke-width="1.7"/><path d="M7 8H17M7 12H13M7 16H10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i><span class="item-name">Project Management</span></a></li>
    @endif

    @if($showWarehouse)
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('warehouse.*') && !request()->routeIs('warehouse.inventory') ? 'active' : '' }}" href="{{ $safeRoute('warehouse.dashboard') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 10L12 3L21 10V21H3V10Z" fill="currentColor" opacity="0.4"/><path d="M9 21V14H15V21" stroke="currentColor" stroke-width="1.7"/></svg></i><span class="item-name">Warehouse</span></a></li>
    @endif

    @if($showInventory)
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('warehouse.inventory') ? 'active' : '' }}" href="{{ $safeRoute('warehouse.inventory') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3L21 8L12 13L3 8L12 3Z" stroke="currentColor" stroke-width="1.7"/><path d="M3 12L12 17L21 12" stroke="currentColor" stroke-width="1.7"/><path d="M3 16L12 21L21 16" stroke="currentColor" stroke-width="1.7"/></svg></i><span class="item-name">Inventory</span></a></li>
    @endif

    @if($showPurchasing)
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('purchasing.*') ? 'active' : '' }}" href="{{ $safeRoute('purchasing.dashboard') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 5H6L8 16H18L21 8H7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="20" r="1" fill="currentColor"/><circle cx="18" cy="20" r="1" fill="currentColor"/></svg></i><span class="item-name">Procurement</span></a></li>
    @endif

    @if($showSales)
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ $safeRoute('sales.dashboard') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 19L9 14L13 17L20 8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 8H20V13" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></i><span class="item-name">Sales</span></a></li>
    @endif

    @if($showAccounting)
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('accounting.*') ? 'active' : '' }}" href="{{ $safeRoute('accounting.dashboard') }}">
            <i class="icon">
                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5 4H19C20.1 4 21 4.9 21 6V18C21 19.1 20.1 20 19 20H5C3.9 20 3 19.1 3 18V6C3 4.9 3.9 4 5 4Z" stroke="currentColor" stroke-width="1.7"/>
                    <path d="M8 8H16M8 12H16M8 16H12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                </svg>
            </i>
            <span class="item-name">Accounting</span>
        </a>
    </li>
    @endif

    @if($showUsersMenu)
    <li class="nav-item">
        <a class="nav-link {{ $usersMenuOpen ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#sidebar-user" role="button" aria-expanded="{{ $usersMenuOpen ? 'true' : 'false' }}" aria-controls="sidebar-user">
            <i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.9488 14.54C8.49884 14.54 5.58789 15.1038 5.58789 17.2795C5.58789 19.4562 8.51765 20.0001 11.9488 20.0001C15.3988 20.0001 18.3098 19.4364 18.3098 17.2606C18.3098 15.084 15.38 14.54 11.9488 14.54Z" fill="currentColor"/><path opacity="0.4" d="M11.949 12.467C14.2851 12.467 16.1583 10.5831 16.1583 8.23351C16.1583 5.88306 14.2851 4 11.949 4C9.61293 4 7.73975 5.88306 7.73975 8.23351C7.73975 10.5831 9.61293 12.467 11.949 12.467Z" fill="currentColor"/></svg></i>
            <span class="item-name">Users</span>
            <i class="right-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></i>
        </a>
        <ul class="sub-nav collapse {{ $usersMenuOpen ? 'show' : '' }}" id="sidebar-user" data-bs-parent="#sidebar">
            @if($showUserProfile)<li class="nav-item"><a class="nav-link {{ request()->is('users/*') && !request()->is('users/create') && !request()->is('users/*/edit') ? 'active' : '' }}" href="{{ $safeRoute('users.show', [auth()->id()]) }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.7"/><path d="M5 20C5.8 16.8 8.4 15 12 15C15.6 15 18.2 16.8 19 20" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i><span class="item-name">User Profile</span></a></li>@endif
            @if($showAddUser)<li class="nav-item"><a class="nav-link {{ request()->routeIs('users.create') ? 'active' : '' }}" href="{{ $safeRoute('users.create') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="8" r="4" stroke="currentColor" stroke-width="1.7"/><path d="M3 20C3.7 16.8 6.1 15 10 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M18 11V17M15 14H21" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i><span class="item-name">Add User</span></a></li>@endif
            @if($showUserList)<li class="nav-item"><a class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}" href="{{ $safeRoute('users.index') }}"><i class="icon"><svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="9" cy="8" r="3" stroke="currentColor" stroke-width="1.7"/><path d="M3.5 19C4.2 16.4 6 15 9 15C12 15 13.8 16.4 14.5 19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M16 8H21M16 12H21M17 16H21" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></i><span class="item-name">User List</span></a></li>@endif
        </ul>
    </li>
    @endif
@endif
</ul>


