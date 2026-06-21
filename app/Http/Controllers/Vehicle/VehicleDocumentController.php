<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use App\Models\VehicleDocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class VehicleDocumentController extends Controller
{
    protected string $table = 'vehicle_documents';

    public function index(Request $request)
    {
        $query = VehicleDocument::query()
            ->with(['vehicle.type'])
            ->latest($this->hasColumn('expiry_date') ? 'expiry_date' : 'id');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($q) use ($search) {
                foreach (['document_type', 'document_no', 'issuing_agency', 'remarks', 'status'] as $column) {
                    if ($this->hasColumn($column)) {
                        $q->orWhere($column, 'like', "%{$search}%");
                    }
                }

                $q->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                    $vehicleQuery->where('vehicle_code', 'like', "%{$search}%")
                        ->orWhere('plate_number', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('vehicle_id') && $this->hasColumn('vehicle_id')) {
            $query->where('vehicle_id', $request->input('vehicle_id'));
        }

        if ($request->filled('document_type') && $this->hasColumn('document_type')) {
            $query->where('document_type', $request->input('document_type'));
        }

        if ($request->filled('expiry_filter') && $this->hasColumn('expiry_date')) {
            $filter = $request->input('expiry_filter');
            $today = now()->toDateString();

            if ($filter === 'expired') {
                $query->whereDate('expiry_date', '<', $today);
            } elseif ($filter === '30_days') {
                $query->whereDate('expiry_date', '>=', $today)
                    ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString());
            } elseif ($filter === '60_days') {
                $query->whereDate('expiry_date', '>=', $today)
                    ->whereDate('expiry_date', '<=', now()->addDays(60)->toDateString());
            } elseif ($filter === 'no_expiry') {
                $query->whereNull('expiry_date');
            }
        }

        $documents = $query->paginate(10)->appends($request->query());

        return view('vehicle.documents.index', [
            'assets' => [],
            'documents' => $documents,
            'vehicles' => $this->vehiclesForDropdown(),
            'documentTypes' => $this->documentTypes(),
            'filters' => $request->only(['search', 'vehicle_id', 'document_type', 'expiry_filter']),
        ]);
    }

    public function create()
    {
        return view('vehicle.documents.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);
        $data = $this->filterExistingColumns($validated);

        if ($this->hasColumn('created_by')) {
            $data['created_by'] = Auth::id();
        }

        if ($this->hasColumn('updated_by')) {
            $data['updated_by'] = Auth::id();
        }

        $fileColumn = $this->fileColumn();
        if ($fileColumn && $request->hasFile('attachment')) {
            $data[$fileColumn] = $request->file('attachment')->store('vehicle-documents', 'public');
        }

        VehicleDocument::create($data);

        return redirect()
            ->route('vehicle.documents.index')
            ->with('success', 'Vehicle document saved successfully.');
    }

    public function show(VehicleDocument $document)
    {
        $document->load(['vehicle.type']);

        return view('vehicle.documents.show', [
            'assets' => [],
            'document' => $document,
        ]);
    }

    public function edit(VehicleDocument $document)
    {
        return view('vehicle.documents.edit', $this->formData($document));
    }

    public function update(Request $request, VehicleDocument $document)
    {
        $validated = $this->validatedData($request);
        $data = $this->filterExistingColumns($validated);

        if ($this->hasColumn('updated_by')) {
            $data['updated_by'] = Auth::id();
        }

        $fileColumn = $this->fileColumn();
        if ($fileColumn && $request->hasFile('attachment')) {
            if (!empty($document->{$fileColumn})) {
                Storage::disk('public')->delete($document->{$fileColumn});
            }

            $data[$fileColumn] = $request->file('attachment')->store('vehicle-documents', 'public');
        }

        $document->update($data);

        return redirect()
            ->route('vehicle.documents.index')
            ->with('success', 'Vehicle document updated successfully.');
    }

    public function destroy(VehicleDocument $document)
    {
        $fileColumn = $this->fileColumn();

        if ($fileColumn && !empty($document->{$fileColumn})) {
            Storage::disk('public')->delete($document->{$fileColumn});
        }

        $document->delete();

        return redirect()
            ->route('vehicle.documents.index')
            ->with('success', 'Vehicle document deleted successfully.');
    }

    protected function formData(?VehicleDocument $document = null): array
    {
        return [
            'assets' => [],
            'document' => $document,
            'vehicles' => $this->vehiclesForDropdown(),
            'documentTypes' => $this->documentTypes(),
            'statuses' => [
                'active' => 'Active',
                'expired' => 'Expired',
                'renewed' => 'Renewed',
                'cancelled' => 'Cancelled',
            ],
        ];
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'document_type' => ['required', 'string', 'max:100'],
            'document_no' => ['nullable', 'string', 'max:255'],
            'issuing_agency' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'renewal_date' => ['nullable', 'date'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);
    }

    protected function filterExistingColumns(array $data): array
    {
        return collect($data)
            ->except(['attachment'])
            ->filter(fn ($value, $key) => $this->hasColumn($key))
            ->toArray();
    }

    protected function hasColumn(string $column): bool
    {
        return Schema::hasTable($this->table) && Schema::hasColumn($this->table, $column);
    }

    protected function fileColumn(): ?string
    {
        foreach (['attachment_path', 'file_path', 'document_path', 'path'] as $column) {
            if ($this->hasColumn($column)) {
                return $column;
            }
        }

        return null;
    }

    protected function vehiclesForDropdown()
    {
        return Vehicle::query()
            ->orderBy('vehicle_code')
            ->orderBy('plate_number')
            ->get();
    }

    protected function documentTypes(): array
    {
        if (class_exists(VehicleDocumentType::class) && Schema::hasTable('vehicle_document_types')) {
            $query = VehicleDocumentType::query();

            if (Schema::hasColumn('vehicle_document_types', 'status')) {
                $query->where(function ($q) {
                    $q->where('status', 'active')->orWhereNull('status');
                });
            }

            if (Schema::hasColumn('vehicle_document_types', 'is_active')) {
                $query->where(function ($q) {
                    $q->where('is_active', true)->orWhereNull('is_active');
                });
            }

            return $query->orderBy('name')
                ->get()
                ->mapWithKeys(fn ($type) => [$type->name => $type->name])
                ->toArray();
        }

        return [
            'OR/CR' => 'OR/CR',
            'Registration' => 'Registration',
            'Insurance' => 'Insurance',
            'Emission Test' => 'Emission Test',
            'Permit' => 'Permit',
            'Franchise' => 'Franchise',
            'Maintenance Receipt' => 'Maintenance Receipt',
            'Other' => 'Other',
        ];
    }
}
