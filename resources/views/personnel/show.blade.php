@extends('layout.app')

@section('title', $personnel->P_NOM . ' ' . $personnel->P_PRENOM . ' — Personnel — ' . config('app.name'))

@push('styles')
<style>
.profile-header {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.profile-photo {
    width: 100px; height: 100px;
    border-radius: var(--radius);
    object-fit: cover;
    border: 2px solid var(--card-border);
    flex-shrink: 0;
}
.profile-meta { flex: 1; min-width: 0; }
.profile-name  { font-size: 1.3rem; font-weight: 700; margin: 0; }
.profile-sub   { color: var(--sidebar-border); font-size: var(--font-size-sm); margin: 2px 0 6px; }

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px 16px;
}
.info-item dt {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--sidebar-border);
    margin-bottom: 1px;
}
.info-item dd {
    font-size: var(--font-size-sm);
    margin: 0;
    word-break: break-word;
}

.section-title {
    font-size: var(--font-size-sm);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--accent);
    border-bottom: 1px solid var(--card-border);
    padding-bottom: 4px;
    margin: 20px 0 12px;
}
.section-title:first-child { margin-top: 0; }

.badge-personnel {
    display: inline-block; padding: 3px 10px; border-radius: 12px;
    font-size: 0.70rem; font-weight: 600; white-space: nowrap;
}
.badge-ben  { background:#FFCC33; color:#5a4000; }
.badge-ext  { background:#e2e8f0; color:#475569; }
.badge-pres { background:#E8D5F5; color:#6b21a8; }
.badge-int  { background:#dbeafe; color:#1e40af; }
.badge-actif   { background:#dcfce7; color:#166534; }
.badge-archive { background:#f1f5f9; color:#64748b; }
.badge-bloqued { background:#fee2e2; color:#991b1b; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 py-3" style="max-width: 900px;">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
            <li class="breadcrumb-item"><a href="{{ route('personnel.index') }}">Personnel</a></li>
            <li class="breadcrumb-item active">{{ $personnel->P_NOM }} {{ $personnel->P_PRENOM }}</li>
        </ol>
    </nav>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">

            {{-- ── Header ─────────────────────────────────────── --}}
            <div class="profile-header">
                <img src="{{ route('personnel.photo', $personnel) }}"
                     alt="Photo {{ $personnel->P_NOM }}"
                     class="profile-photo">

                <div class="profile-meta">
                    <p class="profile-name">
                        @php
                            $civMap = [1 => 'M.', 2 => 'Mme', 3 => 'Dr.', 4 => 'Pr.'];
                        @endphp
                        @if ($personnel->P_CIVILITE && isset($civMap[$personnel->P_CIVILITE]))
                            <span class="text-muted" style="font-weight:400;">{{ $civMap[$personnel->P_CIVILITE] }}</span>
                        @endif
                        {{ $personnel->P_NOM }}
                        @if ($personnel->P_NOM_NAISSANCE && $personnel->P_NOM_NAISSANCE !== $personnel->P_NOM)
                            <small class="text-muted">(née {{ $personnel->P_NOM_NAISSANCE }})</small>
                        @endif
                        {{ $personnel->P_PRENOM }}
                        @if ($personnel->P_PRENOM2)
                            <span class="text-muted" style="font-weight:400;">{{ $personnel->P_PRENOM2 }}</span>
                        @endif
                    </p>
                    <p class="profile-sub">
                        {{ $personnel->P_CODE }}
                        @if ($personnel->section)
                            &nbsp;·&nbsp; {{ $personnel->section->S_CODE }}
                        @endif
                    </p>

                    @php
                        $etat = (int) $personnel->GP_ID === -1 ? 'Bloqué'
                              : ((int) $personnel->P_OLD_MEMBER > 0 ? 'Archivé' : 'Actif');
                        $etatClass = match($etat) { 'Actif' => 'badge-actif', 'Archivé' => 'badge-archive', default => 'badge-bloqued' };
                        $statutMap = ['BEN' => ['Personnel bénévole','badge-ben'], 'EXT' => ['Personnel externe','badge-ext'], 'PRES' => ['Prestataire','badge-pres']];
                        [$statutLbl, $statutCls] = $statutMap[$personnel->P_STATUT] ?? [$personnel->P_STATUT, 'badge-int'];
                    @endphp

                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge-personnel {{ $statutCls }}">{{ $statutLbl }}</span>
                        <span class="badge-personnel {{ $etatClass }}">{{ $etat }}</span>
                        @if ($personnel->P_GRADE)
                            <img src="{{ route('personnel.grade_image', ['grade' => $personnel->P_GRADE]) }}"
                                 alt="{{ $personnel->P_GRADE }}" title="{{ $personnel->P_GRADE }}"
                                 style="height:22px;border-radius:3px;"
                                 onerror="this.outerHTML='<span style=\'font-size:.75rem;\'>' + '{{ e($personnel->P_GRADE) }}' + '</span>'">
                        @endif
                    </div>
                </div>

                <div class="d-flex gap-2 flex-shrink-0 noprint">
                    <a href="{{ route('personnel.edit', $personnel) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i> Modifier
                    </a>
                    <a href="{{ route('personnel.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            </div>

            {{-- ── Info sections ───────────────────────────────── --}}
            <p class="section-title">Coordonnées</p>
            <dl class="info-grid">
                <div class="info-item">
                    <dt>Email</dt>
                    <dd>
                        @if ($personnel->P_EMAIL)
                            <a href="mailto:{{ $personnel->P_EMAIL }}">{{ $personnel->P_EMAIL }}</a>
                        @else —
                        @endif
                    </dd>
                </div>
                <div class="info-item">
                    <dt>Téléphone</dt>
                    <dd>{{ $personnel->P_PHONE ?: '—' }}</dd>
                </div>
                <div class="info-item">
                    <dt>Portable</dt>
                    <dd>{{ $personnel->P_PHONE2 ?: '—' }}</dd>
                </div>
                <div class="info-item">
                    <dt>Adresse</dt>
                    <dd>
                        @if ($personnel->P_ADDRESS || $personnel->P_ZIP_CODE || $personnel->P_CITY)
                            {{ $personnel->P_ADDRESS }}<br>
                            {{ $personnel->P_ZIP_CODE }} {{ $personnel->P_CITY }}
                            @if ($personnel->P_PAYS)<br>{{ $personnel->P_PAYS }}@endif
                        @else —
                        @endif
                    </dd>
                </div>
            </dl>

            <p class="section-title">Informations personnelles</p>
            <dl class="info-grid">
                <div class="info-item">
                    <dt>Date de naissance</dt>
                    <dd>
                        {{ $personnel->P_BIRTHDATE?->format('d/m/Y') ?? '—' }}
                        @if ($personnel->P_BIRTHDATE)
                            <small class="text-muted">({{ $personnel->P_BIRTHDATE->diffInYears(now()) }} ans)</small>
                        @endif
                    </dd>
                </div>
                <div class="info-item">
                    <dt>Lieu de naissance</dt>
                    <dd>
                        {{ $personnel->P_BIRTHPLACE ?: '—' }}
                        @if ($personnel->P_BIRTH_DEP)
                            <span class="text-muted">({{ $personnel->P_BIRTH_DEP }})</span>
                        @endif
                    </dd>
                </div>
                <div class="info-item">
                    <dt>Date d'entrée</dt>
                    <dd>{{ $personnel->P_DATE_ENGAGEMENT?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                @if ($personnel->P_FIN)
                <div class="info-item">
                    <dt>Date de fin</dt>
                    <dd>{{ $personnel->P_FIN->format('d/m/Y') }}</dd>
                </div>
                @endif
                @if ($personnel->P_LICENCE)
                <div class="info-item">
                    <dt>Licence</dt>
                    <dd>
                        {{ $personnel->P_LICENCE }}
                        @if ($personnel->P_LICENCE_EXPIRY)
                            <br><small class="text-muted">Exp. {{ $personnel->P_LICENCE_EXPIRY->format('d/m/Y') }}</small>
                        @endif
                    </dd>
                </div>
                @endif
                <div class="info-item">
                    <dt>Profession</dt>
                    <dd>{{ $personnel->P_PROFESSION ?: '—' }}</dd>
                </div>
            </dl>

            @if ($personnel->P_RELATION_NOM || $personnel->P_RELATION_PRENOM || $personnel->P_RELATION_PHONE)
                <p class="section-title">Contact d'urgence</p>
                <dl class="info-grid">
                    <div class="info-item">
                        <dt>Nom</dt>
                        <dd>{{ trim($personnel->P_RELATION_PRENOM . ' ' . $personnel->P_RELATION_NOM) ?: '—' }}</dd>
                    </div>
                    <div class="info-item">
                        <dt>Téléphone</dt>
                        <dd>{{ $personnel->P_RELATION_PHONE ?: '—' }}</dd>
                    </div>
                    @if ($personnel->P_RELATION_MAIL)
                    <div class="info-item">
                        <dt>Email</dt>
                        <dd><a href="mailto:{{ $personnel->P_RELATION_MAIL }}">{{ $personnel->P_RELATION_MAIL }}</a></dd>
                    </div>
                    @endif
                </dl>
            @endif

            @if ($personnel->OBSERVATION)
                <p class="section-title">Notes</p>
                <p style="font-size:var(--font-size-sm);white-space:pre-wrap;">{{ $personnel->OBSERVATION }}</p>
            @endif

            <p class="section-title">Accès</p>
            <dl class="info-grid">
                <div class="info-item">
                    <dt>Dernière connexion</dt>
                    <dd>{{ $personnel->P_LAST_CONNECT?->format('d/m/Y H:i') ?? 'jamais' }}</dd>
                </div>
                <div class="info-item">
                    <dt>Groupe</dt>
                    <dd>{{ $personnel->groupe?->GP_DESCRIPTION ?? '—' }}</dd>
                </div>
                <div class="info-item">
                    <dt>Indicateurs</dt>
                    <dd>
                        @if ($personnel->P_HIDE)   <span class="badge bg-secondary me-1">Masqué</span> @endif
                        @if ($personnel->P_NOSPAM) <span class="badge bg-secondary me-1">No spam</span> @endif
                        @if ($personnel->NPAI)     <span class="badge bg-warning text-dark me-1">NPAI</span> @endif
                        @if ($personnel->SUSPENDU) <span class="badge bg-danger me-1">Suspendu</span> @endif
                        @if (! $personnel->P_HIDE && ! $personnel->P_NOSPAM && ! $personnel->NPAI && ! $personnel->SUSPENDU)
                            <span class="text-muted">—</span>
                        @endif
                    </dd>
                </div>
            </dl>

        </div>
    </div>
</div>
@endsection
