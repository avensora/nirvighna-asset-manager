@extends('layouts.app', ['title' => 'Income & Expenses', 'subtitle' => 'Finances'])

@section('content')

{{-- Summary strip --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 bg-success-subtle">
            <div class="card-body py-3">
                <p class="text-muted fw-medium mb-1 small">Total Income</p>
                <h5 class="mb-0 fw-bold text-success">{{ format_inr((float)($totals->income ?? 0)) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-danger-subtle">
            <div class="card-body py-3">
                <p class="text-muted fw-medium mb-1 small">Total Expenses</p>
                <h5 class="mb-0 fw-bold text-danger">{{ format_inr((float)($totals->expense ?? 0)) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        @php $net = (float)($totals->income ?? 0) - (float)($totals->expense ?? 0); @endphp
        <div class="card border-0 {{ $net >= 0 ? 'bg-primary-subtle' : 'bg-warning-subtle' }}">
            <div class="card-body py-3">
                <p class="text-muted fw-medium mb-1 small">Net (Filtered)</p>
                <h5 class="mb-0 fw-bold {{ $net >= 0 ? 'text-primary' : 'text-warning' }}">
                    {{ $net >= 0 ? '' : '−' }}{{ format_inr(abs($net)) }}
                </h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 {{ $currentBalance >= 0 ? 'bg-success-subtle' : 'bg-danger-subtle' }}" title="Opening balance + all transactions from {{ $openingBalanceDate }}">
            <div class="card-body py-3">
                <p class="text-muted fw-medium mb-1 small">
                    Company Balance
                    <a href="{{ route('settings.company') }}" class="ms-1 text-muted" title="Set opening balance"><i class="ti ti-settings fs-12"></i></a>
                </p>
                <h5 class="mb-0 fw-bold {{ $currentBalance >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $currentBalance >= 0 ? '' : '−' }}{{ format_inr(abs($currentBalance)) }}
                </h5>
                <small class="text-muted" style="font-size:0.7rem">Opening: {{ format_inr($openingBalance) }} · Since {{ $openingBalanceDate }}</small>
            </div>
        </div>
    </div>
</div>

{{-- Pending approvals alert --}}
@if(auth()->user()->isManager() && $pendingCount > 0)
<div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
    <i class="ti ti-clock-hour-4 fs-20"></i>
    <div>
        <strong>{{ $pendingCount }} transaction{{ $pendingCount > 1 ? 's' : '' }} awaiting approval.</strong>
        <a href="{{ route('transactions.index', ['approval' => 'pending']) }}" class="ms-2">Review now &rarr;</a>
    </div>
</div>
@endif

{{-- Toolbar --}}
<div class="row mb-3 align-items-center">
    <div class="col">
        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Transaction
        </a>
    </div>
    <div class="col-auto d-flex gap-2 align-items-center flex-wrap">
        <form method="GET" class="d-flex gap-2 align-items-center flex-wrap" id="filter-form">
            <select name="type" class="form-select form-select-sm" onchange="this.form.submit()" style="width:140px">
                <option value="">All Types</option>
                <option value="income"  {{ request('type') === 'income'  ? 'selected' : '' }}>Income</option>
                <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Expense</option>
            </select>
            <input type="month" name="month" class="form-control form-control-sm" style="width:150px"
                   value="{{ request('month') }}" onchange="this.form.submit()">
            @if(request('type') || request('month'))
                <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            @endif
        </form>

        {{-- Export dropdown --}}
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="ti ti-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="{{ route('transactions.export', array_filter(['type' => request('type'), 'month' => request('month'), 'format' => 'csv'])) }}">
                        <i class="ti ti-file-type-csv me-2"></i> Export CSV
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('transactions.export', array_filter(['type' => request('type'), 'month' => request('month'), 'format' => 'pdf'])) }}">
                        <i class="ti ti-file-type-pdf me-2"></i> Export PDF
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($transactions->isEmpty())
            <p class="text-muted text-center py-4">
                No transactions yet. <a href="{{ route('transactions.create') }}">Add your first one.</a>
            </p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $txn)
                        <tr class="{{ $txn->approval_status === 'pending' ? 'table-warning' : ($txn->approval_status === 'rejected' ? 'table-danger opacity-75' : '') }}">
                            <td class="text-nowrap">{{ $txn->date->format('d M Y') }}</td>
                            <td>
                                <span class="badge {{ $txn->type->badgeClass() }}">{{ $txn->type->label() }}</span>
                            </td>
                            <td>{{ $txn->category }}</td>
                            <td class="text-muted small">{{ Str::limit($txn->description, 60) }}</td>
                            <td class="text-muted small">{{ $txn->reference ?? '—' }}</td>
                            <td class="text-end fw-semibold {{ $txn->type === \App\Enums\TransactionType::Income ? 'text-success' : 'text-danger' }}">
                                {{ $txn->type === \App\Enums\TransactionType::Expense ? '−' : '' }}{{ format_inr((float)$txn->amount) }}
                            </td>
                            <td class="text-end">
                                @if($txn->approval_status === 'pending')
                                    <span class="badge bg-warning text-dark me-1">Pending</span>
                                    @if(auth()->user()->isManager())
                                    <form action="{{ route('transactions.approve', $txn) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success me-1" title="Approve">
                                            <i class="ti ti-check"></i>
                                        </button>
                                    </form>
                                    @endif
                                @elseif($txn->approval_status === 'rejected')
                                    <span class="badge bg-danger me-1">Rejected</span>
                                @endif
                                <a href="{{ route('transactions.edit', $txn) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="ti ti-pencil"></i>
                                </a>
                                <form action="{{ route('transactions.destroy', $txn) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this transaction?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
                <div class="mt-3">
                    {{ $transactions->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

@endsection
