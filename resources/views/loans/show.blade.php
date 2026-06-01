@extends('layouts.app', ['title' => $loan->source_name, 'subtitle' => 'Loan Register'])

@section('content')

<div class="row g-3">

    {{-- Loan detail --}}
    <div class="col-xl-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">{{ $loan->source_name }}</h5>
                    <small class="text-muted">{{ $loan->source_type->label() }}</small>
                </div>
                <span class="badge {{ $loan->status->badgeClass() }} fs-6">{{ $loan->status->label() }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <p class="text-muted fw-medium mb-1 small">Principal</p>
                        <p class="mb-0 fw-bold fs-5">{{ format_inr((float)$loan->principal_amount) }}</p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted fw-medium mb-1 small">Repaid</p>
                        <p class="mb-0 fw-semibold text-success">{{ format_inr($loan->amountRepaid()) }}</p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted fw-medium mb-1 small">Outstanding</p>
                        <p class="mb-0 fw-bold {{ $loan->amountOutstanding() > 0 ? 'text-danger' : 'text-success' }}">
                            {{ format_inr($loan->amountOutstanding()) }}
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted fw-medium mb-1 small">Borrowed Date</p>
                        <p class="mb-0">{{ $loan->borrowed_date->format('d M Y') }}</p>
                    </div>
                </div>
                @if($loan->due_date || $loan->purpose)
                <div class="row g-3">
                    @if($loan->due_date)
                    <div class="col-md-4">
                        <p class="text-muted fw-medium mb-1 small">Due Date</p>
                        <p class="mb-0 {{ $loan->due_date->isPast() && $loan->status->value !== 'repaid' ? 'text-danger fw-semibold' : '' }}">
                            {{ $loan->due_date->format('d M Y') }}
                        </p>
                    </div>
                    @endif
                    @if($loan->purpose)
                    <div class="col-md-8">
                        <p class="text-muted fw-medium mb-1 small">Purpose</p>
                        <p class="mb-0">{{ $loan->purpose }}</p>
                    </div>
                    @endif
                </div>
                @endif
                @if($loan->notes)
                <div class="mt-3 p-3 bg-light rounded">
                    <p class="text-muted fw-medium mb-1 small">Notes</p>
                    <p class="mb-0">{{ $loan->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Repayment History --}}
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0">Repayment History</h6></div>
            @if($loan->repayments->isEmpty())
                <div class="card-body text-muted">No repayments recorded yet.</div>
            @else
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Recorded By</th>
                                <th class="text-end">Amount</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loan->repayments as $repayment)
                            <tr>
                                <td>{{ $repayment->repaid_date->format('d M Y') }}</td>
                                <td class="text-muted small">{{ $repayment->reference ?? '—' }}</td>
                                <td class="small">{{ $repayment->creator?->name ?? '—' }}</td>
                                <td class="text-end fw-semibold text-success">{{ format_inr((float)$repayment->amount) }}</td>
                                <td class="text-end">
                                    <form action="{{ route('loans.repayments.destroy', [$loan, $repayment]) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this repayment?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @if($repayment->notes)
                            <tr class="border-0">
                                <td colspan="5" class="pt-0 pb-1 small text-muted" style="font-size:0.75rem">{{ $repayment->notes }}</td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Actions panel --}}
    <div class="col-xl-4">

        @if($loan->status->value !== 'repaid')
        <div class="card mb-3">
            <div class="card-header"><h6 class="card-title mb-0">Record Repayment</h6></div>
            <div class="card-body">
                <form action="{{ route('loans.repayments.store', $loan) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Amount <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                   step="0.01" min="0.01" max="{{ $loan->amountOutstanding() }}"
                                   placeholder="0.00" required>
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <small class="text-muted">Max: {{ format_inr($loan->amountOutstanding()) }}</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Date <span class="text-danger">*</span></label>
                        <input type="date" name="repaid_date" class="form-control form-control-sm"
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Reference</label>
                        <input type="text" name="reference" class="form-control form-control-sm"
                               placeholder="Transaction ID / receipt" maxlength="100">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="ti ti-cash me-1"></i> Save Repayment
                    </button>
                </form>
            </div>
        </div>
        @endif

        <div class="card mb-3">
            <div class="card-body d-grid gap-2">
                @if($loan->status->value !== 'repaid')
                <a href="{{ route('loans.edit', $loan) }}" class="btn btn-outline-primary">
                    <i class="ti ti-pencil me-1"></i> Edit Loan
                </a>
                @endif
                @if($loan->repayments->isEmpty())
                <form action="{{ route('loans.destroy', $loan) }}" method="POST"
                      onsubmit="return confirm('Delete this loan record?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="ti ti-trash me-1"></i> Delete Loan
                    </button>
                </form>
                @endif
            </div>
        </div>

    </div>

</div>

<div class="mt-2">
    <a href="{{ route('loans.index') }}" class="text-muted small">
        <i class="ti ti-arrow-left me-1"></i> Back to Loan Register
    </a>
</div>

@endsection
