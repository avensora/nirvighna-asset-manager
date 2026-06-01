<div class="mb-3">
    <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('title') is-invalid @enderror"
        id="title" name="title"
        value="{{ old('title', $expense?->title) }}"
        placeholder="e.g. AWS Server, Office Rent, GST Payment"
        required maxlength="255">
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label" for="amount">Amount (₹) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0.01"
            class="form-control @error('amount') is-invalid @enderror"
            id="amount" name="amount"
            value="{{ old('amount', $expense?->amount) }}"
            required>
        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="category">Category</label>
        <select class="form-select @error('category') is-invalid @enderror" id="category" name="category">
            <option value="">— Select Category —</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ old('category', $expense?->category) === $cat ? 'selected' : '' }}>
                    {{ $cat }}
                </option>
            @endforeach
        </select>
        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label" for="due_date">Due Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('due_date') is-invalid @enderror"
            id="due_date" name="due_date"
            value="{{ old('due_date', $expense?->due_date?->toDateString()) }}"
            required>
        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="recurrence">Recurrence <span class="text-danger">*</span></label>
        <select class="form-select @error('recurrence') is-invalid @enderror" id="recurrence" name="recurrence">
            @foreach($recurrenceTypes as $type)
                <option value="{{ $type->value }}"
                    {{ old('recurrence', $expense?->recurrence?->value ?? 'none') === $type->value ? 'selected' : '' }}>
                    {{ $type->label() }}
                </option>
            @endforeach
        </select>
        @error('recurrence')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label" for="notes">Notes</label>
    <textarea class="form-control @error('notes') is-invalid @enderror"
        id="notes" name="notes" rows="2"
        placeholder="Any additional details...">{{ old('notes', $expense?->notes) }}</textarea>
    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
