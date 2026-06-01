@extends('layouts.app', ['title' => $reimbursement->title, 'subtitle' => 'Reimbursements'])

@section('content')

<div class="row g-3">

    {{-- Detail card --}}
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">{{ $reimbursement->title }}</h5>
                    <small class="text-muted">Submitted by {{ $reimbursement->user->name }}</small>
                </div>
                <span class="badge {{ $reimbursement->status->badgeClass() }} fs-6">{{ $reimbursement->status->label() }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <p class="text-muted fw-medium mb-1 small">Amount</p>
                        <p class="mb-0 fw-bold fs-5 text-primary">{{ format_inr((float)$reimbursement->amount) }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted fw-medium mb-1 small">Date Spent</p>
                        <p class="mb-0">{{ $reimbursement->spent_date->format('d M Y') }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted fw-medium mb-1 small">Category</p>
                        <p class="mb-0">{{ $reimbursement->category ?? '—' }}</p>
                    </div>
                </div>

                @if($reimbursement->project)
                <div class="mb-3">
                    <p class="text-muted fw-medium mb-1 small">Project</p>
                    <p class="mb-0">
                        <a href="{{ route('projects.show', $reimbursement->project) }}">{{ $reimbursement->project->title }}</a>
                    </p>
                </div>
                @endif

                @if($reimbursement->description)
                <div class="p-3 bg-light rounded">
                    <p class="text-muted fw-medium mb-1 small">Description</p>
                    <p class="mb-0">{{ $reimbursement->description }}</p>
                </div>
                @endif

                @if($reimbursement->status === \App\Enums\ReimbursementStatus::Rejected && $reimbursement->rejection_reason)
                <div class="alert alert-danger mt-3 mb-0">
                    <strong>Rejection Reason:</strong> {{ $reimbursement->rejection_reason }}
                </div>
                @endif
            </div>
            <div class="card-footer text-muted small">
                Submitted {{ $reimbursement->created_at->format('d M Y') }}
                @if($reimbursement->approved_at) · Approved {{ $reimbursement->approved_at->format('d M Y') }} @endif
                @if($reimbursement->reimbursed_at) · Reimbursed {{ $reimbursement->reimbursed_at->format('d M Y') }} @endif
            </div>
        </div>
    </div>

    {{-- Actions panel --}}
    <div class="col-xl-4">

        @if(auth()->user()->isManager())
        <div class="card mb-3">
            <div class="card-header"><h6 class="card-title mb-0">Actions</h6></div>
            <div class="card-body d-grid gap-2">

                @if($reimbursement->status === \App\Enums\ReimbursementStatus::Pending)
                <form action="{{ route('reimbursements.approve', $reimbursement) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                        <i class="ti ti-circle-check me-1"></i> Approve
                    </button>
                </form>
                @endif

                @if($reimbursement->status === \App\Enums\ReimbursementStatus::Approved)
                <form action="{{ route('reimbursements.reimburse', $reimbursement) }}" method="POST"
                      onsubmit="return confirm('Mark as reimbursed and record expense transaction?')">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-cash me-1"></i> Mark Reimbursed
                    </button>
                </form>
                @endif

                @if(in_array($reimbursement->status, [\App\Enums\ReimbursementStatus::Pending, \App\Enums\ReimbursementStatus::Approved]))
                <div>
                    <button class="btn btn-outline-danger w-100" type="button"
                            data-bs-toggle="collapse" data-bs-target="#rejectForm">
                        <i class="ti ti-x me-1"></i> Reject
                    </button>
                    <div class="collapse mt-2" id="rejectForm">
                        <form action="{{ route('reimbursements.reject', $reimbursement) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <textarea name="rejection_reason" class="form-control form-control-sm @error('rejection_reason') is-invalid @enderror"
                                          rows="2" placeholder="Reason for rejection" required maxlength="500"></textarea>
                                @error('rejection_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <button type="submit" class="btn btn-danger btn-sm w-100">Confirm Reject</button>
                        </form>
                    </div>
                </div>
                @endif

            </div>
        </div>
        @endif

        @if($reimbursement->status === \App\Enums\ReimbursementStatus::Pending && (auth()->user()->isManager() || $reimbursement->user_id === auth()->id()))
        <div class="card mb-3">
            <div class="card-body d-grid gap-2">
                <a href="{{ route('reimbursements.edit', $reimbursement) }}" class="btn btn-outline-primary">
                    <i class="ti ti-pencil me-1"></i> Edit
                </a>
                <form action="{{ route('reimbursements.destroy', $reimbursement) }}" method="POST"
                      onsubmit="return confirm('Delete this reimbursement request?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="ti ti-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>

</div>

<div class="mt-2">
    <a href="{{ route('reimbursements.index') }}" class="text-muted small">
        <i class="ti ti-arrow-left me-1"></i> Back to Reimbursements
    </a>
</div>

@endsection
