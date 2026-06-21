<?php
// patch_vehicle_setup_controller_keep_tab.php
// Adds keep-active-tab flash behavior to VehicleSetupController without changing database.

$root = __DIR__;
$file = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'Vehicle' . DIRECTORY_SEPARATOR . 'VehicleSetupController.php';

if (!file_exists($file)) {
    echo "ERROR: VehicleSetupController.php not found.\n";
    exit(1);
}

$code = file_get_contents($file);
$backup = $file . '.backup-before-setup-keep-tab-' . date('YmdHis');
copy($file, $backup);
echo "Backup created: {$backup}\n";

$code = str_replace(
    "return back()->with('success', \$this->groupLabel(\$group) . ' saved successfully.');",
    "return back()->with('success', \$this->groupLabel(\$group) . ' saved successfully.')->with('vehicle_setup_tab', \$request->input('active_tab', \$group));",
    $code
);

$code = str_replace(
    "return back()->with('success', \$this->groupLabel(\$group) . ' updated successfully.');",
    "return back()->with('success', \$this->groupLabel(\$group) . ' updated successfully.')->with('vehicle_setup_tab', \$request->input('active_tab', \$group));",
    $code
);

$code = str_replace(
    "return back()->with('success', \$this->groupLabel(\$group) . ' deleted successfully.');",
    "return back()->with('success', \$this->groupLabel(\$group) . ' deleted successfully.')->with('vehicle_setup_tab', request('active_tab', \$group));",
    $code
);

$code = str_replace(
    "return back()->with('error', 'Cannot delete this record because it may already be used by vehicle records.');",
    "return back()->with('error', 'Cannot delete this record because it may already be used by vehicle records.')->with('vehicle_setup_tab', request('active_tab', \$group));",
    $code
);

file_put_contents($file, $code);

echo "VehicleSetupController patched for active tab retention.\n";
