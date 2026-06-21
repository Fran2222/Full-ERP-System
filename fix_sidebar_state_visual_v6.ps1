$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"
$scriptsPath = "resources\views\partials\dashboard\_scripts.blade.php"

Copy-Item $scriptsPath "$scriptsPath.bak-sidebar-visual-v6-$stamp" -Force

$scripts = Get-Content $scriptsPath -Raw

# Remove all previous sidebar memory blocks.
$scripts = [regex]::Replace($scripts, '(?s)\{\{\-\-\s*WMC_SIDEBAR_STATE_MEMORY\s*\-\-\}\}.*?</script>\s*', '')
$scripts = [regex]::Replace($scripts, '(?s)\{\{\-\-\s*WMC_SIDEBAR_STATE_MEMORY_V2\s*\-\-\}\}.*?</script>\s*', '')
$scripts = [regex]::Replace($scripts, '(?s)\{\{\-\-\s*WMC_SIDEBAR_STATE_MEMORY_V3_CLEAN\s*\-\-\}\}.*?</script>\s*', '')
$scripts = [regex]::Replace($scripts, '(?s)\{\{\-\-\s*WMC_SIDEBAR_STATE_NATIVE_TOGGLE_V4\s*\-\-\}\}.*?</script>\s*', '')
$scripts = [regex]::Replace($scripts, '(?s)\{\{\-\-\s*WMC_SIDEBAR_STATE_NATIVE_TOGGLE_V5_ONCE\s*\-\-\}\}.*?</script>\s*', '')
$scripts = [regex]::Replace($scripts, '(?s)\{\{\-\-\s*WMC_SIDEBAR_STATE_VISUAL_V6\s*\-\-\}\}.*?</script>\s*', '')

$js = @'

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

'@

Set-Content $scriptsPath $scripts -NoNewline
Add-Content $scriptsPath $js

php -l $scriptsPath
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "Sidebar visual memory v6 applied."
Write-Host "Backup: $scriptsPath.bak-sidebar-visual-v6-$stamp"