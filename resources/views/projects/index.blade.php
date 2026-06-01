@extends('layouts.app', ['title' => 'Projects', 'subtitle' => 'Projects'])

@section('content')

<div class="d-flex flex-wrap align-items-end gap-2 mb-3">
    <form class="d-flex flex-wrap gap-2 align-items-end toolbar-form" method="GET" action="{{ route('projects.index') }}">
        <input type="text" name="search" class="form-control form-control-sm" style="min-width:140px;flex:1 1 140px;"
               placeholder="Search projects…" value="{{ request('search') }}">
        <select name="status" class="form-select form-select-sm" style="min-width:120px;flex:0 1 auto;">
            <option value="">All Statuses</option>
            @foreach($statuses as $s)
                <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>
                    {{ $s->label() }}
                </option>
            @endforeach
        </select>
        <select name="priority" class="form-select form-select-sm" style="min-width:120px;flex:0 1 auto;">
            <option value="">All Priorities</option>
            @foreach($priorities as $p)
                <option value="{{ $p->value }}" {{ request('priority') === $p->value ? 'selected' : '' }}>
                    {{ $p->label() }}
                </option>
            @endforeach
        </select>
        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
            @if(request()->hasAny(['search','status','priority']))
                <a href="{{ route('projects.index') }}" class="btn btn-sm btn-link text-muted">Clear</a>
            @endif
        </div>
    </form>
    @if(auth()->user()->isManager())
    <div class="ms-auto">
        <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm">
            <i class="ti ti-plus me-1"></i> New Project
        </a>
    </div>
    @endif
</div>

<div class="card">
    <div class="card-body p-0">
        @if($projects->isEmpty())
            <p class="text-muted text-center py-5">
                @if(auth()->user()->isManager())
                    No projects yet. <a href="{{ route('projects.create') }}">Create one.</a>
                @else
                    You have no assigned projects.
                @endif
            </p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Project</th>
                            <th>Client</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Progress</th>
                            <th>Deadline</th>
                            <th>Team</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                        <tr class="{{ $project->isOverdue() ? 'table-danger' : '' }}">
                            <td>
                                <a href="{{ route('projects.show', $project) }}" class="fw-semibold text-body text-decoration-none">
                                    {{ $project->title }}
                                </a>
                                @if($project->isOverdue())
                                    <span class="badge bg-danger ms-1 small">Overdue</span>
                                @endif
                            </td>
                            <td class="text-muted small">
                                {{ $project->client?->name ?? '—' }}
                            </td>
                            <td>
                                <span class="badge {{ $project->status->badgeClass() }}">
                                    {{ $project->status->label() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $project->priority->badgeClass() }}">
                                    {{ $project->priority->label() }}
                                </span>
                            </td>
                            <td style="min-width: 100px;">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: {{ $project->progress }}%"></div>
                                </div>
                                <small class="text-muted">{{ $project->progress }}%</small>
                            </td>
                            <td class="small text-muted">
                                @if($project->deadline)
                                    {{ $project->deadline->format('d M Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @foreach($project->assignees->take(3) as $assignee)
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $assignee->name }}</span>
                                @endforeach
                                @if($project->assignees->count() > 3)
                                    <span class="text-muted small">+{{ $project->assignees->count() - 3 }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="ti ti-eye"></i>
                                </a>
                                @if(auth()->user()->isManager())
                                <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-pencil"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($projects->hasPages())
                <div class="p-3">{{ $projects->links() }}</div>
            @endif
        @endif
    </div>
</div>

@endsection
