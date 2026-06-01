@extends('layouts.app', ['title' => 'Team Members', 'subtitle' => 'Team'])

@section('content')

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
                                @if($member->isManager())
                                    <span class="badge bg-primary-subtle text-primary">Manager</span>
                                @else
                                    <span class="badge bg-info-subtle text-info">Team Member</span>
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
