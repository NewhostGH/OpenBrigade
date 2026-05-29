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

            {{-- ── Compétences ───────────────────────────────────────── --}}
            @php
                $today   = now()->toDateString();
                $warn30  = now()->addDays(30)->toDateString();
            @endphp

            <p class="section-title d-flex justify-content-between align-items-center">
                <span>Compétences</span>
                <button type="button" class="btn btn-sm btn-success noprint"
                    data-bs-toggle="modal" data-bs-target="#qualModal"
                    onclick="openQualModal(null)">
                    <i class="fas fa-plus me-1"></i> Ajouter
                </button>
            </p>

            @if ($personnel->qualifications->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-sm table-hover" style="font-size:var(--font-size-sm);">
                        <thead>
                            <tr>
                                <th>Compétence</th>
                                <th>Valeur</th>
                                <th>Expiration</th>
                                <th class="noprint" style="width:80px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($personnel->qualifications->sortBy('poste.TYPE') as $qual)
                                @php
                                    $exp = $qual->Q_EXPIRATION?->toDateString();
                                    $status = match(true) {
                                        $exp !== null && $exp < $today  => 'expired',
                                        $exp !== null && $exp <= $warn30 => 'expiring',
                                        default => 'ok',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        {{ $qual->poste?->TYPE ?? '?' }}
                                        @if ($qual->poste?->DESCRIPTION)
                                            <small class="text-muted">— {{ $qual->poste->DESCRIPTION }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $qual->Q_VAL ?: '—' }}</td>
                                    <td>
                                        @if ($qual->Q_EXPIRATION)
                                            @php
                                                $cls = match($status) {
                                                    'expired'  => 'text-danger fw-bold',
                                                    'expiring' => 'text-warning fw-bold',
                                                    default    => '',
                                                };
                                            @endphp
                                            <span class="{{ $cls }}">
                                                {{ $qual->Q_EXPIRATION->format('d/m/Y') }}
                                                @if ($status === 'expired') <i class="fas fa-exclamation-triangle ms-1" title="Expirée"></i>
                                                @elseif ($status === 'expiring') <i class="fas fa-clock ms-1" title="Expiration proche"></i>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end noprint">
                                        <button type="button"
                                            class="btn btn-xs btn-light py-0 px-1 me-1"
                                            title="Modifier"
                                            onclick="openQualModal({
                                                ps_id: {{ $qual->PS_ID }},
                                                q_val: {{ json_encode($qual->Q_VAL ?? '') }},
                                                q_exp: {{ json_encode($qual->Q_EXPIRATION?->format('Y-m-d') ?? '') }},
                                                label: {{ json_encode(($qual->poste?->TYPE ?? '') . (($qual->poste?->DESCRIPTION) ? ' — ' . $qual->poste->DESCRIPTION : '')) }}
                                            })">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST"
                                            action="{{ route('personnel.qualification.destroy', [$personnel, $qual->PS_ID]) }}"
                                            class="d-inline"
                                            onsubmit="return confirm('Supprimer cette compétence ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-light py-0 px-1 text-danger"
                                                title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted small">Aucune compétence enregistrée.</p>
            @endif

            {{-- Add/Edit qualification modal --}}
            <div class="modal fade" id="qualModal" tabindex="-1" aria-labelledby="qualModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="qualModalLabel" style="font-size:var(--font-size-base);">
                                Compétence
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="qualForm" method="POST">
                            @csrf
                            <span id="qualMethodField"></span>
                            <div class="modal-body">
                                <div class="mb-2" id="qualPosteWrap">
                                    <label class="form-label" for="qualPosteSelect" style="font-size:var(--font-size-sm);">
                                        Compétence <span class="text-danger">*</span>
                                    </label>
                                    <select id="qualPosteSelect" name="PS_ID" class="form-select form-select-sm" required>
                                        <option value="">— choisir —</option>
                                        @foreach ($postes as $poste)
                                            <option value="{{ $poste->PS_ID }}">
                                                {{ $poste->TYPE }}{{ $poste->DESCRIPTION ? ' — ' . $poste->DESCRIPTION : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="qualPosteLabel" class="mb-2" style="display:none;">
                                    <label class="form-label" style="font-size:var(--font-size-sm);">Compétence</label>
                                    <p class="mb-0" id="qualPosteLabelText" style="font-size:var(--font-size-sm);"></p>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label" for="qualVal" style="font-size:var(--font-size-sm);">
                                        Valeur / résultat
                                    </label>
                                    <input id="qualVal" name="Q_VAL" type="text" class="form-control form-control-sm"
                                        placeholder="ex. Obtenu, 15/20…">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label" for="qualExp" style="font-size:var(--font-size-sm);">
                                        Date d'expiration
                                    </label>
                                    <input id="qualExp" name="Q_EXPIRATION" type="date" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="modal-footer py-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-sm btn-primary" id="qualSubmitBtn">
                                    Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── Cotisations ────────────────────────────────────────── --}}
            <p class="section-title d-flex justify-content-between align-items-center">
                <span>Cotisations</span>
                <button type="button" class="btn btn-sm btn-success noprint"
                    data-bs-toggle="modal" data-bs-target="#cotisModal"
                    onclick="openCotisModal(null)">
                    <i class="fas fa-plus me-1"></i> Ajouter
                </button>
            </p>

            @php
                $cotisations = $personnel->cotisations->sortByDesc('ANNEE');
                $totalNet = $personnel->cotisations
                    ->sum(fn($c) => $c->REMBOURSEMENT ? -abs((float)$c->MONTANT) : (float)$c->MONTANT);
            @endphp

            @if ($cotisations->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-1" style="font-size:var(--font-size-sm);">
                        <thead>
                            <tr>
                                <th>Année</th>
                                <th>Période</th>
                                <th>Date</th>
                                <th class="text-end">Montant</th>
                                <th>Mode</th>
                                <th>Commentaire</th>
                                <th class="noprint" style="width:80px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cotisations as $cotis)
                                <tr>
                                    <td>{{ $cotis->ANNEE }}</td>
                                    <td>{{ $cotis->PERIODE_CODE ?: '—' }}</td>
                                    <td>{{ $cotis->PC_DATE?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="text-end {{ $cotis->REMBOURSEMENT ? 'text-danger' : '' }}">
                                        @if ($cotis->REMBOURSEMENT)
                                            <small class="badge bg-warning text-dark me-1">Remb.</small>
                                        @endif
                                        {{ number_format((float)$cotis->MONTANT, 2, ',', ' ') }} €
                                    </td>
                                    <td>{{ $cotis->typePaiement?->TP_DESCRIPTION ?? '—' }}</td>
                                    <td class="text-muted">{{ $cotis->COMMENTAIRE ?: '' }}</td>
                                    <td class="text-end noprint">
                                        <button type="button"
                                            class="btn btn-xs btn-light py-0 px-1 me-1"
                                            title="Modifier"
                                            onclick="openCotisModal({
                                                pc_id: {{ $cotis->PC_ID }},
                                                annee: {{ $cotis->ANNEE }},
                                                periode: {{ json_encode($cotis->PERIODE_CODE ?? '') }},
                                                date: {{ json_encode($cotis->PC_DATE?->format('Y-m-d') ?? '') }},
                                                montant: {{ (float)$cotis->MONTANT }},
                                                tp_id: {{ $cotis->TP_ID ?: 'null' }},
                                                remb: {{ $cotis->REMBOURSEMENT ? 'true' : 'false' }},
                                                comment: {{ json_encode($cotis->COMMENTAIRE ?? '') }}
                                            })">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST"
                                            action="{{ route('personnel.cotisation.destroy', [$personnel, $cotis->PC_ID]) }}"
                                            class="d-inline"
                                            onsubmit="return confirm('Supprimer cette cotisation ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-light py-0 px-1 text-danger"
                                                title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end text-muted" style="font-size:var(--font-size-xs);">Total net</td>
                                <td class="text-end {{ $totalNet < 0 ? 'text-danger' : '' }}">
                                    {{ number_format($totalNet, 2, ',', ' ') }} €
                                </td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <p class="text-muted small">Aucune cotisation enregistrée.</p>
            @endif

            {{-- Add/Edit cotisation modal --}}
            <div class="modal fade" id="cotisModal" tabindex="-1" aria-labelledby="cotisModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cotisModalLabel" style="font-size:var(--font-size-base);">
                                Cotisation
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="cotisForm" method="POST">
                            @csrf
                            <span id="cotisMethodField"></span>
                            <div class="modal-body">
                                <div class="row g-2">
                                    <div class="col-3">
                                        <label class="form-label" style="font-size:var(--font-size-sm);">
                                            Année <span class="text-danger">*</span>
                                        </label>
                                        <input name="ANNEE" id="cotisAnnee" type="number"
                                            class="form-control form-control-sm"
                                            min="1990" max="2100"
                                            value="{{ date('Y') }}" required>
                                    </div>
                                    <div class="col-3">
                                        <label class="form-label" style="font-size:var(--font-size-sm);">Période</label>
                                        <input name="PERIODE_CODE" id="cotisPeriode" type="text"
                                            class="form-control form-control-sm"
                                            placeholder="ex. T1">
                                    </div>
                                    <div class="col-3">
                                        <label class="form-label" style="font-size:var(--font-size-sm);">
                                            Date <span class="text-danger">*</span>
                                        </label>
                                        <input name="PC_DATE" id="cotisDate" type="date"
                                            class="form-control form-control-sm" required>
                                    </div>
                                    <div class="col-3">
                                        <label class="form-label" style="font-size:var(--font-size-sm);">
                                            Montant (€) <span class="text-danger">*</span>
                                        </label>
                                        <input name="MONTANT" id="cotisMontant" type="number"
                                            class="form-control form-control-sm"
                                            min="0" step="0.01" placeholder="0.00" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" style="font-size:var(--font-size-sm);">Mode de paiement</label>
                                        <select name="TP_ID" id="cotisMode" class="form-select form-select-sm">
                                            <option value="">—</option>
                                            @foreach ($typesPaiement as $tp)
                                                <option value="{{ $tp->TP_ID }}">{{ $tp->TP_DESCRIPTION }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 d-flex align-items-end">
                                        <div class="form-check mb-1">
                                            <input type="checkbox" name="REMBOURSEMENT" id="cotisRemb"
                                                value="1" class="form-check-input">
                                            <label class="form-check-label" for="cotisRemb"
                                                style="font-size:var(--font-size-sm);">
                                                Remboursement
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" style="font-size:var(--font-size-sm);">Commentaire</label>
                                        <input name="COMMENTAIRE" id="cotisComment" type="text"
                                            class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer py-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── GPS ───────────────────────────────────────────────── --}}
            <p class="section-title d-flex justify-content-between align-items-center">
                <span>Géolocalisation</span>
                <a href="{{ route('geolocalisation.index') }}" class="btn btn-sm btn-light noprint"
                   title="Voir la carte">
                    <i class="fas fa-map-marked-alt me-1"></i> Carte
                </a>
            </p>
            @if ($gps && $gps->LAT && $gps->LNG)
                <dl class="info-grid">
                    <div class="info-item">
                        <dt>Coordonnées</dt>
                        <dd>{{ number_format((float)$gps->LAT, 5) }}, {{ number_format((float)$gps->LNG, 5) }}</dd>
                    </div>
                    <div class="info-item">
                        <dt>Adresse</dt>
                        <dd>{{ $gps->ADDRESS ?: '—' }}</dd>
                    </div>
                    <div class="info-item">
                        <dt>Dernière mise à jour</dt>
                        <dd>{{ $gps->DATE_LOC ? date('d/m/Y H:i', strtotime($gps->DATE_LOC)) : '—' }}</dd>
                    </div>
                </dl>
            @else
                <p class="text-muted small">Aucune position GPS enregistrée.</p>
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

@push('scripts')
<script>
function openCotisModal(cotis) {
    var form     = document.getElementById('cotisForm');
    var methodEl = document.getElementById('cotisMethodField');
    var baseRoute = '{{ url('personnel/' . $personnel->P_ID . '/cotisations') }}';

    if (cotis) {
        form.action          = baseRoute + '/' + cotis.pc_id;
        methodEl.innerHTML   = '<input type="hidden" name="_method" value="PATCH">';
        document.getElementById('cotisAnnee').value   = cotis.annee;
        document.getElementById('cotisPeriode').value = cotis.periode;
        document.getElementById('cotisDate').value    = cotis.date;
        document.getElementById('cotisMontant').value = cotis.montant;
        document.getElementById('cotisMode').value    = cotis.tp_id || '';
        document.getElementById('cotisRemb').checked  = cotis.remb;
        document.getElementById('cotisComment').value = cotis.comment;
        document.getElementById('cotisModalLabel').textContent = 'Modifier la cotisation';
    } else {
        form.action          = baseRoute;
        methodEl.innerHTML   = '';
        document.getElementById('cotisAnnee').value   = new Date().getFullYear();
        document.getElementById('cotisPeriode').value = '';
        document.getElementById('cotisDate').value    = '';
        document.getElementById('cotisMontant').value = '';
        document.getElementById('cotisMode').value    = '';
        document.getElementById('cotisRemb').checked  = false;
        document.getElementById('cotisComment').value = '';
        document.getElementById('cotisModalLabel').textContent = 'Ajouter une cotisation';
    }
}

function openQualModal(qual) {
    var form       = document.getElementById('qualForm');
    var methodEl   = document.getElementById('qualMethodField');
    var posteWrap  = document.getElementById('qualPosteWrap');
    var posteLbl   = document.getElementById('qualPosteLabel');
    var posteLblTx = document.getElementById('qualPosteLabelText');
    var valInput   = document.getElementById('qualVal');
    var expInput   = document.getElementById('qualExp');
    var modalTitle = document.getElementById('qualModalLabel');
    var submitBtn  = document.getElementById('qualSubmitBtn');

    if (qual) {
        // ── Edit mode ──────────────────────────────────────────────
        var baseUrl = '{{ route('personnel.show', $personnel) }}';
        form.action = baseUrl.replace(/\/[^\/]+$/, '') + '/{{ $personnel->P_ID }}/qualifications/' + qual.ps_id;
        methodEl.innerHTML = '<input type="hidden" name="_method" value="PATCH">';
        posteWrap.style.display = 'none';
        posteLbl.style.display  = '';
        posteLblTx.textContent  = qual.label;
        valInput.value          = qual.q_val || '';
        expInput.value          = qual.q_exp || '';
        modalTitle.textContent  = 'Modifier la compétence';
    } else {
        // ── Add mode ───────────────────────────────────────────────
        form.action = '{{ route('personnel.qualification.store', $personnel) }}';
        methodEl.innerHTML = '';
        posteWrap.style.display = '';
        posteLbl.style.display  = 'none';
        document.getElementById('qualPosteSelect').value = '';
        valInput.value = '';
        expInput.value = '';
        modalTitle.textContent = 'Ajouter une compétence';
    }
}
</script>
@endpush
