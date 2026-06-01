@extends('layouts.app', ['title' => 'Project Health', 'subtitle' => 'Reports'])

@section('content')

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>All Reports
    </a>
    <h5 class="fw-semibold mb-0">Project Health</h5>
</div>

{{-- Summary KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small fw-medium mb-1">Total Projects</p>
                <h4 class="fw-bold mb-0">{{ $totalProjects }}</h4>
                <small class="text-muted">excl. cancelled</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small fw-medium mb-1">Active</p>
                <h4 class="fw-bold mb-0 text-success">{{ $activeCount }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small fw-medium mb-1">Overdue</p>
                <h4 class="fw-bold mb-0 {{ $overdueCount > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $overdueCount }}
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small fw-medium mb-1">Avg Progress</p>
                <h4 class="fw-bold mb-0">{{ $avgProgress }}%</h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Project Details</h5>
        <a href="{{ route('projects.create') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus me-1"></i>New Project
        </a>
    </div>
    <div class="card-body p-0">
        @if($projects->isEmpty())
            <p class="text-muted text-center py-5">No active projects found.</p>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Project</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th style="min-width:160px">Progress</th>
                        <th>Deadline</th>
                        <th>Budget</th>
                        <th>Assignees</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                    @php
                        $isOverdue = $project->isOverdue();
                        $progressColor = $project->progress >= 75 ? 'success'
                            : ($project->progress >= 40 ? 'primary' : 'warning');
                    @endphp
                    <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                        <td>
                            <a href="{{ route('projects.show', $project) }}" class="fw-medium text-dark">
                                {{ $project->title }}
                            </a>
                        </td>
                        <td class="text-muted small">{{ $project->client?->name ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $project->status->badgeClass() }}">
                                {{ $project->status->label() }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px">
                                    <div class="progress-bar bg-{{ $progressColor }}"
                                         style="width:{{ $project->progress }}%"></div>
                                </div>
                                <small class="fw-semibold" style="width:32px">{{ $project->progress }}%</small>
                            </div>
                        </td>
                        <td>
                            @if($project->deadline)
                                <span class="{{ $isOverdue ? 'text-danger fw-semibold' : 'text-muted' }}">
                                    {{ $project->deadline->format('d M Y') }}
                                </span>
                                @if($isOverdue)
                                    <br><small class="text-danger">{{ $project->deadline->diffForHumans() }}</small>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-muted">
                            @if($project->budget)
                                {{ format_inr((float)$project->budget) }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($project->assignees->isEmpty())
                                <span class="text-muted small">—</span>
                            @else
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($project->assignees->take(3) as $assignee)
                                        <span class="badge bg-primary-subtle text-primary">{{ $assignee->name }}</span>
                                    @endforeach
                                    @if($project->assignees->count() > 3)
                                        <span class="badge bg-secondary-subtle text-secondary">+{{ $project->assignees->count() - 3 }}</span>
                                    @endif
                                </div>
                            @endif
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
