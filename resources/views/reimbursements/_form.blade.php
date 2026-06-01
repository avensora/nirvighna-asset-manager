<div class="mb-3">
    <label class="form-label fw-medium">Title <span class="text-danger">*</span></label>
    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
           value="{{ old('title', $reimbursement->title ?? '') }}" placeholder="e.g. Domain renewal, Travel to client" required maxlength="255">
    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label fw-medium">Amount <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">₹</span>
            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                   value="{{ old('amount', isset($reimbursement) ? (float)$reimbursement->amount : '') }}"
                   step="0.01" min="0.01" placeholder="0.00" required>
            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-medium">Date Spent <span class="text-danger">*</span></label>
        <input type="date" name="spent_date" class="form-control @error('spent_date') is-invalid @enderror"
               value="{{ old('spent_date', isset($reimbursement) ? $reimbursement->spent_date->format('Y-m-d') : date('Y-m-d')) }}" required>
        @error('spent_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-medium">Category</label>
    <select name="category" class="form-select @error('category') is-invalid @enderror">
        <option value="">— Select Category —</option>
        @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ old('category', $reimbursement->category ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
    </select>
    @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

@if($projects->isNotEmpty())
<div class="mb-3">
    <label class="form-label fw-medium">Link to Project</label>
    <select name="project_id" class="form-select @error('project_id') is-invalid @enderror">
        <option value="">— None —</option>
        @foreach($projects as $project)
            <option value="{{ $project->id }}" {{ old('project_id', $reimbursement->project_id ?? '') == $project->id ? 'selected' : '' }}>
                {{ $project->title }}
            </option>
        @endforeach
    </select>
    @error('project_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
@endif

<div class="mb-3">
    <label class="form-label fw-medium">Description</label>
    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
              rows="3" placeholder="What was this expense for?">{{ old('description', $reimbursement->description ?? '') }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
