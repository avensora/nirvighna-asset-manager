@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $lead->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Company</label>
        <input type="text" name="company" class="form-control @error('company') is-invalid @enderror"
               value="{{ old('company', $lead->company ?? '') }}">
        @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $lead->email ?? '') }}">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $lead->phone ?? '') }}" placeholder="+91 …">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Source</label>
        <input type="text" name="source" class="form-control @error('source') is-invalid @enderror"
               value="{{ old('source', $lead->source ?? '') }}" placeholder="e.g. Website, Referral, Cold Call">
        @error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Deal Value (₹)</label>
        <div class="input-group">
            <span class="input-group-text">₹</span>
            <input type="number" name="deal_value" step="0.01" min="0"
                   class="form-control @error('deal_value') is-invalid @enderror"
                   value="{{ old('deal_value', isset($lead) ? $lead->deal_value : '') }}"
                   placeholder="0.00">
        </div>
        @error('deal_value')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Stage <span class="text-danger">*</span></label>
        <select name="stage" class="form-select @error('stage') is-invalid @enderror" required>
            @foreach($stages as $stage)
                <option value="{{ $stage->value }}"
                    {{ old('stage', isset($lead) ? $lead->stage->value : 'new_lead') === $stage->value ? 'selected' : '' }}>
                    {{ $stage->label() }}
                </option>
            @endforeach
        </select>
        @error('stage')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Assigned To</label>
        <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
            <option value="">— Unassigned —</option>
            @foreach($managers as $manager)
                <option value="{{ $manager->id }}"
                    {{ old('assigned_to', isset($lead) ? $lead->assigned_to : '') == $manager->id ? 'selected' : '' }}>
                    {{ $manager->name }}
                </option>
            @endforeach
        </select>
        @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                  rows="4">{{ old('notes', $lead->notes ?? '') }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
