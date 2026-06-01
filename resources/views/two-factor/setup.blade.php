@extends('layouts.app', ['title' => '2FA Setup', 'subtitle' => 'Security'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-6 col-lg-8">

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Set Up Two-Factor Authentication</h5>
            </div>
            <div class="card-body">

                <div class="alert alert-info d-flex gap-2 align-items-start">
                    <i class="ti ti-info-circle fs-5 mt-1 flex-shrink-0"></i>
                    <div>
                        Scan the QR code below with your authenticator app (Google Authenticator, Authy, etc.),
                        then enter the 6-digit code to confirm setup.
                    </div>
                </div>

                {{-- QR Code --}}
                <div class="text-center my-4">
                    {!! $qrImage !!}
                </div>

                {{-- Manual entry --}}
                <div class="mb-4">
                    <label class="form-label text-muted small">Or enter the key manually:</label>
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace" id="manual-key"
                               value="{{ $config->secret }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="copy-key">
                            <i class="ti ti-copy"></i>
                        </button>
                    </div>
                </div>

                {{-- Confirmation form --}}
                <form action="{{ route('2fa.confirm') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Verification Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}"
                               maxlength="6" autocomplete="one-time-code"
                               class="form-control form-control-lg text-center font-monospace @error('code') is-invalid @enderror"
                               placeholder="000000" autofocus>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-shield-check me-1"></i> Verify &amp; Enable
                        </button>
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.getElementById('copy-key').addEventListener('click', function () {
    navigator.clipboard.writeText(document.getElementById('manual-key').value).then(() => {
        this.innerHTML = '<i class="ti ti-check"></i>';
        setTimeout(() => { this.innerHTML = '<i class="ti ti-copy"></i>'; }, 2000);
    });
});
</script>
@endpush

@endsection
