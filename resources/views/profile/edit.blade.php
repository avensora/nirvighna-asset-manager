@extends('layouts.app', ['title' => 'My Profile', 'subtitle' => 'Account'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-7">

        {{-- Profile Info --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Profile Information</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('profile.update') }}" method="POST" novalidate>
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
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="{{ $user->role->label() }}" readonly>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Save Profile</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Change Password</h5>
            </div>
            <div class="card-body">
                @if(session('password_success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('password_success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('profile.password') }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Current Password <span class="text-danger">*</span></label>
                            <input type="password" name="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   autocomplete="current-password">
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">New Password <span class="text-danger">*</span></label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   autocomplete="new-password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation"
                                   class="form-control" autocomplete="new-password">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Two-Factor Authentication --}}
        <div class="card mt-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Two-Factor Authentication</h5>
                @if($twoFactorConfig && $twoFactorConfig->isConfirmed())
                    <span class="badge bg-success">Enabled</span>
                @else
                    <span class="badge bg-secondary">Disabled</span>
                @endif
            </div>
            <div class="card-body">
                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($twoFactorConfig && $twoFactorConfig->isConfirmed())
                    <p class="text-muted mb-3">2FA is active. Your account requires a TOTP code on every login.</p>
                    <form action="{{ route('2fa.disable') }}" method="POST" id="disable-2fa-form">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Confirm your password to disable 2FA</label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Current password" autocomplete="current-password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-danger">Disable 2FA</button>
                    </form>
                @else
                    <p class="text-muted mb-3">Add an extra layer of security using an authenticator app (Google Authenticator, Authy, etc.).</p>
                    <a href="{{ route('2fa.setup') }}" class="btn btn-primary">
                        <i class="ti ti-shield-lock me-1"></i> Enable 2FA
                    </a>
                @endif
            </div>
        </div>

        {{-- Login History --}}
        <div class="card mt-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Recent Logins</h5>
                <a href="{{ route('profile.login-history') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                @if($loginHistory->isEmpty())
                    <p class="text-muted p-3 mb-0">No login history yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date &amp; Time</th>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loginHistory as $entry)
                                    <tr>
                                        <td class="text-nowrap">{{ $entry->created_at->format('d M Y, H:i') }}</td>
                                        <td>{{ $entry->ip_address }}</td>
                                        <td>
                                            @if($entry->status === 'success')
                                                <span class="badge bg-success">Success</span>
                                            @else
                                                <span class="badge bg-danger">Failed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
