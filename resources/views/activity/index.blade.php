@extends('layouts.app', ['title' => 'Activity Log', 'subtitle' => 'System Activity'])

@section('content')

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-medium mb-1">Subject Type</label>
                <select name="subject_type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="Client"      {{ request('subject_type') === 'Client'      ? 'selected' : '' }}>Clients</option>
                    <option value="Invoice"     {{ request('subject_type') === 'Invoice'     ? 'selected' : '' }}>Invoices</option>
                    <option value="Transaction" {{ request('subject_type') === 'Transaction' ? 'selected' : '' }}>Transactions</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-medium mb-1">User</label>
                <select name="causer_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('causer_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-medium mb-1">From</label>
                <input type="date" name="date_from" class="form-control form-control-sm"
                       value="{{ request('date_from') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-medium mb-1">To</label>
                <input type="date" name="date_to" class="form-control form-control-sm"
                       value="{{ request('date_to') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-2 d-flex gap-2">
                @if(request('subject_type') || request('causer_id') || request('date_from') || request('date_to'))
                    <a href="{{ route('activity.index') }}" class="btn btn-sm btn-outline-secondary w-100">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Timeline --}}
<div class="card">
    <div class="card-body">

        @if($activities->isEmpty())
            <p class="text-muted text-center py-5">No activity recorded yet.</p>
        @else
            <div class="list-group list-group-flush">
                @foreach($activities as $activity)
                    @php
                        $subjectClass = class_basename($activity->subject_type ?? '');
                        $icon = match($subjectClass) {
                            'Client'      => 'ti-users',
                            'Invoice'     => 'ti-file-invoice',
                            'Transaction' => 'ti-report-money',
                            default       => 'ti-activity',
                        };
                        $colorClass = match($subjectClass) {
                            'Client'      => 'primary',
                            'Invoice'     => 'info',
                            'Transaction' => 'success',
                            default       => 'secondary',
                        };
                    @endphp
                    <div class="list-group-item px-0 py-3 border-bottom">
                        <div class="d-flex align-items-start gap-3">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title rounded-circle bg-{{ $colorClass }}-subtle text-{{ $colorClass }}">
                                    <i class="ti {{ $icon }} fs-18"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <p class="mb-1 fw-medium text-truncate">{{ $activity->description }}</p>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span class="text-muted small">
                                        <i class="ti ti-user me-1"></i>
                                        {{ $activity->causer?->name ?? 'System' }}
                                    </span>
                                    @if($subjectClass)
                                        <span class="badge bg-light text-muted border small">{{ $subjectClass }}</span>
                                    @endif
                                    @if($activity->properties->isNotEmpty())
                                        @foreach($activity->properties->except(['attributes', 'old']) as $key => $val)
                                            <span class="badge bg-light text-muted border small text-truncate" style="max-width:180px" title="{{ $key }}: {{ $val }}">
                                                {{ $key }}: {{ is_array($val) ? json_encode($val) : $val }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="text-muted text-nowrap small text-end flex-shrink-0">
                                <div>{{ $activity->created_at->format('d M Y') }}</div>
                                <div>{{ $activity->created_at->format('H:i') }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($activities->hasPages())
                <div class="mt-3">
                    {{ $activities->links() }}
                </div>
            @endif
        @endif

    </div>
</div>

@endsection
