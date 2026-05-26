<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion - {{ config('app.name') }}</title>
    @vite('resources/css/app.css')
    <style>
        body {
            background: #f1f1f1;
            margin: 0;
            min-height: 100vh;
            font-family: Roboto, Arial, sans-serif;
        }

        .login-shell {
            min-height: 100vh;
        }

        .login-left {
            background: radial-gradient(circle at 15% 20%, #f8f9fb 0%, #eceff4 38%, #e2e7ef 100%);
            min-height: 260px;
        }

        .login-card {
            max-width: 360px;
            width: 100%;
        }

        .brand-title {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .soft-input {
            background: #ececec;
            border: 0;
            box-shadow: none;
            padding: .85rem 1rem;
        }

        .soft-input:focus {
            background: #dadada;
            box-shadow: none;
        }

        .btn-login {
            padding: .8rem 1.4rem;
            font-weight: 500;
        }

        .forgot-panel {
            display: none;
        }

        .forgot-on .signin-panel {
            display: none;
        }

        .forgot-on .forgot-panel {
            display: block;
        }

        .footer-note {
            font-size: .9rem;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container-fluid login-shell">
        <div class="row min-vh-100">
            <aside class="col-lg-8 d-flex flex-column justify-content-center align-items-center login-left px-4 py-5">
                <img src="{{ asset('images/logo.png') }}" alt="OpenBrigade" style="max-height:72px; max-width:90%;"
                    onerror="this.style.display='none'">
                <h1 class="h4 mt-4 text-center fw-semibold">Organisez personnel et activites avec OpenBrigade</h1>
            </aside>

            <section class="col-lg-4 d-flex align-items-center justify-content-center px-4 py-5 bg-white">
                <div id="authBox" class="login-card">
                    <div class="signin-panel">
                        <div class="mb-4">
                            <div class="brand-title">Bienvenue sur {{ config('app.name') }}</div>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if ($errors->has('login'))
                            <div class="alert alert-danger">{{ $errors->first('login') }}</div>
                        @endif

                        <form id="signinForm" method="POST" action="{{ route('login.attempt') }}" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="login" class="form-label">Identifiant ou adresse e-mail</label>
                                <input id="login" type="text" name="login"
                                    class="form-control soft-input @error('login') is-invalid @enderror"
                                    value="{{ old('login') }}" required autofocus autocomplete="username">
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <label for="password" class="form-label">Mot de passe</label>
                                    <a href="#" id="showForgot" class="text-decoration-none">Mot de passe oublie ?</a>
                                </div>
                                <input id="password" type="password" name="password"
                                    class="form-control soft-input @error('password') is-invalid @enderror" required
                                    autocomplete="current-password">
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Se souvenir de moi</label>
                            </div>

                            <button type="submit" class="btn btn-primary btn-login">Se connecter</button>
                        </form>
                    </div>

                    <div class="forgot-panel">
                        <div class="mb-3">
                            <div class="brand-title">Mot de passe oublie ?</div>
                            <p class="text-muted mt-2 mb-0">Renseignez votre identifiant ou votre e-mail pour lancer une
                                reinitialisation.</p>
                        </div>

                        <form id="forgotForm">
                            <div class="mb-3">
                                <label for="recovery" class="form-label">Identifiant ou e-mail</label>
                                <input id="recovery" type="text" class="form-control soft-input" required>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" id="showSignin" class="btn btn-secondary">Retour</button>
                                <button type="submit" class="btn btn-primary">Valider</button>
                            </div>
                        </form>
                    </div>

                    <div class="footer-note mt-4">
                        {{ date('Y') }} - OpenBrigade
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        (function () {
            var authBox = document.getElementById('authBox');
            var showForgot = document.getElementById('showForgot');
            var showSignin = document.getElementById('showSignin');
            var signinForm = document.getElementById('signinForm');
            var forgotForm = document.getElementById('forgotForm');

            showForgot.addEventListener('click', function (event) {
                event.preventDefault();
                authBox.classList.add('forgot-on');
            });

            showSignin.addEventListener('click', function () {
                authBox.classList.remove('forgot-on');
            });

            signinForm.addEventListener('submit', function (event) {
                var login = document.getElementById('login').value.trim();
                var password = document.getElementById('password').value.trim();
                if (!login || !password) {
                    event.preventDefault();
                    window.alert('Veuillez remplir tous les champs');
                }
            });

            forgotForm.addEventListener('submit', function (event) {
                event.preventDefault();
                window.alert('La reinitialisation de mot de passe sera migree dans une etape dediee.');
            });
        })();
    </script>
</body>

</html>