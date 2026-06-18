{{--
    Standalone shell for error pages shown OUTSIDE the app layout: 5xx
    failures, unauthenticated visitors, and re-login codes (401, 419).
    See App\Support\ErrorPage::layout(). Performs no DB access; degrades to
    unstyled HTML if the asset build is unavailable.
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', config('app.name'))</title>
    @vite('resources/css/app.css')
</head>
<body class="ob-error-page">
    @yield('content')
</body>
</html>
