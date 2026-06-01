@extends('layouts.app', ['title' => 'Edit Lead', 'subtitle' => 'Leads'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Edit Lead — {{ $lead->name }}</h5>
                <a href="{{ route('leads.show', $lead) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('leads.update', $lead) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    @include('leads._form')

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="{{ route('leads.show', $lead) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>

                <hr class="my-4">
                <form action="{{ route('leads.destroy', $lead) }}" method="POST"
                      onsubmit="return confirm('Delete this lead? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="ti ti-trash me-1"></i> Delete Lead
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
