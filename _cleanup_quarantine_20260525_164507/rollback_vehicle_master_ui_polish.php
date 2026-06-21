<?php
// rollback_vehicle_master_ui_polish.php
// Emergency rollback for the last Vehicle Master UI Polish.
// Restores the latest .backup-before-vehicle-master-ui-polish-* backups
// for vehicle create/edit/index/_form pages.
// No database changes.

$root = __DIR__;

echo "============================================================\n";
echo "Rollback Vehicle Master UI Polish\n";
echo "============================================================\n\n";

$targets = [
    'resources/views/vehicle/vehicles/index.blade.php',
    'resources/views/vehicle/vehicles/create.blade.php',
    'resources/views/vehicle/vehicles/edit.blade.php',
    'resources/views/vehicle/vehicles/_form.blade.php',
];

function latest_backup_for($targetPath)
{
    $pattern = $targetPath . '.backup-before-vehicle-master-ui-polish-*';
    $matches = glob($pattern);

    if (!$matches) {
        return null;
    }

    usort($matches, function ($a, $b) {
        return filemtime($b) <=> filemtime($a);
    });

    return $matches[0];
}

$restored = 0;

foreach ($targets as $relative) {
    $target = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);

    echo "Checking: {$relative}\n";

    if (!file_exists($target)) {
        echo "  SKIP: target file missing.\n";
        continue;
    }

    $backup = latest_backup_for($target);

    if (!$backup) {
        echo "  SKIP: no vehicle-master-ui-polish backup found.\n";
        continue;
    }

    $safety = $target . '.backup-before-rollback-master-ui-polish-' . date('YmdHis');
    copy($target, $safety);
    copy($backup, $target);

    echo "  RESTORED from: " . basename($backup) . "\n";
    echo "  Current broken file backed up as: " . basename($safety) . "\n";

    $restored++;
}

echo "\nRestored files: {$restored}\n";

echo "\nClearing compiled Blade views...\n";
$compiledViews = glob($root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . '*.php');
foreach ($compiledViews ?: [] as $compiled) {
    @unlink($compiled);
}
echo "Compiled views cleared by rollback script.\n";

echo "\nNow run:\n";
echo "php artisan optimize:clear\n";
echo "\nThen open:\n";
echo "/vehicle-management/vehicles/create\n";
echo "/vehicle-management/vehicles/1/edit\n";
