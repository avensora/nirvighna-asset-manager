@extends('layouts.app', ['title' => 'Reports', 'subtitle' => 'Business Intelligence'])

@section('content')

<div class="row g-3 mb-4">
    <div class="col-12">
        <h5 class="fw-semibold mb-0">Available Reports</h5>
        <p class="text-muted small mb-0">Click a report to view detailed insights.</p>
    </div>
</div>

<div class="row g-3">

    <div class="col-xl-3 col-md-6">
        <a href="{{ route('reports.revenue') }}" class="text-decoration-none">
            <div class="card h-100 card-hover">
                <div class="card-body d-flex flex-column">
                    <div class="avatar-lg bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center mb-3">
                        <i class="ti ti-chart-bar fs-28"></i>
                    </div>
                    <h5 class="fw-semibold mb-1">Revenue by Client</h5>
                    <p class="text-muted small mb-3">See which clients generate the most revenue. Filterable by year.</p>
                    <div class="mt-auto">
                        <span class="btn btn-sm btn-success w-100">
                            <i class="ti ti-arrow-right me-1"></i>View Report
                        </span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-md-6">
        <a href="{{ route('reports.leads') }}" class="text-decoration-none">
            <div class="card h-100 card-hover">
                <div class="card-body d-flex flex-column">
                    <div class="avatar-lg bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center mb-3">
                        <i class="ti ti-funnel fs-28"></i>
                    </div>
                    <h5 class="fw-semibold mb-1">Lead Conversion Funnel</h5>
                    <p class="text-muted small mb-3">Track lead counts per stage, win rate, and deal value in pipeline.</p>
                    <div class="mt-auto">
                        <span class="btn btn-sm btn-primary w-100">
                            <i class="ti ti-arrow-right me-1"></i>View Report
                        </span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-md-6">
        <a href="{{ route('reports.projects') }}" class="text-decoration-none">
            <div class="card h-100 card-hover">
                <div class="card-body d-flex flex-column">
                    <div class="avatar-lg bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center mb-3">
                        <i class="ti ti-layout-kanban fs-28"></i>
                    </div>
                    <h5 class="fw-semibold mb-1">Project Health</h5>
                    <p class="text-muted small mb-3">Progress bars, overdue deadlines, and budget status per project.</p>
                    <div class="mt-auto">
                        <span class="btn btn-sm btn-warning w-100">
                            <i class="ti ti-arrow-right me-1"></i>View Report
                        </span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-md-6">
        <a href="{{ route('reports.invoices') }}" class="text-decoration-none">
            <div class="card h-100 card-hover">
                <div class="card-body d-flex flex-column">
                    <div class="avatar-lg bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center mb-3">
                        <i class="ti ti-file-invoice fs-28"></i>
                    </div>
                    <h5 class="fw-semibold mb-1">Invoice Aging</h5>
                    <p class="text-muted small mb-3">Unpaid invoices grouped by how long they've been overdue.</p>
                    <div class="mt-auto">
                        <span class="btn btn-sm btn-danger w-100">
                            <i class="ti ti-arrow-right me-1"></i>View Report
                        </span>
                    </div>
                </div>
            </div>
        </a>
    </div>

</div>

@endsection

@push('styles')
<style>
.card-hover { transition: transform .15s, box-shadow .15s; }
.card-hover:hover { transform: translateY(-3px); box-shadow: 0 4px 20px rgba(0,0,0,.1); }
</style>
@endpush
