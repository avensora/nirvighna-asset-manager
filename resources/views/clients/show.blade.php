@extends('layouts.app', ['title' => $client->name, 'subtitle' => 'Clients'])

@section('content')

<div class="row">
    <div class="col-xl-8">

        {{-- Contact Info --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">{{ $client->name }}</h5>
                    @if($client->company)
                        <small class="text-muted">{{ $client->company }}</small>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-primary">
                        <i class="ti ti-pencil me-1"></i> Edit
                    </a>
                    <form action="{{ route('clients.destroy', $client) }}" method="POST"
                          onsubmit="return confirm('Delete {{ addslashes($client->name) }}? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="ti ti-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    @if($client->email)
                    <div class="col-md-6">
                        <p class="text-muted fw-medium mb-1 small">Email</p>
                        <p class="mb-0"><a href="mailto:{{ $client->email }}">{{ $client->email }}</a></p>
                    </div>
                    @endif

                    @if($client->phone)
                    <div class="col-md-6">
                        <p class="text-muted fw-medium mb-1 small">Phone</p>
                        <p class="mb-0">{{ $client->phone }}</p>
                    </div>
                    @endif

                    @if($client->gstin)
                    <div class="col-md-6">
                        <p class="text-muted fw-medium mb-1 small">GSTIN</p>
                        <p class="mb-0 font-monospace">{{ $client->gstin }}</p>
                    </div>
                    @endif

                    @if($client->address || $client->city || $client->state || $client->pincode)
                    <div class="col-md-6">
                        <p class="text-muted fw-medium mb-1 small">Address</p>
                        <p class="mb-0">
                            @if($client->address){{ $client->address }}<br>@endif
                            @if($client->city || $client->state)
                                {{ implode(', ', array_filter([$client->city, $client->state])) }}
                                @if($client->pincode) — {{ $client->pincode }}@endif
                            @endif
                        </p>
                    </div>
                    @endif

                </div>

                @if($client->notes)
                <hr class="my-3">
                <p class="text-muted fw-medium mb-1 small">Notes</p>
                <p class="mb-0">{{ $client->notes }}</p>
                @endif

            </div>
            <div class="card-footer text-muted small">
                Added {{ $client->created_at->format('d M Y') }}
                @if($client->creator) by {{ $client->creator->name }} @endif
                @if($client->updated_at->ne($client->created_at))
                    · Updated {{ $client->updated_at->format('d M Y') }}
                @endif
            </div>
        </div>

    </div>

    <div class="col-xl-4">

        {{-- Invoices --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">Invoices</h6>
                <a href="{{ route('invoices.create', ['client_id' => $client->id]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-plus me-1"></i> New
                </a>
            </div>
            @php $invoices = $client->invoices()->latest()->get(); @endphp
            @if($invoices->isEmpty())
                <div class="card-body">
                    <p class="text-muted text-center py-3 mb-0">No invoices yet.</p>
                </div>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($invoices as $invoice)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('invoices.show', $invoice) }}" class="fw-medium text-body">
                                {{ $invoice->invoice_number }}
                            </a>
                            <br>
                            <small class="text-muted">{{ $invoice->issue_date->format('d M Y') }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-medium">{{ format_inr((float)$invoice->total) }}</div>
                            @if($invoice->isOverdue())
                                <span class="badge bg-danger">Overdue</span>
                            @else
                                <span class="badge {{ $invoice->status->badgeClass() }}">{{ $invoice->status->label() }}</span>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>
</div>

<div class="mt-2">
    <a href="{{ route('clients.index') }}" class="text-muted small">
        <i class="ti ti-arrow-left me-1"></i> Back to Clients
    </a>
</div>

@endsection
