@extends('layouts.app', ['title' => 'Company Settings', 'subtitle' => 'Finances'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Opening Balance</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    Set the company's bank/cash balance as of a specific date. All transactions on or after this date
                    will be used to calculate the current balance shown in the transaction journal.
                </p>

                <form action="{{ route('settings.company.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-medium">Opening Balance <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="opening_balance"
                                   class="form-control @error('opening_balance') is-invalid @enderror"
                                   value="{{ old('opening_balance', $openingBalance) }}"
                                   step="0.01" min="0" placeholder="0.00" required>
                            @error('opening_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-medium">As of Date <span class="text-danger">*</span></label>
                        <input type="date" name="opening_balance_date"
                               class="form-control @error('opening_balance_date') is-invalid @enderror"
                               value="{{ old('opening_balance_date', $openingBalanceDate) }}" required>
                        @error('opening_balance_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Transactions on or after this date will be added/subtracted to compute the current balance.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> Save
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
