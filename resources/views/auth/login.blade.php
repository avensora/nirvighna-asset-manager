@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="card overflow-hidden text-center h-100 p-xxl-4 p-3 mb-0">
    <a href="{{ route('login') }}" class="auth-brand mb-3">
        <img src="{{ asset('boron/assets/images/logo-dark.png') }}" alt="logo" height="30" class="logo-dark">
        <img src="{{ asset('boron/assets/images/logo.png') }}" alt="logo" height="30" class="logo-light">
    </a>

    <h4 class="fw-semibold mb-2">Login to Nirvighna</h4>
    <p class="text-muted mb-4">Enter your email and password to access your account.</p>

    <form method="POST" action="{{ route('login') }}" class="text-start mb-3">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   placeholder="Enter your email" value="{{ old('email') }}" autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="Enter your password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <a href="{{ route('password.request') }}" class="text-muted border-bottom border-dashed">Forgot Password?</a>
        </div>

        <div class="d-grid">
            <button class="btn btn-primary" type="submit">Login</button>
        </div>
    </form>

    <p class="text-muted fs-14 mb-0 mt-auto">
        {{ date('Y') }} &copy; Nirvighna &mdash; An Avensora Product
    </p>
</div>
@endsection
