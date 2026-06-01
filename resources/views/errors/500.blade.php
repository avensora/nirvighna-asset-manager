@extends('layouts.auth')

@section('title', '500 — Server Error')

@section('content')
<div class="card text-center">
    <div class="card-body p-5">
        <i class="ti ti-alert-triangle text-danger" style="font-size:4rem"></i>
        <h1 class="fw-bold my-2" style="font-size:3.5rem;line-height:1">500</h1>
        <h5 class="fw-semibold">Server Error</h5>
        <p class="text-muted mb-4">Something went wrong on our end. Please try again in a moment.</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">
            <i class="ti ti-home me-1"></i> Back to Dashboard
        </a>
    </div>
</div>
@endsection
