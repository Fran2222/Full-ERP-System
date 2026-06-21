<x-app-layout>
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">RFP Types</h3>
            <p class="text-secondary mb-0">Setup code prefixes used for auto-generated RFP numbers.</p>
        </div>
        @can('projects_mgmt.create')
            <a href="{{ route('rfp-types.create') }}" class="btn btn-sm btn-primary">Add RFP Type</a>
        @endcan
    </div>

    <x-wmc.datatable
        tableId="rfp-types-table"
        :columns="[
            ['title' => 'Type'],
            ['title' => 'Description'],
            ['title' => 'Status'],
            ['title' => 'Created'],
            ['title' => 'Action'],
        ]"
        hasFilter="true"
        filterId="rfpTypeStatusFilter">
        <x-slot name="filter">
            <div class="wmc-filter-wrap">
                <label>Filter:</label>
                <div class="filter-select-wrap">
                    <select id="rfpTypeStatusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </x-slot>
    </x-wmc.datatable>
</div>

@push('scripts')
<script>
$(document).ready(function () {
    let table = $('#rfp-types-table').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: "{{ route('rfp-types.list') }}",
            data: function (d) {
                d.status = $('.filter-holder #rfpTypeStatusFilter').val();
            }
        },
        searchDelay: 300,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'row mb-3 align-items-center'<'col-lg-4'l><'col-lg-4 filter-holder'><'col-lg-4'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-3 align-items-center'<'col-sm-6'i><'col-sm-6'p>>",
        columns: [
            { data: 'name', name: 'name', render: function (data, type, row) {
                return `<div><strong>${data || '-'}</strong><br><small>${row.code || ''}</small></div>`;
            }},
            { data: 'description', name: 'description', render: function (data) {
                if (!data) return '-';
                return data.length > 70 ? data.substring(0, 70) + '...' : data;
            }},
            { data: 'status', name: 'status', render: function (data) {
                return (data || '').toLowerCase() === 'active'
                    ? '<span class="text-success fw-semibold">Active</span>'
                    : '<span class="text-danger fw-semibold">Inactive</span>';
            }},
            { data: 'created_at_formatted', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        createdRow: function(row, data) {
            if (data && data.show_url) $(row).attr('title', 'Double-click to view');
        },
        initComplete: function () {
            $('.filter-holder').html($('#rfpTypeStatusFilterSource').html());
            $('#rfpTypeStatusFilterSource').remove();
            $('.filter-holder #rfpTypeStatusFilter').on('change', function () { table.ajax.reload(); });
        }
    });

    $('#rfp-types-table tbody').on('click', 'tr', function () {
        $('#rfp-types-table tbody tr').removeClass('row-active');
        $(this).addClass('row-active');
    });

    $('#rfp-types-table tbody').on('dblclick', 'tr', function () {
        let data = table.row(this).data();
        if (data && data.show_url) window.location.href = data.show_url;
    });

    $(document).on('click', '.delete-rfp-type', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let form = $(this).closest('form');
        let name = $(this).data('name') || 'this RFP type';
        Swal.fire({
            title: 'Are you sure?',
            text: `Delete "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            allowOutsideClick: false
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                headers: { 'Accept': 'application/json' },
                success: function (response) {
                    table.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Done', text: response.message || 'RFP type deleted successfully.' });
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Cannot Delete', text: xhr.responseJSON?.message || 'Something went wrong.' });
                }
            });
        });
    });
});
</script>
@endpush
</x-app-layout>
