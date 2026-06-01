@extends('layouts.auth')

@section('title', '401 — Unauthorized')

@section('content')
<div class="card text-center">
    <div class="card-body p-5">
        <i class="ti ti-lock text-warning" style="font-size:4rem"></i>
        <h1 class="fw-bold my-2" style="font-size:3.5rem;line-height:1">401</h1>
        <h5 class="fw-semibold">Unauthorized</h5>
        <p class="text-muted mb-4">You need to be logged in to view this page.</p>
        <a href="{{ route('login') }}" class="btn btn-primary">
            <i class="ti ti-login me-1"></i> Go to Login
        </a>
    </div>
</div>
@endsection
