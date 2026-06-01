@extends('layouts.app', ['title' => 'Edit Loan', 'subtitle' => 'Loan Register'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Loan — {{ $loan->source_name }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('loans.update', $loan) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('loans._form')
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Save Changes
                        </button>
                        <a href="{{ route('loans.show', $loan) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
