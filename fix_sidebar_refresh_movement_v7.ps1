$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"

$headPath = "resources\views\partials\dashboard\_head.blade.php"
$scriptsPath = "resources\views\partials\dashboard\_scripts.blade.php"

Copy-Item $headPath "$headPath.bak-sidebar-no-flicker-v7-$stamp" -Force
Copy-Item $scriptsPath "$scriptsPath.bak-sidebar-no-flicker-v7-$stamp" -Force

$head = Get-Content $headPath -Raw
$scripts = Get-Content $scriptsPath -Raw

# ------------------------------------------------------------
# 1) Add early no-flicker script/CSS in HEAD
# ------------------------------------------------------------
if ($head -notmatch "WMC_SIDEBAR_NO_FLICKER_V7") {
$early = @'

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

'@

    $head = $early + $head
    Write-Host "Added early sidebar no-flicker script/CSS to head."
} else {
    Write-Host "Early no-flicker block already exists."
}

# ------------------------------------------------------------
# 2) Update v6 script to reveal page after applying sidebar state
# ------------------------------------------------------------
if ($scripts -notmatch "WMC_SIDEBAR_REVEAL_V7") {
$revealJs = @'

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

'@

    Add-Content $scriptsPath $revealJs
    Write-Host "Added sidebar reveal script."
} else {
    Write-Host "Sidebar reveal script already exists."
}

Set-Content $headPath $head -NoNewline

php -l $headPath
php -l $scriptsPath
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "Sidebar refresh movement/no-flicker v7 applied."
Write-Host "Backups:"
Write-Host "$headPath.bak-sidebar-no-flicker-v7-$stamp"
Write-Host "$scriptsPath.bak-sidebar-no-flicker-v7-$stamp"