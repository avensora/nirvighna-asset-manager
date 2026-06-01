@extends('layouts.app', ['title' => 'Scheduled Expenses', 'subtitle' => 'Expected & Recurring Expenses'])

@section('content')

<div class="row mb-3">
    <div class="col">
        <a href="{{ route('scheduled-expenses.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> New Scheduled Expense
        </a>
    </div>
</div>

@if($expenses->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="ti ti-calendar-off fs-40 text-muted mb-3 d-block"></i>
            <p class="text-muted mb-0">No scheduled expenses yet. Add one to start tracking expected costs.</p>
        </div>
    </div>
@else
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Recurrence</th>
                        <th>Last Paid</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $expense)
                    @php
                        $isOverdue  = $expense->status === 'pending' && $expense->due_date->isPast();
                        $isDueSoon  = $expense->status === 'pending' && !$expense->due_date->isPast() && $expense->due_date->diffInDays(today()) <= 7;
                    @endphp
                    <tr class="{{ $isOverdue ? 'table-danger' : ($isDueSoon ? 'table-warning' : '') }}">
                        <td>
                            <span class="fw-medium">{{ $expense->title }}</span>
                            @if($expense->notes)
                                <br><small class="text-muted">{{ Str::limit($expense->notes, 60) }}</small>
                            @endif
                        </td>
                        <td>{{ $expense->category ?? '—' }}</td>
                        <td class="fw-semibold">{{ format_inr((float)$expense->amount) }}</td>
                        <td>
                            {{ $expense->due_date->format('d M Y') }}
                            @if($isOverdue)
                                <br><span class="badge bg-danger-subtle text-danger small">Overdue</span>
                            @elseif($isDueSoon)
                                <br><span class="badge bg-warning-subtle text-warning small">Due Soon</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary">
                                {{ $expense->recurrence->label() }}
                            </span>
                        </td>
                        <td class="text-muted small">
                            {{ $expense->last_paid_at ? $expense->last_paid_at->format('d M Y') : '—' }}
                        </td>
                        <td>
                            @if($expense->status === 'paid')
                                <span class="badge bg-success-subtle text-success">Paid</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">Pending</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                @if($expense->status === 'pending')
                                <form action="{{ route('scheduled-expenses.pay', $expense) }}" method="POST" class="form-pay">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Mark as Paid">
                                        <i class="ti ti-check"></i> Pay
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('scheduled-expenses.edit', $expense) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <form action="{{ route('scheduled-expenses.destroy', $expense) }}" method="POST" class="form-delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
document.querySelectorAll('.form-pay').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (confirm('Mark as paid? This will create an expense transaction.')) {
            this.submit();
        }
    });
});

document.querySelectorAll('.form-delete').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (confirm('Delete this scheduled expense?')) {
            this.submit();
        }
    });
});
</script>
@endpush
