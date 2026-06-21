$ErrorActionPreference = "Stop"

$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root

$stamp = Get-Date -Format "yyyyMMddHHmmss"
$backupDir = "backup_sales_dashboard_polish_$stamp"
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null

$files = @(
    "app\Http\Controllers\Sales\SalesDashboardController.php",
    "resources\views\sales\dashboard.blade.php"
)

foreach ($file in $files) {
    if (Test-Path $file) {
        $dest = Join-Path $backupDir $file
        $destDir = Split-Path $dest -Parent
        New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        Copy-Item $file $dest -Force
    }
}

Copy-Item "sales_dashboard_polish_files\app\Http\Controllers\Sales\SalesDashboardController.php" "app\Http\Controllers\Sales\SalesDashboardController.php" -Force
Copy-Item "sales_dashboard_polish_files\resources\views\sales\dashboard.blade.php" "resources\views\sales\dashboard.blade.php" -Force

php -l "app\Http\Controllers\Sales\SalesDashboardController.php"
php artisan view:clear
php artisan optimize:clear

Write-Host "Sales Dashboard polished successfully."
Write-Host "Backups saved to: $backupDir"
