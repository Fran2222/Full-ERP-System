@php
    /*
    |--------------------------------------------------------------------------
    | Show full welcome banner only on dashboard pages
    |--------------------------------------------------------------------------
    */
    $showWelcomeBanner = request()->routeIs('dashboard')
        || request()->routeIs('hr.dashboard')
        || request()->routeIs('hr.overview')
        || request()->routeIs('module.hr')
        || request()->routeIs('module.hr.overview')
        || request()->is('dashboard')
        || request()->is('hr/dashboard')
        || request()->is('hr')
        || request()->is('module/hr');
@endphp

@if($showWelcomeBanner)
    <div class="iq-navbar-header" style="height: 215px;">
        <div class="container-fluid iq-container">
            <div class="row">
                <div class="col-md-12">
                    <div class="flex-wrap d-flex justify-content-between align-items-center">
                        <div>
                            <h1>Hello, {{ auth()->user()->first_name ?? 'User' }}</h1>
                            <p>Welcome back to Wizmaster Internal System.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="iq-header-img">
            <img src="{{ asset('images/dashboard/top-header.png') }}"
                 alt="header"
                 class="theme-color-default-img img-fluid w-100 h-100 animated-scaleX">
        </div>
    </div>
@else
    {{-- 
        Compact spacer for non-dashboard pages.
        Needed because many pages use content-inner mt-n5.
        This prevents cards/tables from moving too high under the navbar.
    --}}
    <div class="iq-navbar-header" style="height: 105px; background: transparent;">
        <div class="container-fluid iq-container"></div>
    </div>
@endif  