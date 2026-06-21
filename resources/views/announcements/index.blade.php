<x-app-layout :assets="$assets ?? []">
    <div class="container-fluid content-inner mt-n5 py-0 wmc-announcement-page">
        <style>
            .wmc-announcement-page {
                padding-left: 0.55rem !important;
                padding-right: 0.55rem !important;
                padding-bottom: 60px !important;
            }

            .wmc-announcement-card {
                width: 100%;
                max-width: 100%;
                overflow: hidden;
            }

            .wmc-announcement-card .card-header,
            .wmc-announcement-card .card-body {
                padding-left: 0.85rem;
                padding-right: 0.85rem;
            }

            .wmc-announcement-table-wrap {
                width: 100%;
                overflow-x: hidden;
                padding-bottom: 0.25rem;
            }

            .wmc-announcement-table {
                width: 100%;
                min-width: 0;
                table-layout: fixed;
                font-size: 12.6px;
            }

            .wmc-announcement-table th,
            .wmc-announcement-table td {
                padding: 0.68rem 0.52rem !important;
                vertical-align: middle;
                white-space: normal !important;
                word-break: normal;
                overflow-wrap: normal;
            }

            .wmc-announcement-table th {
                color: #64748b;
                font-size: 10.8px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .015em;
                background: #f5f7fb;
                border-color: #eef2f7 !important;
                text-align: left;
            }

            .wmc-announcement-table td {
                border-color: #eef2f7 !important;
            }

            .wmc-announcement-table th:nth-child(1),
            .wmc-announcement-table td:nth-child(1) {
                width: 25%;
            }

            .wmc-announcement-table th:nth-child(2),
            .wmc-announcement-table td:nth-child(2) {
                width: 10%;
            }

            .wmc-announcement-table th:nth-child(3),
            .wmc-announcement-table td:nth-child(3) {
                width: 10%;
            }

            .wmc-announcement-table th:nth-child(4),
            .wmc-announcement-table td:nth-child(4) {
                width: 9%;
                white-space: nowrap !important;
            }

            .wmc-announcement-table th:nth-child(5),
            .wmc-announcement-table td:nth-child(5) {
                width: 8%;
                text-align: center;
            }

            .wmc-announcement-table th:nth-child(6),
            .wmc-announcement-table td:nth-child(6) {
                width: 13%;
            }

            .wmc-announcement-table th:nth-child(7),
            .wmc-announcement-table td:nth-child(7) {
                width: 8%;
                text-align: center;
            }

            .wmc-announcement-table th:nth-child(8),
            .wmc-announcement-table td:nth-child(8) {
                width: 9%;
            }

            .wmc-announcement-table th:nth-child(9),
            .wmc-announcement-table td:nth-child(9) {
                width: 8%;
                min-width: 82px;
                text-align: center;
            }

            .wmc-announcement-subject {
                font-weight: 700;
                color: #0f172a;
                line-height: 1.25;
                display: block;
                max-width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .wmc-announcement-preview {
                max-width: 100%;
                color: #64748b;
                line-height: 1.3;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                word-break: normal;
                overflow-wrap: anywhere;
            }

            .wmc-announcement-cell-truncate {
                display: block;
                max-width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .wmc-announcement-badge {
                font-size: 10.2px;
                padding: 0.26rem 0.46rem;
                white-space: nowrap;
            }

            .wmc-announcement-action-wrap {
                display: flex;
                justify-content: center;
                align-items: center;
                width: 100%;
            }

            .wmc-announcement-action-wrap .btn {
                width: 31px;
                height: 31px;
                padding: 0 !important;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
                flex: 0 0 31px;
            }

            .wmc-announcement-action-wrap .gap-2 {
                gap: 0.35rem !important;
            }

            .wmc-announcement-action-wrap .icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                line-height: 1;
            }

            .wmc-announcement-action-wrap svg {
                width: 15px;
                height: 15px;
            }

            @media (max-width: 1399.98px) {
                .wmc-announcement-table {
                    font-size: 12px;
                }

                .wmc-announcement-table th,
                .wmc-announcement-table td {
                    padding: 0.62rem 0.42rem !important;
                }

                .wmc-announcement-table th {
                    font-size: 10.2px;
                    letter-spacing: 0;
                }

                .wmc-announcement-card .card-header,
                .wmc-announcement-card .card-body {
                    padding-left: 0.7rem;
                    padding-right: 0.7rem;
                }
            }

            @media (max-width: 991.98px) {
                .wmc-announcement-table-wrap {
                    overflow-x: auto;
                }

                .wmc-announcement-table {
                    min-width: 980px;
                }
            }
        </style>

        <div class="row">
            <div class="col-sm-12">
                <div class="card rounded-4 border-0 shadow-sm wmc-announcement-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap bg-white border-0">
                        <div class="header-title">
                            <h4 class="card-title mb-0">Announcements</h4>
                            <p class="mb-0 text-secondary">Manage HR memorandum announcements for the dashboard.</p>
                        </div>

                        @can('announcements.create')
                            <a href="{{ route('announcements.create') }}" class="btn btn-primary mt-2 mt-md-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" class="me-1" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5V19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                New Announcement
                            </a>
                        @endcan
                    </div>

                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success rounded-3">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($announcements->isEmpty())
                            <div class="text-center py-5">
                                <h5 class="mb-2">No announcements yet</h5>
                                <p class="text-secondary mb-0">Create your first HR announcement.</p>
                            </div>
                        @else
                            <div class="table-responsive wmc-announcement-table-wrap">
                                <table class="table table-striped align-middle mb-0 wmc-announcement-table">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>To</th>
                                            <th>From</th>
                                            <th>Date</th>
                                            <th>Days</th>
                                            <th>Visible Until</th>
                                            <th>Status</th>
                                            <th>Posted By</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($announcements as $announcement)
                                            @php
                                                $isExpired = $announcement->expires_at && $announcement->expires_at->isPast();
                                                $postedBy = trim(($announcement->user->first_name ?? '') . ' ' . ($announcement->user->last_name ?? ''));
                                                $postedBy = $postedBy !== '' ? $postedBy : ($announcement->user->full_name ?? 'N/A');
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="wmc-announcement-subject">{{ $announcement->title }}</div>
                                                    <div class="small wmc-announcement-preview mt-1">
                                                        {{ \Illuminate\Support\Str::limit(strip_tags($announcement->content), 100) }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="wmc-announcement-cell-truncate" title="{{ $announcement->memo_to ?? 'All Employees' }}">
                                                        {{ $announcement->memo_to ?? 'All Employees' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="wmc-announcement-cell-truncate" title="{{ $announcement->memo_from ?? 'Management' }}">
                                                        {{ $announcement->memo_from ?? 'Management' }}
                                                    </span>
                                                </td>
                                                <td>{{ optional($announcement->memo_date ?? $announcement->published_at ?? $announcement->created_at)->format('M d, Y') }}</td>
                                                <td>
                                                    @if($announcement->display_days)
                                                        {{ $announcement->display_days }} day(s)
                                                    @else
                                                        <span class="text-secondary">No expiry</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($announcement->expires_at)
                                                        <span class="d-block">{{ $announcement->expires_at->format('M d, Y') }}</span>
                                                        <small class="text-secondary">{{ $announcement->expires_at->format('h:i A') }}</small>
                                                    @else
                                                        <span class="text-secondary">Until unpublished</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($announcement->is_published && !$isExpired)
                                                        <span class="badge bg-success wmc-announcement-badge">Published</span>
                                                    @elseif($announcement->is_published && $isExpired)
                                                        <span class="badge bg-secondary wmc-announcement-badge">Expired</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark wmc-announcement-badge">Draft</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="wmc-announcement-cell-truncate" title="{{ $postedBy }}">
                                                        {{ $postedBy }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="wmc-announcement-action-wrap">
                                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                                            @can('announcements.edit')
                                                                <a href="{{ route('announcements.edit', $announcement->id) }}"
                                                                   class="btn btn-sm btn-primary"
                                                                   title="Edit Announcement">
                                                                    <i class="icon">
                                                                        <svg width="18" viewBox="0 0 24 24" fill="none">
                                                                            <path d="M13.747 3.41095L20.589 10.2529L7.84302 23H1.00098V16.157L13.747 3.41095Z"
                                                                                stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        </svg>
                                                                    </i>
                                                                </a>
                                                            @endcan

                                                            @can('announcements.delete')
                                                                <button type="button"
                                                                        class="btn btn-sm btn-danger js-announcement-delete-btn"
                                                                        data-form-id="announcementDeleteForm{{ $announcement->id }}"
                                                                        data-name="{{ e($announcement->title) }}"
                                                                        title="Delete Announcement">
                                                                    <i class="icon">
                                                                        <svg width="18" viewBox="0 0 24 24" fill="none">
                                                                            <path d="M3 6H5H21" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                                                            <path d="M19 6L18.2 19C18.1 20.1 17.2 21 16.1 21H7.9C6.8 21 5.9 20.1 5.8 19L5 6"
                                                                                stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                                                            <path d="M10 11V17" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                                                            <path d="M14 11V17" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                                                            <path d="M9 6V4C9 3.4 9.4 3 10 3H14C14.6 3 15 3.4 15 4V6"
                                                                                stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                                                        </svg>
                                                                    </i>
                                                                </button>

                                                                <form id="announcementDeleteForm{{ $announcement->id }}"
                                                                      action="{{ route('announcements.destroy', $announcement->id) }}"
                                                                      method="POST"
                                                                      class="d-none">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                </form>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.addEventListener('click', function (event) {
                const deleteButton = event.target.closest('.js-announcement-delete-btn');

                if (!deleteButton) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                const formId = deleteButton.getAttribute('data-form-id');
                const deleteForm = formId ? document.getElementById(formId) : null;
                const announcementName = deleteButton.getAttribute('data-name') || 'this announcement';

                if (!deleteForm) {
                    return;
                }

                const submitDelete = function () {
                    deleteForm.submit();
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Delete Announcement?',
                        text: 'This will permanently delete "' + announcementName + '".',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-danger rounded-3 px-4 ms-2',
                            cancelButton: 'btn btn-light rounded-3 px-4'
                        }
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            submitDelete();
                        }
                    });

                    return;
                }

                const fallbackOverlay = document.createElement('div');
                fallbackOverlay.style.position = 'fixed';
                fallbackOverlay.style.inset = '0';
                fallbackOverlay.style.background = 'rgba(15, 23, 42, .55)';
                fallbackOverlay.style.zIndex = '99999';
                fallbackOverlay.style.display = 'flex';
                fallbackOverlay.style.alignItems = 'center';
                fallbackOverlay.style.justifyContent = 'center';
                fallbackOverlay.innerHTML = `
                    <div style="background:#fff;border-radius:18px;max-width:420px;width:92%;padding:24px;box-shadow:0 20px 60px rgba(15,23,42,.25);">
                        <h5 style="margin:0 0 8px;font-weight:800;color:#0f172a;">Delete Announcement?</h5>
                        <p style="margin:0 0 20px;color:#64748b;">This will permanently delete "${announcementName}".</p>
                        <div style="display:flex;justify-content:flex-end;gap:10px;">
                            <button type="button" class="btn btn-light rounded-3 px-4" data-cancel-delete>Cancel</button>
                            <button type="button" class="btn btn-danger rounded-3 px-4" data-confirm-delete>Yes, delete</button>
                        </div>
                    </div>
                `;

                document.body.appendChild(fallbackOverlay);

                fallbackOverlay.querySelector('[data-cancel-delete]').addEventListener('click', function () {
                    fallbackOverlay.remove();
                });

                fallbackOverlay.querySelector('[data-confirm-delete]').addEventListener('click', function () {
                    fallbackOverlay.remove();
                    submitDelete();
                });
            });

            @if(session('success'))
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Done',
                        text: @json(session('success')),
                        icon: 'success',
                        confirmButtonText: 'OK',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-primary rounded-3 px-4'
                        }
                    });
                }
            @endif
        });
    </script>

</x-app-layout>
