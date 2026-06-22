<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('auth_views.login_page_title') }} — {{ config('app.name') }}</title>
    @vite('resources/css/app.css')
</head>

@php
    $loginOrgName   = isset($appIdentity) ? $appIdentity->shortName() : config('app.name');
    $loginLogoUrl   = isset($appIdentity) ? ($appIdentity->logoUrl() ?? asset('images/logo.png')) : asset('images/logo.png');
    $loginSplashUrl = isset($appIdentity) ? $appIdentity->splashUrl() : null;
@endphp
<body class="ob-login-body">
<noscript>
    <div class="ob-noscript">
        {{ __('auth_views.login_noscript') }}
    </div>
</noscript>
<div class="container-fluid ob-login-shell">
    <div class="row min-vh-100">

        {{-- ── Left branding panel ─────────────────────────────────────────── --}}
        <aside class="col-lg-8 d-flex flex-column justify-content-center align-items-center ob-login-left px-4 py-5"
               @if ($loginSplashUrl) style="background-image:url('{{ $loginSplashUrl }}');background-size:cover;background-position:center;" @endif>
            <img src="{{ $loginLogoUrl }}"
                 alt="{{ $loginOrgName }}"
                 style="max-height:80px; max-width:90%;"
                 onerror="this.style.display='none'">
            <p class="ob-login-left-title mt-4">
                {{ __('auth_views.login_tagline', ['org' => $loginOrgName]) }}
            </p>
        </aside>

        {{-- ── Right sign-in panel ─────────────────────────────────────────── --}}
        <section class="col-lg-4 d-flex align-items-center justify-content-center px-4 py-5 ob-login-right">
            <div id="authBox" class="ob-login-card">

                {{-- Sign-in form --}}
                <div class="ob-login-signin-panel">
                    <div class="mb-4">
                        <div class="ob-login-brand-title">{{ __('auth_views.login_welcome') }}</div>
                        <div class="ob-login-brand-sub">{{ __('auth_views.login_subtitle', ['org' => $loginOrgName]) }}</div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success ob-login-alert mb-3">{{ session('success') }}</div>
                    @endif

                    @if ($errors->has('login'))
                        <div class="alert alert-danger ob-login-alert mb-3" role="alert">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            {{ $errors->first('login') }}
                        </div>
                    @endif

                    <form id="signinForm" method="POST" action="{{ route('login.attempt') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="login" class="form-label">{{ __('auth_views.login_label_login') }}</label>
                            <input id="login" type="text" name="login"
                                class="form-control ob-login-input @error('login') is-invalid @enderror"
                                value="{{ old('login') }}"
                                required autofocus autocomplete="username">
                            @error('login')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <label for="password" class="form-label mb-0">{{ __('auth_views.login_label_password') }}</label>
                                <a href="#" id="showForgot"
                                   class="text-decoration-none"
                                   style="font-size:var(--font-size-xs)">
                                    {{ __('auth_views.login_forgot_link') }}
                                </a>
                            </div>
                            <input id="password" type="password" name="password"
                                class="form-control ob-login-input mt-1 @error('password') is-invalid @enderror"
                                required autocomplete="current-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember"
                                   style="font-size:var(--font-size-sm)">
                                {{ __('auth_views.login_remember') }}
                            </label>
                        </div>

                        {{-- Inline validation message (shown by JS without page reload) --}}
                        <div id="signinError" class="alert alert-danger ob-login-alert mb-3 d-none" role="alert">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            {{ __('auth_views.login_inline_error') }}
                        </div>

                        <button type="submit" class="btn ob-login-btn">{{ __('auth_views.login_btn') }}</button>
                    </form>
                </div>

                {{-- Forgot-password panel --}}
                <div class="ob-login-forgot-panel">
                    <div class="mb-3">
                        <div class="ob-login-brand-title">{{ __('auth_views.forgot_title') }}</div>
                        <p class="ob-login-brand-sub mt-1 mb-0">
                            {{ __('auth_views.forgot_subtitle') }}
                        </p>
                    </div>

                    <a href="{{ route('password.request') }}" class="btn btn-primary btn-sm mt-2 me-2">
                        <i class="fas fa-envelope me-1"></i> {{ __('auth_views.forgot_btn_recover') }}
                    </a>
                    <button type="button" id="showSignin" class="btn btn-secondary btn-sm mt-2">
                        <i class="fas fa-arrow-left me-1"></i> {{ __('auth_views.btn_back') }}
                    </button>
                </div>

                <div class="ob-login-footer">
                    {{ date('Y') }} — {{ config('app.name') }}
                </div>

            </div>
        </section>
    </div>
</div>

@vite(['resources/js/app.js', 'resources/js/ob-auth-login.js'])
</body>
</html>
