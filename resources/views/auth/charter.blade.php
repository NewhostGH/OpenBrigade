<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('auth_views.charter_page_title') }} — {{ config('app.name') }}</title>
    @vite('resources/css/app.css')
</head>

<body>
<div class="ob-charter-shell">
<div class="ob-charter-wrap">

    {{-- Header --}}
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}"
             style="max-height:64px; max-width:80%;" onerror="this.style.display='none'">
        <div class="mt-2" style="font-size:var(--font-size-lg); font-weight:600; color:var(--text-primary);">
            {{ $charteMeta['site'] }}
        </div>
    </div>

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
                {{ __('auth_views.charter_section_title') }}
            </div>
        </div>
        <div class="ob-widget-card-body">

            {{-- Charter text --}}
            <div style="max-height:52vh; overflow-y:auto; font-size:var(--font-size-sm); padding-right:.5rem;">

                @if ($charteText)
                    {!! $charteText !!}
                @else
                    <h5>{{ __('auth_views.charter_art1_title') }}</h5>
                    <p>{!! __('auth_views.charter_art1_body', ['site' => $charteMeta['site'], 'memberSuffix' => $charteMeta['memberSuffix'], 'orgType' => $charteMeta['orgType']]) !!}</p>

                    <h5>{{ __('auth_views.charter_art2_title') }}</h5>
                    <p>{{ __('auth_views.charter_art2_body') }}</p>

                    <h5>{{ __('auth_views.charter_art3_title') }}</h5>
                    <p>{!! __('auth_views.charter_art3_intro', ['site' => $charteMeta['site'], 'memberSuffix' => $charteMeta['memberSuffix'], 'orgType' => $charteMeta['orgType']]) !!}</p>
                    <ul>
                        <li>{{ __('auth_views.charter_art3_li_dispo') }}</li>
                        @if ($charteMeta['nbsections'] > 0)
                            <li>{{ __('auth_views.charter_art3_li_gardes') }}</li>
                        @endif
                        @if ($charteMeta['syndicate'] == 0)
                            <li>{{ __('auth_views.charter_art3_li_competences') }}</li>
                        @endif
                        <li>{{ __('auth_views.charter_art3_li_infos') }}</li>
                        <li>{{ __('auth_views.charter_art3_li_fiche') }}</li>
                        <li>{{ __('auth_views.charter_art3_li_vie', ['orgType' => $charteMeta['orgType']]) }}</li>
                    </ul>
                    <p><small>{{ __('auth_views.charter_art3_note') }}</small></p>

                    <h5>{{ __('auth_views.charter_art4_title') }}</h5>
                    <ul>
                        <li>{{ __('auth_views.charter_art4_li_nuire') }}</li>
                        <li>{{ __('auth_views.charter_art4_li_session') }}</li>
                        <li>{{ __('auth_views.charter_art4_li_navigateur') }}</li>
                        <li>{{ __('auth_views.charter_art4_li_comportement') }}</li>
                    </ul>

                    <h5>{{ __('auth_views.charter_art5_title') }}</h5>
                    <ul>
                        <li>{{ __('auth_views.charter_art5_li_regles') }}</li>
                        <li>{{ __('auth_views.charter_art5_li_confidentiel') }}</li>
                        <li>{{ __('auth_views.charter_art5_li_recommande') }}</li>
                    </ul>

                    <h5>{{ __('auth_views.charter_art6_title') }}</h5>
                    <ul>
                        <li>{{ __('auth_views.charter_art6_li_donnees') }}</li>
                        <li>{{ __('auth_views.charter_art6_li_divulgation_pre') }} <strong>{{ __('auth_views.charter_art6_li_divulgation_strong') }}</strong>.</li>
                        @if ($charteMeta['nbsections'] > 0)
                            <li>{{ __('auth_views.charter_art6_li_secret') }}</li>
                        @endif
                        <li>{{ __('auth_views.charter_art6_li_reseaux') }}</li>
                    </ul>

                    <h5>{{ __('auth_views.charter_art7_title') }}</h5>
                    <ul>
                        <li>{{ __('auth_views.charter_art7_li_loi') }}</li>
                        <li>{{ __('auth_views.charter_art7_li_traces') }}</li>
                    </ul>
                @endif

            </div>

            <hr class="mt-3">

            @if ($acceptDate)
                <div class="alert alert-success mb-3">
                    <i class="fas fa-check-circle me-1"></i>
                    {{ __('auth_views.charter_accepted_on', ['date' => \Carbon\Carbon::parse($acceptDate)->format('d/m/Y à H:i')]) }}
                </div>
                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-home me-1"></i> {{ __('auth_views.charter_back_dashboard') }}
                </a>

            @else

                <form method="POST" action="{{ route('account.charter.accept') }}" class="d-inline">
                    @csrf
                    <div class="form-check mb-3">
                        <input type="checkbox" id="checkAccept" class="form-check-input" required>
                        <label for="checkAccept" class="form-check-label">
                            {{ __('auth_views.charter_check_accept') }}
                        </label>
                    </div>
                    @if ($rgpdExists)
                        <div class="form-check mb-3">
                            <input type="checkbox" id="checkRgpd" class="form-check-input" required>
                            <label for="checkRgpd" class="form-check-label">
                                {{ __('auth_views.charter_check_rgpd') }}
                            </label>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-1"></i> {{ __('auth_views.charter_btn_accept') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('account.charter.reject') }}" class="d-inline ms-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger"
                        onclick="return confirm('{{ __('auth_views.charter_reject_confirm') }}')">
                        <i class="fas fa-times me-1"></i> {{ __('auth_views.charter_btn_reject') }}
                    </button>
                </form>

            @endif

        </div>
    </div>

    <div class="text-center mt-3" style="font-size:var(--font-size-xs); color:var(--text-muted-soft);">
        {{ config('app.name') }}
    </div>

</div>
</div>
</body>

</html>
