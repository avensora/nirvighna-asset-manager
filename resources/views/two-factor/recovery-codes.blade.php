@extends('layouts.app', ['title' => 'Recovery Codes', 'subtitle' => 'Security'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-6 col-lg-8">

        <div class="card border-warning">
            <div class="card-header bg-warning bg-opacity-10">
                <h5 class="card-title mb-0 text-warning">
                    <i class="ti ti-alert-triangle me-1"></i> Save Your Recovery Codes
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <strong>Store these codes in a safe place.</strong> They are shown only once.
                    If you lose access to your authenticator app, you can use one of these codes to log in.
                    Each code can only be used once.
                </div>

                <div class="bg-light rounded p-3 font-monospace mb-4">
                    @foreach($codes as $code)
                        <div class="py-1">{{ $code }}</div>
                    @endforeach
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary" id="copy-codes">
                        <i class="ti ti-copy me-1"></i> Copy All
                    </button>
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        I've saved my codes
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.getElementById('copy-codes').addEventListener('click', function () {
    const codes = @json($codes);
    navigator.clipboard.writeText(codes.join('\n')).then(() => {
        this.innerHTML = '<i class="ti ti-check me-1"></i> Copied!';
        setTimeout(() => { this.innerHTML = '<i class="ti ti-copy me-1"></i> Copy All'; }, 2000);
    });
});
</script>
@endpush

@endsection
