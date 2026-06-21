<x-app-layout :assets="$assets ?? []">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    

    <style>
        .hope-widget-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(17, 38, 146, 0.05);
            transition: all 0.2s ease;
        }

        .hope-widget-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 35px rgba(17, 38, 146, 0.08);
        }

        .hope-widget-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: rgba(58, 87, 232, 0.10);
            color: #3a57e8;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hope-widget-icon svg path,
        .hope-widget-icon svg circle,
        .hope-widget-icon svg rect {
            stroke: currentColor;
        }

        .crm-info-label {
            color: #8a92a6;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .crm-info-value {
            color: #232d42;
            font-weight: 600;
            margin-bottom: 0;
        }

       .input-button {
            cursor: pointer;
        }

        .crm-activity-hidden {
            display: none !important;
        }
        
    </style>

    @php
        $priorityClass = match($lead->priority) {
            'urgent', 'high' => 'danger',
            'medium' => 'warning',
            'low' => 'secondary',
            default => 'secondary',
        };

        $followUpCount = $lead->followUps->count();
        $pendingFollowUpCount = $lead->followUps->where('status', 'pending')->count();

        $assignedName = $lead->assignedUser->name
            ?? trim(($lead->assignedUser->first_name ?? '') . ' ' . ($lead->assignedUser->last_name ?? ''))
            ?: 'Unassigned';

        $creatorName = $lead->creator->name
            ?? trim(($lead->creator->first_name ?? '') . ' ' . ($lead->creator->last_name ?? ''))
            ?: '-';

        $updaterName = $lead->updater->name
            ?? trim(($lead->updater->first_name ?? '') . ' ' . ($lead->updater->last_name ?? ''))
            ?: '-';
    @endphp

    <div class="container-fluid content-inner mt-n5 py-0">

        {{-- Header --}}
        <div class="card rounded-4 mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <span class="badge bg-soft-primary text-primary">
                                {{ $lead->lead_code }}
                            </span>

                            <span class="badge bg-soft-{{ $lead->stage->color ?? 'primary' }} text-{{ $lead->stage->color ?? 'primary' }}">
                                {{ $lead->stage->name ?? 'No Stage' }}
                            </span>

                            <span class="badge bg-soft-{{ $priorityClass }} text-{{ $priorityClass }}">
                                {{ ucfirst($lead->priority ?? 'Medium') }}
                            </span>

                            @if($lead->client)
                                <span class="badge bg-soft-success text-success">
                                    Converted
                                </span>
                            @endif
                        </div>

                        <h4 class="mb-1">{{ $lead->company_name }}</h4>

                        <p class="text-secondary mb-0">
                            Lead details, follow-ups, client link, and activity history.
                        </p>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('crm.pipeline') }}" class="btn btn-light btn-sm">
                            Back To Pipeline
                        </a>

                        <a href="{{ route('crm.leads.edit', $lead->id) }}" class="btn btn-primary btn-sm">
                            Edit Lead
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Widgets --}}
        <div class="row g-3 mb-3">
            <div class="col-lg-3 col-md-6">
                <div class="card hope-widget-card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="hope-widget-icon">
                            <svg width="26" viewBox="0 0 24 24" fill="none">
                                <path d="M12 3V21" stroke-width="2" stroke-linecap="round"/>
                                <path d="M17 7.5C16.2 6.5 14.9 6 13.2 6H10.5C8.6 6 7 7.3 7 9C7 10.6 8.3 11.4 10.2 11.8L13.8 12.6C15.7 13 17 13.8 17 15.5C17 17.2 15.4 18.5 13.5 18.5H10.8C9.1 18.5 7.8 18 7 17" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>

                        <div class="text-end">
                            <p class="text-muted mb-1">Estimated Value</p>
                            <h5 class="mb-0">
                                {{ $lead->estimated_value ? '₱ ' . number_format($lead->estimated_value, 2) : 'Not set' }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card hope-widget-card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="hope-widget-icon">
                            <svg width="26" viewBox="0 0 24 24" fill="none">
                                <path d="M12 12C14.2091 12 16 10.2091 16 8C16 5.79086 14.2091 4 12 4C9.79086 4 8 5.79086 8 8C8 10.2091 9.79086 12 12 12Z" stroke-width="2"/>
                                <path d="M4 20C4 16.6863 7.58172 14 12 14C16.4183 14 20 16.6863 20 20" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>

                        <div class="text-end">
                            <p class="text-muted mb-1">Assigned To</p>
                            <h5 class="mb-0">{{ $assignedName }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="#followups" class="text-decoration-none">
                    <div class="card hope-widget-card h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="hope-widget-icon">
                                <svg width="26" viewBox="0 0 24 24" fill="none">
                                    <path d="M7 2V5M17 2V5M3 9H21" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M5 5H19C20.1046 5 21 5.89543 21 7V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V7C3 5.89543 3.89543 5 5 5Z" stroke-width="2" stroke-linejoin="round"/>
                                    <path d="M9 14H15" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>

                            <div class="text-end">
                                <p class="text-muted mb-1">Pending Follow-ups</p>
                                <h5 class="mb-0">
                                    {{ $pendingFollowUpCount }}
                                    <span class="text-secondary fs-6">/ {{ $followUpCount }}</span>
                                </h5>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                @if($lead->client)
                    <a href="{{ route('clients.show', $lead->client->id) }}" class="text-decoration-none">
                @else
                    <a href="#" class="text-decoration-none">
                @endif
                    <div class="card hope-widget-card h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="hope-widget-icon">
                                <svg width="26" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 7C3 5.89543 3.89543 5 5 5H9L11 7H19C20.1046 7 21 7.89543 21 9V18C21 19.1046 20.1046 20 19 20H5C3.89543 20 3 19.1046 3 18V7Z" stroke-width="2" stroke-linejoin="round"/>
                                    <path d="M9 13L11 15L16 10" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>

                            <div class="text-end">
                                <p class="text-muted mb-1">Converted Client</p>
                                @if($lead->client)
                                    <h6 class="mb-0">
                                        {{ $lead->client->code }} - {{ \Illuminate\Support\Str::limit($lead->client->name, 22) }}
                                    </h6>
                                @else
                                    <h5 class="mb-0">Not converted</h5>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row g-3">

            {{-- Main Content --}}
            <div class="col-xl-8 col-lg-7">

                {{-- Lead & Contact Details --}}
                <div class="card rounded-4 mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Lead & Contact Details</h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <p class="crm-info-label">Company / Lead Name</p>
                                <h6 class="crm-info-value">{{ $lead->company_name }}</h6>
                            </div>

                            <div class="col-md-6">
                                <p class="crm-info-label">Pipeline Stage</p>
                                <h6 class="crm-info-value">{{ $lead->stage->name ?? '-' }}</h6>
                            </div>

                            <div class="col-md-6">
                                <p class="crm-info-label">Priority</p>
                                <h6 class="mb-0 text-{{ $priorityClass }}">
                                    {{ ucfirst($lead->priority ?? 'Medium') }}
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <p class="crm-info-label">Source</p>
                                <h6 class="crm-info-value">{{ $lead->source ?? '-' }}</h6>
                            </div>

                            <div class="col-md-6">
                                <p class="crm-info-label">Contact Person</p>
                                <h6 class="crm-info-value">{{ $lead->contact_person ?? '-' }}</h6>
                            </div>

                            <div class="col-md-6">
                                <p class="crm-info-label">Phone</p>
                                <h6 class="crm-info-value">{{ $lead->phone ?? '-' }}</h6>
                            </div>

                            <div class="col-md-6">
                                <p class="crm-info-label">Email</p>
                                <h6 class="crm-info-value">{{ $lead->email ?? '-' }}</h6>
                            </div>

                            <div class="col-md-6">
                                <p class="crm-info-label">Expected Close Date</p>
                                <h6 class="crm-info-value">
                                    {{ $lead->expected_close_date ? $lead->expected_close_date->format('M d, Y') : '-' }}
                                </h6>
                            </div>

                            <div class="col-12">
                                <p class="crm-info-label">Address</p>
                                <h6 class="crm-info-value">{{ $lead->address ?? '-' }}</h6>
                            </div>
                        </div>
                    </div>
                </div>

          

               {{-- Activity Timeline --}}
                <div class="card rounded-4 mb-3" id="activity-section">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h5 class="mb-0">Activity Timeline</h5>
                            <small class="text-secondary">
                                Recent lead activities and updates.
                            </small>
                        </div>

                        <span class="badge bg-soft-primary text-primary">
                            {{ $lead->activities->count() }} Activities
                        </span>
                    </div>

                    <div class="card-body">

                      {{-- Date Range Filter --}}
                        <div class="row g-2 align-items-end justify-content-end mb-4">
                            <div class="col-md-7">
                                <div class="input-group wrap_flatpicker" id="crmActivityDateRangeWrap">
                                    <input type="text"
                                        id="crmActivityDateRange"
                                        name="activity_date_range"
                                        class="form-control"
                                        placeholder="Range Date Picker"
                                        data-input
                                        readonly>

                                    <a class="input-group-text input-button"
                                    title="Toggle date picker"
                                    data-toggle
                                    href="javascript:void(0)">
                                        <svg width="20" class="icon-20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </a>

                                    <a class="input-group-text input-button"
                                    id="crmActivityClearFilter"
                                    title="Clear date range"
                                    data-clear
                                    href="javascript:void(0)">
                                        <svg width="20" class="icon-20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div id="crmActivityList">
                            @forelse($lead->activities->sortByDesc('created_at') as $activity)
                                @php
                                    $activityDate = $activity->created_at
                                        ? $activity->created_at->format('Y-m-d')
                                        : '';

                                    $activityUser = $activity->user->name
                                        ?? trim(($activity->user->first_name ?? '') . ' ' . ($activity->user->last_name ?? ''))
                                        ?: 'System';
                                @endphp

                                <div class="crm-activity-item d-flex gap-3 mb-3"
                                    data-activity-date="{{ $activityDate }}">
                                    <div>
                                        <div class="avatar-40 rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center">
                                            <svg width="18" viewBox="0 0 24 24" fill="none">
                                                <path d="M12 8V12L15 14"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                                <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"/>
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                            <h6 class="mb-1">
                                                {{ ucwords(str_replace('_', ' ', $activity->activity_type)) }}
                                            </h6>

                                            <small class="text-secondary">
                                                {{ $activity->created_at ? $activity->created_at->format('M d, Y h:i A') : '-' }}
                                            </small>
                                        </div>

                                        <p class="text-secondary mb-1">
                                            {{ $activity->description }}
                                        </p>

                                        <small class="text-secondary">
                                            By {{ $activityUser }}
                                        </small>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <p class="text-secondary mb-0">No activities recorded yet.</p>
                                </div>
                            @endforelse
                        </div>

                        <div id="crmActivityEmptyState" class="text-center py-4 d-none">
                            <p class="text-secondary mb-0">No activities found for selected date range.</p>
                        </div>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3">
                            <small class="text-secondary" id="crmActivityShowingText"></small>

                            <button type="button"
                                    class="btn btn-light btn-sm"
                                    id="crmActivityToggleBtn">
                                Show More
                            </button>
                        </div>

                    </div>
                </div>

            </div>

            {{-- Side Content --}}
            <div class="col-xl-4 col-lg-5">

                {{-- Follow-ups --}}
                <div class="card rounded-4 mb-3" id="followups">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Follow-ups</h5>

                        <button type="button"
                                class="btn btn-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#crmAddFollowUpModal">
                            Add
                        </button>
                    </div>

                    <div class="card-body">
                        @forelse($lead->followUps->sortByDesc('scheduled_at') as $followUp)
                            @php
                                $followUpClass = match($followUp->status) {
                                    'completed' => 'success',
                                    'missed' => 'danger',
                                    'cancelled' => 'secondary',
                                    default => 'warning',
                                };
                            @endphp

                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                    <div>
                                        <h6 class="mb-1">
                                            {{ ucwords(str_replace('_', ' ', $followUp->follow_up_type)) }}
                                        </h6>

                                        <span class="badge bg-soft-{{ $followUpClass }} text-{{ $followUpClass }}">
                                            {{ ucfirst($followUp->status ?? 'pending') }}
                                        </span>
                                    </div>

                                    @if(($followUp->status ?? 'pending') === 'pending')
                                        <div class="dropdown">
                                            <button type="button"
                                                    class="btn btn-soft-light btn-sm"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                Action
                                            </button>

                                            <div class="dropdown-menu dropdown-menu-end">
                                                <form action="{{ route('crm.follow-ups.update-status', $followUp->id) }}"
                                                      method="POST">
                                                    @csrf
                                                    @method('PATCH')

                                                    <input type="hidden" name="status" value="completed">

                                                    <button type="submit" class="dropdown-item text-success">
                                                        Mark Completed
                                                    </button>
                                                </form>

                                                <form action="{{ route('crm.follow-ups.update-status', $followUp->id) }}"
                                                      method="POST">
                                                    @csrf
                                                    @method('PATCH')

                                                    <input type="hidden" name="status" value="missed">

                                                    <button type="submit" class="dropdown-item text-danger">
                                                        Mark Missed
                                                    </button>
                                                </form>

                                                <form action="{{ route('crm.follow-ups.update-status', $followUp->id) }}"
                                                      method="POST">
                                                    @csrf
                                                    @method('PATCH')

                                                    <input type="hidden" name="status" value="cancelled">

                                                    <button type="submit" class="dropdown-item text-secondary">
                                                        Cancel Follow-up
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <small class="text-secondary d-block mb-1">
                                    {{ $followUp->scheduled_at ? $followUp->scheduled_at->format('M d, Y h:i A') : 'No schedule' }}
                                </small>

                                <p class="text-secondary mb-0">
                                    {{ $followUp->remarks ?? 'No remarks.' }}
                                </p>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <p class="text-secondary mb-0">No follow-ups added yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Dates --}}
                <div class="card rounded-4 mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Dates</h5>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <p class="crm-info-label">Next Follow-up Date</p>
                            <h6 class="crm-info-value">
                                {{ $lead->next_follow_up_date ? $lead->next_follow_up_date->format('M d, Y') : '-' }}
                            </h6>
                        </div>

                        <div class="mb-3">
                            <p class="crm-info-label">Created Date</p>
                            <h6 class="crm-info-value">
                                {{ $lead->created_at ? $lead->created_at->format('M d, Y h:i A') : '-' }}
                            </h6>
                        </div>

                        <div>
                            <p class="crm-info-label">Last Updated</p>
                            <h6 class="crm-info-value">
                                {{ $lead->updated_at ? $lead->updated_at->format('M d, Y h:i A') : '-' }}
                            </h6>
                        </div>
                    </div>
                </div>

                {{-- Record Info --}}
                <div class="card rounded-4">
                    <div class="card-header">
                        <h5 class="mb-0">Record Info</h5>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <p class="crm-info-label">Created By</p>
                            <h6 class="crm-info-value">{{ $creatorName }}</h6>
                        </div>

                        <div>
                            <p class="crm-info-label">Last Updated By</p>
                            <h6 class="crm-info-value">{{ $updaterName }}</h6>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="card rounded-4 mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">Notes</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-0 text-secondary" style="white-space: pre-line;">
                            {{ $lead->notes ?? 'No notes added yet.' }}
                        </p>
                    </div>
                </div>

            </div>

        </div>

    </div>

    {{-- Add Follow-up Modal --}}
    <div class="modal fade" id="crmAddFollowUpModal" tabindex="-1" aria-labelledby="crmAddFollowUpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('crm.leads.follow-ups.store', $lead->id) }}"
                  method="POST"
                  class="modal-content rounded-4 needs-validation"
                  novalidate>
                @csrf

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="crmAddFollowUpModalLabel">Add Follow-up</h5>
                        <p class="text-secondary mb-0 small">
                            Schedule the next action for this lead.
                        </p>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">
                                Follow-up Type <span class="text-danger">*</span>
                            </label>

                            <select name="follow_up_type"
                                    class="form-select @error('follow_up_type') is-invalid @enderror"
                                    required>
                                <option value="">Select Follow-up Type</option>
                                <option value="call" {{ old('follow_up_type') === 'call' ? 'selected' : '' }}>Call</option>
                                <option value="email" {{ old('follow_up_type') === 'email' ? 'selected' : '' }}>Email</option>
                                <option value="meeting" {{ old('follow_up_type') === 'meeting' ? 'selected' : '' }}>Meeting</option>
                                <option value="site_visit" {{ old('follow_up_type') === 'site_visit' ? 'selected' : '' }}>Site Visit</option>
                                <option value="quotation" {{ old('follow_up_type') === 'quotation' ? 'selected' : '' }}>Quotation Follow-up</option>
                                <option value="other" {{ old('follow_up_type') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>

                            <div class="invalid-feedback">
                                @error('follow_up_type')
                                    {{ $message }}
                                @else
                                    Please select a follow-up type.
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Schedule <span class="text-danger">*</span>
                            </label>

                            <div class="input-group has-validation">
                                <span class="input-group-text">
                                    <svg width="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M7 2V5M17 2V5M3 9H21M5 5H19C20.1046 5 21 5.89543 21 7V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V7C3 5.89543 3.89543 5 5 5Z"
                                              stroke="currentColor"
                                              stroke-width="1.5"
                                              stroke-linecap="round"/>
                                    </svg>
                                </span>

                                <input type="text"
                                       id="crm_follow_up_scheduled_at"
                                       name="scheduled_at"
                                       class="form-control @error('scheduled_at') is-invalid @enderror"
                                       placeholder="Select follow-up date and time"
                                       value="{{ old('scheduled_at') }}"
                                       readonly
                                       required>

                                <div class="invalid-feedback">
                                    @error('scheduled_at')
                                        {{ $message }}
                                    @else
                                        Please select a follow-up schedule.
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Remarks</label>

                            <textarea name="remarks"
                                      class="form-control @error('remarks') is-invalid @enderror"
                                      rows="4"
                                      placeholder="Example: Call client to confirm quotation review.">{{ old('remarks') }}</textarea>

                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Save Follow-up
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            /*
             * Bootstrap validation
             */
            const forms = document.querySelectorAll('.needs-validation');

            forms.forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }

                    form.classList.add('was-validated');
                }, false);
            });

            /*
             * Follow-up schedule picker
             */
            if (typeof flatpickr !== 'undefined') {
                const followUpInput = document.getElementById('crm_follow_up_scheduled_at');

                if (followUpInput) {
                    flatpickr(followUpInput, {
                        enableTime: true,
                        dateFormat: 'Y-m-d H:i',
                        allowInput: false,
                        clickOpens: true,
                        disableMobile: true
                    });
                }
            }

            /*
             * Re-open follow-up modal after validation error
             */
            @if($errors->has('follow_up_type') || $errors->has('scheduled_at') || $errors->has('remarks'))
                const followUpModalEl = document.getElementById('crmAddFollowUpModal');

                if (followUpModalEl && typeof bootstrap !== 'undefined') {
                    const followUpModal = new bootstrap.Modal(followUpModalEl);
                    followUpModal.show();
                }
            @endif

            /*
             * Activity Timeline date range filter + default recent 5 activities
             */
            const activityItems = Array.from(document.querySelectorAll('.crm-activity-item'));
            const activityRangeInput = document.getElementById('crmActivityDateRange');
            const activityClearBtn = document.getElementById('crmActivityClearFilter');
            const activityToggleBtn = document.getElementById('crmActivityToggleBtn');
            const activityShowingText = document.getElementById('crmActivityShowingText');
            const activityEmptyState = document.getElementById('crmActivityEmptyState');

            let activityStartDate = null;
            let activityEndDate = null;
            let activityExpanded = false;
            const activityDefaultLimit = 5;

            function parseActivityDate(value) {
                if (!value) {
                    return null;
                }

                const date = new Date(value + 'T00:00:00');

                if (Number.isNaN(date.getTime())) {
                    return null;
                }

                return date;
            }

            function getFilteredActivityItems() {
                return activityItems.filter(function (item) {
                    const itemDate = parseActivityDate(item.dataset.activityDate);

                    if (!itemDate) {
                        return false;
                    }

                    if (activityStartDate && itemDate < activityStartDate) {
                        return false;
                    }

                    if (activityEndDate && itemDate > activityEndDate) {
                        return false;
                    }

                    return true;
                });
            }

           function renderActivityItems() {
            const filteredItems = getFilteredActivityItems();
            const total = filteredItems.length;
            const visibleLimit = activityExpanded ? total : activityDefaultLimit;
            const visibleItems = filteredItems.slice(0, visibleLimit);

            activityItems.forEach(function (item) {
                item.classList.add('crm-activity-hidden');
            });

            visibleItems.forEach(function (item) {
                item.classList.remove('crm-activity-hidden');
            });

            if (activityEmptyState) {
                activityEmptyState.classList.toggle('d-none', total > 0);
            }

            if (activityShowingText) {
                if (total === 0) {
                    activityShowingText.textContent = 'Showing 0 activities';
                } else {
                    activityShowingText.textContent = 'Showing '
                        + Math.min(visibleLimit, total)
                        + ' of '
                        + total
                        + ' activities';
                }
            }

            if (activityToggleBtn) {
                if (total <= activityDefaultLimit) {
                    activityToggleBtn.classList.add('d-none');
                } else {
                    activityToggleBtn.classList.remove('d-none');
                    activityToggleBtn.textContent = activityExpanded ? 'Show Less' : 'Show More';
                }
            }
        }

           const activityRangeWrap = document.getElementById('crmActivityDateRangeWrap');

            if (typeof flatpickr !== 'undefined' && activityRangeWrap) {
                flatpickr(activityRangeWrap, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    allowInput: false,
                    clickOpens: false,
                    disableMobile: true,
                    wrap: true,
                    onChange: function (selectedDates) {
                        activityStartDate = selectedDates[0] || null;
                        activityEndDate = selectedDates[1] || selectedDates[0] || null;
                        activityExpanded = false;
                        renderActivityItems();
                    }
                });
            }

           if (activityClearBtn) {
                activityClearBtn.addEventListener('click', function () {
                    activityStartDate = null;
                    activityEndDate = null;
                    activityExpanded = false;

                    if (activityRangeWrap && activityRangeWrap._flatpickr) {
                        activityRangeWrap._flatpickr.clear();
                    }

                    renderActivityItems();
                });
            }
                        if (activityToggleBtn) {
                activityToggleBtn.addEventListener('click', function () {
                    activityExpanded = !activityExpanded;
                    renderActivityItems();
                });
            }

            renderActivityItems();
        });
    </script>
@endpush

</x-app-layout>