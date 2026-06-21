<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\Service\ServiceJobOrder;
use Illuminate\Support\Facades\Schema;

class ServiceDashboardController extends Controller
{
    public function index()
    {
        $query = Schema::hasTable('service_job_orders') ? ServiceJobOrder::query() : null;

        $total = $query ? (clone $query)->count() : 0;
        $pending = $query ? (clone $query)->where('status_text', 'Pending')->count() : 0;
        $ongoing = $query ? (clone $query)->where('status_text', 'Ongoing')->count() : 0;
        $completedThisMonth = $query
            ? (clone $query)->where('status_text', 'Completed')
                ->whereMonth('completed_at', now()->month)
                ->whereYear('completed_at', now()->year)
                ->count()
            : 0;

        $recentJobOrders = $query
            ? ServiceJobOrder::with(['customer', 'serviceType', 'serviceStatus', 'assignedTo'])->latest('id')->limit(8)->get()
            : collect();

        return view('service.dashboard.index', compact('total', 'pending', 'ongoing', 'completedThisMonth', 'recentJobOrders'));
    }
}
