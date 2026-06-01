@extends('layouts.app', ['title' => 'Edit Transaction', 'subtitle' => 'Finances'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-7 col-lg-9">

        <form action="{{ route('transactions.update', $transaction) }}" method="POST" novalidate>
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header"><h6 class="card-title mb-0">Transaction Type</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <input type="radio" class="btn-check" name="type" id="type-income" value="income"
                               {{ old('type', $transaction->type->value) === 'income' ? 'checked' : '' }}
                               required onchange="onTypeChange()">
                        <label class="btn btn-outline-success w-100 py-3" for="type-income">
                            <i class="ti ti-trending-up d-block fs-24 mb-1"></i>
                            Income
                        </label>
                    </div>
                    <div class="col-6">
                        <input type="radio" class="btn-check" name="type" id="type-expense" value="expense"
                               {{ old('type', $transaction->type->value) === 'expense' ? 'checked' : '' }}
                               onchange="onTypeChange()">
                        <label class="btn btn-outline-danger w-100 py-3" for="type-expense">
                            <i class="ti ti-trending-down d-block fs-24 mb-1"></i>
                            Expense
                        </label>
                    </div>
                </div>
                @error('type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="card-title mb-0">Details</h6></div>
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="">— Select category —</option>
                        </select>
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                   step="0.01" min="0.01"
                                   value="{{ old('amount', $transaction->amount) }}" required>
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                               value="{{ old('date', $transaction->date->format('Y-m-d')) }}" required>
                        @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Reference <span class="text-muted small">(optional)</span></label>
                        <input type="text" name="reference" class="form-control @error('reference') is-invalid @enderror"
                               value="{{ old('reference', $transaction->reference) }}"
                               placeholder="e.g. INV-2026-0001, receipt #">
                        @error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description <span class="text-muted small">(optional)</span></label>
                        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Any notes about this transaction">{{ old('description', $transaction->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i> Update Transaction
            </button>
            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>

        </form>

    </div>
</div>

@endsection

@push('scripts')
<script>
const incomeCategories  = ['Project Revenue','Hourly Billing','Retainer','Outsourcing Margin','Consultation Fee','Maintenance Contract','Reimbursement','Other Income'];
const expenseCategories = ['Freelancer Payments','Software & Subscriptions','Domain & Hosting','Salaries & Payroll','Marketing & Ads','Professional Development','Office & Utilities','Legal & Accounting','Equipment & Hardware','Bank & Payment Fees','Travel & Transport','Client Entertainment','Other Expense'];
const savedCategory = @json(old('category', $transaction->category));

function onTypeChange() {
    const type = document.querySelector('input[name=type]:checked')?.value ?? 'income';
    const cats = type === 'income' ? incomeCategories : expenseCategories;
    const sel  = document.getElementById('category');
    sel.innerHTML = '<option value="">— Select category —</option>';
    cats.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c;
        opt.textContent = c;
        if (c === savedCategory) opt.selected = true;
        sel.appendChild(opt);
    });
}

onTypeChange();
</script>
@endpush
