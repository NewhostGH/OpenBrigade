<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', config('app.name'))</title>

    {{-- Built frontend assets (managed via NPM + Vite) --}}
    @vite(['resources/css/app.css'])

    @stack('styles')
</head>

<body>

    {{-- No-JS notice (replaces legacy noscript.php). OpenBrigade relies on
         JavaScript for navigation, tables and forms; warn rather than break. --}}
    <noscript>
        <div class="ob-noscript">
            {{ __('layout.noscript') }}
        </div>
    </noscript>

    @auth
        @include('layout.navbar')
        @include('layout.sidebar')

        <div class="ob-space-left" id="ob-space-left">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mx-3 mt-2" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('layout.dismiss') }}"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mx-3 mt-2" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('layout.dismiss') }}"></button>
                </div>
            @endif

            @yield('content')
        </div>
    @else
        @yield('content')
    @endauth

    {{-- Built frontend JS bundle (imports jQuery/bootstrap and app code) --}}
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>

</html>