@can('permissions.create')

<form action="{{ route('permission.store') }}" method="POST">
@csrf

<!-- form unchanged -->

<div class="d-flex justify-content-end gap-2">
    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" class="btn btn-primary">Generate</button>
</div>

</form>

@else
<div class="alert alert-danger text-center">
    You are not allowed to create permissions.
</div>
@endcan