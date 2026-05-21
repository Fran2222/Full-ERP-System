<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">

        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="card-title mb-1">Apply Travel Order</h4>
                    <p class="text-secondary mb-0">Fill out the travel order request form.</p>
                </div>

                <a href="{{ route('hr.travel-orders.index') }}" class="btn btn-light btn-sm rounded-3">
                    Back
                </a>
            </div>

            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger rounded-3">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="alert alert-info rounded-3">
                    <strong>Note:</strong>
                    For proper HR and Accounting preparation, especially for fund processing, please submit your Travel Order request at least 
                    <strong>2–3 days before your travel date.</strong>
                </div>

                <form method="POST" action="{{ route('hr.travel-orders.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date"
                                name="order_date"
                                class="form-control"
                                value="{{ old('order_date', now()->format('Y-m-d')) }}"
                                required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Employees Authorized to Travel <span class="text-danger">*</span></label>
                            <textarea name="employees_authorized"
                                      rows="4"
                                      class="form-control"
                                      placeholder="Enter one employee per line"
                                      required>{{ old('employees_authorized', auth()->user()->full_name ?? auth()->user()->name ?? '') }}</textarea>
                            <small class="text-secondary">Example: Juan Dela Cruz, Maria Santos, etc.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Travel Start Date <span class="text-danger">*</span></label>
                            <input type="date"
                                   name="travel_start_date"
                                   class="form-control"
                                   value="{{ old('travel_start_date') }}"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Travel End Date <span class="text-danger">*</span></label>
                            <input type="date"
                                   name="travel_end_date"
                                   class="form-control"
                                   value="{{ old('travel_end_date') }}"
                                   required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">You are hereby authorized to travel to <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="destination"
                                   class="form-control"
                                   value="{{ old('destination') }}"
                                   placeholder="Enter destination"
                                   required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Purpose <span class="text-danger">*</span></label>
                            <textarea name="purpose_a"
                                    rows="4"
                                    class="form-control"
                                    placeholder="Enter travel purpose"
                                    required>{{ old('purpose_a') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Remarks / Notes</label>
                            <textarea name="remarks"
                                      rows="3"
                                      class="form-control">{{ old('remarks') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('hr.travel-orders.index') }}" class="btn btn-light rounded-3">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary rounded-3">
                            Submit Travel Order
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</x-app-layout>