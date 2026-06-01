@extends('layouts.app', ['title' => 'Add Client', 'subtitle' => 'Clients'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Client Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('clients.store') }}" method="POST" novalidate>
                    @csrf

                    <div class="row g-3">

                        {{-- Name + Company --}}
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Contact or business name" autofocus>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Company</label>
                            <input type="text" name="company" class="form-control @error('company') is-invalid @enderror"
                                   value="{{ old('company') }}" placeholder="Company name (if different)">
                            @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Email + Phone --}}
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="client@example.com">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone') }}" placeholder="+91 98765 43210">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- GSTIN + Pincode --}}
                        <div class="col-md-6">
                            <label class="form-label">GSTIN</label>
                            <input type="text" name="gstin" class="form-control @error('gstin') is-invalid @enderror"
                                   value="{{ old('gstin') }}" placeholder="22AAAAA0000A1Z5" maxlength="15"
                                   style="text-transform:uppercase">
                            @error('gstin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control @error('pincode') is-invalid @enderror"
                                   value="{{ old('pincode') }}" placeholder="400001" maxlength="6">
                            @error('pincode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Address --}}
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror"
                                      placeholder="Street address">{{ old('address') }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- City + State --}}
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                   value="{{ old('city') }}" placeholder="Mumbai">
                            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control @error('state') is-invalid @enderror"
                                   value="{{ old('state') }}" placeholder="Maharashtra">
                            @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Notes --}}
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                      placeholder="Any additional notes about this client">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Client</button>
                        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@endsection
