@extends('layouts.app', ['title' => 'Leads', 'subtitle' => 'Leads'])

@section('content')

{{-- Toolbar --}}
<div class="d-flex flex-wrap align-items-end gap-2 mb-3">
    <form class="d-flex flex-wrap gap-2 align-items-end toolbar-form" method="GET" action="{{ route('leads.index') }}">
        <input type="hidden" name="view" value="{{ $view }}">
        <input type="text" name="search" class="form-control form-control-sm" style="min-width:140px;flex:1 1 140px;"
               placeholder="Search leads…" value="{{ request('search') }}">
        <select name="stage" class="form-select form-select-sm" style="min-width:130px;flex:0 1 auto;">
            <option value="">All Stages</option>
            @foreach($stages as $stage)
                <option value="{{ $stage->value }}" {{ request('stage') === $stage->value ? 'selected' : '' }}>
                    {{ $stage->label() }}
                </option>
            @endforeach
        </select>
        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
            @if(request()->hasAny(['search','stage']))
                <a href="{{ route('leads.index', ['view' => $view]) }}" class="btn btn-sm btn-link text-muted">Clear</a>
            @endif
        </div>
    </form>
    <div class="d-flex gap-2 align-items-center ms-auto toolbar-actions flex-wrap">
        {{-- View toggle --}}
        <div class="btn-group btn-group-sm">
            <a href="{{ route('leads.index', array_merge(request()->query(), ['view' => 'table'])) }}"
               class="btn btn-outline-secondary {{ $view === 'table' ? 'active' : '' }}" title="Table view">
                <i class="ti ti-list"></i>
            </a>
            <a href="{{ route('leads.index', array_merge(request()->query(), ['view' => 'kanban'])) }}"
               class="btn btn-outline-secondary {{ $view === 'kanban' ? 'active' : '' }}" title="Pipeline view">
                <i class="ti ti-layout-kanban"></i>
            </a>
        </div>
        <a href="{{ route('leads.import.show') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-upload me-1"></i> Import
        </a>
        <a href="{{ route('leads.create') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus me-1"></i> New Lead
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($view === 'kanban')
    {{-- Kanban pipeline --}}
    <div class="d-flex gap-3 overflow-auto pb-3" style="min-height: 70vh; align-items: flex-start;">
        @foreach($stages as $stage)
        @php $stageLeads = $grouped[$stage->value] ?? collect(); @endphp
        <div class="flex-shrink-0" style="width: 260px;">
            <div class="card h-100">
                <div class="card-header py-2 {{ $stage->headerClass() }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">{{ $stage->label() }}</span>
                        <span class="badge bg-white bg-opacity-25 text-white">{{ $stageLeads->count() }}</span>
                    </div>
                </div>
                <div class="card-body p-2" style="overflow-y: auto; max-height: 65vh;">
                    @forelse($stageLeads as $lead)
                    <div class="card mb-2 shadow-sm border-0">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <a href="{{ route('leads.show', $lead) }}"
                                   class="fw-semibold text-body text-decoration-none small">
                                    {{ $lead->name }}
                                </a>
                            </div>
                            @if($lead->company)
                                <div class="text-muted" style="font-size: .75rem;">{{ $lead->company }}</div>
                            @endif
                            @if($lead->source)
                                <div class="text-muted" style="font-size: .75rem;">{{ $lead->source }}</div>
                            @endif
                            @if($lead->deal_value)
                                <div class="text-success fw-semibold" style="font-size: .75rem;">{{ format_inr($lead->deal_value) }}</div>
                            @endif
                            <div class="mt-2 d-flex gap-1 flex-wrap">
                                <form action="{{ route('leads.stage', $lead) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <select name="stage" class="form-select form-select-sm py-0"
                                            style="font-size:.72rem;" onchange="this.form.submit()">
                                        @foreach($stages as $s)
                                            <option value="{{ $s->value }}"
                                                {{ $lead->stage === $s ? 'selected' : '' }}>
                                                {{ $s->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                        <p class="text-muted text-center small py-3">No leads</p>
                    @endforelse
                </div>
            </div>
        </div>
        @endforeach
    </div>

@else
    {{-- Table view --}}
    <div class="card">
        <div class="card-body p-0">
            @if($leads->isEmpty())
                <p class="text-muted text-center py-5">
                    No leads yet. <a href="{{ route('leads.create') }}">Add one</a> or
                    <a href="{{ route('leads.import.show') }}">import from CSV</a>.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Company</th>
                                <th>Stage</th>
                                <th>Source</th>
                                <th>Assigned</th>
                                <th>Created</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leads as $lead)
                            <tr>
                                <td>
                                    <a href="{{ route('leads.show', $lead) }}" class="fw-semibold text-body text-decoration-none">
                                        {{ $lead->name }}
                                    </a>
                                    @if($lead->email)
                                        <div class="text-muted small">{{ $lead->email }}</div>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $lead->company ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $lead->stage->badgeClass() }}">{{ $lead->stage->label() }}</span>
                                </td>
                                <td class="text-muted small">{{ $lead->source ?? '—' }}</td>
                                <td class="text-muted small">{{ $lead->assignee?->name ?? '—' }}</td>
                                <td class="text-muted small">{{ $lead->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('leads.show', $lead) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="{{ route('leads.edit', $lead) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($leads->hasPages())
                    <div class="p-3">{{ $leads->links() }}</div>
                @endif
            @endif
        </div>
    </div>
@endif

@endsection
