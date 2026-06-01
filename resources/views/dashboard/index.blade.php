@extends('layouts.app', ['title' => 'Dashboard', 'subtitle' => 'Overview'])

@section('content')

@if($isManager)

{{-- Month Filter --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2 dash-filter-bar">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        @if(!$isCurrentMonth)
            <span class="badge bg-warning-subtle text-warning fw-medium px-3 py-2">
                <i class="ti ti-calendar-event me-1"></i>Viewing {{ $monthLabel }}
            </span>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-refresh me-1"></i>Back to Current Month
            </a>
        @endif
    </div>
    <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 ms-auto">
        <label class="form-label mb-0 text-muted small fw-semibold d-none d-sm-block">Filter by Month</label>
        <input type="month" name="month" value="{{ $selectedMonth }}"
               class="form-control form-control-sm" style="width:160px;min-width:130px;"
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

@if($isMasterAdmin)
{{-- Master Admin Extra KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-start border-4 border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted fw-medium mb-1">Total Users</p>
                        <h4 class="mb-0 fw-bold">{{ $userCount }}</h4>
                        <small class="text-muted">active accounts</small>
                    </div>
                    <div class="avatar-lg bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center">
                        <i class="ti ti-users fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-start border-4 border-info">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted fw-medium mb-1">Total Leads</p>
                        <h4 class="mb-0 fw-bold">{{ $leadsTotal }}</h4>
                        <small class="text-muted">across all stages</small>
                    </div>
                    <div class="avatar-lg bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center">
                        <i class="ti ti-target fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-start border-4 border-success">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted fw-medium mb-1">Leads Won</p>
                        <h4 class="mb-0 fw-bold text-success">{{ $leadsWon }}</h4>
                        <small class="text-muted">converted to projects</small>
                    </div>
                    <div class="avatar-lg bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center">
                        <i class="ti ti-trophy fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-start border-4 border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted fw-medium mb-1">Conversion Rate</p>
                        <h4 class="mb-0 fw-bold">{{ $conversionRate }}%</h4>
                        <small class="text-muted">of closed leads won</small>
                    </div>
                    <div class="avatar-lg bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center">
                        <i class="ti ti-percentage fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Project Pipeline + Lead Pipeline --}}
<div class="row g-3 mb-3">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Project Pipeline</h5>
                <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @php
                    $statuses = [
                        'planning'  => ['label' => 'Planning',   'color' => 'secondary'],
                        'active'    => ['label' => 'Active',     'color' => 'success'],
                        'on_hold'   => ['label' => 'On Hold',    'color' => 'warning'],
                        'completed' => ['label' => 'Completed',  'color' => 'primary'],
                        'cancelled' => ['label' => 'Cancelled',  'color' => 'danger'],
                    ];
                    $totalProjects = $projectPipelineRaw->sum();
                @endphp
                @if($totalProjects === 0)
                    <p class="text-muted text-center py-3">No projects yet. <a href="{{ route('projects.create') }}">Create one</a>.</p>
                @else
                    @foreach($statuses as $key => $meta)
                    @php $cnt = (int)($projectPipelineRaw->get($key, 0)); @endphp
                    @if($cnt > 0)
                    <div class="d-flex align-items-center mb-2">
                        <div style="width:110px" class="text-muted small fw-medium pipeline-label">{{ $meta['label'] }}</div>
                        <div class="flex-grow-1 mx-2">
                            <div class="progress" style="height:8px">
                                <div class="progress-bar bg-{{ $meta['color'] }}"
                                     style="width:{{ round($cnt / $totalProjects * 100) }}%"></div>
                            </div>
                        </div>
                        <div class="fw-semibold small" style="width:30px;text-align:right">{{ $cnt }}</div>
                    </div>
                    @endif
                    @endforeach
                    <div class="text-end mt-2">
                        <small class="text-muted">{{ $totalProjects }} total project{{ $totalProjects !== 1 ? 's' : '' }}</small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Lead Pipeline</h5>
                <a href="{{ route('leads.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @php
                    $stages = [
                        'new_lead'      => ['label' => 'New Lead',      'color' => 'secondary'],
                        'contacted'     => ['label' => 'Contacted',     'color' => 'info'],
                        'interested'    => ['label' => 'Interested',    'color' => 'primary'],
                        'proposal_sent' => ['label' => 'Proposal Sent', 'color' => 'warning'],
                        'won'           => ['label' => 'Won',           'color' => 'success'],
                        'lost'          => ['label' => 'Lost',          'color' => 'danger'],
                    ];
                    $totalLeads = $leadPipelineRaw->sum();
                @endphp
                @if($totalLeads === 0)
                    <p class="text-muted text-center py-3">No leads yet. <a href="{{ route('leads.create') }}">Add one</a>.</p>
                @else
                    @foreach($stages as $key => $meta)
                    @php $cnt = (int)($leadPipelineRaw->get($key, 0)); @endphp
                    @if($cnt > 0)
                    <div class="d-flex align-items-center mb-2">
                        <div style="width:110px" class="text-muted small fw-medium pipeline-label">{{ $meta['label'] }}</div>
                        <div class="flex-grow-1 mx-2">
                            <div class="progress" style="height:8px">
                                <div class="progress-bar bg-{{ $meta['color'] }}"
                                     style="width:{{ round($cnt / $totalLeads * 100) }}%"></div>
                            </div>
                        </div>
                        <div class="fw-semibold small" style="width:30px;text-align:right">{{ $cnt }}</div>
                    </div>
                    @endif
                    @endforeach
                    <div class="text-end mt-2">
                        <small class="text-muted">{{ $totalLeads }} total lead{{ $totalLeads !== 1 ? 's' : '' }}</small>
                    </div>
                @endif
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

{{-- ===== Team Lead Dashboard ===== --}}
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-1">
            <h5 class="fw-semibold mb-0">My Assigned Projects</h5>
            <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="ti ti-layout-kanban me-1"></i>All Projects
            </a>
        </div>
    </div>
</div>

@if($assignedProjects->isEmpty())
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="ti ti-layout-kanban fs-48 text-muted mb-3 d-block"></i>
                <p class="text-muted mb-0">No projects assigned to you yet.</p>
            </div>
        </div>
    </div>
</div>
@else
<div class="row g-3 mb-3">
    @foreach($assignedProjects as $project)
    @php
        $isOverdue = $project->deadline && $project->deadline->isPast()
            && !in_array($project->status->value, ['completed', 'cancelled']);
        $progressColor = $project->progress >= 75 ? 'success'
            : ($project->progress >= 40 ? 'primary' : 'warning');
    @endphp
    <div class="col-xl-4 col-md-6">
        <div class="card h-100 {{ $isOverdue ? 'border-danger' : '' }}">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="flex-grow-1 overflow-hidden me-2">
                        <a href="{{ route('projects.show', $project) }}" class="fw-semibold text-dark text-truncate d-block">
                            {{ $project->title }}
                        </a>
                        @if($project->client)
                            <small class="text-muted">{{ $project->client->name }}</small>
                        @else
                            <small class="text-muted fst-italic">Internal</small>
                        @endif
                    </div>
                    <span class="badge {{ $project->status->badgeClass() }} flex-shrink-0">
                        {{ $project->status->label() }}
                    </span>
                </div>

                <div class="mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Progress</small>
                        <small class="fw-semibold">{{ $project->progress }}%</small>
                    </div>
                    <div class="progress" style="height:6px">
                        <div class="progress-bar bg-{{ $progressColor }}"
                             style="width:{{ $project->progress }}%"></div>
                    </div>
                </div>

                @if($project->deadline)
                <div class="d-flex align-items-center gap-1 mt-2">
                    <i class="ti ti-calendar-due text-muted fs-14"></i>
                    <small class="{{ $isOverdue ? 'text-danger fw-semibold' : 'text-muted' }}">
                        Due {{ $project->deadline->format('d M Y') }}
                        @if($isOverdue) <span class="badge bg-danger-subtle text-danger ms-1">Overdue</span> @endif
                    </small>
                </div>
                @endif
            </div>
            <div class="card-footer bg-transparent pt-0 pb-2 px-3">
                <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-primary w-100">
                    <i class="ti ti-eye me-1"></i>View Project
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Team Lead: Personal Activity --}}
<div class="row g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">My Recent Activity</h5>
            </div>
            <div class="card-body p-0">
                @if($recentActivity->isEmpty())
                    <p class="text-muted text-center py-4 px-3">No activity recorded yet.</p>
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
                                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
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
