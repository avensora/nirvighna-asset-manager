@extends('layouts.app', ['title' => $project->title, 'subtitle' => 'Projects'])

@section('content')

<div class="row g-4">

    {{-- Left column: project details --}}
    <div class="col-lg-8">

        {{-- Header card --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="mb-1">{{ $project->title }}</h4>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge {{ $project->status->badgeClass() }}">{{ $project->status->label() }}</span>
                            <span class="badge {{ $project->priority->badgeClass() }}">{{ $project->priority->label() }}</span>
                            @if($project->isOverdue())
                                <span class="badge bg-danger">Overdue</span>
                            @endif
                            @if($project->client)
                                <span class="text-muted small">
                                    <i class="ti ti-users me-1"></i>{{ $project->client->name }}
                                </span>
                            @else
                                <span class="text-muted small"><i class="ti ti-building me-1"></i>Internal</span>
                            @endif
                        </div>
                    </div>
                    @if(auth()->user()->isManager())
                    <div class="d-flex gap-2">
                        <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-primary btn-sm">
                            <i class="ti ti-pencil me-1"></i> Edit
                        </a>
                        <form action="{{ route('projects.destroy', $project) }}" method="POST"
                              onsubmit="return confirm('Delete this project? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm">
                                <i class="ti ti-trash me-1"></i> Delete
                            </button>
                        </form>
                    </div>
                    @endif
                </div>

                @if($project->description)
                    <p class="mt-3 mb-0 text-muted">{{ $project->description }}</p>
                @endif
            </div>
        </div>

        {{-- Progress card --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">Progress</h6>
                <span class="fw-bold text-success">{{ $project->progress }}%</span>
            </div>
            <div class="card-body">
                <div class="progress mb-3" style="height: 12px;">
                    <div class="progress-bar bg-success" style="width: {{ $project->progress }}%"
                         role="progressbar" aria-valuenow="{{ $project->progress }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>

                <form action="{{ route('projects.progress', $project) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label small mb-1">Update Progress</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="progress" class="form-control" style="width: 70px;"
                                       min="0" max="100" value="{{ $project->progress }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col">
                            <input type="text" name="notes" class="form-control form-control-sm"
                                   placeholder="Optional note about this update…">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-success btn-sm">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Activity Timeline --}}
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Activity Timeline</h6>
            </div>
            <div class="card-body p-0">
                @if($activities->isEmpty())
                    <p class="text-muted text-center py-4">No activity recorded yet.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($activities as $activity)
                        <li class="list-group-item py-3">
                            <div class="d-flex gap-3">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                                        <i class="ti ti-activity"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold small">{{ $activity->description }}</div>
                                    <div class="text-muted" style="font-size:0.78rem;">
                                        {{ $activity->causer?->name ?? 'System' }}
                                        · {{ $activity->created_at->diffForHumans() }}
                                    </div>
                                    @if($activity->properties->has('notes') && $activity->properties['notes'])
                                        <div class="text-muted small mt-1 fst-italic">{{ $activity->properties['notes'] }}</div>
                                    @endif
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

    </div>

    {{-- Right column: meta + linked data --}}
    <div class="col-lg-4">

        {{-- Project Details --}}
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Details</h6>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between small">
                    <span class="text-muted">Created by</span>
                    <span>{{ $project->creator->name }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between small">
                    <span class="text-muted">Start Date</span>
                    <span>{{ $project->start_date?->format('d M Y') ?? '—' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between small">
                    <span class="text-muted">Deadline</span>
                    <span class="{{ $project->isOverdue() ? 'text-danger fw-semibold' : '' }}">
                        {{ $project->deadline?->format('d M Y') ?? '—' }}
                    </span>
                </li>
                <li class="list-group-item d-flex justify-content-between small">
                    <span class="text-muted">Budget</span>
                    <span>{{ $project->budget ? format_inr($project->budget) : '—' }}</span>
                </li>
                @if($project->notes)
                <li class="list-group-item small">
                    <span class="text-muted d-block mb-1">Notes</span>
                    {{ $project->notes }}
                </li>
                @endif
            </ul>
        </div>

        {{-- Assigned Team --}}
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Assigned Team</h6>
            </div>
            <div class="card-body">
                @if($project->assignees->isEmpty())
                    <p class="text-muted small mb-0">No team leads assigned.</p>
                @else
                    @foreach($project->assignees as $assignee)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:0.75rem;">
                            {{ strtoupper(substr($assignee->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="fw-semibold small">{{ $assignee->name }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $assignee->role->label() }}</div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Linked Invoices --}}
        @if(auth()->user()->isManager() && $project->invoices->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Invoices</h6>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($project->invoices as $invoice)
                <li class="list-group-item d-flex justify-content-between align-items-center small">
                    <a href="{{ route('invoices.show', $invoice) }}" class="text-decoration-none">
                        {{ $invoice->invoice_number }}
                    </a>
                    <div class="text-end">
                        <div>{{ format_inr($invoice->total) }}</div>
                        <span class="badge {{ $invoice->status->badgeClass() }}">{{ $invoice->status->label() }}</span>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Linked Calendar Events --}}
        @if($project->calendarEvents->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Upcoming Events</h6>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($project->calendarEvents->where('start_date', '>=', now())->take(5) as $event)
                <li class="list-group-item small">
                    <div class="fw-semibold">{{ $event->title }}</div>
                    <div class="text-muted">{{ \Carbon\Carbon::parse($event->start_date)->format('d M Y, H:i') }}</div>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

    </div>
</div>

{{-- Project Finance Section (managers only) --}}
@if(auth()->user()->isManager() && ($projectIncome !== null || $projectExpense !== null))
<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0"><i class="ti ti-report-money me-1"></i> Project Finances</h6>
                @php
                    $totalIn  = (float) ($projectIncome?->sum('amount')  ?? 0);
                    $totalOut = (float) ($projectExpense?->sum('amount') ?? 0);
                    $profit   = $totalIn - $totalOut;
                @endphp
                <div class="d-flex gap-3 small">
                    <span class="text-success fw-semibold">Income: {{ format_inr($totalIn) }}</span>
                    <span class="text-danger fw-semibold">Expenses: {{ format_inr($totalOut) }}</span>
                    <span class="fw-bold {{ $profit >= 0 ? 'text-success' : 'text-danger' }}">
                        P&amp;L: {{ $profit >= 0 ? '' : '−' }}{{ format_inr(abs($profit)) }}
                    </span>
                    @if($project->budget)
                        <span class="text-muted">Budget: {{ format_inr((float)$project->budget) }}</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="text-success mb-2"><i class="ti ti-trending-up me-1"></i>Income</h6>
                        @if($projectIncome && $projectIncome->isNotEmpty())
                            <table class="table table-sm">
                                <thead class="table-light"><tr><th>Date</th><th>Category</th><th class="text-end">Amount</th></tr></thead>
                                <tbody>
                                    @foreach($projectIncome as $t)
                                    <tr>
                                        <td class="small">{{ $t->date->format('d M Y') }}</td>
                                        <td class="small">{{ $t->category }}</td>
                                        <td class="text-end fw-semibold text-success">{{ format_inr((float)$t->amount) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted small">No income transactions linked.</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-danger mb-2"><i class="ti ti-trending-down me-1"></i>Expenses</h6>
                        @if($projectExpense && $projectExpense->isNotEmpty())
                            <table class="table table-sm">
                                <thead class="table-light"><tr><th>Date</th><th>Category</th><th class="text-end">Amount</th></tr></thead>
                                <tbody>
                                    @foreach($projectExpense as $t)
                                    <tr>
                                        <td class="small">{{ $t->date->format('d M Y') }}</td>
                                        <td class="small">{{ $t->category }}</td>
                                        <td class="text-end fw-semibold text-danger">{{ format_inr((float)$t->amount) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted small">No expense transactions linked.</p>
                        @endif
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('transactions.create', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="ti ti-plus me-1"></i> Add Transaction for This Project
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
