<x-app-layout :assets="$assets ?? []">
<style>
    #project-gas-slips-table tbody tr {
        cursor: pointer;
    }

    #project-gas-slips-table tbody tr:hover,
    #project-gas-slips-table tbody tr.row-active {
        background-color: #f7f9ff !important;
    }

    #project-gas-slips-table thead th,
    #project-gas-slips-table tbody td {
        text-align: left !important;
        vertical-align: middle;
    }

    #project-gas-slips-table thead th:first-child,
    #project-gas-slips-table tbody td:first-child {
        text-align: center !important;
    }

    #project-gas-slips-table thead th:last-child,
    #project-gas-slips-table tbody td:last-child {
        text-align: center !important;
        white-space: nowrap;
        min-width: 95px;
    }

    #project-gas-slips-table tbody td:last-child .wmc-action-buttons,
    #project-gas-slips-table tbody td:last-child .d-flex {
        display: inline-flex !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
    }

    #project-gas-slips-table tbody td:last-child form {
        display: inline-flex !important;
        margin: 0 !important;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Gas Slips</h4>
                    <p class="mb-0 text-muted">Track gas slip issuance and returns.</p>
                </div>
                @can('projects_mgmt.create')
                    <a href="{{ route('project-gas-slips.create') }}" class="btn btn-sm btn-primary">Create Gas Slip</a>
                @endcan
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <x-wmc.datatable
                    tableId="project-gas-slips-table"
                    :columns="[
                        ['title'=>'Done'],
                        ['title'=>'PO #'],
                        ['title'=>'Plate #'],
                        ['title'=>'Driver'],
                        ['title'=>'Amount'],
                        ['title'=>'Status'],
                        ['title'=>'Actions']
                    ]"
                    :hasFilter="true"
                    filterId="gasStatusFilter"
                >
                    <x-slot name="filter">
                        <div class="wmc-filter-ui">
                            <label>Filter:</label>
                            <input type="hidden" id="gasStatusFilter" value="">

                            <div class="btn-group wmc-clean-dropdown">
                                <button type="button" class="btn wmc-clean-dropdown-main" id="gasStatusFilterText">All Status</button>
                                <button type="button" class="btn wmc-clean-dropdown-toggle dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                                    <span class="visually-hidden">Toggle</span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="#" class="dropdown-item gas-status-option" data-value="" data-label="All Status">All Status</a></li>
                                    <li><a href="#" class="dropdown-item gas-status-option" data-value="issued" data-label="Issued">Issued</a></li>
                                    <li><a href="#" class="dropdown-item gas-status-option" data-value="returned" data-label="Returned">Returned</a></li>
                                </ul>
                            </div>
                        </div>
                    </x-slot>
                </x-wmc.datatable>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function(){
    let table = $('#project-gas-slips-table').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: "{{ route('project-gas-slips.list') }}",
            data: function(d) {
                d.status = $('.filter-holder #gasStatusFilter').val();
            }
        },
        searchDelay: 350,
        pageLength: 10,
        dom: "<'row mb-3 align-items-center'<'col-lg-4'l><'col-lg-4 filter-holder'><'col-lg-4'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-3 align-items-center'<'col-sm-6'i><'col-sm-6'p>>",
        columns: [
            {data:'done_checkbox', name:'status', orderable:false, searchable:false},
            {data:'po_no', name:'po_no'},
            {data:'plate_name', name:'vehicle.plate_name'},
            {data:'drivers_text', name:'drivers_text', orderable:false},
            {data:'amount_formatted', name:'amount'},
            {data:'status_badge', name:'status'},
            {data:'action', name:'action', orderable:false, searchable:false}
        ],
        order: [[1,'desc']],
        createdRow: function(row, data) {
            if (data && data.show_url) {
                $(row).attr('title', 'Double-click to view gas slip');
            }
        },
        initComplete: function() {
            $('.filter-holder').html($('#gasStatusFilterSource').html());
            $('#gasStatusFilterSource').remove();

            $('#project-gas-slips-table_filter input').attr('placeholder','Search PO #, Plate #, Driver');

            $(document).on('click','.filter-holder .gas-status-option',function(e){
                e.preventDefault();
                $('.filter-holder #gasStatusFilter').val($(this).data('value'));
                $('.filter-holder #gasStatusFilterText').text($(this).data('label'));
                table.ajax.reload();
            });
        }
    });

    $('#project-gas-slips-table tbody').on('click','tr',function(){
        $('#project-gas-slips-table tbody tr').removeClass('row-active');
        $(this).addClass('row-active');
    });

    $('#project-gas-slips-table tbody').on('dblclick','tr',function(){
        let data = table.row(this).data();
        if (data && data.show_url) {
            window.location.href = data.show_url;
        }
    });

    $(document).on('change','.gas-slip-done-toggle',function(e){
        e.stopPropagation();

        let cb = $(this);

        $.ajax({
            url: cb.data('url'),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                done: cb.is(':checked') ? 1 : 0
            },
            success: function(){
                table.ajax.reload(null,false);
            },
            error: function(){
                cb.prop('checked', !cb.is(':checked'));
                Swal.fire('Error','Unable to update gas slip status.','error');
            }
        });
    });

    $(document).on('click','.delete-project-gas-slip',function(e){
        e.preventDefault();
        e.stopPropagation();

        let form = $(this).closest('form');

        Swal.fire({
            title:'Are you sure?',
            text:'Delete this gas slip?',
            icon:'warning',
            showCancelButton:true,
            confirmButtonColor:'#d33',
            confirmButtonText:'Yes, delete it'
        }).then(r=>{
            if(!r.isConfirmed) return;

            $.ajax({
                url:form.attr('action'),
                type:'POST',
                data:form.serialize(),
                dataType:'json',
                headers:{Accept:'application/json'},
                success:resp=>{
                    table.ajax.reload(null,false);
                    Swal.fire('Done',resp.message || 'Gas slip deleted.','success');
                },
                error:xhr=>Swal.fire('Error',xhr.responseJSON?.message || 'Something went wrong.','error')
            });
        });
    });
});
</script>
@endpush
</x-app-layout>
