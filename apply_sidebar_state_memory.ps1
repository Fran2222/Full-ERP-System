$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"
$scriptsPath = "resources\views\partials\dashboard\_scripts.blade.php"

Copy-Item $scriptsPath "$scriptsPath.bak-sidebar-memory-$stamp" -Force

$scripts = Get-Content $scriptsPath -Raw

if ($scripts -notmatch "WMC_SIDEBAR_STATE_MEMORY") {
$js = @'

{{-- WMC_SIDEBAR_STATE_MEMORY --}}
<script>
(function () {
    const storageKey = 'wmc_sidebar_collapsed';

    function isSidebarCollapsed() {
        return document.body.classList.contains('sidebar-main') ||
               document.body.classList.contains('sidebar-mini') ||
               document.documentElement.classList.contains('sidebar-main') ||
               document.documentElement.classList.contains('sidebar-mini');
    }

    function applySavedSidebarState() {
        const saved = localStorage.getItem(storageKey);

        if (saved === '1') {
            document.body.classList.add('sidebar-main');
        } else if (saved === '0') {
            document.body.classList.remove('sidebar-main');
        }
    }

    function saveSidebarState() {
        localStorage.setItem(storageKey, isSidebarCollapsed() ? '1' : '0');
    }

    // Apply as early as this partial loads.
    applySavedSidebarState();

    // Save after common Hope UI sidebar toggle clicks.
    document.addEventListener('click', function (event) {
        const toggle = event.target.closest(
            '[data-toggle="main-sidebar"], [data-toggle="sidebar"], .sidebar-toggle, .wrapper-menu, [data-sidebar-toggle]'
        );

        if (!toggle) return;

        setTimeout(saveSidebarState, 150);
        setTimeout(saveSidebarState, 350);
    });

    // Also monitor body class changes in case the theme toggles through JS.
    const observer = new MutationObserver(function () {
        saveSidebarState();
    });

    observer.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
    });

    window.addEventListener('load', function () {
        applySavedSidebarState();
        setTimeout(applySavedSidebarState, 100);
    });
})();
</script>

'@

    Add-Content $scriptsPath $js
    Write-Host "Added sidebar state memory script."
} else {
    Write-Host "Sidebar state memory script already exists. Skipped."
}

php -l $scriptsPath
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "Sidebar state memory applied."
Write-Host "Backup: $scriptsPath.bak-sidebar-memory-$stamp"