<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    <link rel="stylesheet" href="{{ asset('legacy-assets/css/all.css') }}">
    <link rel="stylesheet" href="{{ asset('legacy-assets/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('legacy-assets/css/bootstrap-datepicker.css') }}" media="screen">
    <link rel="stylesheet" href="{{ asset('legacy-assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('legacy-assets/css/print.css') }}" media="print">
    <link rel="stylesheet" href="{{ asset('legacy-assets/css/bootstrap-select.css') }}">
    <link rel="stylesheet" href="{{ asset('legacy-assets/css/bootstrap-table.min.css') }}">
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

    <script src="{{ asset('legacy-assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('legacy-assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('legacy-assets/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('legacy-assets/js/bootstrap-table.min.js') }}"></script>
    <script src="{{ asset('legacy-assets/js/bootstrap-table-fr-FR.js') }}"></script>
    <script src="{{ asset('legacy-assets/js/checkForm.js') }}"></script>
    @stack('scripts')
    <script>
    // Collapse/decollapse lateral menu
    $(document).ready(function() {
        var isCollapsed = sessionStorage.getItem('isCollapsed');
        if (isCollapsed == 1) {
            $('#space-left').addClass('collapsed');
            $('.navbar-lateral').css({width: 49, overflow: 'hidden'});
            $('.collapse-menu').hide();
            $('.decollapse-menu').show();
            $('.dropdown-lateral span').hide();
            $('.div-lateral').hide();
        }
        $('.collapse-menu').on('click', function() {
            sessionStorage.setItem('isCollapsed', 1);
            $('#space-left').addClass('collapsed');
            $('.navbar-lateral').css({width: 49, overflow: 'hidden'});
            $('.collapse-menu').hide();
            $('.decollapse-menu').show();
            $('.dropdown-lateral span').hide();
            $('.div-lateral').hide();
        });
        $('.decollapse-menu').on('click', function() {
            sessionStorage.setItem('isCollapsed', 0);
            $('#space-left').removeClass('collapsed');
            $('.navbar-lateral').css({width: 220, overflow: 'hidden'});
            $('.decollapse-menu').hide();
            $('.collapse-menu').show();
            $('.dropdown-lateral span').show();
            $('.div-lateral').show();
        });
    });
    </script>
</body>
</html>
