@extends('layout.app')

@section('title', "Conditions d'utilisation — " . config('app.name'))

@section('content')

<div class="mx-3 mt-3">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="ob-widget-card">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title">
                        <i class="fas fa-file-contract me-1"></i>
                        Conditions d'utilisation — {{ $charteMeta['site'] }}
                    </div>
                </div>
                <div class="ob-widget-card-body">

                    <div class="ob-charte-text" style="max-height:55vh; overflow-y:auto; font-size:var(--font-size-sm); padding-right:.5rem;">

                        <h5>Article 1 : Finalité du document</h5>
                        <p>Le présent document définit les principales règles d'usage du site
                        «&nbsp;{{ $charteMeta['site'] }}&nbsp;» mis à disposition du personnel
                        {{ $charteMeta['memberSuffix'] }}{{ $charteMeta['orgType'] }}.</p>

                        <h5>Article 2 : Domaine d'application</h5>
                        <p>Il s'applique à toutes les personnes explicitement autorisées à utiliser le dit site
                        et qui disposent officiellement des clés personnelles d'accès.</p>

                        <h5>Article 3 : Cadre d'utilisation</h5>
                        <p>Le site «&nbsp;{{ $charteMeta['site'] }}&nbsp;» a pour vocation de permettre à l'ensemble
                        du personnel{{ $charteMeta['memberSuffix'] }} {{ $charteMeta['orgType'] }} de :</p>
                        <ul>
                            <li>saisir ses disponibilités ou indisponibilités mensuelles,</li>
                            @if ($charteMeta['nbsections'] > 0)
                                <li>consulter le tableau de gardes mensuelles,</li>
                            @endif
                            @if ($charteMeta['syndicate'] == 0)
                                <li>visualiser ses compétences opérationnelles,</li>
                            @endif
                            <li>prendre connaissance des différentes informations ou consignes,</li>
                            <li>mettre à jour sa fiche de renseignements personnels,</li>
                            <li>s'informer sur la vie {{ $charteMeta['orgType'] }}.</li>
                        </ul>
                        <p><small>Cette liste est non exhaustive ; l'administrateur du site peut à tout moment la faire évoluer.</small></p>

                        <h5>Article 4 : Règles d'utilisation</h5>
                        <ul>
                            <li>L'utilisateur s'engage à ne pas effectuer d'opérations pouvant nuire au bon fonctionnement du site.</li>
                            <li>L'utilisateur est seul responsable de sa session et s'engage à se déconnecter après chaque utilisation.</li>
                            <li>L'utilisateur s'engage à ne pas accepter l'enregistrement des mots de passe par le navigateur.</li>
                            <li>L'utilisateur s'engage à faire preuve d'un comportement exemplaire lors de l'usage de ce site.</li>
                        </ul>

                        <h5>Article 5 : Compte utilisateur et mot de passe</h5>
                        <ul>
                            <li>Chaque utilisateur doit définir un mot de passe en respectant les règles de sécurité du site.</li>
                            <li>Un compte utilisateur est strictement personnel et confidentiel. L'utilisateur ne doit en aucun cas communiquer son mot de passe.</li>
                            <li>Il est recommandé de ne pas utiliser le même mot de passe que sur d'autres applications.</li>
                        </ul>

                        <h5>Article 6 : Confidentialité</h5>
                        <ul>
                            <li>Les données du site ne doivent en aucun cas être utilisées en dehors du cadre pour lequel elles sont destinées.</li>
                            <li>La divulgation des données du site à des tiers est <strong>STRICTEMENT INTERDITE</strong>.</li>
                            @if ($charteMeta['nbsections'] > 0)
                                <li>L'article 226-13/14 du code de procédure pénale soumet tout sapeur-pompier au secret professionnel et médical.</li>
                            @endif
                            <li>Toute transmission d'information relative au service via les réseaux sociaux est strictement interdite.</li>
                        </ul>

                        <h5>Article 7 : Informatique et liberté</h5>
                        <ul>
                            <li>Conformément à la Loi Informatique et Libertés du 6 janvier 1978, l'utilisateur dispose d'un droit d'accès, de modification et de suppression des données personnelles le concernant.</li>
                            <li>Les connexions des utilisateurs ainsi que les différentes actions effectuées sur le site sont tracées.</li>
                        </ul>

                    </div>

                    <hr class="mt-3">

                    @if ($acceptDate)
                        <div class="alert alert-success mb-3">
                            <i class="fas fa-check-circle me-1"></i>
                            Vous avez accepté ces conditions le {{ \Carbon\Carbon::parse($acceptDate)->format('d/m/Y à H:i') }}.
                        </div>

                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-home me-1"></i> Retour au tableau de bord
                        </a>

                        @if ($canReset)
                            <form method="POST" action="{{ route('account.charter.reset') }}" class="d-inline ms-2">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning btn-sm"
                                    onclick="return confirm('Forcer tous les utilisateurs à réaccepter la charte ?')">
                                    <i class="fas fa-redo me-1"></i> Forcer la réacceptation
                                </button>
                            </form>
                        @endif

                    @else
                        <form method="POST" action="{{ route('account.charter.accept') }}" class="d-inline">
                            @csrf
                            <div class="form-check mb-3">
                                <input type="checkbox" id="checkAccept" class="form-check-input" required>
                                <label for="checkAccept" class="form-check-label">
                                    J'ai lu et j'accepte les conditions d'utilisation et je m'engage à les respecter.
                                </label>
                            </div>
                            @if ($rgpdExists)
                                <div class="form-check mb-3">
                                    <input type="checkbox" id="checkRgpd" class="form-check-input" required>
                                    <label for="checkRgpd" class="form-check-label">
                                        J'accepte le Règlement Général sur la Protection des Données (RGPD).
                                    </label>
                                </div>
                            @endif
                            <button type="submit" class="btn btn-primary" id="btnAccept">
                                <i class="fas fa-check me-1"></i> Accepter et continuer
                            </button>
                        </form>

                        <form method="POST" action="{{ route('account.charter.reject') }}" class="d-inline ms-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger"
                                onclick="return confirm('Refuser les conditions entraînera votre déconnexion. Continuer ?')">
                                <i class="fas fa-times me-1"></i> Refuser et se déconnecter
                            </button>
                        </form>
                    @endif

                </div>
            </div>

        </div>
    </div>
</div>

@endsection
