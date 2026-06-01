@extends('layouts.app', ['title' => 'Invoices', 'subtitle' => 'Business'])

@section('content')

<div class="row mb-3 align-items-center">
    <div class="col">
        <a href="{{ route('invoices.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> New Invoice
        </a>
    </div>
    <div class="col-auto">
        <form method="GET" class="d-flex gap-2 align-items-center">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width:140px">
                <option value="">All Statuses</option>
                <option value="draft"  {{ request('status') === 'draft'  ? 'selected' : '' }}>Draft</option>
                <option value="sent"   {{ request('status') === 'sent'   ? 'selected' : '' }}>Sent</option>
                <option value="paid"   {{ request('status') === 'paid'   ? 'selected' : '' }}>Paid</option>
            </select>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($invoices->isEmpty())
            <p class="text-muted text-center py-4">No invoices yet. <a href="{{ route('invoices.create') }}">Create your first invoice.</a></p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Client</th>
                            <th>Status</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                        <tr>
                            <td>
                                <a href="{{ route('invoices.show', $invoice) }}" class="fw-semibold text-body">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td>{{ $invoice->client->name }}</td>
                            <td>
                                @if($invoice->isOverdue())
                                    <span class="badge bg-danger">Overdue</span>
                                @else
                                    <span class="badge {{ $invoice->status->badgeClass() }}">{{ $invoice->status->label() }}</span>
                                @endif
                            </td>
                            <td>{{ $invoice->issue_date->format('d M Y') }}</td>
                            <td>{{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>
                            <td class="text-end fw-semibold">{{ format_inr($invoice->total) }}</td>
                            <td class="text-end">
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="ti ti-eye"></i>
                                </a>
                                @if($invoice->status !== \App\Enums\InvoiceStatus::Paid)
                                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="ti ti-pencil"></i>
                                </a>
                                @endif
                                <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline-info me-1" target="_blank">
                                    <i class="ti ti-file-download"></i>
                                </a>
                                @if($invoice->status !== \App\Enums\InvoiceStatus::Paid)
                                <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete {{ $invoice->invoice_number }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="mt-3">
                    {{ $invoices->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

@endsection
