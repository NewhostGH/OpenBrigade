<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Mot de passe oublié — {{ config('app.name') }}</title>
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

                @if (isset($submitted) && $submitted)
                    <div class="mb-4">
                        <div class="ob-login-brand-title">Demande envoyée</div>
                        <p class="ob-login-brand-sub mt-2">
                            Si un compte correspondant à votre identifiant ou adresse e-mail existe,
                            vous recevrez un e-mail contenant un lien pour réinitialiser votre mot de passe.
                        </p>
                        <p class="ob-login-brand-sub">Vérifiez également votre dossier spam.</p>
                    </div>
                    <a href="{{ route('login') }}" class="btn ob-login-btn">Retour à la connexion</a>
                @else
                    <div class="mb-4">
                        <div class="ob-login-brand-title">Mot de passe oublié</div>
                        <p class="ob-login-brand-sub mt-1 mb-0">
                            Indiquez votre identifiant ou adresse e-mail pour recevoir un lien de réinitialisation.
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
                            <label for="recovery" class="form-label">Identifiant ou adresse e-mail</label>
                            <input type="text" id="recovery" name="recovery"
                                   class="form-control ob-login-input @error('recovery') is-invalid @enderror"
                                   value="{{ old('recovery') }}"
                                   required autofocus autocomplete="username">
                            @error('recovery')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn ob-login-btn">
                            Envoyer le lien
                        </button>
                    </form>
                @endif

                <div class="ob-login-footer mt-3">
                    <a href="{{ route('login') }}" class="text-decoration-none" style="font-size:var(--font-size-xs)">
                        <i class="fas fa-arrow-left me-1"></i> Retour à la connexion
                    </a>
                </div>

            </div>
        </section>
    </div>
</div>
</body>
</html>
