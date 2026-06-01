@extends('layouts.app', ['title' => 'Invite Team Member', 'subtitle' => 'Team'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Invite Team Member</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('team.store') }}" method="POST" novalidate>
                    @csrf

                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Jane Doe" autofocus>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="jane@example.com">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror">
                                <option value="team_lead" {{ old('role', 'team_lead') === 'team_lead' ? 'selected' : '' }}>Team Lead</option>
                                <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                                @if(auth()->user()->isMasterAdmin())
                                <option value="master_admin" {{ old('role') === 'master_admin' ? 'selected' : '' }}>Master Admin</option>
                                @endif
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <p class="text-muted small mb-0">
                                <i class="ti ti-info-circle me-1"></i>
                                A password setup email will be sent to this address. They can set their own password using the link.
                            </p>
                        </div>

                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Send Invite</button>
                        <a href="{{ route('team.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@endsection
