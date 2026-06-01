@extends('layouts.auth')

@section('title', '403 — Forbidden')

@section('content')
<div class="card text-center">
    <div class="card-body p-5">
        <i class="ti ti-shield-off text-danger" style="font-size:4rem"></i>
        <h1 class="fw-bold my-2" style="font-size:3.5rem;line-height:1">403</h1>
        <h5 class="fw-semibold">Access Denied</h5>
        <p class="text-muted mb-4">{{ $exception->getMessage() ?: "You don't have permission to access this page." }}</p>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="btn btn-primary">
            <i class="ti ti-arrow-left me-1"></i> Go Back
        </a>
    </div>
</div>
@endsection
