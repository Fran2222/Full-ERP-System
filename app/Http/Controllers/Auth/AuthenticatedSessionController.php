<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = auth()->user()->load('moduleAssignments');

        $moduleRoutes = [
            'hr' => route('module.hr'),
            'inventory' => route('warehouse.inventory'),
            'warehouse' => route('warehouse.inventory'),
            'procurement' => route('purchasing.dashboard'),
            'purchasing' => route('purchasing.dashboard'),
            'sales' => route('sales.dashboard'),
            'accounting' => route('accounting.dashboard'),
            'payroll' => route('module.payroll'),
            'reports' => route('module.reports'),
        ];

        $primaryAssignment = $user->moduleAssignments->firstWhere('is_primary', true);

        if ($primaryAssignment && isset($moduleRoutes[$primaryAssignment->module])) {
            if ($user->can($primaryAssignment->module . '.view')) {
                return redirect()->intended($moduleRoutes[$primaryAssignment->module]);
            }
        }

        foreach ($user->moduleAssignments as $assignment) {
            if (isset($moduleRoutes[$assignment->module]) && $user->can($assignment->module . '.view')) {
                return redirect()->intended($moduleRoutes[$assignment->module]);
            }
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}