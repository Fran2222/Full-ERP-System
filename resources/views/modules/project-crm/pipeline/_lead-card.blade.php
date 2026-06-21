@php
    $assignedName = optional($lead->assignedUser)->name
        ?? optional($lead->assignedUser)->first_name
        ?? 'Unassigned';

    $assignedInitial = $assignedName !== 'Unassigned'
        ? strtoupper(substr($assignedName, 0, 1))
        : '?';

    $priorityClass = match($lead->priority) {
        'urgent', 'high' => 'danger',
        'medium' => 'warning',
        'low' => 'secondary',
        default => 'secondary',
    };
@endphp

<div class="col-12 group__item crm-lead-card"
     data-lead-id="{{ $lead->id }}"
     data-search="{{ strtolower(($lead->company_name ?? '') . ' ' . ($lead->contact_person ?? '') . ' ' . ($lead->source ?? '') . ' ' . $assignedName) }}">

    <div class="card rounded-4 mb-2 mx-auto"
         style="width: 100%; max-width: 315px; border-left: 4px solid var(--bs-{{ $stage->color ?? 'primary' }}); border-top: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;">
        <div class="card-body p-3">

            {{-- Header --}}
            <div class="d-grid grid-flow-col align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center text-secondary">
                    <p class="mb-0 small">{{ $lead->lead_code }}</p>

                    <svg width="16" viewBox="0 0 24 24" fill="none" class="mx-1">
                        <path d="M8.5 5L15.5 12L8.5 19"
                              stroke="currentColor"
                              stroke-width="1.5"
                              stroke-linecap="round"
                              stroke-linejoin="round"></path>
                    </svg>

                    <p class="mb-0 small">{{ $stage->name }}</p>
                </div>

                <span class="badge bg-soft-{{ $priorityClass }} text-{{ $priorityClass }} px-2 py-1">
                    {{ ucfirst($lead->priority ?? 'Medium') }}
                </span>
            </div>

            {{-- Lead Name --}}
            <h6 class="mb-2 fw-bold text-dark">
                {{ $lead->company_name }}
            </h6>

            {{-- Estimated Value --}}
            <p class="text-secondary small mb-3">
                Est. Value:
                <span class="fw-semibold text-body">
                    {{ $lead->estimated_value ? '₱ ' . number_format($lead->estimated_value, 2) : 'Not set' }}
                </span>
            </p>

            {{-- Action Buttons --}}
            <div class="d-flex align-items-center mb-3">

                {{-- Edit Lead --}}
                <a href="{{ route('crm.leads.edit', $lead->id) }}"
                   class="btn btn-icon btn-soft-light me-2"
                   data-bs-toggle="tooltip"
                   title="Edit Lead">
                    <div class="btn-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" viewBox="0 0 24 24" fill="none">
                            <path d="M17 3.99988L4.99998 3.99998C3.89542 3.99999 3 4.89542 3 5.99998V19C3 20.1046 3.89543 21 5 21H18C19.1046 21 20 20.1046 20 19V6.69039"
                                  stroke="currentColor"
                                  stroke-linecap="round"/>
                            <path d="M12 12L21 3"
                                  stroke="currentColor"
                                  stroke-linecap="round"/>
                        </svg>
                    </div>
                </a>

                {{-- Add Follow-up --}}
                <a href="{{ route('crm.leads.show', $lead->id) }}#followups"
                   class="btn btn-icon btn-soft-light me-2"
                   data-bs-toggle="tooltip"
                   title="Add Follow-up">
                    <div class="btn-inner">
                        <svg width="18" viewBox="0 0 24 24" fill="none">
                            <path d="M3.09277 9.40421H20.9167"
                                  stroke="currentColor"
                                  stroke-width="1.5"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"></path>
                            <path d="M16.442 13.3097H16.4512"
                                  stroke="currentColor"
                                  stroke-width="1.5"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"></path>
                            <path d="M12.0045 13.3097H12.0137"
                                  stroke="currentColor"
                                  stroke-width="1.5"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"></path>
                            <path d="M7.55818 13.3097H7.56744"
                                  stroke="currentColor"
                                  stroke-width="1.5"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"></path>
                            <path d="M16.0433 2V5.29078"
                                  stroke="currentColor"
                                  stroke-width="1.5"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"></path>
                            <path d="M7.96515 2V5.29078"
                                  stroke="currentColor"
                                  stroke-width="1.5"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"></path>
                            <path fill-rule="evenodd"
                                  clip-rule="evenodd"
                                  d="M16.2383 3.5791H7.77096C4.83427 3.5791 3 5.21504 3 8.22213V17.2718C3 20.3261 4.83427 21.9999 7.77096 21.9999H16.229C19.175 21.9999 21 20.3545 21 17.3474V8.22213C21.0092 5.21504 19.1842 3.5791 16.2383 3.5791Z"
                                  stroke="currentColor"
                                  stroke-width="1.5"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"></path>
                        </svg>
                    </div>
                </a>

                {{-- View Lead --}}
                <a href="{{ route('crm.leads.show', $lead->id) }}"
                   class="btn btn-icon btn-soft-light me-2"
                   data-bs-toggle="tooltip"
                   title="View Lead">
                    <div class="btn-inner">
                        <svg width="18" viewBox="0 0 24 24" fill="none">
                            <path d="M2 12C2 12 5.636 5.333 12 5.333C18.364 5.333 22 12 22 12C22 12 18.364 18.667 12 18.667C5.636 18.667 2 12 2 12Z"
                                  stroke="currentColor"
                                  stroke-width="1.5"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                            <path d="M12 14.5C13.3807 14.5 14.5 13.3807 14.5 12C14.5 10.6193 13.3807 9.5 12 9.5C10.6193 9.5 9.5 10.6193 9.5 12C9.5 13.3807 10.6193 14.5 12 14.5Z"
                                  stroke="currentColor"
                                  stroke-width="1.5"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </div>
                </a>

                {{-- More Actions --}}
                <div class="dropdown">
                    <button type="button"
                            class="btn btn-icon btn-soft-light me-2"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        <div class="btn-inner">
                            <svg width="18" viewBox="0 0 24 24" fill="none">
                                <path d="M19 8.5L12 15.5L5 8.5"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"></path>
                            </svg>
                        </div>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end">
                       @if($lead->client_id)
                            <a class="dropdown-item" href="{{ route('clients.show', $lead->client_id) }}">
                                View Client
                            </a>
                        @else
                            <form action="{{ route('crm.leads.convert-client', $lead->id) }}"
                                method="POST">
                                @csrf

                                <button type="submit" class="dropdown-item">
                                    Convert to Client
                                </button>
                            </form>
                        @endif

                        <a class="dropdown-item"
                        href="{{ route('crm.leads.create-project', $lead->id) }}">
                            Create Project
                        </a>

                        <div class="dropdown-divider"></div>

                        <form action="{{ route('crm.leads.archive', $lead->id) }}"
                            method="POST"
                            class="js-archive-lead-form">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="dropdown-item text-danger">
                                Archive Lead
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Assigned Staff --}}
                <div class="iq-media-group-1">
                    <a href="#" class="iq-media-1">
                        <div class="icon iq-icon-box-2 text-primary"
                             data-bs-toggle="tooltip"
                             title="Assigned: {{ $assignedName }}">
                            {{ $assignedInitial }}
                        </div>
                    </a>

                    <a href="#" class="iq-media-1">
                        <div class="icon iq-icon-box-2 text-danger"
                             data-bs-toggle="tooltip"
                             title="Assign Staff">
                            <svg width="16" height="16" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
                            </svg>
                        </div>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>