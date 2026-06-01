@extends('layouts.app', ['title' => 'Edit Scheduled Expense', 'subtitle' => $expense->title])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Scheduled Expense</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('scheduled-expenses.update', $expense) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    @include('scheduled-expenses._form', ['expense' => $expense])
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="{{ route('scheduled-expenses.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
