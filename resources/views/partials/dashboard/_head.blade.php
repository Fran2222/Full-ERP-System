
{{-- WMC_SIDEBAR_NO_FLICKER_V7 --}}
<script>
(function () {
    try {
        if (localStorage.getItem('wmc_sidebar_visual_state_v6') === 'collapsed') {
            document.documentElement.classList.add('wmc-sidebar-restoring');
        }
    } catch (e) {}
})();
</script>
<style>
    html.wmc-sidebar-restoring body {
        opacity: 0 !important;
    }
</style>
<!-- Favicon -->
<link rel="shortcut icon" href="{{ asset('images/wizmaster-favicon.ico') }}" />
<link rel="icon" type="image/x-icon" href="{{ asset('images/wizmaster-favicon.ico') }}" />
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/wizmaster-favicon.png') }}" />
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/wizmaster-favicon.png') }}" />
<link rel="apple-touch-icon" href="{{ asset('images/wizmaster-favicon.png') }}" />

<link rel="stylesheet" href="{{ asset('css/libs.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/hope-ui.css?v=1.1.0') }}">
<link rel="stylesheet" href="{{ asset('css/custom.css?v=1.1.0') }}">
<link rel="stylesheet" href="{{ asset('css/dark.css?v=1.1.0') }}">
<link rel="stylesheet" href="{{ asset('css/rtl.css?v=1.1.0') }}">
<link rel="stylesheet" href="{{ asset('css/customizer.css?v=1.1.0') }}">

<!-- Fullcalender CSS -->
<link rel="stylesheet" href="{{ asset('vendor/fullcalendar/core/main.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/fullcalendar/daygrid/main.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/fullcalendar/timegrid/main.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/fullcalendar/list/main.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/Leaflet/leaflet.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/vanillajs-datepicker/dist/css/datepicker.min.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/aos/dist/aos.css') }}" />

<style>
    th.hide-search input {
        display: none;
    }
</style>