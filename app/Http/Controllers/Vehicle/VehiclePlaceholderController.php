<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;

class VehiclePlaceholderController extends Controller
{
    public function vehicles()
    {
        return view('vehicle.placeholders.vehicles');
    }

    public function assignments()
    {
        return view('vehicle.placeholders.assignments');
    }

    public function maintenance()
    {
        return view('vehicle.placeholders.maintenance');
    }

    public function documents()
    {
        return view('vehicle.placeholders.documents');
    }

    public function reports()
    {
        return view('vehicle.placeholders.reports');
    }
}
