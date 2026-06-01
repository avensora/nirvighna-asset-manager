@extends('layouts.app', ['title' => 'New Reimbursement', 'subtitle' => 'Reimbursements'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Submit Reimbursement Request</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('reimbursements.store') }}" method="POST">
                    @csrf
                    @include('reimbursements._form')
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-send me-1"></i> Submit Request
                        </button>
                        <a href="{{ route('reimbursements.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
