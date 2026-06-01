@extends('layouts.app', ['title' => 'Reimbursements', 'subtitle' => 'Finances'])

@section('content')

{{-- Summary strip --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Pending</p>
                <p class="fw-bold fs-5 text-warning mb-0">{{ format_inr((float)$pendingTotal) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Approved (Awaiting Payment)</p>
                <p class="fw-bold fs-5 text-info mb-0">{{ format_inr((float)$approvedTotal) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Reimbursed (All Time)</p>
                <p class="fw-bold fs-5 text-success mb-0">{{ format_inr((float)$reimbursedTotal) }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3 align-items-center">
    <div class="col">
        <a href="{{ route('reimbursements.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> New Request
        </a>
    </div>
    <div class="col-auto">
        <form method="GET" class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width:160px">
                <option value="">All Statuses</option>
                @foreach(\App\Enums\ReimbursementStatus::cases() as $s)
                    <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($reimbursements->isEmpty())
            <p class="text-muted text-center py-4">No reimbursement requests found.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            @if(auth()->user()->isManager())
                                <th>Submitted By</th>
                            @endif
                            <th>Title</th>
                            <th>Category</th>
                            <th>Project</th>
                            <th>Date Spent</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reimbursements as $r)
                        <tr>
                            @if(auth()->user()->isManager())
                                <td>{{ $r->user->name }}</td>
                            @endif
                            <td>
                                <a href="{{ route('reimbursements.show', $r) }}" class="fw-semibold text-body">
                                    {{ $r->title }}
                                </a>
                            </td>
                            <td class="text-muted small">{{ $r->category ?? '—' }}</td>
                            <td class="text-muted small">{{ $r->project?->title ?? '—' }}</td>
                            <td class="small">{{ $r->spent_date->format('d M Y') }}</td>
                            <td class="text-end fw-semibold">{{ format_inr((float)$r->amount) }}</td>
                            <td><span class="badge {{ $r->status->badgeClass() }}">{{ $r->status->label() }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('reimbursements.show', $r) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="ti ti-eye"></i>
                                </a>
                                @if($r->status === \App\Enums\ReimbursementStatus::Pending && (auth()->user()->isManager() || $r->user_id === auth()->id()))
                                <a href="{{ route('reimbursements.edit', $r) }}" class="btn btn-sm btn-outline-primary ms-1">
                                    <i class="ti ti-pencil"></i>
                                </a>
                                @endif
                                @if(auth()->user()->isManager() && $r->status === \App\Enums\ReimbursementStatus::Pending)
                                <form action="{{ route('reimbursements.approve', $r) }}" method="POST" class="d-inline ms-1">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                        <i class="ti ti-check"></i>
                                    </button>
                                </form>
                                @endif
                                @if(auth()->user()->isManager() && $r->status === \App\Enums\ReimbursementStatus::Approved)
                                <form action="{{ route('reimbursements.reimburse', $r) }}" method="POST" class="d-inline ms-1"
                                      onsubmit="return confirm('Mark as reimbursed?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary" title="Mark Reimbursed">
                                        <i class="ti ti-cash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($reimbursements->hasPages())
                <div class="mt-3">{{ $reimbursements->links() }}</div>
            @endif
        @endif
    </div>
</div>

@endsection
