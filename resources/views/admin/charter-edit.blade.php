@extends('layout.app')

@section('title', "Modifier la charte — " . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Sécurité', 'url' => route('admin.security', ['tab' => 'charter'])],
    ['label' => 'Charte'],
]"/>

<div class="mx-3 mt-3">
<div class="row g-3">

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

                <form method="POST" action="{{ route('admin.security.charter.save') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="charte_text" class="form-label">Contenu HTML</label>
                        <textarea id="charte_text" name="charte_text" rows="24"
                                  class="form-control font-monospace"
                                  style="font-size:var(--font-size-xs); resize:vertical;"
                                  placeholder="Laissez vide pour utiliser le texte par défaut."
                        >{{ old('charte_text', $charteText) }}</textarea>
                        <div class="form-text">
                            Tags autorisés : <code>h5</code> <code>p</code> <code>ul</code> <code>li</code>
                            <code>strong</code> <code>em</code> <code>small</code>.
                            Vider le champ restaure le texte généré automatiquement.
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" id="force_reaccept" name="force_reaccept"
                                   value="1" class="form-check-input"
                                   {{ old('force_reaccept') ? 'checked' : '' }}>
                            <label for="force_reaccept" class="form-check-label fw-semibold">
                                Publier une nouvelle version (demander la réacceptation)
                            </label>
                        </div>
                        <div class="form-text ms-4">
                            Met à jour l'horodatage de version. Les utilisateurs ayant accepté avant cette
                            date devront réaccepter à leur prochaine connexion.
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>
                        <a href="{{ route('admin.security', ['tab' => 'charter']) }}" class="btn btn-outline-secondary">
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
                    <i class="fas fa-info-circle me-1"></i> À propos
                </div>
            </div>
            <div class="ob-widget-card-body" style="font-size:var(--font-size-sm);">

                @if ($updatedAt)
                    <p>
                        <i class="fas fa-history me-1 text-muted"></i>
                        Dernière publication&nbsp;:
                        <strong>{{ \Carbon\Carbon::parse($updatedAt)->format('d/m/Y à H:i') }}</strong>
                    </p>
                    <hr>
                @endif

                <p class="text-muted mb-2">
                    <i class="fas fa-lightbulb me-1"></i>
                    Sans texte personnalisé, la charte est générée automatiquement depuis les paramètres
                    de l'organisation.
                </p>
                <p class="text-muted mb-0">
                    <i class="fas fa-shield-alt me-1"></i>
                    Cocher «&nbsp;Publier une nouvelle version&nbsp;» enregistre l'horodatage de publication.
                    Seuls les utilisateurs ayant accepté <em>avant</em> cette date seront invités à réaccepter.
                    Leurs acceptances précédentes ne sont pas effacées.
                </p>

            </div>
        </div>
    </div>

</div>
</div>

@endsection
