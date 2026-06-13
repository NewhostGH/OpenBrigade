<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Nouveau mot de passe — {{ config('app.name') }}</title>
    @vite('resources/css/app.css')
</head>

<body class="ob-login-body">
<div class="container-fluid ob-login-shell">
    <div class="row min-vh-100">

        <aside class="col-lg-8 d-flex flex-column justify-content-center align-items-center ob-login-left px-4 py-5">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}"
                 style="max-height:80px; max-width:90%;"
                 onerror="this.style.display='none'">
            <p class="ob-login-left-title mt-4">
                Organisez le personnel et les activités avec {{ config('app.name') }}
            </p>
        </aside>

        <section class="col-lg-4 d-flex align-items-center justify-content-center px-4 py-5 ob-login-right">
            <div class="ob-login-card">

                @if ($valid)
                    <div class="mb-4">
                        <div class="ob-login-brand-title">Nouveau mot de passe</div>
                        <p class="ob-login-brand-sub mt-2">
                            Votre mot de passe temporaire vous a été envoyé par e-mail.
                        </p>
                        @if ($newPass)
                            <div class="alert alert-info mt-3" style="font-family:monospace; font-size:1.1em;">
                                {{ $newPass }}
                            </div>
                            <p class="ob-login-brand-sub" style="font-size:var(--font-size-xs);">
                                Ce mot de passe expire immédiatement — vous devrez en choisir un nouveau
                                lors de votre prochaine connexion.
                            </p>
                        @endif
                    </div>
                @else
                    <div class="mb-4">
                        <div class="ob-login-brand-title">Lien invalide</div>
                        <p class="ob-login-brand-sub mt-2">
                            Ce lien de réinitialisation est invalide ou a expiré (validité : 24 h).
                            Faites une nouvelle demande si nécessaire.
                        </p>
                    </div>
                @endif

                <a href="{{ route('login') }}" class="btn ob-login-btn">
                    <i class="fas fa-sign-in-alt me-1"></i> Se connecter
                </a>

                <div class="ob-login-footer mt-3">
                    @if (! $valid)
                        <a href="{{ route('password.request') }}" class="text-decoration-none"
                           style="font-size:var(--font-size-xs);">
                            Nouvelle demande
                        </a>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>
</body>
</html>
