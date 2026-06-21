<x-app-layout>
    <div class="container-fluid content-inner py-0">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card border-0" style="border-radius:20px; box-shadow:0 14px 38px rgba(15,23,42,.07);">
            <div class="card-header border-0 pb-0">
                <h4 class="fw-bold mb-1">Edit Holiday</h4>
                <p class="text-secondary mb-0">Update the holiday details used by Attendance.</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('hr.holidays.update', $holiday) }}">
                    @method('PUT')
                    @include('hr.holidays._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
