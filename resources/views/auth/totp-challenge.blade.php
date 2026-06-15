@extends('layout.app')

@section('title', 'Vérification en deux étapes — ' . config('app.name'))

@section('content')

<div class="d-flex justify-content-center align-items-start pt-5">
<div style="width:100%;max-width:400px;">

    <div class="ob-widget-card">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-mobile-alt me-1"></i> Vérification en deux étapes
            </div>
        </div>
        <div class="ob-widget-card-body">

            <p class="text-muted mb-4" style="font-size:var(--font-size-sm);">
                Saisissez le code à 6 chiffres affiché par votre application d'authentification
                (Google Authenticator, Authy…).
            </p>

            <form method="POST" action="{{ route('totp.challenge.verify') }}">
                @csrf

                <div class="mb-3">
                    <label for="code" class="form-label fw-semibold">Code TOTP</label>
                    <input type="text" id="code" name="code"
                           class="form-control form-control-lg text-center font-monospace @error('code') is-invalid @enderror"
                           inputmode="numeric" pattern="[0-9]*" maxlength="6"
                           autocomplete="one-time-code" autofocus
                           placeholder="000 000">
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-1"></i> Vérifier
                    </button>
                </div>

            </form>

            <hr>

            <details style="font-size:var(--font-size-sm);">
                <summary class="text-muted" style="cursor:pointer;">
                    Utiliser un code de récupération
                </summary>
                <form method="POST" action="{{ route('totp.challenge.verify') }}" class="mt-3">
                    @csrf
                    <div class="mb-2">
                        <input type="text" name="recovery_code"
                               class="form-control font-monospace @error('recovery_code') is-invalid @enderror"
                               placeholder="xxxx-xxxx-xxxx-xxxx"
                               autocomplete="off">
                        @error('recovery_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                        Utiliser ce code
                    </button>
                </form>
            </details>

            <div class="mt-3 text-center" style="font-size:var(--font-size-xs);">
                <a href="{{ route('login') }}" class="text-muted">
                    <i class="fas fa-arrow-left me-1"></i> Retour à la connexion
                </a>
            </div>

        </div>
    </div>

</div>
</div>

@endsection
