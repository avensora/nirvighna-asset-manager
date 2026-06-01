@extends('layouts.app', ['title' => $invoice->invoice_number, 'subtitle' => 'Invoices'])

@section('content')

<div class="row g-3">

    {{-- Main invoice card --}}
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">{{ $invoice->invoice_number }}</h5>
                    <small class="text-muted">{{ $invoice->client->name }}</small>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    @if($invoice->isOverdue())
                        <span class="badge bg-danger fs-6">Overdue</span>
                    @else
                        <span class="badge {{ $invoice->status->badgeClass() }} fs-6">{{ $invoice->status->label() }}</span>
                    @endif
                    <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                        <i class="ti ti-file-download me-1"></i> PDF
                    </a>
                    @if($invoice->status !== \App\Enums\InvoiceStatus::Paid)
                    <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-primary">
                        <i class="ti ti-pencil me-1"></i> Edit
                    </a>
                    @endif
                </div>
            </div>

            <div class="card-body">

                {{-- Dates --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <p class="text-muted fw-medium mb-1 small">Issue Date</p>
                        <p class="mb-0">{{ $invoice->issue_date->format('d M Y') }}</p>
                    </div>
                    @if($invoice->due_date)
                    <div class="col-md-4">
                        <p class="text-muted fw-medium mb-1 small">Due Date</p>
                        <p class="mb-0 {{ $invoice->isOverdue() ? 'text-danger fw-semibold' : '' }}">
                            {{ $invoice->due_date->format('d M Y') }}
                        </p>
                    </div>
                    @endif
                    <div class="col-md-4">
                        <p class="text-muted fw-medium mb-1 small">Client</p>
                        <p class="mb-0">
                            <a href="{{ route('clients.show', $invoice->client) }}">{{ $invoice->client->name }}</a>
                            @if($invoice->client->company)
                                <br><small class="text-muted">{{ $invoice->client->company }}</small>
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Line items --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $i => $item)
                            <tr>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td>{{ $item->description }}</td>
                                <td class="text-end">{{ number_format((float)$item->quantity, 2) }}</td>
                                <td class="text-end">{{ format_inr((float)$item->unit_price) }}</td>
                                <td class="text-end fw-medium">{{ format_inr((float)$item->amount) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end text-muted">Subtotal</td>
                                <td class="text-end">{{ format_inr((float)$invoice->subtotal) }}</td>
                            </tr>
                            @if((float)$invoice->discount_amount > 0)
                            <tr>
                                <td colspan="4" class="text-end text-muted">Discount</td>
                                <td class="text-end text-danger">− {{ format_inr((float)$invoice->discount_amount) }}</td>
                            </tr>
                            @endif
                            @if((float)$invoice->tax_rate > 0)
                            <tr>
                                <td colspan="4" class="text-end text-muted">GST ({{ number_format((float)$invoice->tax_rate, 2) }}%)</td>
                                <td class="text-end">{{ format_inr((float)$invoice->tax_amount) }}</td>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <td colspan="4" class="text-end fw-bold">Total</td>
                                <td class="text-end fw-bold fs-5">{{ format_inr((float)$invoice->total) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($invoice->notes)
                <div class="mt-3 p-3 bg-light rounded">
                    <p class="text-muted fw-medium mb-1 small">Notes</p>
                    <p class="mb-0">{{ $invoice->notes }}</p>
                </div>
                @endif

            </div>

            <div class="card-footer text-muted small">
                Created {{ $invoice->created_at->format('d M Y') }}
                @if($invoice->creator) by {{ $invoice->creator->name }} @endif
            </div>
        </div>
    </div>

    {{-- Actions panel --}}
    <div class="col-xl-4">

        {{-- Status actions --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="card-title mb-0">Actions</h6></div>
            <div class="card-body d-grid gap-2">

                @if($invoice->client->email && $invoice->status !== \App\Enums\InvoiceStatus::Paid)
                <form action="{{ route('invoices.send', $invoice) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-info w-100">
                        <i class="ti ti-mail-forward me-1"></i>
                        @if($invoice->status === \App\Enums\InvoiceStatus::Sent)
                            Resend to {{ $invoice->client->email }}
                        @else
                            Send to {{ $invoice->client->email }}
                        @endif
                    </button>
                </form>
                @elseif(!$invoice->client->email)
                <p class="text-muted small mb-0">
                    <i class="ti ti-mail-off me-1"></i> No email on client record — add one to enable sending.
                </p>
                @endif

                @if($invoice->status !== \App\Enums\InvoiceStatus::Paid)
                <form action="{{ route('invoices.mark-paid', $invoice) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                        <i class="ti ti-circle-check me-1"></i> Mark as Paid
                    </button>
                </form>
                @else
                <form action="{{ route('invoices.mark-unpaid', $invoice) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning w-100">
                        <i class="ti ti-circle-x me-1"></i> Mark as Unpaid
                    </button>
                </form>
                @endif

            </div>
        </div>

        {{-- Danger zone --}}
        @if($invoice->status !== \App\Enums\InvoiceStatus::Paid)
        <div class="card border-danger">
            <div class="card-body">
                <form action="{{ route('invoices.destroy', $invoice) }}" method="POST"
                      onsubmit="return confirm('Delete {{ addslashes($invoice->invoice_number) }}? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="ti ti-trash me-1"></i> Delete Invoice
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>

</div>

<div class="mt-2">
    <a href="{{ route('invoices.index') }}" class="text-muted small">
        <i class="ti ti-arrow-left me-1"></i> Back to Invoices
    </a>
</div>

@endsection
