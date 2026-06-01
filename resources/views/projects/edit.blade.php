@extends('layouts.app', ['title' => 'Edit Project', 'subtitle' => 'Projects'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit — {{ $project->title }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('projects.update', $project) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    @include('projects._form')

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
