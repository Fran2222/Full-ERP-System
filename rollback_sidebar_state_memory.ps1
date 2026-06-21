$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"
$scriptsPath = "resources\views\partials\dashboard\_scripts.blade.php"

Copy-Item $scriptsPath "$scriptsPath.bak-before-sidebar-memory-rollback-$stamp" -Force

$scripts = Get-Content $scriptsPath -Raw

# Remove all WMC sidebar memory script blocks.
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

Set-Content $scriptsPath $scripts -NoNewline

php -l $scriptsPath
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "Sidebar memory scripts removed. Layout should be back to normal."
Write-Host "Backup: $scriptsPath.bak-before-sidebar-memory-rollback-$stamp"