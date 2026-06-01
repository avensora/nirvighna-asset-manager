<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title>@yield('title') | Nirvighna</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preload" href="{{ asset('boron/assets/fonts/tabler-icons.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('boron/assets/fonts/tabler-icons-filled.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('boron/assets/fonts/tabler-icons-outline.woff2') }}" as="font" type="font/woff2" crossorigin>
    <script src="{{ asset('boron/assets/js/config.js') }}"></script>
    <link href="{{ asset('boron/assets/css/vendor.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('boron/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style">
    <link href="{{ asset('boron/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">
</head>

<body>
    <div class="auth-bg d-flex min-vh-100 justify-content-center align-items-center">
        <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
            <div class="col-xl-4 col-lg-5 col-md-6">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ asset('boron/assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('boron/assets/js/app.js') }}"></script>

    @stack('scripts')
</body>
</html>
