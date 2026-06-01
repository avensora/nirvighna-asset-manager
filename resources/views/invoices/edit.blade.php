@extends('layouts.app', ['title' => 'Edit ' . $invoice->invoice_number, 'subtitle' => 'Invoices'])

@section('content')

<form action="{{ route('invoices.update', $invoice) }}" method="POST" id="invoice-form" novalidate>
@csrf
@method('PUT')

<div class="row g-3">

    {{-- Left: main form --}}
    <div class="col-xl-8">

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Client <span class="text-danger">*</span></label>
                        <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                            <option value="">— Select client —</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}"
                                    {{ old('client_id', $invoice->client_id) == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}{{ $client->company ? ' ('.$client->company.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                        <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror"
                               value="{{ old('issue_date', $invoice->issue_date->format('Y-m-d')) }}" required>
                        @error('issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                               value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}">
                        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Line Items</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width:200px">Description</th>
                                <th style="width:100px">Qty</th>
                                <th style="width:130px">Unit Price (₹)</th>
                                <th style="width:130px">Amount (₹)</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody">
                            @php
                                $existingItems = old('items')
                                    ? collect(old('items'))->values()->all()
                                    : $invoice->items->map(fn($i) => [
                                        'description' => $i->description,
                                        'quantity'    => $i->quantity,
                                        'unit_price'  => $i->unit_price,
                                        'amount'      => $i->amount,
                                      ])->all();
                            @endphp
                            @foreach($existingItems as $i => $item)
                            <tr class="item-row">
                                <td><input type="text" name="items[{{ $i }}][description]"
                                           class="form-control form-control-sm" value="{{ $item['description'] }}" required></td>
                                <td><input type="number" name="items[{{ $i }}][quantity]" data-field="quantity"
                                           class="form-control form-control-sm" step="0.01" min="0.01"
                                           value="{{ $item['quantity'] }}" required
                                           oninput="updateRowAmount(this.closest('tr'))"></td>
                                <td><input type="number" name="items[{{ $i }}][unit_price]" data-field="unit_price"
                                           class="form-control form-control-sm" step="0.01" min="0"
                                           value="{{ $item['unit_price'] }}" required
                                           oninput="updateRowAmount(this.closest('tr'))"></td>
                                <td><input type="number" name="items[{{ $i }}][amount]" data-field="amount"
                                           class="form-control form-control-sm bg-light"
                                           value="{{ $item['amount'] }}" readonly tabindex="-1"></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()">
                    <i class="ti ti-plus me-1"></i> Add Item
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-control">{{ old('notes', $invoice->notes) }}</textarea>
            </div>
        </div>

    </div>

    {{-- Right: totals + submit --}}
    <div class="col-xl-4">

        <div class="card mb-3">
            <div class="card-header"><h6 class="card-title mb-0">Summary</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">GST Rate (%)</label>
                    <input type="number" name="tax_rate" id="tax_rate" class="form-control"
                           step="0.01" min="0" max="100"
                           value="{{ old('tax_rate', $invoice->tax_rate) }}"
                           oninput="recalculate()">
                </div>
                <div class="mb-3">
                    <label class="form-label">Discount</label>
                    <div class="input-group">
                        <input type="number" id="discount_input" class="form-control"
                               step="0.01" min="0"
                               value="{{ old('discount_amount', $invoice->discount_amount) }}"
                               oninput="recalculate()">
                        <button type="button" class="btn btn-outline-secondary" id="discount-type-btn"
                                onclick="toggleDiscountType()" title="Switch between fixed amount and percentage">₹</button>
                    </div>
                    <input type="hidden" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', $invoice->discount_amount) }}">
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Subtotal</span>
                    <span id="summary-subtotal" class="fw-medium">₹0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-1" id="discount-row" style="display:none!important">
                    <span class="text-muted" id="summary-discount-label">Discount</span>
                    <span id="summary-discount" class="text-danger">−₹0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-1" id="tax-row" style="display:none!important">
                    <span class="text-muted">GST</span>
                    <span id="summary-tax">₹0.00</span>
                </div>
                <div class="d-flex justify-content-between mt-2 pt-2 border-top">
                    <span class="fw-bold fs-5">Total</span>
                    <span id="summary-total" class="fw-bold fs-5 text-primary">₹0.00</span>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i> Update Invoice
            </button>
            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>

    </div>
</div>

</form>
@endsection

@push('scripts')
<script>
let rowIndex = {{ count($existingItems) }};
let discountType = 'fixed';

function formatINR(n) {
    return '₹' + new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n || 0);
}

function toggleDiscountType() {
    discountType = discountType === 'fixed' ? 'percent' : 'fixed';
    const btn = document.getElementById('discount-type-btn');
    btn.textContent = discountType === 'fixed' ? '₹' : '%';
    document.getElementById('discount_input').placeholder = discountType === 'percent' ? 'e.g. 10' : '';
    recalculate();
}

function updateRowAmount(row) {
    const qty   = parseFloat(row.querySelector('[data-field=quantity]').value)   || 0;
    const price = parseFloat(row.querySelector('[data-field=unit_price]').value) || 0;
    row.querySelector('[data-field=amount]').value = (qty * price).toFixed(2);
    recalculate();
}

function recalculate() {
    let subtotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        subtotal += parseFloat(row.querySelector('[data-field=amount]').value) || 0;
    });

    const taxRate    = parseFloat(document.getElementById('tax_rate').value)       || 0;
    const discountIn = parseFloat(document.getElementById('discount_input').value) || 0;
    const discount   = discountType === 'percent' ? subtotal * discountIn / 100 : discountIn;
    const tax        = subtotal * taxRate / 100;
    const total      = Math.max(0, subtotal - discount + tax);

    document.getElementById('discount_amount').value = discount.toFixed(2);

    document.getElementById('summary-subtotal').textContent = formatINR(subtotal);
    document.getElementById('summary-tax').textContent      = formatINR(tax);
    document.getElementById('summary-discount').textContent = '−' + formatINR(discount);
    document.getElementById('summary-total').textContent    = formatINR(total);

    const discountLabel = discountType === 'percent' && discountIn > 0
        ? `Discount (${discountIn}%)`
        : 'Discount';
    document.getElementById('summary-discount-label').textContent = discountLabel;

    document.getElementById('tax-row').style.display      = taxRate  > 0 ? '' : 'none';
    document.getElementById('discount-row').style.display = discount > 0 ? '' : 'none';
}

function addItem() {
    const tbody = document.getElementById('items-tbody');
    const idx   = rowIndex++;
    const tr    = document.createElement('tr');
    tr.className = 'item-row';
    tr.innerHTML = `
        <td><input type="text" name="items[${idx}][description]" class="form-control form-control-sm" required></td>
        <td><input type="number" name="items[${idx}][quantity]" data-field="quantity" class="form-control form-control-sm" step="0.01" min="0.01" value="1" required oninput="updateRowAmount(this.closest('tr'))"></td>
        <td><input type="number" name="items[${idx}][unit_price]" data-field="unit_price" class="form-control form-control-sm" step="0.01" min="0" value="0" required oninput="updateRowAmount(this.closest('tr'))"></td>
        <td><input type="number" name="items[${idx}][amount]" data-field="amount" class="form-control form-control-sm bg-light" value="0.00" readonly tabindex="-1"></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)"><i class="ti ti-trash"></i></button></td>
    `;
    tbody.appendChild(tr);
    tr.querySelector('input[type=text]').focus();
    recalculate();
}

function removeItem(btn) {
    if (document.querySelectorAll('.item-row').length <= 1) return;
    btn.closest('tr').remove();
    recalculate();
}

recalculate();
</script>
@endpush
