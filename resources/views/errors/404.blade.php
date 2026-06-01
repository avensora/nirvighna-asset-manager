@extends('layouts.auth')

@section('title', '404 — Page Not Found')

@section('content')
<div class="card text-center">
    <div class="card-body p-5">
        <i class="ti ti-search-off text-secondary" style="font-size:4rem"></i>
        <h1 class="fw-bold my-2" style="font-size:3.5rem;line-height:1">404</h1>
        <h5 class="fw-semibold">Page Not Found</h5>
        <p class="text-muted mb-4">The page you're looking for doesn't exist or has been moved.</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">
            <i class="ti ti-home me-1"></i> Back to Dashboard
        </a>
    </div>
</div>
@endsection
