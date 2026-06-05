<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Connexion — {{ config('app.name') }}</title>
    @vite('resources/css/app.css')
</head>

<body class="ob-login-body">
<div class="container-fluid ob-login-shell">
    <div class="row min-vh-100">

        {{-- ── Left branding panel ─────────────────────────────────────────── --}}
        <aside class="col-lg-8 d-flex flex-column justify-content-center align-items-center ob-login-left px-4 py-5">
            <img src="{{ asset('images/logo.png') }}"
                 alt="{{ config('app.name') }}"
                 style="max-height:80px; max-width:90%;"
                 onerror="this.style.display='none'">
            <p class="ob-login-left-title mt-4">
                Organisez le personnel et les activités avec {{ config('app.name') }}
            </p>
        </aside>

        {{-- ── Right sign-in panel ─────────────────────────────────────────── --}}
        <section class="col-lg-4 d-flex align-items-center justify-content-center px-4 py-5 ob-login-right">
            <div id="authBox" class="ob-login-card">

                {{-- Sign-in form --}}
                <div class="ob-login-signin-panel">
                    <div class="mb-4">
                        <div class="ob-login-brand-title">Bienvenue</div>
                        <div class="ob-login-brand-sub">Connectez-vous à {{ config('app.name') }}</div>
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
                            <label for="login" class="form-label">Identifiant ou adresse e-mail</label>
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
                                <label for="password" class="form-label mb-0">Mot de passe</label>
                                <a href="#" id="showForgot"
                                   class="text-decoration-none"
                                   style="font-size:var(--font-size-xs)">
                                    Mot de passe oublié ?
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
                                Se souvenir de moi
                            </label>
                        </div>

                        {{-- Inline validation message (shown by JS without page reload) --}}
                        <div id="signinError" class="alert alert-danger ob-login-alert mb-3 d-none" role="alert">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            Veuillez remplir l'identifiant et le mot de passe.
                        </div>

                        <button type="submit" class="btn ob-login-btn">Se connecter</button>
                    </form>
                </div>

                {{-- Forgot-password panel --}}
                <div class="ob-login-forgot-panel">
                    <div class="mb-3">
                        <div class="ob-login-brand-title">Mot de passe oublié ?</div>
                        <p class="ob-login-brand-sub mt-1 mb-0">
                            Contactez votre administrateur pour réinitialiser votre mot de passe,
                            {{-- TODO: Migrate code --}}
                            ou utilisez la page <a href="{{ url('/legacy/change_password.php') }}">Changer mon mot de passe</a>
                            si vous êtes déjà connecté.
                        </p>
                    </div>

                    <button type="button" id="showSignin" class="btn btn-secondary btn-sm mt-2">
                        <i class="fas fa-arrow-left me-1"></i> Retour
                    </button>
                </div>

                <div class="ob-login-footer">
                    {{ date('Y') }} — {{ config('app.name') }}
                </div>

            </div>
        </section>
    </div>
</div>

@vite('resources/js/app.js')
<script>
(function () {
    var authBox    = document.getElementById('authBox');
    var showForgot = document.getElementById('showForgot');
    var showSignin = document.getElementById('showSignin');
    var signinForm = document.getElementById('signinForm');
    var signinErr  = document.getElementById('signinError');

    showForgot.addEventListener('click', function (e) {
        e.preventDefault();
        authBox.classList.add('ob-login-forgot-on');
    });

    showSignin.addEventListener('click', function () {
        authBox.classList.remove('ob-login-forgot-on');
    });

    signinForm.addEventListener('submit', function (e) {
        var login    = document.getElementById('login').value.trim();
        var password = document.getElementById('password').value.trim();
        if (!login || !password) {
            e.preventDefault();
            signinErr.classList.remove('d-none');
            (login ? document.getElementById('password') : document.getElementById('login')).focus();
        } else {
            signinErr.classList.add('d-none');
        }
    });
}());
</script>
</body>
</html>
