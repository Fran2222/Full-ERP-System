<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    private function access(string $permission): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->can($permission)
                || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
            ),
            403,
            'Unauthorized sales customer action.'
        );
    }

    private function canAccess(string $permission): bool
    {
        $user = auth()->user();

        return $user && (
            $user->can($permission)
            || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
        );
    }

    public function index(Request $request)
    {
        $this->access('sales.customers.view');

        if ($request->ajax()) {
            $table = (new Customer())->getTable();

            $customers = Customer::query()
                ->select($table . '.*')
                ->orderByDesc($table . '.id');

            $canEditCustomer = $this->canAccess('sales.customers.edit');
            $canDeleteCustomer = $this->canAccess('sales.customers.delete');

            return DataTables::eloquent($customers)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('customer_code', 'ilike', '%' . $search . '%')
                                ->orWhere('customer_name', 'ilike', '%' . $search . '%')
                                ->orWhere('contact_person', 'ilike', '%' . $search . '%')
                                ->orWhere('phone', 'ilike', '%' . $search . '%')
                                ->orWhere('email', 'ilike', '%' . $search . '%')
                                ->orWhere('tin', 'ilike', '%' . $search . '%')
                                ->orWhere('payment_terms', 'ilike', '%' . $search . '%');
                        });
                    }
                })
                ->editColumn('customer_code', function ($row) {
                    return '<span class="fw-semibold text-primary">' . e($row->customer_code) . '</span>';
                })
                ->addColumn('customer_display', function ($row) {
                    $html = '<div class="fw-semibold text-dark">' . e($row->customer_name) . '</div>';

                    if ($row->tin) {
                        $html .= '<div class="text-secondary small">TIN: ' . e($row->tin) . '</div>';
                    }

                    return $html;
                })
                ->editColumn('contact_person', function ($row) {
                    return '<span class="text-secondary">' . e($row->contact_person ?: '-') . '</span>';
                })
                ->editColumn('phone', function ($row) {
                    return '<span class="text-secondary">' . e($row->phone ?: '-') . '</span>';
                })
                ->editColumn('email', function ($row) {
                    return '<span class="text-secondary">' . e($row->email ?: '-') . '</span>';
                })
                ->editColumn('status', function ($row) {
                    if ($row->status) {
                        return '<span class="badge rounded-pill bg-success-subtle text-success px-3 py-2">Active</span>';
                    }

                    return '<span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-2">Inactive</span>';
                })
                ->addColumn('action', function ($row) use ($canEditCustomer, $canDeleteCustomer) {
                    if (!$canEditCustomer && !$canDeleteCustomer) {
                        return '';
                    }

                    $html = '<div class="d-inline-flex gap-1">';

                    if ($canEditCustomer) {
                        $html .= '
                            <a href="' . route('sales.customers.edit', $row) . '"
                               class="wmc-action-btn wmc-action-edit"
                               title="Edit">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z"
                                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        ';
                    }

                    if ($canDeleteCustomer) {
                        $html .= '
                            <button type="button"
                                    class="wmc-action-btn wmc-action-delete delete-customer"
                                    data-url="' . route('sales.customers.destroy', $row) . '"
                                    data-name="' . e($row->customer_name) . '"
                                    title="Delete">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10 11v5M14 11v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>
                        ';
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->rawColumns([
                    'customer_code',
                    'customer_display',
                    'contact_person',
                    'phone',
                    'email',
                    'status',
                    'action',
                ])
                ->toJson();
        }

        return view('sales.customers.index');
    }

    public function create()
    {
        $this->access('sales.customers.create');

        return view('sales.customers.create');
    }

    public function store(Request $request)
    {
        $this->access('sales.customers.create');

        Customer::create($this->validated($request));

        return redirect()
            ->route('sales.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer)
    {
        $this->access('sales.customers.edit');

        return view('sales.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $this->access('sales.customers.edit');

        $customer->update($this->validated($request, $customer->id));

        return redirect()
            ->route('sales.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $this->access('sales.customers.delete');

        $name = $customer->customer_name;
        $customer->delete();

        if (request()->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Customer "' . $name . '" deleted successfully.',
            ]);
        }

        return redirect()
            ->route('sales.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'customer_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'customer_code')->ignore($ignoreId),
            ],
            'customer_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'billing_address' => ['nullable', 'string'],
            'shipping_address' => ['nullable', 'string'],
            'tin' => ['nullable', 'string', 'max:255'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'boolean'],
        ]);

        $data['customer_code'] = strtoupper($data['customer_code']);
        $data['status'] = $request->boolean('status');

        return $data;
    }
}