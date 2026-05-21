@php
    $canEdit = auth()->user()->can('roles.edit');
    $canCreate = auth()->user()->can('roles.create');
@endphp

@if(!$canEdit && !$canCreate)
<div class="alert alert-danger text-center">
    You are not allowed to manage roles.
</div>
@else

<form action="{{ $isEdit ? route('role.update',$role->id) : route('role.store') }}" method="POST">
@csrf
@if($isEdit) @method('PUT') @endif

<!-- ROLE TITLE -->
<input type="text" name="title" class="form-control mb-3"
       value="{{ $isEdit ? $role->title : '' }}"
       required>

<!-- PERMISSIONS -->
<div class="row">
@foreach($grouped as $module=>$perms)
<div class="col-md-6">
    <div class="border p-2 mb-2">
        <strong>{{ $module }}</strong>

        @foreach($perms as $perm)
        <div>
            <input type="checkbox"
                   name="permissions[]"
                   value="{{ $perm->name }}"
                   {{ $isEdit && $role->hasPermissionTo($perm->name) ? 'checked' : '' }}>
            {{ $perm->name }}
        </div>
        @endforeach

    </div>
</div>
@endforeach
</div>

<button type="submit" class="btn btn-primary">
    {{ $isEdit ? 'Update' : 'Save' }}
</button>

</form>

@endif