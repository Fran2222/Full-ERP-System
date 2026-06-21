<?php
// vehicle_create_edit_emergency_diagnostic.php
// READ ONLY. Captures latest Laravel errors for Vehicle Create/Edit 500.
// It writes a focused report to: vehicle_create_edit_latest_error.txt
// No database changes. No file modifications.

$root = __DIR__;
$outFile = $root . DIRECTORY_SEPARATOR . 'vehicle_create_edit_latest_error.txt';
$logFile = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'laravel.log';

ob_start();

echo "=====================================================================\n";
echo "Vehicle Create/Edit Emergency Diagnostic\n";
echo "Mode: READ ONLY / NO DATABASE CHANGES / NO FILE CHANGES\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";
echo "=====================================================================\n\n";

$paths = [
    'app/Http/Controllers/Vehicle/VehicleController.php',
    'resources/views/vehicle/vehicles/create.blade.php',
    'resources/views/vehicle/vehicles/edit.blade.php',
    'resources/views/vehicle/vehicles/index.blade.php',
    'resources/views/vehicle/vehicles/show.blade.php',
    'resources/views/vehicle/vehicles/_form.blade.php',
    'resources/views/components/app-layout.blade.php',
    'resources/views/layouts/app.blade.php',
    'routes/web.php',
];

echo "File existence check:\n";
echo "---------------------------------------------------------------------\n";
foreach ($paths as $relative) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    echo (file_exists($path) ? '[OK]   ' : '[MISS] ') . $relative . "\n";
}

echo "\nPHP syntax check:\n";
echo "---------------------------------------------------------------------\n";
$controller = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'Vehicle' . DIRECTORY_SEPARATOR . 'VehicleController.php';
if (file_exists($controller)) {
    echo shell_exec('php -l "' . $controller . '" 2>&1') . "\n";
} else {
    echo "VehicleController.php missing.\n";
}

echo "\nRoute list vehicle create/edit:\n";
echo "---------------------------------------------------------------------\n";
echo shell_exec('php artisan route:list 2>&1 | findstr /i "vehicle-management/vehicles"') . "\n";

echo "\nVehicle views first/last layout markers:\n";
echo "---------------------------------------------------------------------\n";
foreach (['create.blade.php', 'edit.blade.php', 'index.blade.php', 'show.blade.php'] as $view) {
    $path = $root . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'vehicle' . DIRECTORY_SEPARATOR . 'vehicles' . DIRECTORY_SEPARATOR . $view;
    echo "\n--- {$view} ---\n";
    if (!file_exists($path)) {
        echo "MISSING\n";
        continue;
    }
    $lines = file($path);
    $total = count($lines);
    echo "Total lines: {$total}\n";
    echo "First 8 lines:\n";
    for ($i = 0; $i < min(8, $total); $i++) {
        echo str_pad((string)($i + 1), 4, ' ', STR_PAD_LEFT) . ': ' . rtrim($lines[$i]) . "\n";
    }
    echo "Last 8 lines:\n";
    for ($i = max(0, $total - 8); $i < $total; $i++) {
        echo str_pad((string)($i + 1), 4, ' ', STR_PAD_LEFT) . ': ' . rtrim($lines[$i]) . "\n";
    }
}

echo "\nLatest Laravel ERROR chunks:\n";
echo "---------------------------------------------------------------------\n";
if (!file_exists($logFile)) {
    echo "laravel.log not found: {$logFile}\n";
} else {
    $log = file_get_contents($logFile);
    $chunks = preg_split('/\n(?=\[\d{4}-\d{2}-\d{2})/', $log);
    $matches = [];

    foreach ($chunks as $chunk) {
        if (
            stripos($chunk, 'vehicle-management/vehicles/create') !== false ||
            stripos($chunk, 'vehicle-management/vehicles') !== false ||
            stripos($chunk, 'VehicleController') !== false ||
            stripos($chunk, 'vehicle.vehicles.create') !== false ||
            stripos($chunk, 'vehicle.vehicles.edit') !== false ||
            stripos($chunk, 'vehicle/vehicles/create') !== false ||
            stripos($chunk, 'vehicle/vehicles/edit') !== false ||
            stripos($chunk, 'resources\\views\\vehicle\\vehicles') !== false ||
            stripos($chunk, 'View [layouts.app] not found') !== false ||
            stripos($chunk, 'local.ERROR') !== false
        ) {
            $matches[] = $chunk;
        }
    }

    $matches = array_slice($matches, -6);

    if (empty($matches)) {
        echo "No matching Laravel error chunks found.\n";
    } else {
        foreach ($matches as $idx => $entry) {
            echo "\n================ MATCH " . ($idx + 1) . " ================\n";
            echo $entry . "\n";
        }
    }
}

echo "\n=====================================================================\n";
echo "End of diagnostic. Upload this file to ChatGPT:\n";
echo $outFile . "\n";
echo "=====================================================================\n";

$content = ob_get_clean();
file_put_contents($outFile, $content);
echo $content;
echo "\nSaved report to: {$outFile}\n";
