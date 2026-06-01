@extends('layouts.auth')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="card overflow-hidden text-center h-100 p-xxl-4 p-3 mb-0">
    <a href="{{ route('login') }}" class="auth-brand mb-3">
        <img src="{{ asset('boron/assets/images/logo-dark.png') }}" alt="logo" height="30" class="logo-dark">
        <img src="{{ asset('boron/assets/images/logo.png') }}" alt="logo" height="30" class="logo-light">
    </a>

    <h4 class="fw-semibold mb-2">Two-Factor Authentication</h4>
    <p class="text-muted mb-4">Enter the 6-digit code from your authenticator app, or paste a recovery code.</p>

    <form method="POST" action="{{ route('2fa.verify') }}" class="text-start mb-3">
        @csrf

        <div class="mb-3">
            <label class="form-label">Authentication Code</label>
            <input type="text" name="code" inputmode="numeric"
                   maxlength="20" autocomplete="one-time-code"
                   class="form-control form-control-lg text-center font-monospace @error('code') is-invalid @enderror"
                   placeholder="000000" autofocus>
            @error('code')
                <div class="invalid-feedback text-center">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary">Verify</button>
        </div>
    </form>

    <p class="text-muted small mb-0">
        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            Use a different account
        </a>
    </p>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
@endsection
