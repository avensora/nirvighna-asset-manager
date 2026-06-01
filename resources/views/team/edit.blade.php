@extends('layouts.app', ['title' => 'Edit Team Member', 'subtitle' => 'Team'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit — {{ $user->name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('team.update', $user) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            @php $isSelf = $user->id === auth()->id(); @endphp
                            <select name="role" class="form-select @error('role') is-invalid @enderror"
                                    {{ $isSelf ? 'disabled' : '' }}>
                                <option value="team_lead" {{ old('role', $user->role->value) === 'team_lead' ? 'selected' : '' }}>Team Lead</option>
                                <option value="manager" {{ old('role', $user->role->value) === 'manager' ? 'selected' : '' }}>Manager</option>
                                @if(auth()->user()->isMasterAdmin())
                                <option value="master_admin" {{ old('role', $user->role->value) === 'master_admin' ? 'selected' : '' }}>Master Admin</option>
                                @endif
                            </select>
                            @if($isSelf)
                                <input type="hidden" name="role" value="{{ $user->role->value }}">
                            @endif
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                       value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                       {{ $isSelf ? 'disabled' : '' }}>
                                @if($isSelf)
                                    <input type="hidden" name="is_active" value="1">
                                @endif
                                <label class="form-check-label" for="is_active">Account Active</label>
                            </div>
                            <div class="form-text text-muted">Inactive members cannot log in.</div>
                        </div>

                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="{{ route('team.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@endsection
