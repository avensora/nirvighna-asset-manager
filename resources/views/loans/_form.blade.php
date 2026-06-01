<div class="row g-3 mb-3">
    <div class="col-md-8">
        <label class="form-label fw-medium">Source Name <span class="text-danger">*</span></label>
        <input type="text" name="source_name" class="form-control @error('source_name') is-invalid @enderror"
               value="{{ old('source_name', $loan->source_name ?? '') }}" placeholder="e.g. John Doe, HDFC Bank" required maxlength="255">
        @error('source_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-medium">Source Type <span class="text-danger">*</span></label>
        <select name="source_type" class="form-select @error('source_type') is-invalid @enderror">
            @foreach($sourceTypes as $type)
                <option value="{{ $type->value }}" {{ old('source_type', ($loan->source_type ?? null)?->value ?? 'person') === $type->value ? 'selected' : '' }}>
                    {{ $type->label() }}
                </option>
            @endforeach
        </select>
        @error('source_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="form-label fw-medium">Amount Borrowed <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">₹</span>
            <input type="number" name="principal_amount" class="form-control @error('principal_amount') is-invalid @enderror"
                   value="{{ old('principal_amount', isset($loan) ? (float)$loan->principal_amount : '') }}"
                   step="0.01" min="0.01" placeholder="0.00" required>
            @error('principal_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-medium">Borrowed Date <span class="text-danger">*</span></label>
        <input type="date" name="borrowed_date" class="form-control @error('borrowed_date') is-invalid @enderror"
               value="{{ old('borrowed_date', isset($loan) ? $loan->borrowed_date->format('Y-m-d') : date('Y-m-d')) }}" required>
        @error('borrowed_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-medium">Due Date</label>
        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
               value="{{ old('due_date', isset($loan) && $loan->due_date ? $loan->due_date->format('Y-m-d') : '') }}">
        @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-medium">Purpose</label>
    <input type="text" name="purpose" class="form-control @error('purpose') is-invalid @enderror"
           value="{{ old('purpose', $loan->purpose ?? '') }}" placeholder="Why was this loan taken?" maxlength="255">
    @error('purpose') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label fw-medium">Notes</label>
    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
              rows="3" placeholder="Interest terms, repayment schedule, etc.">{{ old('notes', $loan->notes ?? '') }}</textarea>
    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
