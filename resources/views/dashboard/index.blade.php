@extends('layout.app')

@section('title', 'Tableau de bord – ' . config('app.name'))

@section('content')
<div class="dashboard-wrap">

    {{-- ── Stats KPI bar ─────────────────────────────────────────────────── --}}
    @include('dashboard.widgets.stats')

    {{-- ── Alert banners ────────────────────────────────────────────────── --}}
    @if ($passwordExpiry)
        <div class="dash-alert {{ $passwordExpiry['expired'] ? 'dash-alert-danger' : 'dash-alert-warning' }}">
            <i class="fas fa-key"></i>
            @if ($passwordExpiry['expired'])
                Votre mot de passe a <strong>expiré</strong>.
            @else
                Votre mot de passe expire dans <strong>{{ $passwordExpiry['days'] }} jours</strong>
                (le {{ $passwordExpiry['expiry'] }}).
            @endif
            <a href="{{ url('/legacy/change_password.php') }}" class="ms-2">Changer maintenant</a>
        </div>
    @endif

    @if (!empty($competenceAlerts))
        <div class="dash-alert dash-alert-warning">
            <i class="fas fa-certificate"></i>
            Expiration prochaine de vos compétences :
            @foreach ($competenceAlerts as $c)
                <strong>{{ $c->TYPE }}</strong> expire dans {{ $c->NB }} jours
                ({{ $c->Q_EXPIRATION }})@if (!$loop->last), @endif
            @endforeach
            &mdash;
            <a href="{{ url('/legacy/upd_personnel.php?pompier=' . auth()->user()->P_ID . '&tab=2') }}">
                Voir le détail
            </a>
        </div>
    @endif

    {{-- ── 3-column widget grid ──────────────────────────────────────────── --}}
    <div class="dash-columns">

        {{-- Column 1 --}}
        <div>
            @include('dashboard.widgets.welcome')
            @include('dashboard.widgets.events')
        </div>

        {{-- Column 2 --}}
        <div>
            @include('dashboard.widgets.duty')
            @include('dashboard.widgets.infos')
            @include('dashboard.widgets.mc')
            @include('dashboard.widgets.birthdays')
            @include('dashboard.widgets.remplacements')
        </div>

        {{-- Column 3 --}}
        <div>
            @include('dashboard.widgets.vehicles')
            @include('dashboard.widgets.consumables')
            @include('dashboard.widgets.cp')
            @include('dashboard.widgets.horaires')
            @include('dashboard.widgets.training')
            @include('dashboard.widgets.about')
        </div>

    </div>
</div>
@endsection
