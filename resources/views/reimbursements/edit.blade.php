@extends('layouts.app', ['title' => 'Edit Reimbursement', 'subtitle' => 'Reimbursements'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Reimbursement</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('reimbursements.update', $reimbursement) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('reimbursements._form')
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Save Changes
                        </button>
                        <a href="{{ route('reimbursements.show', $reimbursement) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
