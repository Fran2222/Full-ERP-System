<!-- Backend Bundle JavaScript -->
<script src="{{ asset('js/libs.min.js')}}"></script>
@if(in_array('data-table',$assets ?? []))
<script src="{{ asset('vendor/datatables/buttons.server-side.js')}}"></script>
@endif
@if(in_array('chart',$assets ?? []))
    <!-- apexchart JavaScript -->
    <script src="{{asset('js/charts/apexcharts.js') }}"></script>
    <!-- widgetchart JavaScript -->
    <script src="{{asset('js/charts/widgetcharts.js') }}"></script>
    <script src="{{asset('js/charts/dashboard.js') }}"></script>
@endif

<!-- mapchart JavaScript -->
<script src="{{asset('vendor/Leaflet/leaflet.js') }} "></script>
<script src="{{asset('js/charts/vectore-chart.js') }}"></script>


<!-- fslightbox JavaScript -->
<script src="{{asset('js/plugins/fslightbox.js')}}"></script>
<script src="{{asset('js/plugins/slider-tabs.js') }}"></script>
<script src="{{asset('js/plugins/form-wizard.js')}}"></script>

<!-- settings JavaScript -->
<script src="{{asset('js/plugins/setting.js')}}"></script>

<script src="{{asset('js/plugins/circle-progress.js') }}"></script>
@if(in_array('animation',$assets ?? []))
<!--aos javascript-->
<script src="{{asset('vendor/aos/dist/aos.js')}}"></script>
@endif

@if(in_array('calender',$assets ?? []))
<!-- Fullcalender Javascript -->
<script src="{{asset('vendor/fullcalendar/core/main.js')}}"></script>
<script src="{{asset('vendor/fullcalendar/daygrid/main.js')}}"></script>
<script src="{{asset('vendor/fullcalendar/timegrid/main.js')}}"></script>
<script src="{{asset('vendor/fullcalendar/list/main.js')}}"></script>
<script src="{{asset('vendor/fullcalendar/interaction/main.js')}}"></script>
<script src="{{asset('vendor/moment.min.js')}}"></script>
<script src="{{asset('js/plugins/calender.js')}}"></script>
@endif

<script src="{{asset('vendor/vanillajs-datepicker/dist/js/datepicker-full.js')}}"></script>

@stack('scripts')

<script src="{{asset('js/plugins/prism.mini.js')}}"></script>

<!-- Custom JavaScript -->
<script src="{{asset('js/hope-ui.js') }}"></script>
<script src="{{asset('js/modelview.js')}}"></script>

{{-- WMC_CHAT_PRESENCE_PING --}}
@auth
<script>
(function () {
    const pingUrl = "{{ route('chat.presence.ping') }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    async function wmcPingPresence() {
        if (!csrfToken) return;

        try {
            await fetch(pingUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });
        } catch (error) {
            // Silent fail: presence should never interrupt normal system use.
        }
    }

    wmcPingPresence();
    setInterval(wmcPingPresence, 60000);
})();
</script>
@endauth


{{-- WMC_CHAT_BADGE_REALTIME_POLL --}}
@auth
<script>
(function () {
    const chatUnreadUrl = "{{ route('chat.unread-count') }}";

    function setChatBadge(count) {
        const badge = document.getElementById('wmcChatHeaderBadge');
        if (!badge) return;

        const value = Number(count || 0);

        if (value > 0) {
            badge.textContent = value > 99 ? '99+' : String(value);
            badge.classList.remove('d-none');
        } else {
            badge.textContent = '0';
            badge.classList.add('d-none');
        }
    }

    async function refreshChatBadge() {
        try {
            const response = await fetch(chatUnreadUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) return;

            const data = await response.json();
            setChatBadge(data.unread_count || 0);
        } catch (error) {
            // Silent fail: badge should not interrupt normal system use.
        }
    }

    window.wmcRefreshChatBadge = refreshChatBadge;

    refreshChatBadge();
    setInterval(refreshChatBadge, 5000);

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            refreshChatBadge();
        }
    });
})();
</script>
@endauth





{{-- WMC_SIDEBAR_STATE_VISUAL_V6 --}}
<script>
(function () {
    const storageKey = 'wmc_sidebar_visual_state_v6'; // collapsed | expanded
    let applying = false;
    let appliedOnce = false;

    function getSidebar() {
        return document.querySelector('.sidebar')
            || document.querySelector('.iq-sidebar')
            || document.querySelector('aside.sidebar')
            || document.querySelector('[data-sidebar]');
    }

    function getToggle() {
        return document.querySelector('.sidebar-toggle[data-toggle="sidebar"]')
            || document.querySelector('[data-toggle="sidebar"]')
            || document.querySelector('[data-toggle="main-sidebar"]')
            || document.querySelector('.wrapper-menu');
    }

    function isVisuallyCollapsed() {
        const sidebar = getSidebar();

        if (sidebar) {
            const rect = sidebar.getBoundingClientRect();
            const width = Math.round(rect.width || sidebar.offsetWidth || 0);

            // In your screenshot, collapsed sidebar is icon-only around 70px.
            if (width > 0 && width <= 120) {
                return true;
            }

            if (width >= 170) {
                return false;
            }
        }

        const brandText = document.querySelector('.logo-title, .wiz-sidebar-shadow-text, .wiz-sidebar-corporation-text');
        if (brandText) {
            const style = window.getComputedStyle(brandText);
            if (style.display === 'none' || style.visibility === 'hidden' || brandText.offsetWidth <= 5) {
                return true;
            }
        }

        const body = document.body;
        const html = document.documentElement;

        return body.classList.contains('sidebar-main')
            || body.classList.contains('sidebar-mini')
            || body.classList.contains('mini-sidebar')
            || body.classList.contains('sidebar-collapsed')
            || html.classList.contains('sidebar-main')
            || html.classList.contains('sidebar-mini')
            || html.classList.contains('mini-sidebar')
            || html.classList.contains('sidebar-collapsed');
    }

    function saveVisualState() {
        localStorage.setItem(storageKey, isVisuallyCollapsed() ? 'collapsed' : 'expanded');
    }

    function clickNativeToggleOnce() {
        const toggle = getToggle();
        if (!toggle) return;

        applying = true;
        toggle.click();

        setTimeout(function () {
            applying = false;
            saveVisualState();
        }, 600);
    }

    function applySavedStateOnce() {
        if (appliedOnce) return;
        appliedOnce = true;

        const saved = localStorage.getItem(storageKey);

        if (saved === 'collapsed') {
            setTimeout(function () {
                if (!isVisuallyCollapsed()) {
                    clickNativeToggleOnce();
                }
            }, 150);
        }

        if (saved === 'expanded') {
            setTimeout(function () {
                if (isVisuallyCollapsed()) {
                    clickNativeToggleOnce();
                }
            }, 150);
        }
    }

    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('.sidebar-toggle[data-toggle="sidebar"], [data-toggle="sidebar"], [data-toggle="main-sidebar"], .wrapper-menu');
        if (!toggle || applying) return;

        // Save based on actual visual width after Hope UI finishes toggling.
        setTimeout(saveVisualState, 250);
        setTimeout(saveVisualState, 700);
        setTimeout(saveVisualState, 1200);
    }, true);

    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(applySavedStateOnce, 700);
    });

    window.addEventListener('load', function () {
        setTimeout(applySavedStateOnce, 1000);
    });

    window.wmcSidebarMemoryState = function () {
        const sidebar = getSidebar();
        return {
            saved: localStorage.getItem(storageKey),
            visualCollapsed: isVisuallyCollapsed(),
            sidebarWidth: sidebar ? Math.round(sidebar.getBoundingClientRect().width || sidebar.offsetWidth || 0) : null,
            toggleFound: !!getToggle(),
            body: document.body.className,
            html: document.documentElement.className,
            toggle: getToggle() ? {
                className: getToggle().className,
                active: getToggle().getAttribute('data-active')
            } : null
        };
    };

    window.wmcSidebarSetCollapsed = function () {
        localStorage.setItem(storageKey, 'collapsed');
    };

    window.wmcSidebarSetExpanded = function () {
        localStorage.setItem(storageKey, 'expanded');
    };

    window.wmcSidebarForgetState = function () {
        localStorage.removeItem(storageKey);
    };
})();
</script>


<script>
/* WMC_SIDEBAR_REVEAL_V7 */
(function () {
    function revealSidebarRestoredPage() {
        document.documentElement.classList.remove('wmc-sidebar-restoring');
    }

    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(revealSidebarRestoredPage, 1300);
    });

    window.addEventListener('load', function () {
        setTimeout(revealSidebarRestoredPage, 1400);
    });

    setTimeout(revealSidebarRestoredPage, 1800);
})();
</script>

