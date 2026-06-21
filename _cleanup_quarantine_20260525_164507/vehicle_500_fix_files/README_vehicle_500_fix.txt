Vehicle Management 500 Error Fix

Likely cause:
The current Laravel version in this project does not support Illuminate\Http\Request::integer().
The VehicleController index used request()->integer(), which can cause a 500 error when opening /vehicle-management/vehicles.

Apply:
1. Extract this ZIP directly into C:\xampp\htdocs\wizhopeui
2. Run:
   cd C:\xampp\htdocs\wizhopeui
   php apply_vehicle_500_fix.php
   php artisan optimize:clear
3. Open:
   /vehicle-management/vehicles

Changed file:
- app/Http/Controllers/Vehicle/VehicleController.php
