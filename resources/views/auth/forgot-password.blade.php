@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="card overflow-hidden text-center h-100 p-xxl-4 p-3 mb-0">
    <a href="{{ route('login') }}" class="auth-brand mb-3">
        <img src="{{ asset('boron/assets/images/logo-dark.png') }}" alt="logo" height="30" class="logo-dark">
        <img src="{{ asset('boron/assets/images/logo.png') }}" alt="logo" height="30" class="logo-light">
    </a>

    <h4 class="fw-semibold mb-2">Reset your Password</h4>
    <p class="text-muted mb-4">Enter your email address and we'll send you a link to reset your password.</p>

    @if(session('status'))
        <div class="alert alert-success mb-3">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="text-start mb-3">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   placeholder="Enter your email" value="{{ old('email') }}" autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid">
            <button class="btn btn-primary" type="submit">Send Reset Link</button>
        </div>
    </form>

    <p class="text-muted fs-14">Remember your password? <a href="{{ route('login') }}" class="fw-semibold text-primary">Login</a></p>
</div>
@endsection
