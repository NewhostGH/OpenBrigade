@extends('layout.app')

@section('title', $personnel->P_NOM . ' ' . $personnel->P_PRENOM . ' — Personnel — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Personnel', 'url' => route('personnel.index')],
    ['label' => $personnel->P_NOM . ' ' . $personnel->P_PRENOM],
]"/>


<div class="mx-3 mt-3">

    {{-- ── Profile header card ─────────────────────────────────────────────── --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                @if ($personnel->civiliteLabel())
                    <span class="fw-normal text-muted">{{ $personnel->civiliteLabel() }}</span>
                @endif
                {{ strtoupper($personnel->P_NOM) }} {{ $personnel->P_PRENOM }}
                @if ($personnel->P_ABBREGE)
                    <span class="text-muted fw-normal" style="font-size:var(--font-size-sm);">· {{ $personnel->P_ABBREGE }}</span>
                @endif
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('personnel.edit', $personnel) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-edit me-1"></i> Modifier
                </a>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" title="Exporter">
                        <i class="fas fa-download"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('personnel.vcard', $personnel) }}">
                                <i class="fas fa-address-card me-2 text-muted"></i> vCard (.vcf)
                            </a>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item" data-livret-btn
                                onclick="window.__downloadLivretPdf && window.__downloadLivretPdf({{ $personnel->P_ID }})">
                                <i class="fas fa-file-pdf me-2 text-danger"></i> Livret (PDF)
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item" data-carte-btn
                                onclick="window.__downloadCartePdf && window.__downloadCartePdf({{ $personnel->P_ID }})">
                                <i class="fas fa-id-card me-2 text-danger"></i> Carte adhérent (PDF)
                            </button>
                        </li>
                    </ul>
                </div>
                <a href="{{ route('personnel.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
        </div>

        <div class="ob-widget-card-body">
            <div class="row g-4 align-items-start">

                {{-- ── Photo ───────────────────────────────────────────── --}}
                <div class="col-auto" style="flex:0 0 auto;">
                    <img src="{{ $personnel->getAvatarUrl() }}"
                         alt="Photo {{ $personnel->P_NOM }}"
                         style="width:96px; height:96px; object-fit:cover; border-radius:var(--radius-md);
                                border:2px solid var(--component-border);">
                </div>

                {{-- ── Identity dl ─────────────────────────────────────── --}}
                <div class="col">
                    <dl class="mb-0" style="display:grid; grid-template-columns:auto 1fr; gap:5px 16px;
                                            font-size:var(--font-size-sm); align-items:baseline;">
                        <dt class="text-muted fw-normal">Matricule</dt>
                        <dd class="mb-0 fw-semibold">{{ $personnel->P_CODE }}</dd>

                        @feature('multi_site')
                        <dt class="text-muted fw-normal" title="Section où le membre se situe dans l'organigramme">Section principale</dt>
                        <dd class="mb-0">{{ $personnel->section?->S_CODE ?: '—' }}
                            @if($personnel->section?->S_DESCRIPTION)
                                <span class="text-muted">— {{ $personnel->section->S_DESCRIPTION }}</span>
                            @endif
                        </dd>
                        @endfeature

                        <dt class="text-muted fw-normal">Grade</dt>
                        <dd class="mb-0">
                            @if ($personnel->P_GRADE)
                                <img src="{{ route('personnel.grade-image', ['grade' => $personnel->P_GRADE]) }}"
                                     alt="{{ $personnel->P_GRADE }}" title="{{ $personnel->P_GRADE }}"
                                     class="ob-grade-img"
                                     onerror="this.outerHTML='<span>{{ e($personnel->P_GRADE) }}</span>'">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>

                        <dt class="text-muted fw-normal">Statut</dt>
                        <dd class="mb-0">
                            <span class="ob-badge {{ $personnel->statutBadgeClass() }}">{{ $personnel->statutBadgeLabel() }}</span>
                            <span class="ob-badge {{ $personnel->etatBadgeClass() }} ms-1">{{ $personnel->etatBadgeLabel() }}</span>
                        </dd>

                        @if ($personnel->P_DATE_ENGAGEMENT)
                        <dt class="text-muted fw-normal">Engagement</dt>
                        <dd class="mb-0">{{ $personnel->P_DATE_ENGAGEMENT->format('d/m/Y') }}</dd>
                        @endif

                        @if ($company)
                        <dt class="text-muted fw-normal">Entreprise</dt>
                        <dd class="mb-0">{{ $company->C_NAME }}</dd>
                        @endif
                    </dl>

                    @if ($personnel->P_HIDE || $personnel->P_NOSPAM || $personnel->NPAI || $personnel->SUSPENDU)
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        @if ($personnel->P_HIDE)   <span class="ob-badge ob-badge-archive">Masqué</span> @endif
                        @if ($personnel->P_NOSPAM) <span class="ob-badge ob-badge-archive">No spam</span> @endif
                        @if ($personnel->NPAI)     <span class="ob-badge ob-badge-ben">NPAI</span> @endif
                        @if ($personnel->SUSPENDU) <span class="ob-badge ob-badge-bloqued">Suspendu</span> @endif
                    </div>
                    @endif
                </div>

                {{-- ── Quick-stat cards ────────────────────────────────── --}}
                <div class="col-md-5">
                    <div class="row g-2">
                        @php
                            $stats = [
                                ['icon' => 'fas fa-calendar-check', 'label' => 'Activités',
                                 'value' => $participation->count(), 'color' => '#2563eb', 'bg' => '#eff6ff'],
                                ['icon' => 'fas fa-certificate', 'label' => 'Compétences',
                                 'value' => $personnel->qualifications->count(), 'color' => '#7c3aed', 'bg' => '#f5f3ff'],
                                ['icon' => 'fas fa-euro-sign', 'label' => 'Cotisations (net)',
                                 'value' => number_format($personnel->cotis_net, 2, ',', ' ') . ' €',
                                 'color' => $personnel->cotis_net < 0 ? '#dc2626' : '#16a34a',
                                 'bg'    => $personnel->cotis_net < 0 ? '#fff1f2' : '#f0fdf4'],
                                ['icon' => 'fas fa-clock', 'label' => 'Dernière connexion',
                                 'value' => $personnel->P_LAST_CONNECT?->format('d/m/Y') ?? 'jamais',
                                 'color' => '#64748b', 'bg' => '#f8fafc'],
                            ];
                        @endphp
                        @foreach ($stats as $stat)
                        <div class="col-6">
                            <div style="border-left:3px solid {{ $stat['color'] }};
                                        background:{{ $stat['bg'] }};
                                        border-radius:0 var(--radius-sm) var(--radius-sm) 0;
                                        padding:10px 14px;">
                                <div style="font-size:var(--font-size-xs); color:{{ $stat['color'] }}; font-weight:600; margin-bottom:3px;">
                                    <i class="{{ $stat['icon'] }} me-1"></i>{{ $stat['label'] }}
                                </div>
                                <div style="font-size:1rem; font-weight:700; color:{{ $stat['color'] }}; letter-spacing:.01em;">
                                    {{ $stat['value'] }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Sidebar + content layout ─────────────────────────────────────────── --}}
    <div class="d-flex gap-3 align-items-start">

        {{-- ── Left sidebar nav ────────────────────────────────────────────── --}}
        <div class="ob-pers-sidenav-wrap noprint">
            <div class="ob-widget-card">
                <div class="ob-widget-card-body p-0">
                    <nav>
                        @foreach ($sideNav as $item)
                        <a href="#{{ $item['id'] }}" class="ob-pers-sidenav-link{{ $loop->first ? ' active' : '' }}">
                            <i class="{{ $item['icon'] }}" style="width:14px; text-align:center;"></i>
                            {{ $item['label'] }}
                            @if (!empty($item['badge']))
                                <span class="ob-badge ob-badge-archive" style="margin-left:auto;">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </div>

        {{-- ── Main content ────────────────────────────────────────────────── --}}
        <div style="flex:1; min-width:0;">

            {{-- ▸ Information ─────────────────────────────────────────────── --}}
            <div id="section-info" data-pers-section>
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-address-book"></i> Coordonnées</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <dl class="ob-info-grid mb-0">
                            <div class="ob-info-item">
                                <dt>Email</dt>
                                <dd>
                                    @if ($personnel->P_EMAIL)
                                        <a href="mailto:{{ $personnel->P_EMAIL }}">{{ $personnel->P_EMAIL }}</a>
                                    @else —
                                    @endif
                                </dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>Téléphone</dt>
                                <dd>{{ $personnel->P_PHONE ?: '—' }}</dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>Portable</dt>
                                <dd>{{ $personnel->P_PHONE2 ?: '—' }}</dd>
                            </div>
                            <div class="ob-info-item">
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
                            @if ($personnel->NPAI)
                            <div class="ob-info-item">
                                <dt>NPAI</dt>
                                <dd>
                                    <span class="ob-badge ob-badge-bloqued">Adresse invalide</span>
                                    @if (!empty($personnel->DATE_NPAI))
                                        <small class="text-muted ms-1">depuis le {{ \Carbon\Carbon::parse($personnel->DATE_NPAI)->format('d/m/Y') }}</small>
                                    @endif
                                </dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-id-badge"></i> Informations personnelles</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <dl class="ob-info-grid mb-0">
                            <div class="ob-info-item">
                                <dt>Date de naissance</dt>
                                <dd>
                                    {{ $personnel->P_BIRTHDATE?->format('d/m/Y') ?? '—' }}
                                    @if ($personnel->P_BIRTHDATE)
                                        <small class="text-muted">({{ $personnel->P_BIRTHDATE->diffInYears(now()) }} ans)</small>
                                    @endif
                                </dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>Lieu de naissance</dt>
                                <dd>
                                    {{ $personnel->P_BIRTHPLACE ?: '—' }}
                                    @if ($personnel->P_BIRTH_DEP)
                                        <span class="text-muted">({{ $personnel->P_BIRTH_DEP }})</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>Nom de naissance</dt>
                                <dd>{{ $personnel->P_NOM_NAISSANCE ?: '—' }}</dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>Date de fin</dt>
                                <dd>{{ $personnel->P_FIN?->format('d/m/Y') ?? '—' }}</dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>Profession</dt>
                                <dd>{{ $personnel->P_PROFESSION ?: '—' }}</dd>
                            </div>
                            @if ($personnel->P_LICENCE)
                            <div class="ob-info-item">
                                <dt>Licence / permis</dt>
                                <dd>
                                    {{ $personnel->P_LICENCE }}
                                    @if ($personnel->P_LICENCE_DATE)
                                        <small class="text-muted">du {{ $personnel->P_LICENCE_DATE->format('d/m/Y') }}</small>
                                    @endif
                                    @if ($personnel->P_LICENCE_EXPIRY)
                                        <br><small class="{{ $personnel->P_LICENCE_EXPIRY->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                            Exp. {{ $personnel->P_LICENCE_EXPIRY->format('d/m/Y') }}
                                        </small>
                                    @endif
                                </dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                @if ($personnel->P_RELATION_NOM || $personnel->P_RELATION_PRENOM || $personnel->P_RELATION_PHONE)
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-phone-alt"></i> Contact d'urgence</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <dl class="ob-info-grid mb-0">
                            <div class="ob-info-item">
                                <dt>Nom</dt>
                                <dd>{{ trim($personnel->P_RELATION_PRENOM . ' ' . $personnel->P_RELATION_NOM) ?: '—' }}</dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>Téléphone</dt>
                                <dd>{{ $personnel->P_RELATION_PHONE ?: '—' }}</dd>
                            </div>
                            @if ($personnel->P_RELATION_MAIL)
                            <div class="ob-info-item">
                                <dt>Email</dt>
                                <dd><a href="mailto:{{ $personnel->P_RELATION_MAIL }}">{{ $personnel->P_RELATION_MAIL }}</a></dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>
                @endif

                @if ($personnel->OBSERVATION)
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-sticky-note"></i> Notes</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <p class="mb-0" style="font-size:var(--font-size-sm); white-space:pre-wrap;">{{ $personnel->OBSERVATION }}</p>
                    </div>
                </div>
                @endif
            </div>{{-- /section-info --}}

            {{-- ▸ Compétences ─────────────────────────────────────────────── --}}
            <div id="section-competences" data-pers-section>
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-certificate"></i> Compétences
                            @if($personnel->qualifications->isNotEmpty())
                                <span class="ob-badge ob-badge-archive ms-1">{{ $personnel->qualifications->count() }}</span>
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-success noprint"
                                data-bs-toggle="modal" data-bs-target="#qualModal"
                                onclick="openQualModal(null)">
                            <i class="fas fa-plus me-1"></i> Ajouter
                        </button>
                    </div>
                    <div class="ob-widget-card-body p-0">
                        @if ($personnel->qualifications->isNotEmpty())
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size:var(--font-size-sm);">
                                <thead style="background:var(--table-header-bg);color:var(--table-header-text);">
                                    <tr>
                                        <th class="ps-3">Compétence</th>
                                        <th>Valeur</th>
                                        <th>Expiration</th>
                                        <th class="noprint" style="width:80px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($personnel->qualifications->sortBy('poste.TYPE') as $qual)
                                        @php
                                            $exp    = $qual->Q_EXPIRATION?->toDateString();
                                            $status = match(true) {
                                                $exp !== null && $exp < $today   => 'expired',
                                                $exp !== null && $exp <= $warn30 => 'expiring',
                                                default => 'ok',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="ps-3">
                                                {{ $qual->poste?->TYPE ?? '?' }}
                                                @if ($qual->poste?->DESCRIPTION)
                                                    <small class="text-muted">— {{ $qual->poste->DESCRIPTION }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $qual->Q_VAL ?: '—' }}</td>
                                            <td>
                                                @if ($qual->Q_EXPIRATION)
                                                    @php $cls = match($status) { 'expired' => 'text-danger fw-bold', 'expiring' => 'text-warning fw-bold', default => '' }; @endphp
                                                    <span class="{{ $cls }}">
                                                        {{ $qual->Q_EXPIRATION->format('d/m/Y') }}
                                                        @if ($status === 'expired') <i class="fas fa-exclamation-triangle ms-1"></i>
                                                        @elseif ($status === 'expiring') <i class="fas fa-clock ms-1"></i>
                                                        @endif
                                                    </span>
                                                @else <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3 noprint">
                                                <button type="button" class="btn btn-xs btn-light py-0 px-1 me-1"
                                                        onclick="openQualModal({
                                                            ps_id: {{ $qual->PS_ID }},
                                                            q_val: {{ json_encode($qual->Q_VAL ?? '') }},
                                                            q_exp: {{ json_encode($qual->Q_EXPIRATION?->format('Y-m-d') ?? '') }},
                                                            label: {{ json_encode(($qual->poste?->TYPE ?? '') . ($qual->poste?->DESCRIPTION ? ' — ' . $qual->poste->DESCRIPTION : '')) }}
                                                        })">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST"
                                                      action="{{ route('personnel.qualification.destroy', [$personnel, $qual->PS_ID]) }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Supprimer cette compétence ?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-light py-0 px-1 text-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="ob-widget-empty p-3">Aucune compétence enregistrée.</p>
                        @endif
                    </div>
                </div>
            </div>{{-- /section-competences --}}

            {{-- ▸ Cotisations ────────────────────────────────────────────── --}}
            <div id="section-cotisations" data-pers-section>
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-euro-sign"></i> Cotisations
                            @if($cotisations->isNotEmpty())
                                <span class="ob-badge ob-badge-archive ms-1">{{ $cotisations->count() }}</span>
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-success noprint"
                                data-bs-toggle="modal" data-bs-target="#cotisModal"
                                onclick="openCotisModal(null)">
                            <i class="fas fa-plus me-1"></i> Ajouter
                        </button>
                    </div>
                    <div class="ob-widget-card-body p-0">
                        @if ($cotisations->isNotEmpty())
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size:var(--font-size-sm);">
                                <thead style="background:var(--table-header-bg);color:var(--table-header-text);">
                                    <tr>
                                        <th class="ps-3">Année</th>
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
                                            <td class="ps-3">{{ $cotis->ANNEE }}</td>
                                            <td>{{ $cotis->PERIODE_CODE ?: '—' }}</td>
                                            <td>{{ $cotis->PC_DATE?->format('d/m/Y') ?? '—' }}</td>
                                            <td class="text-end {{ $cotis->REMBOURSEMENT ? 'text-danger' : '' }}">
                                                @if ($cotis->REMBOURSEMENT)
                                                    <span class="badge bg-warning text-dark me-1">Remb.</span>
                                                @endif
                                                {{ number_format((float)$cotis->MONTANT, 2, ',', ' ') }} €
                                            </td>
                                            <td>{{ $cotis->typePaiement?->TP_DESCRIPTION ?? '—' }}</td>
                                            <td class="text-muted">{{ $cotis->COMMENTAIRE ?: '' }}</td>
                                            <td class="text-end pe-3 noprint">
                                                <button type="button" class="btn btn-xs btn-light py-0 px-1 me-1"
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
                                                      action="{{ route('personnel.dues.destroy', [$personnel, $cotis->PC_ID]) }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Supprimer cette cotisation ?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-light py-0 px-1 text-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="3" class="text-end ps-3"
                                            style="font-size:var(--font-size-xs);color:var(--text-muted-soft);">Total net</td>
                                        <td class="text-end {{ $personnel->cotis_net < 0 ? 'text-danger' : '' }}">
                                            {{ number_format($personnel->cotis_net, 2, ',', ' ') }} €
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        @else
                            <p class="ob-widget-empty p-3">Aucune cotisation enregistrée.</p>
                        @endif
                    </div>
                </div>
            </div>{{-- /section-cotisations --}}

            {{-- ▸ Participation ───────────────────────────────────────────── --}}
            <div id="section-participation" data-pers-section>
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-calendar-check"></i> Participation aux activités
                            @if($participation->isNotEmpty())
                                <span class="ob-badge ob-badge-archive ms-1">{{ $participation->count() }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="ob-widget-card-body p-0">
                        @if ($participation->isNotEmpty())
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size:var(--font-size-sm);">
                                <thead style="background:var(--table-header-bg);color:var(--table-header-text);">
                                    <tr>
                                        <th class="ps-3">Activité</th>
                                        <th>Date</th>
                                        <th class="text-end">Durée</th>
                                        <th class="text-end">Km</th>
                                        <th>Présence</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($participation as $ep)
                                        @php
                                            $presClass = match(true) {
                                                (bool) $ep->EP_ABSENT && !(bool) $ep->EP_EXCUSE => 'ob-badge-bloqued',
                                                (bool) $ep->EP_ABSENT && (bool) $ep->EP_EXCUSE  => 'ob-badge-ben',
                                                default => 'ob-badge-actif',
                                            };
                                            $presLbl = match(true) {
                                                (bool) $ep->EP_ABSENT && !(bool) $ep->EP_EXCUSE => 'Absent',
                                                (bool) $ep->EP_ABSENT && (bool) $ep->EP_EXCUSE  => 'Excusé',
                                                default => 'Présent',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <a href="{{ route('event.show', $ep->E_CODE) }}" class="text-decoration-none">
                                                    {{ $ep->E_LIBELLE ?: $ep->E_CODE }}
                                                </a>
                                            </td>
                                            <td style="color:var(--text-muted-soft)">
                                                {{ $ep->E_DATE_DEBUT ? \Carbon\Carbon::parse($ep->E_DATE_DEBUT)->format('d/m/Y') : '—' }}
                                            </td>
                                            <td class="text-end" style="color:var(--text-muted-soft)">
                                                {{ $ep->EP_DUREE ? number_format((float) $ep->EP_DUREE, 1) . ' h' : '—' }}
                                            </td>
                                            <td class="text-end" style="color:var(--text-muted-soft)">
                                                {{ $ep->EP_KM ? $ep->EP_KM . ' km' : '—' }}
                                            </td>
                                            <td><span class="ob-badge {{ $presClass }}">{{ $presLbl }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if ($participation->count() >= 50)
                                <p class="p-3 mb-0 text-muted" style="font-size:var(--font-size-xs);">
                                    Affichage limité aux 50 dernières participations.
                                </p>
                            @endif
                        @else
                            <p class="ob-widget-empty p-3">Aucune participation enregistrée.</p>
                        @endif
                    </div>
                </div>
            </div>{{-- /section-participation --}}

            {{-- ▸ Dotation ───────────────────────────────────────────────── --}}
            <div id="section-dotation" data-pers-section>
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-box"></i> Dotation</div>
                        <button class="btn btn-sm btn-success noprint" disabled title="Fonctionnalité à venir">
                            <i class="fas fa-plus me-1"></i> Ajouter
                        </button>
                    </div>
                    <div class="ob-widget-card-body">
                        <p class="ob-widget-empty mb-0">Aucune dotation enregistrée.</p>
                    </div>
                </div>
            </div>{{-- /section-dotation --}}

            {{-- ▸ Documents ──────────────────────────────────────────────── --}}
            <div id="section-documents" data-pers-section>
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-file-alt"></i> Documents</div>
                        <button class="btn btn-sm btn-success noprint" disabled title="Fonctionnalité à venir">
                            <i class="fas fa-plus me-1"></i> Ajouter
                        </button>
                    </div>
                    <div class="ob-widget-card-body">
                        <p class="ob-widget-empty mb-0">Aucun document enregistré.</p>
                    </div>
                </div>
            </div>{{-- /section-documents --}}

            {{-- ▸ Notes de frais ─────────────────────────────────────────── --}}
            <div id="section-notedfrais" data-pers-section>
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-receipt"></i> Notes de frais</div>
                        <button class="btn btn-sm btn-success noprint" disabled title="Fonctionnalité à venir">
                            <i class="fas fa-plus me-1"></i> Ajouter
                        </button>
                    </div>
                    <div class="ob-widget-card-body">
                        <p class="ob-widget-empty mb-0">Aucune note de frais enregistrée.</p>
                    </div>
                </div>
            </div>{{-- /section-notedfrais --}}

            {{-- ▸ Disponibilité ──────────────────────────────────────────── --}}
            <div id="section-disponibilite" data-pers-section>
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-calendar-day"></i> Disponibilité</div>
                        <button class="btn btn-sm btn-success noprint" disabled title="Fonctionnalité à venir">
                            <i class="fas fa-plus me-1"></i> Ajouter
                        </button>
                    </div>
                    <div class="ob-widget-card-body">
                        <p class="ob-widget-empty mb-0">Aucune disponibilité enregistrée.</p>
                    </div>
                </div>
            </div>{{-- /section-disponibilite --}}

            {{-- ▸ Calendrier ─────────────────────────────────────────────── --}}
            <div id="section-calendrier" data-pers-section>
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-calendar"></i> Calendrier</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <p class="ob-widget-empty mb-0">Aucune entrée de calendrier.</p>
                    </div>
                </div>
            </div>{{-- /section-calendrier --}}

            {{-- ▸ Absences ───────────────────────────────────────────────── --}}
            <div id="section-absences" data-pers-section>
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-user-times"></i> Absences</div>
                        <button class="btn btn-sm btn-success noprint" disabled title="Fonctionnalité à venir">
                            <i class="fas fa-plus me-1"></i> Ajouter
                        </button>
                    </div>
                    <div class="ob-widget-card-body">
                        <p class="ob-widget-empty mb-0">Aucune absence enregistrée.</p>
                    </div>
                </div>
            </div>{{-- /section-absences --}}

            {{-- ▸ Géolocalisation ────────────────────────────────────────── --}}
            <div id="section-geo" data-pers-section>
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-map-marker-alt"></i> Géolocalisation</div>
                        <a href="{{ route('geolocation.index') }}" class="btn btn-sm btn-light noprint">
                            <i class="fas fa-map-marked-alt me-1"></i> Carte globale
                        </a>
                    </div>
                    <div class="ob-widget-card-body">
                        @if ($gps && $gps->LAT && $gps->LNG)
                            <dl class="ob-info-grid mb-0">
                                <div class="ob-info-item">
                                    <dt>Coordonnées</dt>
                                    <dd>{{ number_format((float)$gps->LAT, 5) }}, {{ number_format((float)$gps->LNG, 5) }}</dd>
                                </div>
                                <div class="ob-info-item">
                                    <dt>Adresse</dt>
                                    <dd>{{ $gps->ADDRESS ?: '—' }}</dd>
                                </div>
                                <div class="ob-info-item">
                                    <dt>Dernière mise à jour</dt>
                                    <dd>{{ $gps->DATE_LOC ? date('d/m/Y H:i', strtotime($gps->DATE_LOC)) : '—' }}</dd>
                                </div>
                            </dl>
                        @else
                            <p class="ob-widget-empty mb-0">Aucune position GPS enregistrée.</p>
                        @endif
                    </div>
                </div>
            </div>{{-- /section-geo --}}

            {{-- ▸ Accès ─────────────────────────────────────────────────── --}}
            <div id="section-acces" data-pers-section>
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-shield-alt"></i> Droits d'accès</div>
                        <div class="ob-widget-card-actions d-flex gap-2">
                            @if (auth()->user()->hasPermission(9) || auth()->user()->hasPermission(25))
                                <a href="{{ route('personnel.send-credentials.show', $personnel->P_ID) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-key me-1"></i>Identifiants
                                </a>
                            @endif
                            @if (auth()->user()->hasPermission(9))
                                <a href="{{ route('personnel.edit', $personnel->P_ID) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-pen me-1"></i>Gérer
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="ob-widget-card-body">
                        <dl class="ob-info-grid mb-0">
                            <div class="ob-info-item">
                                <dt>Groupes d'accès</dt>
                                <dd>
                                    @forelse ($personnelGroups as $gname)
                                        <span class="ob-badge ob-badge-ext me-1">{{ $gname }}</span>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>Dernière connexion</dt>
                                <dd>{{ $personnel->P_LAST_CONNECT?->format('d/m/Y H:i') ?? 'jamais' }}</dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>Connexions</dt>
                                <dd>{{ $personnel->P_NB_CONNECT ?? '0' }}</dd>
                            </div>
                            @if ($personnel->P_ACCEPT_DATE)
                            <div class="ob-info-item">
                                <dt>Charte acceptée</dt>
                                <dd>{{ $personnel->P_ACCEPT_DATE->format('d/m/Y H:i') }}</dd>
                            </div>
                            @endif
                            @if ($personnel->P_ACCEPT_DATE2)
                            <div class="ob-info-item">
                                <dt>Charte 2 acceptée</dt>
                                <dd>{{ $personnel->P_ACCEPT_DATE2->format('d/m/Y H:i') }}</dd>
                            </div>
                            @endif
                            <div class="ob-info-item">
                                <dt>Indicateurs</dt>
                                <dd>
                                    @if ($personnel->P_HIDE)   <span class="ob-badge ob-badge-archive me-1">Masqué</span> @endif
                                    @if ($personnel->P_NOSPAM) <span class="ob-badge ob-badge-archive me-1">No spam</span> @endif
                                    @if ($personnel->NPAI)     <span class="ob-badge ob-badge-ben me-1">NPAI</span> @endif
                                    @if ($personnel->SUSPENDU) <span class="ob-badge ob-badge-bloqued me-1">Suspendu</span> @endif
                                    @if (!$personnel->P_HIDE && !$personnel->P_NOSPAM && !$personnel->NPAI && !$personnel->SUSPENDU)
                                        <span class="text-muted">—</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Section memberships (ob_personnel_section) --}}
                    @feature('multi_site')
                    <div class="ob-widget-card-subheader">
                        <div>
                            <i class="fas fa-sitemap me-1"></i> Sections
                            @if ($personnel->section)
                                <span class="text-muted fw-normal ms-2" style="font-size:var(--font-size-sm);">
                                    — principale : <strong>{{ $personnel->section->S_CODE }}</strong>
                                    @if ($personnel->section->S_DESCRIPTION)
                                        <span class="fw-normal">{{ $personnel->section->S_DESCRIPTION }}</span>
                                    @endif
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="ob-widget-card-body">
                        @forelse ($personnelSections as $s)
                            <span class="ob-badge ob-badge-int me-1 mb-1">{{ $s->S_CODE }}{{ $s->S_DESCRIPTION ? ' — '.$s->S_DESCRIPTION : '' }}</span>
                        @empty
                            <span class="text-muted" style="font-size:var(--font-size-sm);">Aucune section attribuée.</span>
                        @endforelse
                    </div>
                    @endfeature

                    {{-- Section-scoped organisational roles (ob_user_assignment) — read-only;
                         edited from the member's CRUD form, "Accès" tab. --}}
                    <div class="ob-widget-card-subheader">
                        <div><i class="fas fa-user-tie me-1"></i> @feature('multi_site')Rôles par section @else Rôles organisationnels @endfeature</div>
                    </div>
                    <div class="ob-widget-card-body p-0">
                        @if ($roleAssignments->isEmpty())
                            <div class="ob-widget-empty p-3">Aucun rôle attribué.</div>
                        @else
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>@feature('multi_site')<th>Section</th>@endfeature<th>Rôle</th></tr>
                                </thead>
                                <tbody>
                                @foreach ($roleAssignments as $a)
                                    <tr>
                                        @feature('multi_site')
                                        <td class="align-middle" style="font-size:var(--font-size-sm);">{{ $a->section_name }}</td>
                                        @endfeature
                                        <td class="align-middle" style="font-size:var(--font-size-sm);">{{ $a->role_name }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>{{-- /section-acces --}}

            {{-- ▸ Historique ─────────────────────────────────────────────── --}}
            <div id="section-historique" data-pers-section>
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-history"></i> Historique</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <p class="ob-widget-empty mb-0">Aucun historique disponible.</p>
                    </div>
                </div>
            </div>{{-- /section-historique --}}

        </div>{{-- /content --}}
    </div>{{-- /sidebar layout --}}

</div>

{{-- ── Qualification modal ─────────────────────────────────────────────────── --}}
<div class="modal fade" id="qualModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qualModalLabel" style="font-size:var(--font-size-base);">Compétence</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="qualForm" method="POST">
                @csrf
                <span id="qualMethodField"></span>
                <div class="modal-body">
                    <div class="mb-2" id="qualPosteWrap">
                        <label class="form-label" style="font-size:var(--font-size-sm);">
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
                        <label class="form-label" style="font-size:var(--font-size-sm);">Valeur / résultat</label>
                        <input id="qualVal" name="Q_VAL" type="text" class="form-control form-control-sm"
                               placeholder="ex. Obtenu, 15/20…">
                    </div>
                    <div>
                        <label class="form-label" style="font-size:var(--font-size-sm);">Date d'expiration</label>
                        <input id="qualExp" name="Q_EXPIRATION" type="date" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Cotisation modal ─────────────────────────────────────────────────────── --}}
<div class="modal fade" id="cotisModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cotisModalLabel" style="font-size:var(--font-size-base);">Cotisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cotisForm" method="POST">
                @csrf
                <span id="cotisMethodField"></span>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-3">
                            <label class="form-label" style="font-size:var(--font-size-sm);">Année *</label>
                            <input name="ANNEE" id="cotisAnnee" type="number" class="form-control form-control-sm"
                                   min="1990" max="2100" value="{{ date('Y') }}" required>
                        </div>
                        <div class="col-3">
                            <label class="form-label" style="font-size:var(--font-size-sm);">Période</label>
                            <select name="PERIODE_CODE" id="cotisPeriode" class="form-select form-select-sm">
                                @foreach ($periodes as $p)
                                    <option value="{{ $p->P_CODE }}" {{ $p->P_CODE === 'A' ? 'selected' : '' }}>
                                        {{ ucfirst($p->P_DESCRIPTION) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label" style="font-size:var(--font-size-sm);">Date *</label>
                            <input name="PC_DATE" id="cotisDate" type="date" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-3">
                            <label class="form-label" style="font-size:var(--font-size-sm);">Montant (€) *</label>
                            <input name="MONTANT" id="cotisMontant" type="number" class="form-control form-control-sm"
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
                                <input type="checkbox" name="REMBOURSEMENT" id="cotisRemb" value="1" class="form-check-input">
                                <label class="form-check-label" for="cotisRemb" style="font-size:var(--font-size-sm);">Remboursement</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="font-size:var(--font-size-sm);">Commentaire</label>
                            <input name="COMMENTAIRE" id="cotisComment" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>window.PERS_SHOW_CONFIG = { cotisUrl: '{{ url('personnel/' . $personnel->P_ID . '/cotisations') }}', qualUrl: '{{ url('personnel/' . $personnel->P_ID . '/qualifications') }}' };</script>
@vite(['resources/js/ob-personnel-show.js', 'resources/js/ob-pdf-personnel.js'])
@endpush
