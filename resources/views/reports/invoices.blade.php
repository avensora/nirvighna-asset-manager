@extends('layouts.app', ['title' => 'Invoice Aging', 'subtitle' => 'Reports'])

@section('content')

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>All Reports
    </a>
    <h5 class="fw-semibold mb-0">Invoice Aging Report</h5>
</div>

{{-- Summary KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small fw-medium mb-1">Total Unpaid Invoices</p>
                <h4 class="fw-bold mb-0">{{ $totalUnpaid }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small fw-medium mb-1">Total Outstanding Value</p>
                <h4 class="fw-bold mb-0 text-danger">{{ format_inr($totalUnpaidValue) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small fw-medium mb-1">Overdue (90+ days)</p>
                @php $criticalCount = $buckets['90_plus']['items']->count(); @endphp
                <h4 class="fw-bold mb-0 {{ $criticalCount > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $criticalCount }}
                </h4>
            </div>
        </div>
    </div>
</div>

@if($totalUnpaid === 0)
<div class="card">
    <div class="card-body text-center py-5">
        <i class="ti ti-circle-check fs-48 text-success mb-3 d-block"></i>
        <h5 class="text-success">All caught up!</h5>
        <p class="text-muted mb-0">No unpaid invoices at this time.</p>
    </div>
</div>
@else

@foreach($buckets as $key => $bucket)
@if($bucket['items']->isNotEmpty())
<div class="card mb-3">
    <div class="card-header d-flex align-items-center gap-2">
        <span class="badge bg-{{ $bucket['color'] === 'orange' ? 'warning' : $bucket['color'] }}">
            {{ $bucket['items']->count() }}
        </span>
        <h5 class="card-title mb-0">{{ $bucket['label'] }}</h5>
        <span class="ms-auto fw-semibold text-muted small">
            Total: {{ format_inr((float) $bucket['items']->sum('total')) }}
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice #</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th class="text-end">Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bucket['items'] as $invoice)
                    <tr>
                        <td class="fw-medium">{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->client?->name ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $invoice->status->badgeClass() }}">
                                {{ $invoice->status->label() }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $invoice->issue_date->format('d M Y') }}</td>
                        <td class="{{ in_array($key, ['1_30','31_60','61_90','90_plus']) ? 'text-danger fw-semibold' : 'text-muted' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="text-end fw-semibold">{{ format_inr((float)$invoice->total) }}</td>
                        <td class="text-end">
                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endforeach

@endif

@endsection
