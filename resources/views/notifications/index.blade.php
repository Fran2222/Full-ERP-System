@extends('layouts.app')

@section('content')
<div class="container-fluid content-inner">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">Notifications</h4>
                <p class="text-muted mb-0">System-wide alerts assigned to you.</p>
            </div>

            <form action="{{ url('/notifications/mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">Mark All Read</button>
            </form>
        </div>

        <div class="card-body p-0">
            @forelse($notifications as $notification)
                <a href="{{ url('/notifications/' . $notification->id . '/open') }}"
                   class="d-flex align-items-start gap-3 p-3 border-bottom text-decoration-none {{ $notification->is_read ? 'bg-white' : 'bg-soft-danger' }}">
                    <div class="pt-1">
                        @if(! $notification->is_read)
                            <span class="badge bg-danger rounded-pill">&nbsp;</span>
                        @else
                            <span class="badge bg-light rounded-pill">&nbsp;</span>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between gap-3 flex-wrap">
                            <h6 class="mb-1 text-dark">{{ $notification->title }}</h6>
                            <small class="text-muted">{{ optional($notification->created_at)->format('M d, Y h:i A') }}</small>
                        </div>
                        <p class="mb-1 text-muted">{{ $notification->message }}</p>
                        <span class="badge bg-primary-subtle text-primary">{{ ucfirst($notification->module ?? 'system') }}</span>
                    </div>
                </a>
            @empty
                <div class="p-5 text-center text-muted">
                    No notifications yet.
                </div>
            @endforelse
        </div>

        @if(method_exists($notifications, 'links'))
            <div class="card-footer bg-white">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection