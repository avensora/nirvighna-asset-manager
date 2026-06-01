@extends('layouts.app', ['title' => 'Revenue by Client', 'subtitle' => 'Reports'])

@section('content')

{{-- Header + Year Filter --}}
<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>All Reports
    </a>
    <h5 class="fw-semibold mb-0 me-auto">Revenue by Client — {{ $year }}</h5>
    <form method="GET" action="{{ route('reports.revenue') }}" class="d-flex align-items-center gap-2">
        <label class="form-label mb-0 text-muted small fw-semibold d-none d-sm-block">Year</label>
        <select name="year" class="form-select form-select-sm" style="width:100px" onchange="this.form.submit()">
            @foreach($availableYears as $yr)
                <option value="{{ $yr }}" {{ $yr == $year ? 'selected' : '' }}>{{ $yr }}</option>
            @endforeach
        </select>
    </form>
</div>

{{-- Summary KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small fw-medium mb-1">Total Revenue ({{ $year }})</p>
                <h4 class="fw-bold mb-0 text-success">{{ format_inr($grandTotal) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small fw-medium mb-1">Paid Invoices</p>
                <h4 class="fw-bold mb-0">{{ $invoiceCount }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small fw-medium mb-1">Avg per Invoice</p>
                <h4 class="fw-bold mb-0">{{ $invoiceCount > 0 ? format_inr($grandTotal / $invoiceCount) : '₹0' }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">

    {{-- Monthly Bar Chart --}}
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Monthly Revenue — {{ $year }}</h5>
            </div>
            <div class="card-body">
                <div id="monthly-revenue-chart"></div>
            </div>
        </div>
    </div>

    {{-- Top Clients Table --}}
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Revenue by Client</h5>
            </div>
            <div class="card-body p-0">
                @if($byClient->isEmpty())
                    <p class="text-muted text-center py-4">No paid invoices in {{ $year }}.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Client</th>
                                    <th>Invoices</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byClient as $i => $row)
                                <tr>
                                    <td class="text-muted small">{{ $i + 1 }}</td>
                                    <td class="fw-medium">{{ $row['client_name'] }}</td>
                                    <td>{{ $row['count'] }}</td>
                                    <td class="text-end fw-semibold text-success">{{ format_inr($row['total']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="2" class="fw-semibold">Total</td>
                                    <td>{{ $invoiceCount }}</td>
                                    <td class="text-end fw-bold text-success">{{ format_inr($grandTotal) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
new ApexCharts(document.getElementById('monthly-revenue-chart'), {
    chart: { type: 'bar', height: 300, toolbar: { show: false } },
    series: [{ name: 'Revenue', data: @json($monthly->pluck('total')) }],
    xaxis: { categories: @json($monthly->pluck('month')) },
    yaxis: {
        labels: { formatter: val => '₹' + new Intl.NumberFormat('en-IN').format(val) }
    },
    colors: ['#0acf97'],
    plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
    dataLabels: { enabled: false },
    tooltip: {
        y: { formatter: val => '₹' + new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2 }).format(val) }
    },
    grid: { borderColor: '#f1f3fa' },
    responsive: [{ breakpoint: 576, options: { chart: { height: 220 } } }],
}).render();
</script>
@endpush
