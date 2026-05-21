<aside class="sidebar sidebar-default navs-rounded-all">
    <div class="sidebar-header d-flex align-items-center justify-content-start">
        <a href="{{ route('dashboard') }}" class="navbar-brand d-flex align-items-center gap-2 text-decoration-none">

            <div class="wiz-logo-container">
                <img src="{{ asset('images/wizmaster-logo.png') }}"
                     alt="Wizmaster"
                     class="wiz-logo">
            </div>

            <div class="wiz-sidebar-text-wrap">
                <h4 class="logo-title mb-0 wiz-sidebar-shadow-text">Wizmaster</h4>
            </div>
        </a>

        <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
            <i class="icon">
                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.25 12.2744L19.25 12.2744" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M10.2998 18.2988L4.2498 12.2748L10.2998 6.24976" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </i>
        </div>
    </div>

    <div class="sidebar-body pt-0 data-scrollbar">
        <div class="sidebar-list" id="sidebar">
            @include('partials.dashboard.vertical-nav')
        </div>
    </div>

    <div class="sidebar-footer"></div>
</aside>