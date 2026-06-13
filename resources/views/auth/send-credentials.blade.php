@extends('layout.app')

@section('title', 'Envoyer identifiants — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Personnel', 'url' => route('personnel.index')],
    ['label' => strtoupper($personnel->P_NOM) . ' ' . $personnel->P_PRENOM, 'url' => route('personnel.show', $personnel)],
    ['label' => 'Envoyer identifiants'],
]"/>

<div class="mx-3 mt-3">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="ob-widget-card">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title">
                        <i class="fas fa-key me-1"></i>
                        Identifiants — {{ strtoupper($personnel->P_NOM) }} {{ $personnel->P_PRENOM }}
                    </div>
                </div>
                <div class="ob-widget-card-body">

                    @if ($mode === null)
                        {{-- Step 1: choose mode --}}
                        <p class="mb-3">
                            Un nouveau mot de passe temporaire va être généré pour
                            <strong>{{ strtoupper($personnel->P_NOM) }} {{ $personnel->P_PRENOM }}</strong>.
                            L'utilisateur devra le changer à sa prochaine connexion.
                        </p>

                        <dl class="ob-info-grid mb-4">
                            <div class="ob-info-item">
                                <dt>Identifiant</dt>
                                <dd>{{ $personnel->P_CODE ?? '—' }}</dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>E-mail enregistré</dt>
                                <dd>
                                    @if ($personnel->P_EMAIL)
                                        {{ $personnel->P_EMAIL }}
                                    @else
                                        <span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i> Aucun e-mail enregistré</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        <form method="POST" action="{{ route('personnel.send-credentials', $personnel) }}">
                            @csrf
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" name="mode" value="manual" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i> Mode manuel
                                    <small class="d-block fw-normal">Afficher le mot de passe ici</small>
                                </button>
                                <button type="submit" name="mode" value="auto" class="btn btn-outline-primary"
                                    @if (! $personnel->P_EMAIL) disabled title="Aucun e-mail enregistré" @endif>
                                    <i class="fas fa-envelope me-1"></i> Envoi automatique
                                    <small class="d-block fw-normal">Envoyer par e-mail</small>
                                </button>
                            </div>
                        </form>

                    @elseif ($mode === 'manual')
                        {{-- Step 2: manual result --}}
                        <div class="alert alert-success mb-3">
                            <i class="fas fa-check-circle me-1"></i>
                            Le mot de passe a été régénéré avec succès.
                        </div>

                        <p>Communiquez les informations suivantes à
                        <strong>{{ strtoupper($personnel->P_NOM) }} {{ $personnel->P_PRENOM }}</strong> :</p>

                        <dl class="ob-info-grid mb-4">
                            <div class="ob-info-item">
                                <dt>Identifiant</dt>
                                <dd>
                                    <code class="user-select-all">{{ $personnel->P_CODE ?? '—' }}</code>
                                </dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>Mot de passe temporaire</dt>
                                <dd>
                                    <code class="user-select-all">{{ $newPass }}</code>
                                </dd>
                            </div>
                            @if ($personnel->P_PHONE)
                                <div class="ob-info-item">
                                    <dt>Téléphone</dt>
                                    <dd><a href="tel:{{ $personnel->P_PHONE }}">{{ $personnel->P_PHONE }}</a></dd>
                                </div>
                            @endif
                            @if ($personnel->P_EMAIL)
                                <div class="ob-info-item">
                                    <dt>E-mail</dt>
                                    <dd><a href="mailto:{{ $personnel->P_EMAIL }}">{{ $personnel->P_EMAIL }}</a></dd>
                                </div>
                            @endif
                        </dl>

                        <p class="text-muted" style="font-size:var(--font-size-sm);">
                            Ce mot de passe expire immédiatement — l'utilisateur devra en choisir un nouveau à la prochaine connexion.
                        </p>

                    @elseif ($mode === 'auto')
                        {{-- Step 2: auto result --}}
                        @if ($sent ?? false)
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-envelope me-1"></i>
                                Identifiants envoyés par e-mail à {{ $personnel->P_EMAIL }}.
                            </div>
                        @else
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Le mot de passe a été régénéré, mais l'envoi automatique par e-mail n'est pas encore disponible.
                                {{-- TODO: COMM — wire up NotificationService email sending --}}
                                Communiquez le mot de passe temporaire manuellement.
                            </div>
                            <p>
                                Mot de passe temporaire pour
                                <strong>{{ strtoupper($personnel->P_NOM) }} {{ $personnel->P_PRENOM }}</strong> :
                            </p>
                            <p><code class="user-select-all fs-5">{{ $newPass }}</code></p>
                        @endif
                    @endif

                    <hr class="mt-4">
                    <a href="{{ route('personnel.show', $personnel) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Retour à la fiche
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
