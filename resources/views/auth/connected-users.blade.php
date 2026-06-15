@extends('layout.app')

@section('title', 'Utilisateurs connectés — ' . config('app.name'))

@section('content')

    <x-ob-breadcrumb :items="[
            ['label' => 'Administration'],
            ['label' => 'Connexions'],
        ]" />

    <div class="mx-3 mt-3">
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-users me-1"></i> Utilisateurs connectés
                </div>
                <div class="ob-widget-card-actions">
                    <span class="text-muted" style="font-size:var(--font-size-sm);">
                        {{ $connected->count() }} utilisateur(s) actif(s) ces 10&nbsp;dernières minutes
                    </span>
                    <a href="{{ route('account.connected-users') }}" class="btn btn-sm btn-outline-secondary ms-2">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </div>

            @if ($connected->isEmpty())
                <div class="ob-widget-card-body">
                    <div class="ob-widget-empty">Aucun utilisateur connecté en ce moment.</div>
                </div>
            @else
                <div class="ob-widget-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" id="connectedTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px;"></th>
                                    <th>Nom</th>
                                    <th>Section</th>
                                    <th>Système</th>
                                    <th>Navigateur</th>
                                    <th>Connexion</th>
                                    <th>Dernière activité</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($connected as $row)
                                    @php
                                        $os = $row->A_OS ?? '';
                                        $br = $row->A_BROWSER ?? '';
                                        $isMobile = in_array($os, ['Android', 'iOS']);

                                        $osIcon = $isMobile ? 'fas fa-mobile-alt' : 'fas fa-desktop';
                                        $osColor = $isMobile ? '#333' : '#888';

                                        $brIcon = match (true) {
                                            str_contains($br, 'Chrome') => 'fab fa-chrome',
                                            str_contains($br, 'Firefox') => 'fab fa-firefox',
                                            str_contains($br, 'Edge') => 'fab fa-edge',
                                            str_contains($br, 'Safari') => 'fab fa-safari',
                                            default => 'fab fa-internet-explorer',
                                        };
                                        $brColor = match (true) {
                                            str_contains($br, 'Chrome') => '#29a744',
                                            str_contains($br, 'Firefox') => '#ff6600',
                                            str_contains($br, 'Edge') => '#3333ff',
                                            str_contains($br, 'Safari') => '#b3b3ff',
                                            default => '#3399ff',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="align-middle text-center">
                                            <a href="{{ route('personnel.show', $row->P_ID) }}">
                                                @if ($row->P_PHOTO && file_exists(public_path('trombi/' . $row->P_PHOTO)))
                                                    <img src="{{ asset('trombi/' . $row->P_PHOTO) }}" class="rounded"
                                                        style="height:34px; width:34px; object-fit:cover;" alt="">
                                                @else
                                                    <i class="fas fa-user-circle fa-2x text-muted"></i>
                                                @endif
                                            </a>
                                        </td>
                                        <td class="align-middle">
                                            <a href="{{ route('personnel.show', $row->P_ID) }}"
                                                class="text-decoration-none fw-semibold">
                                                {{ strtoupper($row->P_NOM) }} {{ $row->P_PRENOM }}
                                            </a>
                                        </td>
                                        <td class="align-middle">
                                            <span class="ob-badge ob-badge-archive">{{ $row->S_CODE }}</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <i class="{{ $osIcon }}" style="color:{{ $osColor }};" title="{{ $os }}"></i>
                                        </td>
                                        <td class="align-middle text-center">
                                            <i class="{{ $brIcon }}" style="color:{{ $brColor }};" title="{{ $br }}"></i>
                                        </td>
                                        <td class="align-middle" style="font-size:var(--font-size-sm); white-space:nowrap;">
                                            {{ $row->A_DEBUT ? \Carbon\Carbon::parse($row->A_DEBUT)->format('H:i') : '—' }}
                                        </td>
                                        <td class="align-middle" style="font-size:var(--font-size-sm); white-space:nowrap;">
                                            {{ $row->A_FIN ? \Carbon\Carbon::parse($row->A_FIN)->format('H:i') : '—' }}
                                        </td>
                                        <td class="align-middle text-muted" style="font-size:var(--font-size-sm);">
                                            {{ $row->A_IP ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection