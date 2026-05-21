@if(session('success'))
    <div class="alert alert-success rounded-3">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger rounded-3">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger rounded-3">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
