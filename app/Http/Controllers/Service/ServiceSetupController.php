<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\Service\ServiceStatus;
use App\Models\Service\ServiceType;
use Illuminate\Http\Request;

class ServiceSetupController extends Controller
{
    public function index()
    {
        $serviceTypes = ServiceType::orderBy('name')->get();
        $statuses = ServiceStatus::orderBy('sort_order')->orderBy('name')->get();

        return view('service.setup.index', compact('serviceTypes', 'statuses'));
    }

    public function storeType(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:service_types,name'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        ServiceType::create($data);

        return back()->with('success', 'Service type saved successfully.');
    }

    public function storeStatus(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:service_statuses,name'],
            'color' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
            'is_closed' => ['nullable', 'boolean'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $data['is_closed'] = (bool) $request->input('is_closed', false);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        ServiceStatus::create($data);

        return back()->with('success', 'Service status saved successfully.');
    }
}
