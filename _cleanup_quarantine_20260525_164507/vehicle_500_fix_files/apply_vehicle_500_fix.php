<?php
/**
 * Vehicle Management 500 Error Fix
 * Usage: php apply_vehicle_500_fix.php
 * Fixes Laravel versions where Request::integer() does not exist.
 */
$root = __DIR__;
$source = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'Vehicle' . DIRECTORY_SEPARATOR . 'VehicleController.php';
$target = $root . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'Vehicle' . DIRECTORY_SEPARATOR . 'VehicleController.php';

// If script is copied directly to project root together with app folder, this resolves project root.
if (file_exists($root . DIRECTORY_SEPARATOR . 'artisan')) {
    $target = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'Vehicle' . DIRECTORY_SEPARATOR . 'VehicleController.php';
    $source = $root . DIRECTORY_SEPARATOR . 'vehicle_500_fix_files' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'Vehicle' . DIRECTORY_SEPARATOR . 'VehicleController.php';
}

// Alternative: if extracted inside project root, source may be in app already. In that case no script is needed.
$projectRoot = getcwd();
if (!file_exists($projectRoot . DIRECTORY_SEPARATOR . 'artisan')) {
    fwrite(STDERR, "ERROR: Run this from Laravel project root: C:\\xampp\\htdocs\\wizhopeui\n");
    exit(1);
}
$source = $projectRoot . DIRECTORY_SEPARATOR . 'vehicle_500_fix_files' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'Vehicle' . DIRECTORY_SEPARATOR . 'VehicleController.php';
$target = $projectRoot . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'Vehicle' . DIRECTORY_SEPARATOR . 'VehicleController.php';
if (!file_exists($source)) {
    fwrite(STDERR, "ERROR: Source file not found: {$source}\nExtract the ZIP directly into C:\\xampp\\htdocs\\wizhopeui first.\n");
    exit(1);
}
if (!is_dir(dirname($target))) {
    mkdir(dirname($target), 0777, true);
}
if (file_exists($target)) {
    copy($target, $target . '.backup-before-vehicle-500-fix-' . date('YmdHis'));
}
copy($source, $target);
echo "VehicleController.php updated.\n";
echo "Now run: php artisan optimize:clear\n";
