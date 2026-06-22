<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('auth_views.reset_req_title') }} — {{ config('app.name') }}</title>
    @vite('resources/css/app.css')
</head>

<body class="ob-login-body">
    <div class="container-fluid ob-login-shell">
        <div class="row min-vh-100">

            <aside
                class="col-lg-8 d-flex flex-column justify-content-center align-items-center ob-login-left px-4 py-5">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}"
                    style="max-height:80px; max-width:90%;" onerror="this.style.display='none'">
                <p class="ob-login-left-title mt-4">
                    {{ __('auth_views.login_tagline', ['org' => config('app.name')]) }}
                </p>
            </aside>

            <section class="col-lg-4 d-flex align-items-center justify-content-center px-4 py-5 ob-login-right">
                <div class="ob-login-card">

                    @if ($mailDisabled ?? false)
                        <div class="mb-4">
                            <div class="ob-login-brand-title">{{ __('auth_views.reset_unavailable_title') }}</div>
                            <p class="ob-login-brand-sub mt-2">
                                {{ __('auth_views.reset_unavailable_body') }}
                            </p>
                        </div>
                    @elseif (isset($submitted) && $submitted)
                        <div class="mb-4">
                            <div class="ob-login-brand-title">{{ __('auth_views.reset_sent_title') }}</div>
                            <p class="ob-login-brand-sub mt-2">
                                {{ __('auth_views.reset_sent_body') }}
                            </p>
                            <p class="ob-login-brand-sub">{{ __('auth_views.reset_sent_spam') }}</p>
                        </div>
                        <a href="{{ route('login') }}" class="btn ob-login-btn">{{ __('auth_views.reset_back_btn') }}</a>
                    @else
                        <div class="mb-4">
                            <div class="ob-login-brand-title">{{ __('auth_views.reset_req_title') }}</div>
                            <p class="ob-login-brand-sub mt-1 mb-0">
                                {{ __('auth_views.reset_req_subtitle') }}
                            </p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger ob-login-alert mb-3" role="alert">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="mb-4">
                                <label for="recovery" class="form-label">{{ __('auth_views.reset_req_label') }}</label>
                                <input type="text" id="recovery" name="recovery"
                                    class="form-control ob-login-input @error('recovery') is-invalid @enderror"
                                    value="{{ old('recovery') }}" required autofocus autocomplete="username">
                                @error('recovery')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn ob-login-btn">
                                {{ __('auth_views.reset_req_btn') }}
                            </button>
                        </form>
                    @endif

                    <div class="ob-login-footer mt-3">
                        <a href="{{ route('login') }}" class="text-decoration-none"
                            style="font-size:var(--font-size-xs)">
                            <i class="fas fa-arrow-left me-1"></i> {{ __('auth_views.reset_back_link') }}
                        </a>
                    </div>

                </div>
            </section>
        </div>
    </div>
</body>

</html>