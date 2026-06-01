@extends('layouts.app', ['title' => 'New Scheduled Expense', 'subtitle' => 'Schedule an Expected Expense'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Scheduled Expense Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('scheduled-expenses.store') }}" method="POST" novalidate>
                    @csrf
                    @include('scheduled-expenses._form', ['expense' => null])
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="{{ route('scheduled-expenses.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
