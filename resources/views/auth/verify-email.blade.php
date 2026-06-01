@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
<div class="card overflow-hidden text-center h-100 p-xxl-4 p-3 mb-0">
    <a href="{{ route('login') }}" class="auth-brand mb-3">
        <img src="{{ asset('boron/assets/images/logo-dark.png') }}" alt="logo" height="30" class="logo-dark">
        <img src="{{ asset('boron/assets/images/logo.png') }}" alt="logo" height="30" class="logo-light">
    </a>

    <div class="avatar-xl bg-success-subtle text-success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
        <i class="ti ti-mail fs-32"></i>
    </div>

    <h4 class="fw-semibold mb-2">Check your Email</h4>
    <p class="text-muted mb-4">
        Thanks for registering! Before you continue, please verify your email address by clicking the link we just sent to <strong>{{ auth()->user()->email }}</strong>.
    </p>

    @if(session('status') === 'verification-link-sent')
        <div class="alert alert-success mb-3">A new verification link has been sent to your email address.</div>
    @endif

    <div class="d-grid gap-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary w-100">Resend Verification Email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100">Log Out</button>
        </form>
    </div>
</div>
@endsection
