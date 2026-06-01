@extends('layouts.app', ['title' => 'New Lead', 'subtitle' => 'Leads'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Create Lead</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('leads.store') }}" method="POST" novalidate>
                    @csrf

                    @include('leads._form')

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Create Lead</button>
                        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
