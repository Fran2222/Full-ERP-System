<?php

namespace App\Http\Controllers\Service;

use App\Support\ServiceOperationsPhase3Helper;
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





    public function show($job_order)
    {
        $id = is_object($job_order) ? ($job_order->id ?? null) : $job_order;

        abort_if(! \Illuminate\Support\Facades\Schema::hasTable('service_job_orders'), 404);

        $jobOrder = \Illuminate\Support\Facades\DB::table('service_job_orders')
            ->where('id', $id)
            ->first();

        abort_if(! $jobOrder, 404);

        $jobOrder->customer_name = '-';
        $jobOrder->customer_code = '';
        $jobOrder->contact_person = '-';
        $jobOrder->customer_phone = '';
        $jobOrder->customer_email = '';
        $jobOrder->billing_address = '';

        if (!empty($jobOrder->customer_id) && \Illuminate\Support\Facades\Schema::hasTable('customers')) {
            $customer = \Illuminate\Support\Facades\DB::table('customers')->where('id', $jobOrder->customer_id)->first();
            if ($customer) {
                $jobOrder->customer_name = $customer->customer_name ?? $customer->name ?? '-';
                $jobOrder->customer_code = $customer->customer_code ?? $customer->code ?? '';
                $jobOrder->contact_person = $customer->contact_person ?? '-';
                $jobOrder->customer_phone = $customer->phone ?? $customer->contact_number ?? '';
                $jobOrder->customer_email = $customer->email ?? '';
                $jobOrder->billing_address = $customer->billing_address ?? $customer->address ?? '';
            }
        }

        $jobOrder->service_type_name = '-';
        if (!empty($jobOrder->service_type_id) && \Illuminate\Support\Facades\Schema::hasTable('service_types')) {
            $type = \Illuminate\Support\Facades\DB::table('service_types')->where('id', $jobOrder->service_type_id)->first();
            $jobOrder->service_type_name = $type->name ?? '-';
        }

        $jobOrder->service_status_name = '-';
        $jobOrder->service_status_color = '#198754';
        if (!empty($jobOrder->service_status_id) && \Illuminate\Support\Facades\Schema::hasTable('service_statuses')) {
            $status = \Illuminate\Support\Facades\DB::table('service_statuses')->where('id', $jobOrder->service_status_id)->first();
            $jobOrder->service_status_name = $status->name ?? '-';
            $jobOrder->service_status_color = $status->color ?? $status->badge_color ?? '#198754';
        }

        $jobOrder->branch_name = '-';
        if (!empty($jobOrder->branch_id) && \Illuminate\Support\Facades\Schema::hasTable('branches')) {
            $branch = \Illuminate\Support\Facades\DB::table('branches')->where('id', $jobOrder->branch_id)->first();
            $jobOrder->branch_name = $branch->name ?? $branch->branch_name ?? '-';
        }

        $jobOrder->technician_first_name = '';
        $jobOrder->technician_last_name = '';
        $jobOrder->technician_email = '';
        if (!empty($jobOrder->assigned_to_user_id) && \Illuminate\Support\Facades\Schema::hasTable('users')) {
            $user = \Illuminate\Support\Facades\DB::table('users')->where('id', $jobOrder->assigned_to_user_id)->first();
            if ($user) {
                if (!empty($user->name)) {
                    $nameParts = explode(' ', $user->name, 2);
                    $jobOrder->technician_first_name = $nameParts[0] ?? '';
                    $jobOrder->technician_last_name = $nameParts[1] ?? '';
                } else {
                    $jobOrder->technician_first_name = $user->first_name ?? '';
                    $jobOrder->technician_last_name = $user->last_name ?? '';
                }
                $jobOrder->technician_email = $user->email ?? '';
            }
        }

        $jobOrder->vehicle_code = '';
        $jobOrder->plate_number = '';
        $jobOrder->vehicle_brand = '';
        $jobOrder->vehicle_model = '';
        if (!empty($jobOrder->vehicle_id) && \Illuminate\Support\Facades\Schema::hasTable('vehicles')) {
            $vehicle = \Illuminate\Support\Facades\DB::table('vehicles')->where('id', $jobOrder->vehicle_id)->first();
            if ($vehicle) {
                $jobOrder->vehicle_code = $vehicle->vehicle_code ?? $vehicle->code ?? '';
                $jobOrder->plate_number = $vehicle->plate_number ?? '';
                $jobOrder->vehicle_brand = $vehicle->brand ?? '';
                $jobOrder->vehicle_model = $vehicle->model ?? '';
            }
        }

        $reports = collect();
        $reportPhotos = collect();
        $reportMaterials = collect();

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('service_job_order_reports')) {
                $reports = \Illuminate\Support\Facades\DB::table('service_job_order_reports')
                    ->where('service_job_order_id', $jobOrder->id)
                    ->orderByDesc('created_at')
                    ->get();

                foreach ($reports as $report) {
                    $report->reporter_name = '-';
                    if (!empty($report->reported_by_user_id) && \Illuminate\Support\Facades\Schema::hasTable('users')) {
                        $reportUser = \Illuminate\Support\Facades\DB::table('users')->where('id', $report->reported_by_user_id)->first();
                        $report->reporter_name = $reportUser->name
                            ?? trim(($reportUser->first_name ?? '') . ' ' . ($reportUser->last_name ?? ''))
                            ?: ($reportUser->email ?? '-');
                    }

                    $report->status_name = null;
                    if (!empty($report->status_update_id) && \Illuminate\Support\Facades\Schema::hasTable('service_statuses')) {
                        $reportStatus = \Illuminate\Support\Facades\DB::table('service_statuses')->where('id', $report->status_update_id)->first();
                        $report->status_name = $reportStatus->name ?? null;
                    }
                }

                if ($reports->count() && \Illuminate\Support\Facades\Schema::hasTable('service_job_order_report_photos')) {
                    $reportPhotos = \Illuminate\Support\Facades\DB::table('service_job_order_report_photos')
                        ->whereIn('service_job_order_report_id', $reports->pluck('id')->all())
                        ->orderBy('id')
                        ->get()
                        ->groupBy('service_job_order_report_id');
                }

                if ($reports->count()) {
                    $reportMaterials = ServiceOperationsPhase3Helper::materialsForReports($reports->pluck('id')->all());
                }
            }
        } catch (\Throwable $e) {
            report($e);
            $reports = collect();
            $reportPhotos = collect();
            $reportMaterials = collect();
        }

        $assets = [];

        return view('service.job-orders.show', compact('jobOrder', 'reports', 'reportPhotos', 'reportMaterials', 'assets'));
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



    private function tableRows(string $table, array $columns = [], string $orderBy = 'id')
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
            return collect();
        }

        $query = \Illuminate\Support\Facades\DB::table($table);

        /*
         * PostgreSQL-safe status filtering:
         * Do NOT use Schema::getColumnType() here because this Laravel version may require
         * Doctrine DBAL. Instead, read the actual database type from information_schema.
         */
        if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'status')) {
            $statusType = null;

            try {
                $row = \Illuminate\Support\Facades\DB::selectOne(
                    "select data_type, udt_name from information_schema.columns
                     where table_schema = current_schema()
                       and table_name = ?
                       and column_name = ?
                     limit 1",
                    [$table, 'status']
                );

                if ($row) {
                    $statusType = strtolower((string) ($row->udt_name ?: $row->data_type));
                }
            } catch (\Throwable $e) {
                $statusType = null;
            }

            $query->where(function ($statusQuery) use ($table, $statusType) {
                if (in_array($statusType, ['bool', 'boolean'], true)) {
                    $statusQuery->where($table . '.status', true)
                        ->orWhereNull($table . '.status');
                    return;
                }

                if (in_array($statusType, ['int2', 'int4', 'int8', 'smallint', 'integer', 'bigint'], true)) {
                    $statusQuery->where($table . '.status', 1)
                        ->orWhereNull($table . '.status');
                    return;
                }

                /*
                 * Text/varchar fallback.
                 * Keep this only for text status columns like 'active'/'inactive'.
                 */
                $statusQuery->where($table . '.status', 'active')
                    ->orWhere($table . '.status', 'Active')
                    ->orWhere($table . '.status', '1')
                    ->orWhereNull($table . '.status');
            });
        }

        if ($orderBy && \Illuminate\Support\Facades\Schema::hasColumn($table, $orderBy)) {
            $query->orderBy($orderBy);
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn($table, 'name')) {
            $query->orderBy('name');
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn($table, 'id')) {
            $query->orderBy('id');
        }

        return $query->get();
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
