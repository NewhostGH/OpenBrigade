@extends('layout.app')

@section('title', 'Tableau de bord – ' . config('app.name'))

@section('content')
<div class="ob-dash-wrap">

    {{-- ── Stats KPI bar ─────────────────────────────────────────────────── --}}
    @include('dashboard.widgets.stats')

    {{-- ── Alert banners ────────────────────────────────────────────────── --}}
    @if ($passwordExpiry)
        <div class="ob-dash-alert {{ $passwordExpiry['expired'] ? 'ob-dash-alert-danger' : 'ob-dash-alert-warning' }}">
            <i class="fas fa-key"></i>
            @if ($passwordExpiry['expired'])
                Votre mot de passe a <strong>expiré</strong>.
            @else
                Votre mot de passe expire dans <strong>{{ $passwordExpiry['days'] }} jours</strong>
                (le {{ $passwordExpiry['expiry'] }}).
            @endif
            {{-- TODO: Migrate code — change_password.php has no native route yet --}}
            <a href="{{ url('/legacy/change_password.php') }}" class="ms-2">Changer maintenant</a>
        </div>
    @endif

    @if (!empty($competenceAlerts))
        <div class="ob-dash-alert ob-dash-alert-warning">
            <i class="fas fa-certificate"></i>
            Expiration prochaine de vos compétences :
            @foreach ($competenceAlerts as $c)
                <strong>{{ $c->TYPE }}</strong> expire dans {{ $c->NB }} jours
                ({{ $c->Q_EXPIRATION }})@if (!$loop->last), @endif
            @endforeach
            &mdash;
            <a href="{{ route('personnel.qualifications', auth()->user()->P_ID) }}">
                Voir le détail
            </a>
        </div>
    @endif

    {{-- ── 3-column widget grid ──────────────────────────────────────────── --}}
    <div class="ob-dash-columns">

        {{-- Column 1: profil + astreinte + section + plannings + alertes financières --}}
        <div>
            @include('dashboard.widgets.welcome')
            @include('dashboard.widgets.duty')
            @include('dashboard.widgets.birthdays')
            @include('dashboard.widgets.horaires')
            @include('dashboard.widgets.unpaid')
            @include('dashboard.widgets.stats-missing')
        </div>

        {{-- Column 2: activités personnelles + congés + matériel + remplacements + consignes --}}
        <div>
            @include('dashboard.widgets.mes-activites')
            @include('dashboard.widgets.cp')
            @include('dashboard.widgets.vehicles')
            @include('dashboard.widgets.consumables')
            @include('dashboard.widgets.remplacements')
            @include('dashboard.widgets.replacement-requests')
            @include('dashboard.widgets.infos')
        </div>

        {{-- Column 3: main courante + frais + calendrier + formation + à propos --}}
        <div>
            @include('dashboard.widgets.mc')
            @include('dashboard.widgets.expenses')
            @include('dashboard.widgets.events')
            @include('dashboard.widgets.training')
            @include('dashboard.widgets.about')
        </div>

    </div>
</div>
@endsection
