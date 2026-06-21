<x-app-layout :assets="$assets ?? []">
<div class="row"><div class="col-12"><div class="card rounded-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><h4 class="card-title mb-1">Store Names</h4><p class="mb-0 text-muted">Manage store names used for project expenses.</p></div>
        @can('projects_mgmt.create')<a href="{{ route('store-names.create') }}" class="btn btn-sm btn-primary">Add Store</a>@endcan
    </div>
    <div class="card-body">
        @if(session('success'))<div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if(session('error'))<div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        <x-wmc.datatable tableId="store-names-table" :columns="[['title'=>'Store Name'],['title'=>'Contact Person'],['title'=>'Contact Number'],['title'=>'Status'],['title'=>'Actions']]" :hasFilter="true" filterId="storeStatusFilter">
            <x-slot name="filter"><div class="wmc-filter-ui"><label>Filter:</label><input type="hidden" id="storeStatusFilter" value=""><div class="btn-group wmc-clean-dropdown"><button type="button" class="btn wmc-clean-dropdown-main" id="storeStatusFilterText">All Status</button><button type="button" class="btn wmc-clean-dropdown-toggle dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"><span class="visually-hidden">Toggle</span></button><ul class="dropdown-menu"><li><a class="dropdown-item store-status-option" href="#" data-value="" data-label="All Status">All Status</a></li><li><a class="dropdown-item store-status-option" href="#" data-value="active" data-label="Active">Active</a></li><li><a class="dropdown-item store-status-option" href="#" data-value="inactive" data-label="Inactive">Inactive</a></li></ul></div></div></x-slot>
        </x-wmc.datatable>
    </div>
</div></div></div>
@push('scripts')
<script>
$(function(){
    let table=$('#store-names-table').DataTable({processing:false,serverSide:true,ajax:{url:"{{ route('store-names.list') }}",data:function(d){d.status=$('.filter-holder #storeStatusFilter').val();}},searchDelay:400,pageLength:10,lengthMenu:[10,25,50,100],dom:"<'row mb-3 align-items-center'<'col-lg-4'l><'col-lg-4 filter-holder'><'col-lg-4'f>><'row'<'col-sm-12'tr>><'row mt-3 align-items-center'<'col-sm-6'i><'col-sm-6'p>>",columns:[{data:'name',name:'name',render:function(data,type,row){let name=data||'';return `<div><strong>${name}</strong><br><small>${row.code||''}</small></div>`;}},{data:'contact_person',name:'contact_person',render:d=>d||'-'},{data:'contact_number',name:'contact_number',render:d=>d||'-'},{data:'status',name:'status',render:function(d){return (d||'').toLowerCase()==='active'?'<span class="text-success fw-semibold">Active</span>':'<span class="text-danger fw-semibold">Inactive</span>';}},{data:'action',name:'action',orderable:false,searchable:false}],order:[[0,'asc']],createdRow:function(row,data){if(data&&data.show_url)$(row).attr('title','Double-click to view');},initComplete:function(){$('.filter-holder').html($('#storeStatusFilterSource').html());$('#storeStatusFilterSource').remove();$(document).on('click','.filter-holder .store-status-option',function(e){e.preventDefault();$('.filter-holder #storeStatusFilter').val($(this).data('value'));$('.filter-holder #storeStatusFilterText').text($(this).data('label'));table.ajax.reload();});}});
    $('#store-names-table tbody').on('dblclick','tr',function(){let data=table.row(this).data();if(data&&data.show_url)window.location.href=data.show_url;});
    $(document).on('click','.delete-store-name',function(e){e.preventDefault();let form=$(this).closest('form');let name=$(this).data('name')||'this store';Swal.fire({title:'Are you sure?',text:`Delete "${name}"?`,icon:'warning',showCancelButton:true,confirmButtonColor:'#d33',cancelButtonColor:'#6c757d',confirmButtonText:'Yes, delete it'}).then((r)=>{if(!r.isConfirmed)return;$.ajax({url:form.attr('action'),type:'POST',data:form.serialize(),dataType:'json',headers:{Accept:'application/json'},success:function(resp){table.ajax.reload(null,false);Swal.fire('Done',resp.message||'Store deleted.','success');},error:function(xhr){Swal.fire('Error',xhr.responseJSON?.message||'Something went wrong.','error');}});});});
});
</script>
@endpush
</x-app-layout>
