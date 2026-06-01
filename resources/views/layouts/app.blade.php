<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-sidenav-size="default" data-menu-color="dark" data-topbar-color="light">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Dashboard') | Nirvighna</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preload" href="{{ asset('boron/assets/fonts/tabler-icons.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('boron/assets/fonts/tabler-icons-filled.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('boron/assets/fonts/tabler-icons-outline.woff2') }}" as="font" type="font/woff2" crossorigin>
    <script src="{{ asset('boron/assets/js/config.js') }}"></script>
    <link href="{{ asset('boron/assets/css/vendor.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('boron/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style">
    <link href="{{ asset('boron/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet" type="text/css">

    @stack('head-css')
</head>

<body>
    <div class="wrapper">

        @include('partials.sidenav')

        @include('partials.topbar')

        <div class="page-content">

            <div class="page-container">

                @if(isset($title))
                    @include('partials.page-title', ['title' => $title, 'subtitle' => $subtitle ?? null])
                @endif

                @include('partials.alerts')

                @yield('content')

            </div>

            @include('partials.footer')

        </div>

    </div>

    @include('partials.customizer')

    <script src="{{ asset('boron/assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('boron/assets/js/app.js') }}"></script>

    @stack('scripts')

    <script>
    // Persist sidebar toggle state across page navigations
    (function () {
        var btn = document.querySelector('.sidenav-toggle-button');
        if (!btn) return;
        btn.addEventListener('click', function () {
            setTimeout(function () {
                var size = document.documentElement.getAttribute('data-sidenav-size');
                if (size === 'full') return; // mobile overlay — don't persist
                try {
                    var cfg = JSON.parse(sessionStorage.getItem('__BORON_CONFIG__') || '{}');
                    if (cfg.sidenav) {
                        cfg.sidenav.size = size;
                        sessionStorage.setItem('__BORON_CONFIG__', JSON.stringify(cfg));
                    }
                } catch (e) {}
            }, 0);
        });
    })();
    </script>
</body>
</html>
