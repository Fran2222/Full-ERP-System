$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"
$scriptsPath = "resources\views\partials\dashboard\_scripts.blade.php"

Copy-Item $scriptsPath "$scriptsPath.bak-sidebar-native-v4-$stamp" -Force

$scripts = Get-Content $scriptsPath -Raw

# Remove all previous sidebar memory scripts that directly modified classes.
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

$scripts = [regex]::Replace(
    $scripts,
    '(?s)\{\{\-\-\s*WMC_SIDEBAR_STATE_MEMORY_V3_CLEAN\s*\-\-\}\}.*?</script>\s*',
    ''
)

$scripts = [regex]::Replace(
    $scripts,
    '(?s)\{\{\-\-\s*WMC_SIDEBAR_STATE_NATIVE_TOGGLE_V4\s*\-\-\}\}.*?</script>\s*',
    ''
)

$js = @'

{{-- WMC_SIDEBAR_STATE_NATIVE_TOGGLE_V4 --}}
<script>
(function () {
    const storageKey = 'wmc_sidebar_native_state_v4'; // collapsed | expanded
    let internalToggle = false;

    function getSidebarToggle() {
        return document.querySelector('.sidebar-toggle[data-toggle="sidebar"]')
            || document.querySelector('[data-toggle="sidebar"]')
            || document.querySelector('[data-toggle="main-sidebar"]')
            || document.querySelector('.wrapper-menu');
    }

    function saveNextStateByUserClick() {
        const current = localStorage.getItem(storageKey) || 'expanded';
        const next = current === 'collapsed' ? 'expanded' : 'collapsed';
        localStorage.setItem(storageKey, next);
    }

    function applySavedByNativeClick() {
        const saved = localStorage.getItem(storageKey);
        if (saved !== 'collapsed') return;

        const toggle = getSidebarToggle();
        if (!toggle) return;

        // Page normally loads expanded. Click once using the theme's own toggle.
        internalToggle = true;
        toggle.click();

        setTimeout(function () {
            internalToggle = false;
        }, 500);
    }

    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('.sidebar-toggle[data-toggle="sidebar"], [data-toggle="sidebar"], [data-toggle="main-sidebar"], .wrapper-menu');
        if (!toggle) return;
        if (internalToggle) return;

        saveNextStateByUserClick();
    }, true);

    // Apply only once after theme is ready. No direct class manipulation.
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(applySavedByNativeClick, 350);
    });

    window.addEventListener('load', function () {
        setTimeout(applySavedByNativeClick, 450);
    });

    window.wmcSidebarMemoryState = function () {
        return {
            saved: localStorage.getItem(storageKey),
            toggleFound: !!getSidebarToggle()
        };
    };

    window.wmcSidebarForgetState = function () {
        localStorage.removeItem(storageKey);
    };
})();
</script>

'@

Add-Content $scriptsPath $js

Set-Content $scriptsPath $scripts -NoNewline
Add-Content $scriptsPath $js

php -l $scriptsPath
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "Sidebar native toggle memory v4 applied."
Write-Host "Backup: $scriptsPath.bak-sidebar-native-v4-$stamp"