<x-app-layout>
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">Document Control</h3>
            <p class="text-secondary mb-0">Controlled form registry, revision metadata, and active document versions.</p>
        </div>
        @can('projects_mgmt.create')
            <a href="{{ route('document-controls.create') }}" class="btn btn-sm btn-primary">Add Document</a>
        @endcan
    </div>

    <x-wmc.datatable
        tableId="document-controls-table"
        :columns="[
            ['title' => 'Form Name'],
            ['title' => 'Type'],
            ['title' => 'Document No.'],
            ['title' => 'Revision'],
            ['title' => 'Effective Date'],
            ['title' => 'Status'],
            ['title' => 'Actions'],
        ]"
        hasFilter="true"
        filterId="documentControlStatusFilter">
        <x-slot name="filter">
            <div class="wmc-filter-wrap">
                <label>Filter:</label>
                <div class="filter-select-wrap">
                    <select id="documentControlStatusFilter">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="archived">Archived</option>
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
    let table = $('#document-controls-table').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: "{{ route('document-controls.list') }}",
            data: function (d) {
                d.status = $('.filter-holder #documentControlStatusFilter').val();
            }
        },
        searchDelay: 300,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'row mb-3 align-items-center'<'col-lg-4'l><'col-lg-4 filter-holder'><'col-lg-4'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-3 align-items-center'<'col-sm-6'i><'col-sm-6'p>>",
        columns: [
            { data: 'form_name', name: 'form_name', render: function (data) { return `<strong>${data || '-'}</strong>`; }},
            { data: 'type', name: 'type', render: function (data) { return data || '-'; }},
            { data: 'document_no', name: 'document_no', render: function (data) { return `<span class="fw-semibold">${data || '-'}</span>`; }},
            { data: 'revision_no', name: 'revision_no', render: function (data) { return data || '00'; }},
            { data: 'effective_date_formatted', name: 'effective_date' },
            { data: 'status_badge', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc'], [1, 'asc'], [3, 'desc']],
        createdRow: function(row, data) {
            if (data && data.show_url) $(row).attr('title', 'Double-click to view document');
        },
        initComplete: function () {
            $('.filter-holder').html($('#documentControlStatusFilterSource').html());
            $('#documentControlStatusFilterSource').remove();
            $('.filter-holder #documentControlStatusFilter').on('change', function () { table.ajax.reload(); });
        }
    });

    $('#document-controls-table tbody').on('click', 'tr', function () {
        $('#document-controls-table tbody tr').removeClass('row-active');
        $(this).addClass('row-active');
    });

    $('#document-controls-table tbody').on('dblclick', 'tr', function () {
        let data = table.row(this).data();
        if (data && data.show_url) window.location.href = data.show_url;
    });

    $(document).on('click', '.delete-document-control', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let form = $(this).closest('form');
        let name = $(this).data('name') || 'this document';
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
                    Swal.fire({ icon: 'success', title: 'Done', text: response.message || 'Document deleted successfully.' });
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Cannot Delete', text: xhr.responseJSON?.message || 'Something went wrong.' });
                }
            });
        });
    });

    $(document).on('submit', '.new-revision-form', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let form = this;
        Swal.fire({
            title: 'Create new revision?',
            text: 'This will create a new Draft revision. The current active revision will not be archived until the draft is published.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Create Draft',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });

    $(document).on('submit', '.publish-revision-form', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let form = this;
        Swal.fire({
            title: 'Publish this revision?',
            text: 'The previous active revision for the same form/type will be archived automatically.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Publish',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endpush
</x-app-layout>
