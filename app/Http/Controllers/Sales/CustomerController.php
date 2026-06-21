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

                    $html = '<div class="d-flex align-items-center justify-content-center gap-2 wmc-action-buttons">';

                    if ($canEditCustomer) {
                        $html .= '
                            <a href="' . route('sales.customers.edit', $row) . '"
                               class="btn btn-sm btn-primary wmc-action-btn"
                               title="Edit"
                               aria-label="Edit">
                                <i class="icon d-inline-flex align-items-center justify-content-center">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M13.747 3.41095L20.589 10.2529L7.84302 23H1.00098V16.157L13.747 3.41095Z"
                                            stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </i>
                            </a>
                        ';
                    }

                    if ($canDeleteCustomer) {
                        $html .= '
                            <button type="button"
                                    class="btn btn-sm btn-danger wmc-action-btn delete-customer"
                                    data-url="' . route('sales.customers.destroy', $row) . '"
                                    data-name="' . e($row->customer_name) . '"
                                    title="Delete"
                                    aria-label="Delete">
                                <i class="icon d-inline-flex align-items-center justify-content-center">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M3 6H5H21" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M19 6L18.2 19C18.1 20.1 17.2 21 16.1 21H7.9C6.8 21 5.9 20.1 5.8 19L5 6"
                                            stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M10 11V17" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M14 11V17" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M9 6V4C9 3.4 9.4 3 10 3H14C14.6 3 15 3.4 15 4V6"
                                            stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </i>
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