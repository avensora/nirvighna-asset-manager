@extends('layouts.app', ['title' => 'Month Closing', 'subtitle' => 'Finances'])

@section('content')

<div class="row g-4">

    {{-- Close a Month --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0">Close a Month</h6></div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Closing a month locks it for reference. Transactions in closed periods cannot be edited or deleted.
                    You can only close past months.
                </p>
                <form action="{{ route('month-closings.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-medium">Month <span class="text-danger">*</span></label>
                        <input type="month" name="month_input" class="form-control"
                               max="{{ now()->subMonth()->format('Y-m') }}"
                               value="{{ now()->subMonth()->format('Y-m') }}"
                               id="monthInput" required>
                    </div>
                    <input type="hidden" name="year"  id="yearField">
                    <input type="hidden" name="month" id="monthField">
                    <button type="submit" class="btn btn-warning w-100"
                            onclick="splitMonth()">
                        <i class="ti ti-lock me-1"></i> Close Month
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Closed months list --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0">Closed Months</h6></div>
            <div class="card-body p-0">
                @if($closings->isEmpty())
                    <p class="text-muted text-center py-4">No months closed yet.</p>
                @else
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th class="text-end">Income</th>
                                <th class="text-end">Expenses</th>
                                <th class="text-end">Profit/Loss</th>
                                <th>Closed By</th>
                                <th>Closed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($closings as $closing)
                            @php $pl = (float)$closing->total_income - (float)$closing->total_expenses; @endphp
                            <tr>
                                <td class="fw-semibold">{{ $closing->monthLabel() }}</td>
                                <td class="text-end text-success">{{ format_inr((float)$closing->total_income) }}</td>
                                <td class="text-end text-danger">{{ format_inr((float)$closing->total_expenses) }}</td>
                                <td class="text-end fw-bold {{ $pl >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $pl >= 0 ? '' : '−' }}{{ format_inr(abs($pl)) }}
                                </td>
                                <td class="small">{{ $closing->closer?->name ?? '—' }}</td>
                                <td class="small text-muted">{{ $closing->closed_at->format('d M Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
function splitMonth() {
    const val = document.getElementById('monthInput').value; // "YYYY-MM"
    if (val) {
        const parts = val.split('-');
        document.getElementById('yearField').value  = parts[0];
        document.getElementById('monthField').value = parseInt(parts[1], 10);
    }
}
</script>
@endpush
