<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\TravelOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TravelOrderController extends Controller
{
    private function canManageTravelOrders(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['super admin', 'super-admin', 'superadmin', 'admin', 'hr'])
            || $user->can('hr.leave.requests.view')
            || $user->can('accounting.view');
    }

    public function index(Request $request)
    {
        $canManageTravelOrders = $this->canManageTravelOrders();

        $query = TravelOrder::with(['requester', 'reviewer'])
            ->latest();

        if (! $canManageTravelOrders) {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $travelOrders = $query->paginate(10)->withQueryString();

        $today = Carbon::today();

        return view('hr.travel-orders.index', compact(
            'travelOrders',
            'canManageTravelOrders',
            'today'
        ));
    }

    public function create()
    {
        return view('hr.travel-orders.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_date' => ['required', 'date'],
            'employees_authorized' => ['required', 'string'],
            'destination' => ['required', 'string', 'max:255'],
            'purpose_a' => ['required', 'string'],
            'travel_start_date' => ['required', 'date'],
            'travel_end_date' => ['required', 'date', 'after_or_equal:travel_start_date'],
            'remarks' => ['nullable', 'string'],
        ]);

        $employeesAuthorized = collect(preg_split('/\r\n|\r|\n/', $validated['employees_authorized']))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->values()
            ->toArray();

        TravelOrder::create([
            'user_id' => auth()->id(),
            'order_date' => $validated['order_date'],
            'to' => null,
            'employees_authorized' => $employeesAuthorized,
            'destination' => $validated['destination'],
            'purpose_a' => $validated['purpose_a'],
            'purpose_b' => null,
            'travel_start_date' => $validated['travel_start_date'],
            'travel_end_date' => $validated['travel_end_date'],
            'remarks' => $validated['remarks'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('hr.travel-orders.index')
            ->with('success', 'Travel Order request submitted successfully.');
    }

    public function show(TravelOrder $travelOrder)
    {
        $canManageTravelOrders = $this->canManageTravelOrders();

        if (! $canManageTravelOrders && $travelOrder->user_id !== auth()->id()) {
            abort(403);
        }

        return view('hr.travel-orders.show', compact(
            'travelOrder',
            'canManageTravelOrders'
        ));
    }

    public function print(TravelOrder $travelOrder)
    {
        $canManageTravelOrders = $this->canManageTravelOrders();

        if (! $canManageTravelOrders && $travelOrder->user_id !== auth()->id()) {
            abort(403);
        }

        return view('hr.travel-orders.print', compact('travelOrder'));
    }

    public function approve(TravelOrder $travelOrder)
    {
        if (! $this->canManageTravelOrders()) {
            abort(403);
        }

        $travelOrder->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        return redirect()
            ->route('hr.travel-orders.show', $travelOrder)
            ->with('success', 'Travel Order approved successfully.');
    }

    public function reject(Request $request, TravelOrder $travelOrder)
    {
        if (! $this->canManageTravelOrders()) {
            abort(403);
        }

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string'],
        ]);

        $travelOrder->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return redirect()
            ->route('hr.travel-orders.show', $travelOrder)
            ->with('success', 'Travel Order rejected successfully.');
    }
}