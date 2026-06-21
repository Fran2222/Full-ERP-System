$ErrorActionPreference = "Stop"
$root = "C:\xampp\htdocs\wizhopeui"
Set-Location $root
$stamp = Get-Date -Format "yyyyMMddHHmmss"
$backupDir = "backup_sales_dashboard_branch_scope_$stamp"
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null

$files = @(
    @{ Source = "sales_dashboard_branch_scope_files\app\Http\Controllers\Sales\SalesDashboardController.php"; Target = "app\Http\Controllers\Sales\SalesDashboardController.php" },
    @{ Source = "sales_dashboard_branch_scope_files\resources\views\sales\dashboard.blade.php"; Target = "resources\views\sales\dashboard.blade.php" }
)

foreach ($file in $files) {
    if (!(Test-Path $file.Source)) { throw "Missing source file: $($file.Source). Extract the whole ZIP first." }
    if (Test-Path $file.Target) {
        $backupPath = Join-Path $backupDir $file.Target
        New-Item -ItemType Directory -Path (Split-Path $backupPath -Parent) -Force | Out-Null
        Copy-Item $file.Target $backupPath -Force
    }
    Copy-Item $file.Source $file.Target -Force
}

php -l app\Http\Controllers\Sales\SalesDashboardController.php
php artisan view:clear
php artisan optimize:clear

Write-Host "Sales Dashboard branch scope fix applied. Backup: $backupDir"
Write-Host "BOD/Super Admin: branch dropdown + All Branches option."
Write-Host "Non-BOD: assigned branch only, including branch performance panel."
