@extends('layout.app')

@section('title', "Modifier la charte — " . config('app.name'))

@section('content')

<div class="mx-3 mt-3">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb ob-breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.settings') }}">Administration</a></li>
            <li class="breadcrumb-item active">Modifier la charte</li>
        </ol>
    </nav>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">

            <div class="ob-widget-card">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title">
                        <i class="fas fa-file-contract me-1"></i> Texte de la charte
                    </div>
                    <a href="{{ route('account.charter') }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                        <i class="fas fa-eye me-1"></i> Aperçu
                    </a>
                </div>
                <div class="ob-widget-card-body">

                    <form method="POST" action="{{ route('admin.charter.save') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="charte_text" class="form-label">
                                Contenu HTML de la charte
                            </label>
                            <textarea id="charte_text" name="charte_text" rows="24"
                                      class="form-control font-monospace"
                                      style="font-size:var(--font-size-xs); resize:vertical;"
                                      placeholder="Laissez vide pour utiliser le texte par défaut généré automatiquement."
                            >{{ old('charte_text', $charteText) }}</textarea>
                            <div class="form-text">
                                HTML autorisé : <code>h5</code>, <code>p</code>, <code>ul</code>, <code>li</code>, <code>strong</code>, <code>em</code>, <code>small</code>.
                                Laissez vide pour revenir au texte par défaut.
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" id="force_reaccept" name="force_reaccept"
                                       value="1" class="form-check-input"
                                       {{ old('force_reaccept') ? 'checked' : '' }}>
                                <label for="force_reaccept" class="form-check-label fw-semibold">
                                    Demander la réacceptation à tous les utilisateurs
                                </label>
                            </div>
                            <div class="form-text ms-4">
                                Si coché, les utilisateurs qui ont accepté la charte avant cette sauvegarde
                                devront la relire et l'accepter de nouveau à leur prochaine connexion.
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Enregistrer
                            </button>
                            <a href="{{ route('account.charter') }}" class="btn btn-outline-secondary">
                                Annuler
                            </a>
                        </div>

                    </form>

                </div>
            </div>

        </div>

        <div class="col-lg-4">
            <div class="ob-widget-card">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title">
                        <i class="fas fa-info-circle me-1"></i> Informations
                    </div>
                </div>
                <div class="ob-widget-card-body" style="font-size:var(--font-size-sm);">

                    @if ($updatedAt)
                        <p>
                            <i class="fas fa-history me-1 text-muted"></i>
                            Dernière publication avec réacceptation obligatoire :
                            <strong>{{ \Carbon\Carbon::parse($updatedAt)->format('d/m/Y à H:i') }}</strong>
                        </p>
                        <hr>
                    @endif

                    <p class="text-muted mb-1">
                        <i class="fas fa-lightbulb me-1"></i>
                        Le texte par défaut est généré automatiquement à partir des paramètres
                        de l'organisation (nom, type, sections, syndicat).
                    </p>
                    <p class="text-muted mb-1">
                        <i class="fas fa-shield-alt me-1"></i>
                        La case « Demander la réacceptation » met à jour un horodatage de version.
                        Seuls les utilisateurs ayant accepté <em>avant</em> cette date seront bloqués ;
                        leurs acceptances précédentes ne sont pas effacées.
                    </p>
                    <p class="text-muted mb-0">
                        <i class="fas fa-redo me-1"></i>
                        Pour forcer immédiatement la réacceptation sans modifier le texte,
                        utilisez le bouton <em>Forcer la réacceptation</em> sur la page de la charte.
                    </p>

                </div>
            </div>
        </div>
    </div>

</div>

@endsection
