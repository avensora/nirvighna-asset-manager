@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="card overflow-hidden text-center h-100 p-xxl-4 p-3 mb-0">
    <a href="{{ route('login') }}" class="auth-brand mb-3">
        <img src="{{ asset('boron/assets/images/logo-dark.png') }}" alt="logo" height="30" class="logo-dark">
        <img src="{{ asset('boron/assets/images/logo.png') }}" alt="logo" height="30" class="logo-light">
    </a>

    <h4 class="fw-semibold mb-2">Create your account</h4>
    <p class="text-muted mb-4">Fill in your details to get started with Nirvighna.</p>

    <form method="POST" action="{{ route('register') }}" class="text-start mb-3">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="name">Full Name</label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                   placeholder="Enter your name" value="{{ old('name') }}" autofocus>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   placeholder="Enter your email" value="{{ old('email') }}">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="Enter a password (min 8 characters)">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                   placeholder="Repeat your password">
        </div>

        <div class="d-grid">
            <button class="btn btn-primary" type="submit">Create Account</button>
        </div>
    </form>

    <p class="text-muted fs-14">Already have an account? <a href="{{ route('login') }}" class="fw-semibold text-primary">Login</a></p>

    <p class="text-muted fs-14 mb-0 mt-auto">
        {{ date('Y') }} &copy; Nirvighna &mdash; An Avensora Product
    </p>
</div>
@endsection
