@extends('layouts.app', ['title' => $lead->name, 'subtitle' => 'Leads'])

@section('content')

<div class="row g-4">

    {{-- Left: Lead details --}}
    <div class="col-lg-8">

        {{-- Header card --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="mb-1">{{ $lead->name }}</h4>
                        @if($lead->company)
                            <p class="text-muted mb-1"><i class="ti ti-building me-1"></i>{{ $lead->company }}</p>
                        @endif
                        <span class="badge {{ $lead->stage->badgeClass() }} fs-6">{{ $lead->stage->label() }}</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('leads.edit', $lead) }}" class="btn btn-outline-primary btn-sm">
                            <i class="ti ti-pencil me-1"></i> Edit
                        </a>
                        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>

                <hr>

                <div class="row g-3">
                    @if($lead->email)
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Email</small>
                        <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
                    </div>
                    @endif
                    @if($lead->phone)
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Phone</small>
                        <a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a>
                    </div>
                    @endif
                    @if($lead->deal_value)
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Deal Value</small>
                        <span class="fw-semibold text-success">{{ format_inr($lead->deal_value) }}</span>
                    </div>
                    @endif
                    @if($lead->source)
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Source</small>
                        {{ $lead->source }}
                    </div>
                    @endif
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Created</small>
                        {{ $lead->created_at->format('d M Y') }} by {{ $lead->creator?->name ?? '—' }}
                    </div>
                    @if($lead->assignee)
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Assigned To</small>
                        {{ $lead->assignee->name }}
                    </div>
                    @endif
                </div>

                @if($lead->notes)
                <div class="mt-3">
                    <small class="text-muted d-block mb-1">Notes</small>
                    <p class="mb-0" style="white-space: pre-line;">{{ $lead->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Activity timeline --}}
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Activity</h6>
            </div>
            <div class="card-body p-0">
                @if($activities->isEmpty())
                    <p class="text-muted text-center py-4">No activity recorded.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($activities as $activity)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="fw-semibold">{{ $activity->causer?->name ?? 'System' }}</span>
                                    <span class="text-muted ms-1">{{ $activity->description }}</span>
                                </div>
                                <small class="text-muted text-nowrap ms-3">
                                    {{ $activity->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- Right: Stage mover + Convert --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Move Stage</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('leads.stage', $lead) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">Current Stage</label>
                        <select name="stage" class="form-select">
                            @foreach($stages as $stage)
                                <option value="{{ $stage->value }}"
                                    {{ $lead->stage === $stage ? 'selected' : '' }}>
                                    {{ $stage->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Stage</button>
                </form>
            </div>
        </div>

        <div class="card border-success">
            <div class="card-header bg-success-subtle">
                <h6 class="card-title mb-0 text-success">Convert to Project</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Creates a new project from this lead.
                    @if($lead->deal_value)
                        Budget will be set to <strong>{{ format_inr($lead->deal_value) }}</strong>.
                    @endif
                    @if($lead->stage !== \App\Enums\LeadStage::Won)
                        Lead will be marked as <strong>Won</strong>.
                    @endif
                </p>
                <form action="{{ route('leads.convert', $lead) }}" method="POST"
                      onsubmit="return confirm('Convert this lead into a project?')">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                        <i class="ti ti-arrows-transfer-up me-1"></i> Convert to Project
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

@endsection
