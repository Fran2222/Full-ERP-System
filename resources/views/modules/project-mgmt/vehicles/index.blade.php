<x-app-layout :assets="$assets ?? []">
<style>
  .vehicle-members-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        flex-wrap: nowrap;
    }

    .vehicle-member-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 2px solid #3a57e8;
        background: #ffffff;
        color: #3a57e8;
        font-size: 11px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-left: -7px;
        line-height: 1;
        text-transform: uppercase;
    }

    .vehicle-member-avatar:first-child {
        margin-left: 0;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Vehicle List</h4>
                    <p class="mb-0 text-muted">Manage vehicles and assigned drivers used for gas slips.</p>
                </div>
                @can('projects_mgmt.create')
                    <a href="{{ route('project-vehicles.create') }}" class="btn btn-sm btn-primary">Add Vehicle</a>
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
                    tableId="project-vehicles-table"
                    :columns="[
                        ['title'=>'Vehicle Code'],
                        ['title'=>'Plate / Vehicle'],
                        ['title'=>'Driver/s'],
                        ['title'=>'Status'],
                        ['title'=>'Actions']
                    ]"
                />
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function(){
    let table = $('#project-vehicles-table').DataTable({
        processing: false,
        serverSide: true,
        ajax: "{{ route('project-vehicles.list') }}",
        searchDelay: 350,
        pageLength: 10,
        dom: "<'row mb-3 align-items-center'<'col-lg-6'l><'col-lg-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-3 align-items-center'<'col-sm-6'i><'col-sm-6'p>>",
        columns: [
            {data:'vehicle_code', name:'vehicle_code'},
            {data:'plate_name', name:'plate_name'},
            {data:'drivers_badges', name:'drivers_badges', orderable:false, searchable:false},
            {data:'status_badge', name:'status'},
            {data:'action', name:'action', orderable:false, searchable:false}
        ],
        order: [[0,'desc']]
    });

    $('#project-vehicles-table tbody').on('click','tr',function(){$('#project-vehicles-table tbody tr').removeClass('row-active');$(this).addClass('row-active');});
    $('#project-vehicles-table tbody').on('dblclick','tr',function(){let data=table.row(this).data();if(data&&data.show_url){window.location.href=data.show_url;}});
    $(document).on('click','.delete-project-vehicle',function(e){
        e.preventDefault();
        e.stopPropagation();

        let form = $(this).closest('form');

        Swal.fire({
            title:'Are you sure?',
            text:'Delete this vehicle?',
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
                    Swal.fire('Done',resp.message || 'Vehicle deleted.','success');
                },
                error:xhr=>Swal.fire('Error',xhr.responseJSON?.message || 'Something went wrong.','error')
            });
        });
    });
});
</script>
@endpush
</x-app-layout>
