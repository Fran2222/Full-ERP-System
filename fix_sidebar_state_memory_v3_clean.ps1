$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"
$scriptsPath = "resources\views\partials\dashboard\_scripts.blade.php"

Copy-Item $scriptsPath "$scriptsPath.bak-sidebar-memory-v3-$stamp" -Force

$scripts = Get-Content $scriptsPath -Raw

# Remove old v1 and v2 sidebar memory blocks.
$scripts = [regex]::Replace(
    $scripts,
    '(?s)\{\{\-\-\s*WMC_SIDEBAR_STATE_MEMORY\s*\-\-\}\}.*?</script>\s*',
    ''
)

$scripts = [regex]::Replace(
    $scripts,
    '(?s)\{\{\-\-\s*WMC_SIDEBAR_STATE_MEMORY_V2\s*\-\-\}\}.*?</script>\s*',
    ''
)

$js = @'

{{-- WMC_SIDEBAR_STATE_MEMORY_V3_CLEAN --}}
<script>
(function () {
    const storageKey = 'wmc_sidebar_state_v3'; // collapsed | expanded

    function toggles() {
        return Array.from(document.querySelectorAll('[data-toggle="sidebar"], [data-toggle="main-sidebar"], .sidebar-toggle, .wrapper-menu, [data-sidebar-toggle]'));
    }

    function setSidebarState(state) {
        const collapsed = state === 'collapsed';

        [document.documentElement, document.body].forEach(function (el) {
            if (!el) return;

            if (collapsed) {
                el.classList.add('sidebar-main');
                el.classList.add('sidebar-mini');
            } else {
                el.classList.remove('sidebar-main');
                el.classList.remove('sidebar-mini');
                el.classList.remove('mini-sidebar');
                el.classList.remove('sidebar-collapsed');
                el.classList.remove('nav-small');
            }
        });

        toggles().forEach(function (toggle) {
            toggle.setAttribute('data-active', collapsed ? 'false' : 'true');
        });
    }

    function applySaved() {
        const saved = localStorage.getItem(storageKey);
        if (saved === 'collapsed' || saved === 'expanded') {
            setSidebarState(saved);
        }
    }

    function currentSavedOrDomState() {
        const saved = localStorage.getItem(storageKey);
        if (saved === 'collapsed' || saved === 'expanded') return saved;

        const collapsedNow =
            document.body.classList.contains('sidebar-main') ||
            document.body.classList.contains('sidebar-mini') ||
            document.documentElement.classList.contains('sidebar-main') ||
            document.documentElement.classList.contains('sidebar-mini') ||
            toggles().some(function (toggle) {
                return toggle.getAttribute('data-active') === 'false';
            });

        return collapsedNow ? 'collapsed' : 'expanded';
    }

    function saveState(state) {
        localStorage.setItem(storageKey, state);
        setSidebarState(state);
    }

    // Apply on load and after theme JS runs.
    applySaved();

    document.addEventListener('DOMContentLoaded', function () {
        applySaved();
        setTimeout(applySaved, 50);
        setTimeout(applySaved, 250);
        setTimeout(applySaved, 700);
        setTimeout(applySaved, 1200);
    });

    window.addEventListener('load', function () {
        applySaved();
        setTimeout(applySaved, 100);
        setTimeout(applySaved, 400);
        setTimeout(applySaved, 1000);
    });

    // Important: save by intention, not by theme class timing.
    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('[data-toggle="sidebar"], [data-toggle="main-sidebar"], .sidebar-toggle, .wrapper-menu, [data-sidebar-toggle]');
        if (!toggle) return;

        const current = currentSavedOrDomState();
        const next = current === 'collapsed' ? 'expanded' : 'collapsed';

        saveState(next);

        setTimeout(function () { setSidebarState(next); }, 80);
        setTimeout(function () { setSidebarState(next); }, 250);
        setTimeout(function () { setSidebarState(next); }, 600);
    });

    window.wmcSetSidebarCollapsed = function () {
        saveState('collapsed');
    };

    window.wmcSetSidebarExpanded = function () {
        saveState('expanded');
    };

    window.wmcGetSidebarState = function () {
        return {
            saved: localStorage.getItem(storageKey),
            body: document.body.className,
            html: document.documentElement.className,
            toggles: toggles().map(function (x) {
                return {
                    className: x.className,
                    active: x.getAttribute('data-active')
                };
            })
        };
    };
})();
</script>

'@

Add-Content $scriptsPath $js

php -l $scriptsPath
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "Sidebar state memory v3 clean applied."
Write-Host "Backup: $scriptsPath.bak-sidebar-memory-v3-$stamp"