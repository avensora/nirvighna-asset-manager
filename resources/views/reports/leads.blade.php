@extends('layouts.app', ['title' => 'Lead Conversion Funnel', 'subtitle' => 'Reports'])

@section('content')

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>All Reports
    </a>
    <h5 class="fw-semibold mb-0">Lead Conversion Funnel</h5>
</div>

{{-- Summary KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small fw-medium mb-1">Total Leads</p>
                <h4 class="fw-bold mb-0">{{ $total }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small fw-medium mb-1">Leads Won</p>
                <h4 class="fw-bold mb-0 text-success">{{ $won }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small fw-medium mb-1">Win Rate</p>
                <h4 class="fw-bold mb-0 {{ $wonRate >= 50 ? 'text-success' : ($wonRate >= 25 ? 'text-warning' : 'text-danger') }}">
                    {{ $wonRate }}%
                </h4>
                <small class="text-muted">of {{ $closed }} closed</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small fw-medium mb-1">Won Deal Value</p>
                <h4 class="fw-bold mb-0 text-success">{{ format_inr($wonValue) }}</h4>
                <small class="text-muted">of {{ format_inr($totalValue) }} total pipeline</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">

    {{-- Funnel Chart --}}
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Leads by Stage</h5>
            </div>
            <div class="card-body">
                @if($total === 0)
                    <p class="text-muted text-center py-5">No leads yet. <a href="{{ route('leads.create') }}">Add your first lead</a>.</p>
                @else
                    <div id="lead-funnel-chart"></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Stage Breakdown Table --}}
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Stage Breakdown</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Stage</th>
                                <th class="text-center">Count</th>
                                <th class="text-end">Deal Value</th>
                                <th class="text-center">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stages as $stage)
                            @php
                                $row = $stageCounts->get($stage->value);
                                $cnt = $row ? (int) $row->cnt : 0;
                                $val = $row ? (float) $row->value : 0;
                                $pct = $total > 0 ? round($cnt / $total * 100) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge {{ $stage->badgeClass() }}">{{ $stage->label() }}</span>
                                </td>
                                <td class="text-center fw-semibold">{{ $cnt }}</td>
                                <td class="text-end">{{ $val > 0 ? format_inr($val) : '—' }}</td>
                                <td class="text-center text-muted small">{{ $pct }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-semibold">Total</td>
                                <td class="text-center fw-bold">{{ $total }}</td>
                                <td class="text-end fw-semibold">{{ format_inr($totalValue) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@if($total > 0)
@push('scripts')
<script>
@php
    $chartLabels  = [];
    $chartCounts  = [];
    $chartColors  = ['#6c757d', '#0dcaf0', '#0d6efd', '#ffc107', '#0acf97', '#fa5c7c'];
    $stageValues  = ['new_lead','contacted','interested','proposal_sent','won','lost'];
    $stageLabels  = ['New Lead','Contacted','Interested','Proposal Sent','Won','Lost'];
    foreach ($stageValues as $i => $sv) {
        $row = $stageCounts->get($sv);
        $chartLabels[] = $stageLabels[$i];
        $chartCounts[] = $row ? (int) $row->cnt : 0;
    }
@endphp
new ApexCharts(document.getElementById('lead-funnel-chart'), {
    chart: { type: 'bar', height: 300, toolbar: { show: false } },
    series: [{ name: 'Leads', data: @json($chartCounts) }],
    xaxis: { categories: @json($chartLabels) },
    yaxis: { labels: { formatter: val => Math.round(val) } },
    colors: @json($chartColors),
    plotOptions: {
        bar: { columnWidth: '50%', borderRadius: 4, distributed: true }
    },
    legend: { show: false },
    dataLabels: { enabled: true },
    grid: { borderColor: '#f1f3fa' },
    responsive: [{ breakpoint: 576, options: { chart: { height: 220 } } }],
}).render();
</script>
@endpush
@endif
