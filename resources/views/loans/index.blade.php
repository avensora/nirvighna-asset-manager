@extends('layouts.app', ['title' => 'Loan Register', 'subtitle' => 'Finances'])

@section('content')

{{-- Summary --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Total Borrowed (All Time)</p>
                <p class="fw-bold fs-5 text-primary mb-0">{{ format_inr((float)$totalBorrowed) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Total Outstanding</p>
                <p class="fw-bold fs-5 text-danger mb-0">{{ format_inr((float)$totalOutstanding) }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3 align-items-center">
    <div class="col">
        <a href="{{ route('loans.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Record Loan
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($loans->isEmpty())
            <p class="text-muted text-center py-4">No loans recorded yet.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Source</th>
                            <th>Type</th>
                            <th>Purpose</th>
                            <th>Borrowed</th>
                            <th>Due</th>
                            <th class="text-end">Principal</th>
                            <th class="text-end">Repaid</th>
                            <th class="text-end">Outstanding</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loans as $loan)
                        @php
                            $repaid      = (float)($loan->repayments_sum_amount ?? 0);
                            $outstanding = max(0, (float)$loan->principal_amount - $repaid);
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('loans.show', $loan) }}" class="fw-semibold text-body">
                                    {{ $loan->source_name }}
                                </a>
                            </td>
                            <td class="small text-muted">{{ $loan->source_type->label() }}</td>
                            <td class="small text-muted">{{ $loan->purpose ?? '—' }}</td>
                            <td class="small">{{ $loan->borrowed_date->format('d M Y') }}</td>
                            <td class="small {{ $loan->due_date && $loan->due_date->isPast() && $loan->status->value !== 'repaid' ? 'text-danger fw-semibold' : '' }}">
                                {{ $loan->due_date?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="text-end fw-semibold">{{ format_inr((float)$loan->principal_amount) }}</td>
                            <td class="text-end text-success">{{ format_inr($repaid) }}</td>
                            <td class="text-end fw-bold {{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">
                                {{ format_inr($outstanding) }}
                            </td>
                            <td><span class="badge {{ $loan->status->badgeClass() }}">{{ $loan->status->label() }}</span></td>
                            <td>
                                <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($loans->hasPages())
                <div class="mt-3">{{ $loans->links() }}</div>
            @endif
        @endif
    </div>
</div>

@endsection
