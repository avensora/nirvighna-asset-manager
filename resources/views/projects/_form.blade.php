{{-- Shared form fields for create/edit --}}
<div class="row g-3">

    <div class="col-12">
        <label class="form-label">Project Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', $project->title ?? '') }}" placeholder="e.g. Website Redesign" autofocus>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            @foreach(\App\Enums\ProjectStatus::cases() as $s)
                <option value="{{ $s->value }}"
                    {{ old('status', $project->status->value ?? 'planning') === $s->value ? 'selected' : '' }}>
                    {{ $s->label() }}
                </option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Priority <span class="text-danger">*</span></label>
        <select name="priority" class="form-select @error('priority') is-invalid @enderror">
            @foreach(\App\Enums\ProjectPriority::cases() as $p)
                <option value="{{ $p->value }}"
                    {{ old('priority', $project->priority->value ?? 'medium') === $p->value ? 'selected' : '' }}>
                    {{ $p->label() }}
                </option>
            @endforeach
        </select>
        @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Client <span class="text-muted small">(optional — leave blank for internal projects)</span></label>
        <select name="client_id" class="form-select @error('client_id') is-invalid @enderror">
            <option value="">— Internal / No Client —</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}"
                    {{ old('client_id', $project->client_id ?? '') == $client->id ? 'selected' : '' }}>
                    {{ $client->name }}{{ $client->company ? ' ('.$client->company.')' : '' }}
                </option>
            @endforeach
        </select>
        @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Start Date</label>
        <input type="text" name="start_date" id="start_date"
               class="form-control flatpickr-date @error('start_date') is-invalid @enderror"
               value="{{ old('start_date', isset($project) && $project->start_date ? $project->start_date->format('Y-m-d') : '') }}"
               placeholder="YYYY-MM-DD">
        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Deadline</label>
        <input type="text" name="deadline" id="deadline"
               class="form-control flatpickr-date @error('deadline') is-invalid @enderror"
               value="{{ old('deadline', isset($project) && $project->deadline ? $project->deadline->format('Y-m-d') : '') }}"
               placeholder="YYYY-MM-DD">
        @error('deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Budget (₹)</label>
        <input type="number" name="budget" step="0.01" min="0"
               class="form-control @error('budget') is-invalid @enderror"
               value="{{ old('budget', $project->budget ?? '') }}" placeholder="0.00">
        @error('budget')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Assign Team Leads</label>
        <select name="assignee_ids[]" class="form-select @error('assignee_ids') is-invalid @enderror" multiple
                style="height: 120px;">
            @foreach($teamLeads as $lead)
                <option value="{{ $lead->id }}"
                    {{ in_array($lead->id, old('assignee_ids', $assignedIds ?? [])) ? 'selected' : '' }}>
                    {{ $lead->name }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Hold Ctrl / ⌘ to select multiple.</div>
        @error('assignee_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3"
                  class="form-control @error('description') is-invalid @enderror"
                  placeholder="What is this project about?">{{ old('description', $project->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Internal Notes</label>
        <textarea name="notes" rows="2"
                  class="form-control @error('notes') is-invalid @enderror"
                  placeholder="Any notes for the team…">{{ old('notes', $project->notes ?? '') }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    flatpickr('.flatpickr-date', { dateFormat: 'Y-m-d', allowInput: true });
});
</script>
@endpush
