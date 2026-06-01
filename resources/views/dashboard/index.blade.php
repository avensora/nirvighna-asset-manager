@extends('layouts.app', ['title' => 'Dashboard', 'subtitle' => 'Overview'])

@section('content')

@if($isManager)

{{-- Month Filter --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center gap-2">
        @if(!$isCurrentMonth)
            <span class="badge bg-warning-subtle text-warning fw-medium px-3 py-2">
                <i class="ti ti-calendar-event me-1"></i>Viewing {{ $monthLabel }}
            </span>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-refresh me-1"></i>Back to Current Month
            </a>
        @endif
    </div>
    <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-2">
        <label class="form-label mb-0 text-muted small fw-semibold">Filter by Month</label>
        <input type="month" name="month" value="{{ $selectedMonth }}"
               class="form-control form-control-sm" style="width:160px;"
               onchange="this.form.submit()">
    </form>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted fw-medium mb-1">Revenue ({{ $monthLabel }})</p>
                        <h4 class="mb-0 fw-bold">{{ format_inr((float)$revenueThisMonth) }}</h4>
                    </div>
                    <div class="avatar-lg bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center">
                        <i class="ti ti-trending-up fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted fw-medium mb-1">Expenses ({{ $monthLabel }})</p>
                        <h4 class="mb-0 fw-bold">{{ format_inr((float)$expensesThisMonth) }}</h4>
                    </div>
                    <div class="avatar-lg bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center">
                        <i class="ti ti-trending-down fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted fw-medium mb-1">Net Profit ({{ $monthLabel }})</p>
                        <h4 class="mb-0 fw-bold {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $netProfit < 0 ? '−' : '' }}{{ format_inr(abs($netProfit)) }}
                        </h4>
                    </div>
                    <div class="avatar-lg bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center">
                        <i class="ti ti-report-money fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted fw-medium mb-1">Outstanding Invoices</p>
                        <h4 class="mb-0 fw-bold">{{ format_inr((float)$outstandingTotal) }}</h4>
                        <small class="text-muted">{{ $outstandingCount }} invoice{{ $outstandingCount !== 1 ? 's' : '' }} unpaid</small>
                    </div>
                    <div class="avatar-lg bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center">
                        <i class="ti ti-file-invoice fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($upcomingExpenses->isNotEmpty())
{{-- Upcoming Scheduled Expenses --}}
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Upcoming Scheduled Expenses</h5>
                <a href="{{ route('scheduled-expenses.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Due</th>
                                <th>Recurrence</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingExpenses as $exp)
                            @php $overdue = $exp->due_date->isPast(); @endphp
                            <tr class="{{ $overdue ? 'table-danger' : '' }}">
                                <td class="fw-medium">{{ $exp->title }}</td>
                                <td class="text-muted small">{{ $exp->category ?? '—' }}</td>
                                <td class="fw-semibold">{{ format_inr((float)$exp->amount) }}</td>
                                <td>
                                    {{ $exp->due_date->format('d M Y') }}
                                    @if($overdue)
                                        <span class="badge bg-danger-subtle text-danger ms-1">Overdue</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-secondary-subtle text-secondary">{{ $exp->recurrence->label() }}</span></td>
                                <td class="text-end">
                                    <form action="{{ route('scheduled-expenses.pay', $exp) }}" method="POST" class="form-pay-dash d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="ti ti-check me-1"></i>Pay
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Chart + Activity --}}
<div class="row g-3">

    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Income vs Expenses</h5>
                <small class="text-muted">6 months ending {{ $monthLabel }}</small>
            </div>
            <div class="card-body">
                <div id="income-expense-chart"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Activity</h5>
                <a href="{{ route('activity.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @if($recentActivity->isEmpty())
                    <p class="text-muted text-center py-4 px-3">No activity yet.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($recentActivity as $log)
                        <li class="list-group-item px-3 py-2">
                            <div class="d-flex align-items-start gap-2">
                                <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1" style="width:32px;height:32px">
                                    <i class="ti ti-user fs-14"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="mb-0 small fw-medium text-truncate">{{ $log->description }}</p>
                                    <small class="text-muted">
                                        {{ $log->causer?->name ?? 'System' }}
                                        &middot; {{ $log->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

</div>

@else

{{-- Team Member Dashboard --}}
<div class="row g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Activity</h5>
            </div>
            <div class="card-body p-0">
                @if($recentActivity->isEmpty())
                    <p class="text-muted text-center py-4 px-3">No activity yet.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($recentActivity as $log)
                        <li class="list-group-item px-3 py-2">
                            <div class="d-flex align-items-start gap-2">
                                <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1" style="width:32px;height:32px">
                                    <i class="ti ti-user fs-14"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="mb-0 small fw-medium text-truncate">{{ $log->description }}</p>
                                    <small class="text-muted">
                                        {{ $log->causer?->name ?? 'System' }}
                                        &middot; {{ $log->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

@endif

@endsection

@if($isManager)
@push('scripts')
<script>
const chartOptions = {
    chart: {
        type: 'bar',
        height: 300,
        toolbar: { show: false },
    },
    series: [
        { name: 'Income',   data: @json($chartIncome) },
        { name: 'Expenses', data: @json($chartExpenses) },
    ],
    xaxis: {
        categories: @json($chartMonths),
    },
    yaxis: {
        labels: {
            formatter: val => '₹' + new Intl.NumberFormat('en-IN').format(val),
        },
    },
    colors: ['#0acf97', '#fa5c7c'],
    plotOptions: {
        bar: { columnWidth: '50%', borderRadius: 4 },
    },
    dataLabels: { enabled: false },
    legend: { position: 'top' },
    tooltip: {
        y: {
            formatter: val => '₹' + new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2 }).format(val),
        },
    },
    grid: { borderColor: '#f1f3fa' },
};

new ApexCharts(document.getElementById('income-expense-chart'), chartOptions).render();

setTimeout(() => location.reload(), 5 * 60 * 1000);

document.querySelectorAll('.form-pay-dash').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (confirm('Mark as paid? This will create an expense transaction.')) {
            this.submit();
        }
    });
});
</script>
@endpush
@endif
