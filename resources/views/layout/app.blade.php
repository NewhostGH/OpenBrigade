<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    {{-- Built frontend assets (managed via NPM + Vite) --}}
    @vite(['resources/css/app.css'])
    <style>@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap');</style>

    @stack('styles')
</head>
<body>

@auth
    @include('layout.navbar')
    @include('layout.sidebar')

    <div class="space-left" style="position:relative;top:-2px" id="space-left">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-3 mt-2" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mx-3 mt-2" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
