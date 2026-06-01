@extends('layouts.app', ['title' => 'Notifications', 'subtitle' => 'Notifications'])

@section('content')

<div class="row mb-3 align-items-center">
    <div class="col">
        <h5 class="mb-0">Notifications</h5>
    </div>
    <div class="col-auto">
        @if($notifications->total() > 0)
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-checks me-1"></i>Mark all read
            </button>
        </form>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        @forelse($notifications as $notif)
        <div class="d-flex align-items-start p-3 border-bottom {{ $notif->isRead() ? '' : 'bg-light' }}">
            <div class="me-3 mt-1">
                @php
                    $icon = match($notif->type) {
                        'project_assigned'        => 'ti-briefcase text-primary',
                        'project_progress'        => 'ti-chart-bar text-info',
                        'lead_stage'              => 'ti-target text-warning',
                        'invoice_overdue'         => 'ti-file-invoice text-danger',
                        default                   => 'ti-bell text-secondary',
                    };
                @endphp
                <span class="avatar-sm bg-white border rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                    <i class="ti {{ $icon }} fs-18"></i>
                </span>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <p class="mb-1 fw-semibold {{ $notif->isRead() ? 'text-muted' : '' }}">{{ $notif->title }}</p>
                    <small class="text-muted text-nowrap ms-3">{{ $notif->created_at->diffForHumans() }}</small>
                </div>
                <p class="mb-1 text-muted small">{{ $notif->body }}</p>
            </div>
            @if(! $notif->isRead())
            <div class="ms-3 mt-1">
                <form method="POST" action="{{ route('notifications.read', $notif) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Mark read">
                        <i class="ti ti-check fs-14"></i>
                    </button>
                </form>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="ti ti-bell-off fs-40 d-block mb-2"></i>
            No notifications yet.
        </div>
        @endforelse
    </div>
</div>

@if($notifications->hasPages())
<div class="mt-3">
    {{ $notifications->links() }}
</div>
@endif

@endsection
