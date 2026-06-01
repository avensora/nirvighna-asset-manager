@extends('layouts.app', ['title' => 'New Invoice', 'subtitle' => 'Invoices'])

@section('content')

<form action="{{ route('invoices.store') }}" method="POST" id="invoice-form" novalidate>
@csrf

<div class="row g-3">

    {{-- Left: main form --}}
    <div class="col-xl-8">

        {{-- Header fields --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Client <span class="text-danger">*</span></label>
                        <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                            <option value="">— Select client —</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}{{ $client->company ? ' ('.$client->company.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                        <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror"
                               value="{{ old('issue_date', now()->format('Y-m-d')) }}" required>
                        @error('issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                               value="{{ old('due_date') }}">
                        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Line items --}}
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
                            @php $oldItems = old('items', [['description'=>'','quantity'=>1,'unit_price'=>0]]); @endphp
                            @foreach($oldItems as $i => $item)
                            <tr class="item-row">
                                <td><input type="text" name="items[{{ $i }}][description]"
                                           class="form-control form-control-sm @error('items.'.$i.'.description') is-invalid @enderror"
                                           value="{{ $item['description'] ?? '' }}" required></td>
                                <td><input type="number" name="items[{{ $i }}][quantity]" data-field="quantity"
                                           class="form-control form-control-sm" step="0.01" min="0.01"
                                           value="{{ $item['quantity'] ?? 1 }}" required
                                           oninput="updateRowAmount(this.closest('tr'))"></td>
                                <td><input type="number" name="items[{{ $i }}][unit_price]" data-field="unit_price"
                                           class="form-control form-control-sm" step="0.01" min="0"
                                           value="{{ $item['unit_price'] ?? 0 }}" required
                                           oninput="updateRowAmount(this.closest('tr'))"></td>
                                <td><input type="number" name="items[{{ $i }}][amount]" data-field="amount"
                                           class="form-control form-control-sm bg-light"
                                           value="{{ isset($item['quantity'],$item['unit_price']) ? round($item['quantity']*$item['unit_price'],2) : 0 }}"
                                           readonly tabindex="-1"></td>
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

        {{-- Notes --}}
        <div class="card">
            <div class="card-body">
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-control"
                          placeholder="Payment terms, bank details, or any other notes">{{ old('notes') }}</textarea>
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
                           step="0.01" min="0" max="100" value="{{ old('tax_rate', 0) }}"
                           oninput="recalculate()">
                </div>
                <div class="mb-3">
                    <label class="form-label">Discount</label>
                    <div class="input-group">
                        <input type="number" id="discount_input" class="form-control"
                               step="0.01" min="0" value="{{ old('discount_amount', 0) }}"
                               oninput="recalculate()">
                        <button type="button" class="btn btn-outline-secondary" id="discount-type-btn"
                                onclick="toggleDiscountType()" title="Switch between fixed amount and percentage">₹</button>
                    </div>
                    <input type="hidden" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', 0) }}">
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
                <i class="ti ti-device-floppy me-1"></i> Save Invoice
            </button>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>

    </div>
</div>

</form>
@endsection

@push('scripts')
<script>
let rowIndex = {{ count(old('items', [[]])) }};
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

    const taxRate    = parseFloat(document.getElementById('tax_rate').value)      || 0;
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

// Init
recalculate();
</script>
@endpush
