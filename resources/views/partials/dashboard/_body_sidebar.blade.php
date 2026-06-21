<style>
/* WMC_SIDEBAR_BRAND_WIZMASTER_COLOR_SPLIT */
.sidebar .navbar-brand,
.iq-sidebar .navbar-brand {
    min-width: 0 !important;
}

.sidebar .navbar-brand .wiz-sidebar-brand-stack,
.iq-sidebar .navbar-brand .wiz-sidebar-brand-stack,
.sidebar .navbar-brand .wiz-sidebar-text-wrap,
.iq-sidebar .navbar-brand .wiz-sidebar-text-wrap {
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-start !important;
    justify-content: center !important;
    min-width: 0 !important;
    overflow: hidden !important;
}

.sidebar .navbar-brand .wiz-sidebar-shadow-text,
.iq-sidebar .navbar-brand .wiz-sidebar-shadow-text {
    display: flex !important;
    align-items: baseline !important;
    gap: 2px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 21px !important;
    font-weight: 900 !important;
    line-height: .88 !important;
    letter-spacing: .04em !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
    margin: 0 !important;
}

.sidebar .navbar-brand .wiz-sidebar-shadow-text .wiz-brand-red,
.iq-sidebar .navbar-brand .wiz-sidebar-shadow-text .wiz-brand-red,
.sidebar .navbar-brand h4.logo-title .wiz-brand-red,
.iq-sidebar .navbar-brand h4.logo-title .wiz-brand-red {
    color: #e53935 !important;
    -webkit-text-fill-color: #e53935 !important;
}

.sidebar .navbar-brand .wiz-sidebar-shadow-text .wiz-brand-blue,
.iq-sidebar .navbar-brand .wiz-sidebar-shadow-text .wiz-brand-blue,
.sidebar .navbar-brand h4.logo-title .wiz-brand-blue,
.iq-sidebar .navbar-brand h4.logo-title .wiz-brand-blue {
    color: #3157f4 !important;
    -webkit-text-fill-color: #3157f4 !important;
}

.sidebar .navbar-brand .wiz-sidebar-corporation-text,
.iq-sidebar .navbar-brand .wiz-sidebar-corporation-text {
    display: block !important;
    color: #111827 !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    line-height: .9 !important;
    letter-spacing: .30em !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
    margin-top: 4px !important;
    padding-left: 1px !important;
}
</style>

<aside class="sidebar sidebar-default navs-rounded-all">
    <div class="sidebar-header d-flex align-items-center justify-content-start">
        <a href="{{ route('dashboard') }}" class="navbar-brand d-flex align-items-center gap-2 text-decoration-none">

            <div class="wiz-logo-container">
                <img src="{{ asset('images/wizmaster-logo.png') }}"
                     alt="Wizmaster"
                     class="wiz-logo">
            </div>

            <div class="wiz-sidebar-text-wrap wiz-sidebar-brand-stack">
                <h4 class="logo-title mb-0 wiz-sidebar-shadow-text">
                    <span class="wiz-brand-red">WIZ</span><span class="wiz-brand-blue">MASTER</span>
                </h4>
                <span class="wiz-sidebar-corporation-text">CORPORATION</span>
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