@extends('layouts.app', ['title' => 'New Project', 'subtitle' => 'Projects'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Create Project</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('projects.store') }}" method="POST" novalidate>
                    @csrf

                    @include('projects._form')

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Create Project</button>
                        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
