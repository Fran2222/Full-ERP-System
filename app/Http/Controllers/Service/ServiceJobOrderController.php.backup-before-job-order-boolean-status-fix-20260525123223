<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\Service\ServiceJobOrder;
use App\Models\Service\ServiceStatus;
use App\Models\Service\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ServiceJobOrderController extends Controller
{
    public function index(Request $request)
    {
        $jobOrders = ServiceJobOrder::with(['customer', 'serviceType', 'serviceStatus', 'assignedTo'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('job_order_no', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('site_address', 'like', "%{$search}%")
                        ->orWhere('priority', 'like', "%{$search}%")
                        ->orWhere('status_text', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status_id'), function ($query, $statusId) {
                $query->where('service_status_id', $statusId);
            })
            ->latest('id')
            ->paginate(10)
            ->appends($request->query());

        $statuses = ServiceStatus::orderBy('sort_order')->orderBy('name')->get();

        return view('service.job-orders.index', compact('jobOrders', 'statuses'));
    }

    public function create()
    {
        return view('service.job-orders.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['job_order_no'] = $this->generateJobOrderNo();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();
        $data['status_text'] = $this->statusText($data['service_status_id'] ?? null);

        $jobOrder = ServiceJobOrder::create($data);

        return redirect()->route('service.job-orders.show', $jobOrder)
            ->with('success', 'Service Job Order created successfully.');
    }

    public function show(ServiceJobOrder $jobOrder)
    {
        $jobOrder->load(['customer', 'serviceType', 'serviceStatus', 'branch', 'assignedTo', 'vehicle', 'reports.reportedBy']);

        return view('service.job-orders.show', compact('jobOrder'));
    }

    public function edit(ServiceJobOrder $jobOrder)
    {
        return view('service.job-orders.edit', array_merge($this->formData(), compact('jobOrder')));
    }

    public function update(Request $request, ServiceJobOrder $jobOrder)
    {
        $data = $this->validated($request);
        $data['updated_by'] = auth()->id();
        $data['status_text'] = $this->statusText($data['service_status_id'] ?? null);

        if ($data['status_text'] === 'Completed' && empty($data['completed_at'])) {
            $data['completed_at'] = now();
        }

        $jobOrder->update($data);

        return redirect()->route('service.job-orders.show', $jobOrder)
            ->with('success', 'Service Job Order updated successfully.');
    }

    public function destroy(ServiceJobOrder $jobOrder)
    {
        $jobOrder->delete();

        return redirect()->route('service.job-orders.index')
            ->with('success', 'Service Job Order deleted successfully.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['nullable', 'integer'],
            'service_type_id' => ['nullable', 'integer'],
            'service_status_id' => ['nullable', 'integer'],
            'branch_id' => ['nullable', 'integer'],
            'assigned_to_user_id' => ['nullable', 'integer'],
            'vehicle_id' => ['nullable', 'integer'],
            'subject' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'string', 'max:50'],
            'requested_date' => ['nullable', 'date'],
            'scheduled_at' => ['nullable', 'date'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'site_address' => ['nullable', 'string'],
            'concern' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);
    }

    protected function formData(): array
    {
        return [
            'customers' => $this->tableRows('customers', ['customer_name', 'name'], 'customer_name'),
            'serviceTypes' => Schema::hasTable('service_types') ? ServiceType::where('status', 'active')->orderBy('name')->get() : collect(),
            'statuses' => Schema::hasTable('service_statuses') ? ServiceStatus::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get() : collect(),
            'branches' => $this->tableRows('branches', ['name', 'branch_name'], 'name'),
            'users' => $this->tableRows('users', ['last_name', 'first_name', 'name', 'email'], 'name'),
            'vehicles' => $this->tableRows('vehicles', ['vehicle_code', 'plate_number'], 'vehicle_code'),
        ];
    }

    protected function tableRows(string $table, array $columns, string $orderColumn)
    {
        if (! Schema::hasTable($table)) {
            return collect();
        }

        $query = DB::table($table);

        if (Schema::hasColumn($table, 'status')) {
            $query->where(function ($q) use ($table) {
                $q->where($table . '.status', true)
                    ->orWhere($table . '.status', 'active')
                    ->orWhereNull($table . '.status');
            });
        }

        if (! Schema::hasColumn($table, $orderColumn)) {
            $orderColumn = 'id';
        }

        return $query->orderBy($orderColumn)->get();
    }

    protected function statusText($statusId): string
    {
        if ($statusId && Schema::hasTable('service_statuses')) {
            $status = ServiceStatus::find($statusId);
            if ($status) {
                return $status->name;
            }
        }

        return 'Pending';
    }

    protected function generateJobOrderNo(): string
    {
        $prefix = 'JO-' . now()->format('Ymd') . '-';
        $count = ServiceJobOrder::where('job_order_no', 'like', $prefix . '%')->count() + 1;

        return $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
