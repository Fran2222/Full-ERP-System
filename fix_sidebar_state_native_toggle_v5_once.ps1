$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"
$scriptsPath = "resources\views\partials\dashboard\_scripts.blade.php"

Copy-Item $scriptsPath "$scriptsPath.bak-sidebar-native-v5-$stamp" -Force

$scripts = Get-Content $scriptsPath -Raw

# Remove all previous sidebar memory blocks.
$scripts = [regex]::Replace($scripts, '(?s)\{\{\-\-\s*WMC_SIDEBAR_STATE_MEMORY\s*\-\-\}\}.*?</script>\s*', '')
$scripts = [regex]::Replace($scripts, '(?s)\{\{\-\-\s*WMC_SIDEBAR_STATE_MEMORY_V2\s*\-\-\}\}.*?</script>\s*', '')
$scripts = [regex]::Replace($scripts, '(?s)\{\{\-\-\s*WMC_SIDEBAR_STATE_MEMORY_V3_CLEAN\s*\-\-\}\}.*?</script>\s*', '')
$scripts = [regex]::Replace($scripts, '(?s)\{\{\-\-\s*WMC_SIDEBAR_STATE_NATIVE_TOGGLE_V4\s*\-\-\}\}.*?</script>\s*', '')
$scripts = [regex]::Replace($scripts, '(?s)\{\{\-\-\s*WMC_SIDEBAR_STATE_NATIVE_TOGGLE_V5_ONCE\s*\-\-\}\}.*?</script>\s*', '')

$js = @'

{{-- WMC_SIDEBAR_STATE_NATIVE_TOGGLE_V5_ONCE --}}
<script>
(function () {
    const storageKey = 'wmc_sidebar_native_state_v5';
    let internalClick = false;
    let appliedOnce = false;

    function getToggle() {
        return document.querySelector('.sidebar-toggle[data-toggle="sidebar"]')
            || document.querySelector('[data-toggle="sidebar"]')
            || document.querySelector('[data-toggle="main-sidebar"]')
            || document.querySelector('.wrapper-menu');
    }

    function isCollapsedNow() {
        const body = document.body;
        const html = document.documentElement;
        const toggle = getToggle();

        return body.classList.contains('sidebar-main')
            || body.classList.contains('sidebar-mini')
            || body.classList.contains('mini-sidebar')
            || body.classList.contains('sidebar-collapsed')
            || html.classList.contains('sidebar-main')
            || html.classList.contains('sidebar-mini')
            || html.classList.contains('mini-sidebar')
            || html.classList.contains('sidebar-collapsed')
            || (toggle && toggle.getAttribute('data-active') === 'false');
    }

    function saveActualState() {
        localStorage.setItem(storageKey, isCollapsedNow() ? 'collapsed' : 'expanded');
    }

    function applySavedOnce() {
        if (appliedOnce) return;
        appliedOnce = true;

        const saved = localStorage.getItem(storageKey);
        if (saved !== 'collapsed') return;

        // If already collapsed, do nothing.
        if (isCollapsedNow()) return;

        const toggle = getToggle();
        if (!toggle) return;

        // Use Hope UI's own toggle only once.
        internalClick = true;
        toggle.click();

        setTimeout(function () {
            internalClick = false;
            localStorage.setItem(storageKey, 'collapsed');
        }, 500);
    }

    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('.sidebar-toggle[data-toggle="sidebar"], [data-toggle="sidebar"], [data-toggle="main-sidebar"], .wrapper-menu');
        if (!toggle || internalClick) return;

        // Save actual state after Hope UI finishes toggling.
        setTimeout(saveActualState, 150);
        setTimeout(saveActualState, 400);
    }, true);

    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(applySavedOnce, 600);
    });

    window.addEventListener('load', function () {
        setTimeout(applySavedOnce, 900);
    });

    window.wmcSidebarMemoryState = function () {
        return {
            saved: localStorage.getItem(storageKey),
            collapsedNow: isCollapsedNow(),
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

'@

Set-Content $scriptsPath $scripts -NoNewline
Add-Content $scriptsPath $js

php -l $scriptsPath
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "Sidebar native toggle memory v5 applied."
Write-Host "Backup: $scriptsPath.bak-sidebar-native-v5-$stamp"