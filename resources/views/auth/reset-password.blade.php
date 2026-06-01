@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="card overflow-hidden text-center h-100 p-xxl-4 p-3 mb-0">
    <a href="{{ route('login') }}" class="auth-brand mb-3">
        <img src="{{ asset('boron/assets/images/logo-dark.png') }}" alt="logo" height="30" class="logo-dark">
        <img src="{{ asset('boron/assets/images/logo.png') }}" alt="logo" height="30" class="logo-light">
    </a>

    <h4 class="fw-semibold mb-2">Create New Password</h4>
    <p class="text-muted mb-4">Your new password must be different from your previous password.</p>

    <form method="POST" action="{{ route('password.store') }}" class="text-start mb-3">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   placeholder="Enter your email" value="{{ old('email', $request->email) }}" autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">New Password</label>
            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="Enter new password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                   placeholder="Repeat new password">
        </div>

        <div class="d-grid">
            <button class="btn btn-primary" type="submit">Reset Password</button>
        </div>
    </form>
</div>
@endsection
