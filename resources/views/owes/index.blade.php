@extends('layouts.app', ['title' => 'Who Owes Whom', 'subtitle' => 'Finances'])

@section('content')

{{-- Net Balance Banner --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card {{ $netBalance >= 0 ? 'border-success' : 'border-danger' }}">
            <div class="card-body text-center py-4">
                <p class="text-muted small mb-1">Company Net Balance (Income − Expenses)</p>
                <p class="fw-bold mb-1" style="font-size:2rem; color:{{ $netBalance >= 0 ? '#198754' : '#dc3545' }}">
                    {{ $netBalance >= 0 ? '' : '−' }}{{ format_inr(abs($netBalance)) }}
                </p>
                <p class="text-muted small mb-0">
                    Total Income: <strong class="text-success">{{ format_inr($totalIncome) }}</strong> &nbsp;·&nbsp;
                    Total Expenses: <strong class="text-danger">{{ format_inr($totalExpense) }}</strong>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- Column 1: We owe employees --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="ti ti-receipt-refund text-warning me-1"></i> We Owe Employees
                </h6>
                <span class="badge bg-warning text-dark">{{ format_inr($totalOwedToEmployees) }}</span>
            </div>
            <div class="card-body p-0">
                @if($pendingReimbursements->isEmpty())
                    <p class="text-muted text-center py-4 px-3">No pending reimbursements.</p>
                @else
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Requests</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingReimbursements as $row)
                            <tr>
                                <td>
                                    <a href="{{ route('reimbursements.index', ['status' => 'pending']) }}" class="text-body fw-medium small">
                                        {{ $row['user']->name }}
                                    </a>
                                </td>
                                <td class="text-end fw-semibold text-warning">{{ format_inr($row['total']) }}</td>
                                <td class="text-end text-muted small">{{ $row['count'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-bold">Total</td>
                                <td class="text-end fw-bold text-warning" colspan="2">{{ format_inr($totalOwedToEmployees) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @endif
            </div>
            @if($pendingReimbursements->isNotEmpty())
            <div class="card-footer">
                <a href="{{ route('reimbursements.index') }}" class="text-muted small">
                    View all reimbursements <i class="ti ti-arrow-right ms-1"></i>
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Column 2: Clients owe us --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="ti ti-file-invoice text-primary me-1"></i> Clients Owe Us
                </h6>
                <span class="badge bg-primary">{{ format_inr($totalOwedByClients) }}</span>
            </div>
            <div class="card-body p-0">
                @if($outstandingInvoices->isEmpty())
                    <p class="text-muted text-center py-4 px-3">No outstanding client balances.</p>
                @else
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Client</th>
                                <th class="text-end">Due</th>
                                <th class="text-end">Invoices</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($outstandingInvoices as $row)
                            <tr>
                                <td>
                                    <span class="fw-medium small">{{ $row['client']?->name ?? 'Unknown' }}</span>
                                </td>
                                <td class="text-end fw-semibold text-primary">{{ format_inr($row['total_due']) }}</td>
                                <td class="text-end text-muted small">{{ $row['invoice_count'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-bold">Total</td>
                                <td class="text-end fw-bold text-primary" colspan="2">{{ format_inr($totalOwedByClients) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @endif
            </div>
            @if($outstandingInvoices->isNotEmpty())
            <div class="card-footer">
                <a href="{{ route('invoices.index', ['status' => 'sent']) }}" class="text-muted small">
                    View outstanding invoices <i class="ti ti-arrow-right ms-1"></i>
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Column 3: We owe lenders --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="ti ti-building-bank text-danger me-1"></i> We Owe Lenders
                </h6>
                <span class="badge bg-danger">{{ format_inr($totalOwedToLenders) }}</span>
            </div>
            <div class="card-body p-0">
                @if($outstandingLoans->isEmpty())
                    <p class="text-muted text-center py-4 px-3">No outstanding loans.</p>
                @else
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Source</th>
                                <th>Due</th>
                                <th class="text-end">Outstanding</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($outstandingLoans as $row)
                            <tr>
                                <td>
                                    <a href="{{ route('loans.show', $row['loan']) }}" class="text-body fw-medium small">
                                        {{ $row['loan']->source_name }}
                                    </a>
                                    <br><small class="text-muted" style="font-size:0.7rem">{{ $row['loan']->source_type->label() }}</small>
                                </td>
                                <td class="small {{ $row['loan']->due_date && $row['loan']->due_date->isPast() ? 'text-danger fw-semibold' : 'text-muted' }}">
                                    {{ $row['loan']->due_date?->format('d M Y') ?? '—' }}
                                </td>
                                <td class="text-end fw-semibold text-danger">{{ format_inr($row['outstanding']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-bold" colspan="2">Total</td>
                                <td class="text-end fw-bold text-danger">{{ format_inr($totalOwedToLenders) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @endif
            </div>
            @if($outstandingLoans->isNotEmpty())
            <div class="card-footer">
                <a href="{{ route('loans.index') }}" class="text-muted small">
                    View loan register <i class="ti ti-arrow-right ms-1"></i>
                </a>
            </div>
            @endif
        </div>
    </div>

</div>

@endsection
