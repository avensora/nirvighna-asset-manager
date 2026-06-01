@extends('layouts.app', ['title' => 'Team Members', 'subtitle' => 'Team'])

@section('content')

@if(session('invite_link'))
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <div class="fw-semibold mb-2"><i class="ti ti-link me-1"></i> Invite link for {{ session('invite_name') }}</div>
    <div class="input-group">
        <input type="text" id="invite-link-input" class="form-control form-control-sm font-monospace"
               value="{{ session('invite_link') }}" readonly>
        <button class="btn btn-outline-secondary btn-sm" type="button"
                onclick="navigator.clipboard.writeText(document.getElementById('invite-link-input').value).then(()=>{this.textContent='Copied!';setTimeout(()=>{this.innerHTML='<i class=\'ti ti-copy\'></i> Copy'},1500)})">
            <i class="ti ti-copy"></i> Copy
        </button>
    </div>
    <small class="text-muted mt-1 d-block">Share this link with {{ session('invite_name') }}. It expires in 60 minutes.</small>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row mb-3">
    <div class="col">
        <a href="{{ route('team.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Invite Member
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($members->isEmpty())
            <p class="text-muted text-center py-4">No team members yet.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $member)
                        <tr>
                            <td class="fw-semibold">
                                {{ $member->name }}
                                @if($member->id === auth()->id())
                                    <span class="badge bg-secondary-subtle text-secondary ms-1">You</span>
                                @endif
                            </td>
                            <td>{{ $member->email }}</td>
                            <td>
                                @if($member->isMasterAdmin())
                                    <span class="badge bg-danger-subtle text-danger">Master Admin</span>
                                @elseif($member->role === \App\Enums\UserRole::Manager)
                                    <span class="badge bg-primary-subtle text-primary">Manager</span>
                                @else
                                    <span class="badge bg-info-subtle text-info">Team Lead</span>
                                @endif
                            </td>
                            <td>
                                @if($member->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $member->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <form action="{{ route('team.invite-link', $member) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Get invite link">
                                        <i class="ti ti-link"></i>
                                    </button>
                                </form>
                                <a href="{{ route('team.edit', $member) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
