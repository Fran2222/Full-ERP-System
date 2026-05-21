<x-app-layout :assets="$assets ?? []">

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="card users-card rounded-4 border-0 shadow-sm">
        <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h4 class="card-title mb-1 fw-bold">Users</h4>
                    <p class="text-secondary mb-0">
                        Manage users, roles, branches, departments, and module access levels.
                    </p>
                </div>

                @can('users.create')
                    <a href="{{ route('users.create') }}" class="btn btn-primary users-soft-btn">
                        Add User
                    </a>
                @endcan
            </div>
        </div>

        <div class="card-body px-4 pb-4">
            @if(session('success'))
                <div class="alert alert-success rounded-3 mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger rounded-3 mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="table-responsive users-table-wrap">
                <table id="users-table" class="table table-hover align-middle mb-0 users-table w-100">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>User</th>
                            <th>Organization</th>
                            <th>Role</th>
                            <th>Primary Module</th>
                            <th>Module Access</th>
                            <th style="width: 95px;">Status</th>
                            <th class="text-end users-actions-col" style="width: 115px;">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let table = $('#users-table').DataTable({
        processing: false,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ajax: "{{ route('users.list') }}",
        order: [[0, 'asc']],
        searchDelay: 250,
        columns: [
            {
                data: 'id',
                name: 'id'
            },
            {
                data: null,
                name: 'full_name',
                render: function(data, type, row) {
                    return `
                        <div class="users-main-text">${row.full_name ?? '-'}</div>
                        <div class="users-sub-text">${row.email ?? '-'}</div>
                    `;
                }
            },
            {
                data: null,
                name: 'branch_name',
                render: function(data, type, row) {
                    return `
                        <div class="users-main-text">${row.branch_name ?? '-'}</div>
                        <div class="users-sub-text">${row.department_name ?? '-'}</div>
                    `;
                }
            },
            {
                data: 'role_names',
                name: 'role_names',
                orderable: false,
                searchable: true
            },
            {
                data: 'primary_module_name',
                name: 'primary_module_name',
                orderable: false,
                searchable: false
            },
            {
                data: 'module_access_summary',
                name: 'module_access_summary',
                orderable: false,
                searchable: false
            },
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ],
        language: {
            search: '',
            searchPlaceholder: 'Search users...',
            lengthMenu: 'Show _MENU_ entries',
            emptyTable: 'No users found.',
            zeroRecords: 'No matching users found.'
        },
        dom:
            "<'row g-3 align-items-center mb-3'<'col-lg-6 col-md-6'l><'col-lg-6 col-md-6'f>>" +
            "<'row'<'col-12'tr>>" +
            "<'row g-3 align-items-center mt-3'<'col-lg-6 col-md-6'i><'col-lg-6 col-md-6'p>>",
        columnDefs: [
            {
                targets: [0, 3, 4, 6, 7],
                className: 'text-nowrap'
            },
            {
                targets: 7,
                className: 'text-end users-actions-col'
            },
            {
                targets: 5,
                width: '330px'
            }
        ],
        initComplete: function() {
            const usersSearchInput = $('#users-table_filter input');
            const usersLengthSelect = $('#users-table_length select');

            usersSearchInput.off();

            let usersSearchTimer = null;

            usersSearchInput.on('input', function () {
                const value = this.value;

                clearTimeout(usersSearchTimer);

                usersSearchTimer = setTimeout(function () {
                    table.search(value).draw();
                }, 250);
            });

            usersLengthSelect.off('change').on('change', function () {
                table.page.len($(this).val()).draw();
            });
        }
    });

    $(document).on('click', '.delete-user', function() {
        let url = $(this).data('url');
        let name = $(this).data('name') || 'this user';

        const runDelete = function() {
            $.ajax({
                url: url,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    table.ajax.reload(null, false);

                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Done',
                            text: res.message || 'User deleted successfully.',
                            timer: 1400,
                            showConfirmButton: false
                        });
                    } else {
                        alert(res.message || 'User deleted successfully.');
                    }
                },
                error: function(xhr) {
                    let message = 'Something went wrong while deleting.';

                    if (xhr.status === 403) {
                        message = 'You are not authorized to delete this user.';
                    }

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Unable to delete',
                            text: message
                        });
                    } else {
                        alert(message);
                    }
                }
            });
        };

        if (window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'Are you sure?',
                text: 'Delete "' + name + '"?',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#f04438',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    runDelete();
                }
            });
        } else {
            if (confirm('Are you sure you want to delete "' + name + '"?')) {
                runDelete();
            }
        }
    });
});
</script>

<style>
    .users-card {
        background: #ffffff;
        border-radius: 18px !important;
        border: 1px solid #edf0f5 !important;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055) !important;
        overflow: hidden;
    }

    .users-soft-btn {
        border-radius: 10px;
        padding: 10px 18px;
        font-weight: 700;
    }

    .users-table-wrap {
        border: 1px solid #edf0f5;
        border-radius: 16px;
        overflow-x: auto;
        overflow-y: hidden;
        width: 100%;
        scrollbar-width: thin;
        margin-bottom: 18px;
    }

    .users-table-wrap::-webkit-scrollbar {
        height: 9px;
    }

    .users-table-wrap::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 999px;
    }

    .users-table-wrap::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }

    .users-table {
        table-layout: fixed;
        width: 100% !important;
        min-width: 1280px;
    }

    .users-table thead th {
        background: #f4f6fb;
        color: #8a94a6;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.035em;
        border-bottom: 0;
        padding: 13px 12px;
        white-space: nowrap;
    }

    .users-table tbody td {
        padding: 14px 12px;
        border-bottom: 1px solid #edf0f5;
        vertical-align: middle;
        color: #475569;
        font-size: 13px;
    }

    .users-table tbody tr {
        transition: all 0.18s ease-in-out;
    }

    .users-table tbody tr:hover {
        background: #f8faff;
    }

    .users-table th:nth-child(1),
    .users-table td:nth-child(1) {
        width: 55px;
    }

    .users-table th:nth-child(2),
    .users-table td:nth-child(2) {
        width: 190px;
    }

    .users-table th:nth-child(3),
    .users-table td:nth-child(3) {
        width: 175px;
    }

    .users-table th:nth-child(4),
    .users-table td:nth-child(4) {
        width: 135px;
    }

    .users-table th:nth-child(5),
    .users-table td:nth-child(5) {
        width: 150px;
    }

    .users-table th:nth-child(6),
    .users-table td:nth-child(6) {
        width: 390px;
    }

    .users-table th:nth-child(7),
    .users-table td:nth-child(7) {
        width: 85px;
    }

    .users-table th:nth-child(8),
    .users-table td:nth-child(8) {
        width: 115px;
    }

    .users-table th.users-actions-col,
    .users-table td.users-actions-col {
        position: sticky;
        right: 0;
        z-index: 2;
        background: #ffffff;
        box-shadow: -10px 0 18px rgba(15, 23, 42, 0.04);
    }

    .users-table thead th.users-actions-col {
        z-index: 4;
        background: #f4f6fb;
    }

    .users-table tbody tr:hover td.users-actions-col {
        background: #f8faff;
    }

    .users-action-buttons,
    .users-action-buttons > .d-flex {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: nowrap;
        white-space: nowrap;
    }

    .users-main-text {
        color: #1f2937;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .users-sub-text {
        color: #8a94a6;
        font-size: 12px;
        line-height: 1.3;
        margin-top: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .users-table .badge {
        border-radius: 999px;
        font-size: 10px;
        font-weight: 800;
        padding: 5px 8px;
        line-height: 1;
        white-space: nowrap;
    }

    .users-table .bg-primary {
        background: #3f5cff !important;
    }

    .users-table .bg-light.text-dark.border {
        background: #f8fafc !important;
        border-color: #e5e7eb !important;
        color: #334155 !important;
    }

    .users-table .d-flex.flex-wrap.gap-1 {
        gap: 4px !important;
    }

    .users-table .users-actions-col .btn-sm {
        width: 34px;
        height: 34px;
        padding: 0 !important;
        border-radius: 9px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        line-height: 1;
        background: #ffffff !important;
        box-shadow: none !important;
        transition: all 0.18s ease-in-out;
    }

    .users-table .users-actions-col .btn-sm.btn-primary {
        border: 1.5px solid #3f5cff !important;
        color: #3f5cff !important;
    }

    .users-table .users-actions-col .btn-sm.btn-primary:hover {
        background: #3f5cff !important;
        color: #ffffff !important;
    }

    .users-table .users-actions-col .btn-sm.btn-danger {
        border: 1.5px solid #f04438 !important;
        color: #f04438 !important;
    }

    .users-table .users-actions-col .btn-sm.btn-danger:hover {
        background: #f04438 !important;
        color: #ffffff !important;
    }

    .users-table .users-actions-col .btn-sm svg {
        width: 15px;
        height: 15px;
    }

    .users-table .users-actions-col .btn-sm svg path {
        stroke: currentColor !important;
    }

    .users-table .users-actions-col .btn-sm .icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    div.dataTables_wrapper div.dataTables_filter {
        text-align: right;
    }

    div.dataTables_wrapper div.dataTables_filter input {
        min-width: 240px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        padding: 9px 12px;
        margin-left: 8px;
    }

    div.dataTables_wrapper div.dataTables_length select {
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        padding: 7px 32px 7px 10px;
    }

    .dataTables_info {
        color: #64748b;
        font-size: 13px;
    }

    .pagination {
        margin-bottom: 0;
        justify-content: flex-end;
    }

    div.dataTables_wrapper .row:last-child {
        padding: 14px 4px 4px;
        margin-top: 10px !important;
        align-items: center;
    }

    div.dataTables_wrapper .dataTables_paginate {
        padding-top: 0;
    }

    div.dataTables_wrapper .dataTables_info {
        padding-top: 0;
    }

    .users-processing {
        color: #3f5cff;
        font-weight: 700;
        padding: 12px;
    }

    @media (max-width: 1199px) {
        .users-table thead th {
            font-size: 10px;
            padding: 12px 9px;
        }

        .users-table tbody td {
            padding: 13px 9px;
            font-size: 12px;
        }

        .users-table th:nth-child(2),
        .users-table td:nth-child(2) {
            width: 170px;
        }

        .users-table th:nth-child(3),
        .users-table td:nth-child(3) {
            width: 155px;
        }

        .users-table th:nth-child(4),
        .users-table td:nth-child(4) {
            width: 115px;
        }

        .users-table th:nth-child(5),
        .users-table td:nth-child(5) {
            width: 135px;
        }

        .users-table .badge {
            font-size: 9px;
            padding: 5px 7px;
        }
    }

    @media (max-width: 991px) {
        .users-table-wrap {
            overflow-x: auto;
        }

        .users-table {
            min-width: 980px;
        }

        div.dataTables_wrapper div.dataTables_filter {
            text-align: left;
        }

        div.dataTables_wrapper div.dataTables_filter input {
            width: 100%;
            min-width: unset;
            margin-left: 0;
            margin-top: 8px;
        }
    }
</style>
@endpush

</x-app-layout>