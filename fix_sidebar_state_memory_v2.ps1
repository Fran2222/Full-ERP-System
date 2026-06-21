$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"
$scriptsPath = "resources\views\partials\dashboard\_scripts.blade.php"

Copy-Item $scriptsPath "$scriptsPath.bak-sidebar-memory-v2-$stamp" -Force

$scripts = Get-Content $scriptsPath -Raw

# Remove old WMC sidebar memory block to avoid conflict
$scripts = [regex]::Replace(
    $scripts,
    '(?s)\r?\n?\{\{\-\-\s*WMC_SIDEBAR_STATE_MEMORY\s*\-\-\}\}.*?</script>\s*',
    "`r`n"
)

$js = @'

{{-- WMC_SIDEBAR_STATE_MEMORY_V2 --}}
<script>
(function () {
    const storageKey = 'wmc_sidebar_state_v2'; // values: collapsed | expanded

    function getToggles() {
        return Array.from(document.querySelectorAll('[data-toggle="sidebar"], [data-toggle="main-sidebar"], .sidebar-toggle, .wrapper-menu, [data-sidebar-toggle]'));
    }

    function hasCollapsedClass() {
        const classTargets = [document.body, document.documentElement].filter(Boolean);
        return classTargets.some(function (el) {
            return el.classList.contains('sidebar-main') ||
                   el.classList.contains('sidebar-mini') ||
                   el.classList.contains('mini-sidebar') ||
                   el.classList.contains('sidebar-collapsed') ||
                   el.classList.contains('nav-small');
        });
    }

    function setCollapsedClasses(collapsed) {
        const classTargets = [document.body, document.documentElement].filter(Boolean);

        classTargets.forEach(function (el) {
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

        getToggles().forEach(function (toggle) {
            toggle.setAttribute('data-active', collapsed ? 'false' : 'true');
        });
    }

    function applySavedState() {
        const saved = localStorage.getItem(storageKey);

        if (saved === 'collapsed') {
            setCollapsedClasses(true);
        }

        if (saved === 'expanded') {
            setCollapsedClasses(false);
        }
    }

    function saveStateFromDom() {
        const toggles = getToggles();
        const hasInactiveToggle = toggles.some(function (toggle) {
            return toggle.getAttribute('data-active') === 'false';
        });

        const collapsed = hasCollapsedClass() || hasInactiveToggle;
        localStorage.setItem(storageKey, collapsed ? 'collapsed' : 'expanded');
    }

    function saveStateAfterThemeToggle() {
        setTimeout(saveStateFromDom, 80);
        setTimeout(saveStateFromDom, 250);
        setTimeout(saveStateFromDom, 600);
    }

    // Apply immediately when this script loads.
    applySavedState();

    // Theme scripts sometimes override after load, so re-apply.
    document.addEventListener('DOMContentLoaded', function () {
        applySavedState();
        setTimeout(applySavedState, 50);
        setTimeout(applySavedState, 250);
        setTimeout(applySavedState, 700);
        setTimeout(applySavedState, 1200);
    });

    window.addEventListener('load', function () {
        applySavedState();
        setTimeout(applySavedState, 100);
        setTimeout(applySavedState, 500);
        setTimeout(applySavedState, 1200);
    });

    // Save when user clicks sidebar toggle.
    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('[data-toggle="sidebar"], [data-toggle="main-sidebar"], .sidebar-toggle, .wrapper-menu, [data-sidebar-toggle]');
        if (!toggle) return;

        saveStateAfterThemeToggle();
    });

    // Watch class/data-active changes.
    const observer = new MutationObserver(function () {
        saveStateFromDom();
    });

    function startObserver() {
        if (document.body) {
            observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
        }

        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

        getToggles().forEach(function (toggle) {
            observer.observe(toggle, { attributes: true, attributeFilter: ['data-active', 'class'] });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startObserver);
    } else {
        startObserver();
    }

    window.wmcApplySidebarState = applySavedState;
    window.wmcSaveSidebarState = saveStateFromDom;
})();
</script>

'@

Add-Content $scriptsPath $js

php -l $scriptsPath
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "Sidebar state memory v2 applied."
Write-Host "Backup: $scriptsPath.bak-sidebar-memory-v2-$stamp"