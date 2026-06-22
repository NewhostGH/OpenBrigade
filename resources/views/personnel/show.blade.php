@extends('layout.app')

@section('title', $personnel->P_NOM . ' ' . $personnel->P_PRENOM . ' — Personnel — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('personnel.title'), 'url' => route('personnel.index')],
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
                    <i class="fas fa-edit me-1"></i> {{ __('personnel.btn_edit') }}
                </a>
                @if(auth()->id() === $personnel->P_ID || auth()->user()->hasPermission(2))
                <a href="{{ route('personnel.preferences', $personnel) }}" class="btn btn-sm btn-outline-secondary noprint" title="{{ __('personnel.btn_preferences_title') }}">
                    <i class="fas fa-sliders-h"></i>
                </a>
                @endif
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" title="{{ __('personnel.btn_export_title') }}">
                        <i class="fas fa-download"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('personnel.vcard', $personnel) }}">
                                <i class="fas fa-address-card me-2 text-muted"></i> {{ __('personnel.export_vcard') }}
                            </a>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item" data-livret-btn
                                onclick="window.__downloadLivretPdf && window.__downloadLivretPdf({{ $personnel->P_ID }})">
                                <i class="fas fa-file-pdf me-2 text-danger"></i> {{ __('personnel.export_livret') }}
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item" data-carte-btn
                                onclick="window.__downloadCartePdf && window.__downloadCartePdf({{ $personnel->P_ID }})">
                                <i class="fas fa-id-card me-2 text-danger"></i> {{ __('personnel.export_carte') }}
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
                         alt="{{ __('personnel.photo_alt', ['name' => $personnel->P_NOM]) }}"
                         style="width:96px; height:96px; object-fit:cover; border-radius:var(--radius-md);
                                border:2px solid var(--component-border);">
                </div>

                {{-- ── Identity dl ─────────────────────────────────────── --}}
                <div class="col">
                    <dl class="mb-0" style="display:grid; grid-template-columns:auto 1fr; gap:5px 16px;
                                            font-size:var(--font-size-sm); align-items:baseline;">
                        <dt class="text-muted fw-normal">{{ __('personnel.field_matricule') }}</dt>
                        <dd class="mb-0 fw-semibold">{{ $personnel->P_CODE }}</dd>

                        @feature('multi_site')
                        <dt class="text-muted fw-normal" title="{{ __('personnel.field_section_title') }}">{{ __('personnel.field_section_principale') }}</dt>
                        <dd class="mb-0">{{ $personnel->section?->S_CODE ?: '—' }}
                            @if($personnel->section?->S_DESCRIPTION)
                                <span class="text-muted">— {{ $personnel->section->S_DESCRIPTION }}</span>
                            @endif
                        </dd>
                        @endfeature

                        <dt class="text-muted fw-normal">{{ __('personnel.field_grade') }}</dt>
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

                        <dt class="text-muted fw-normal">{{ __('personnel.field_statut') }}</dt>
                        <dd class="mb-0">
                            <span class="ob-badge {{ $personnel->statutBadgeClass() }}">{{ $personnel->statutBadgeLabel() }}</span>
                            <span class="ob-badge {{ $personnel->etatBadgeClass() }} ms-1">{{ $personnel->etatBadgeLabel() }}</span>
                        </dd>

                        @if ($personnel->P_DATE_ENGAGEMENT)
                        <dt class="text-muted fw-normal">{{ __('personnel.field_engagement') }}</dt>
                        <dd class="mb-0">{{ $personnel->P_DATE_ENGAGEMENT->format('d/m/Y') }}</dd>
                        @endif

                        @if ($company)
                        <dt class="text-muted fw-normal">{{ __('personnel.field_entreprise') }}</dt>
                        <dd class="mb-0">{{ $company->C_NAME }}</dd>
                        @endif
                    </dl>

                    @if ($personnel->P_HIDE || $personnel->P_NOSPAM || $personnel->NPAI || $personnel->SUSPENDU)
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        @if ($personnel->P_HIDE)   <span class="ob-badge ob-badge-archive">{{ __('personnel.badge_masque') }}</span> @endif
                        @if ($personnel->P_NOSPAM) <span class="ob-badge ob-badge-archive">{{ __('personnel.badge_nospam') }}</span> @endif
                        @if ($personnel->NPAI)     <span class="ob-badge ob-badge-ben">{{ __('personnel.field_npai') }}</span> @endif
                        @if ($personnel->SUSPENDU) <span class="ob-badge ob-badge-bloqued">{{ __('personnel.badge_suspendu') }}</span> @endif
                    </div>
                    @endif
                </div>

                {{-- ── Quick-stat cards ────────────────────────────────── --}}
                <div class="col-md-5">
                    <div class="row g-2">
                        @php
                            $stats = [
                                ['icon' => 'fas fa-calendar-check', 'label' => __('personnel.stat_activites'),
                                 'value' => $participation->count(), 'color' => '#2563eb', 'bg' => '#eff6ff'],
                                ['icon' => 'fas fa-certificate', 'label' => __('personnel.stat_competences'),
                                 'value' => $personnel->qualifications->count(), 'color' => '#7c3aed', 'bg' => '#f5f3ff'],
                                ['icon' => 'fas fa-euro-sign', 'label' => __('personnel.stat_cotisations'),
                                 'value' => number_format($personnel->cotis_net, 2, ',', ' ') . ' €',
                                 'color' => $personnel->cotis_net < 0 ? '#dc2626' : '#16a34a',
                                 'bg'    => $personnel->cotis_net < 0 ? '#fff1f2' : '#f0fdf4'],
                                ['icon' => 'fas fa-clock', 'label' => __('personnel.stat_last_connect'),
                                 'value' => $personnel->P_LAST_CONNECT?->format('d/m/Y') ?? __('personnel.stat_never'),
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
                    {{-- Links are generated by ob-personnel-show.js from the
                         [data-pers-section] blocks (their data-nav-* attributes),
                         so the menu always matches the sections actually rendered
                         and their order — no separate list to keep in sync. --}}
                    <nav id="persSideNav"></nav>
                </div>
            </div>
        </div>

        {{-- ── Main content ────────────────────────────────────────────────── --}}
        <div style="flex:1; min-width:0;">

            {{-- ▸ Information ─────────────────────────────────────────────── --}}
            <div id="section-info" data-pers-section data-nav-icon="fas fa-user" data-nav-label="Information">
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-address-book"></i> {{ __('personnel.section_coordonnees') }}</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <dl class="ob-info-grid mb-0">
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_email') }}</dt>
                                <dd>
                                    @if ($personnel->P_EMAIL)
                                        <a href="mailto:{{ $personnel->P_EMAIL }}">{{ $personnel->P_EMAIL }}</a>
                                    @else —
                                    @endif
                                </dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_telephone') }}</dt>
                                <dd>{{ $personnel->P_PHONE ?: '—' }}</dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_portable') }}</dt>
                                <dd>{{ $personnel->P_PHONE2 ?: '—' }}</dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_adresse') }}</dt>
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
                                <dt>{{ __('personnel.field_npai') }}</dt>
                                <dd>
                                    <span class="ob-badge ob-badge-bloqued">{{ __('personnel.badge_adresse_invalide') }}</span>
                                    @if (!empty($personnel->DATE_NPAI))
                                        <small class="text-muted ms-1">{{ __('personnel.depuis_le', ['date' => \Carbon\Carbon::parse($personnel->DATE_NPAI)->format('d/m/Y')]) }}</small>
                                    @endif
                                </dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-id-badge"></i> {{ __('personnel.section_infos_perso') }}</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <dl class="ob-info-grid mb-0">
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_date_naissance') }}</dt>
                                <dd>
                                    {{ $personnel->P_BIRTHDATE?->format('d/m/Y') ?? '—' }}
                                    @if ($personnel->P_BIRTHDATE)
                                        <small class="text-muted">({{ __('personnel.age_ans', ['age' => $personnel->P_BIRTHDATE->diffInYears(now())]) }})</small>
                                    @endif
                                </dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_lieu_naissance') }}</dt>
                                <dd>
                                    {{ $personnel->P_BIRTHPLACE ?: '—' }}
                                    @if ($personnel->P_BIRTH_DEP)
                                        <span class="text-muted">({{ $personnel->P_BIRTH_DEP }})</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_nom_naissance') }}</dt>
                                <dd>{{ $personnel->P_NOM_NAISSANCE ?: '—' }}</dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_date_fin') }}</dt>
                                <dd>{{ $personnel->P_FIN?->format('d/m/Y') ?? '—' }}</dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_profession') }}</dt>
                                <dd>{{ $personnel->P_PROFESSION ?: '—' }}</dd>
                            </div>
                            @if ($personnel->P_LICENCE)
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_licence') }}</dt>
                                <dd>
                                    {{ $personnel->P_LICENCE }}
                                    @if ($personnel->P_LICENCE_DATE)
                                        <small class="text-muted">{{ __('personnel.licence_du', ['date' => $personnel->P_LICENCE_DATE->format('d/m/Y')]) }}</small>
                                    @endif
                                    @if ($personnel->P_LICENCE_EXPIRY)
                                        <br><small class="{{ $personnel->P_LICENCE_EXPIRY->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                            {{ __('personnel.licence_exp', ['date' => $personnel->P_LICENCE_EXPIRY->format('d/m/Y')]) }}
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
                        <div class="ob-widget-card-title"><i class="fas fa-phone-alt"></i> {{ __('personnel.section_urgence') }}</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <dl class="ob-info-grid mb-0">
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_nom') }}</dt>
                                <dd>{{ trim($personnel->P_RELATION_PRENOM . ' ' . $personnel->P_RELATION_NOM) ?: '—' }}</dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_telephone') }}</dt>
                                <dd>{{ $personnel->P_RELATION_PHONE ?: '—' }}</dd>
                            </div>
                            @if ($personnel->P_RELATION_MAIL)
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_email') }}</dt>
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
                        <div class="ob-widget-card-title"><i class="fas fa-sticky-note"></i> {{ __('personnel.section_notes') }}</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <p class="mb-0" style="font-size:var(--font-size-sm); white-space:pre-wrap;">{{ $personnel->OBSERVATION }}</p>
                    </div>
                </div>
                @endif
            </div>{{-- /section-info --}}

            {{-- ▸ Compétences ─────────────────────────────────────────────── --}}
            <div id="section-competences" data-pers-section data-nav-icon="fas fa-certificate" data-nav-label="Compétences" data-nav-badge="{{ $personnel->qualifications->count() ?: '' }}">
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-certificate"></i> {{ __('personnel.section_competences') }}
                            @if($personnel->qualifications->isNotEmpty())
                                <span class="ob-badge ob-badge-archive ms-1">{{ $personnel->qualifications->count() }}</span>
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-success noprint"
                                data-bs-toggle="modal" data-bs-target="#qualModal"
                                onclick="openQualModal(null)">
                            <i class="fas fa-plus me-1"></i> {{ __('common.add') }}
                        </button>
                    </div>
                    <div class="ob-widget-card-body p-0">
                        @if ($personnel->qualifications->isNotEmpty())
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size:var(--font-size-sm);">
                                <thead style="background:var(--table-header-bg);color:var(--table-header-text);">
                                    <tr>
                                        <th class="ps-3">{{ __('personnel.col_competence') }}</th>
                                        <th>{{ __('personnel.col_valeur') }}</th>
                                        <th>{{ __('personnel.col_expiration') }}</th>
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
                                                      onsubmit="return confirm('{{ __('personnel.confirm_del_competence') }}')">
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
                            <p class="ob-widget-empty p-3">{{ __('personnel.empty_competences') }}</p>
                        @endif
                    </div>
                </div>
            </div>{{-- /section-competences --}}

            {{-- ▸ Formations ──────────────────────────────────────────────── --}}
            <div id="section-formations" data-pers-section data-nav-icon="fas fa-graduation-cap" data-nav-label="Formations" data-nav-badge="{{ $formations->count() ?: '' }}">
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-graduation-cap"></i> {{ __('personnel.section_formations') }}
                            @if($formations->isNotEmpty())
                                <span class="ob-badge ob-badge-archive ms-1">{{ $formations->count() }}</span>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            @if($formations->isNotEmpty())
                                <a href="{{ route('personnel.export.formations', $personnel) }}"
                                   class="btn btn-sm btn-outline-secondary noprint" title="{{ __('personnel.export_formations_title') }}">
                                    <i class="fas fa-file-excel"></i>
                                </a>
                            @endif
                            @if(auth()->user()->hasPermission(4))
                                <button type="button" class="btn btn-sm btn-success noprint"
                                        data-bs-toggle="modal" data-bs-target="#addTrainingModal">
                                    <i class="fas fa-plus me-1"></i> {{ __('common.add') }}
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="ob-widget-card-body p-0">
                        @if($formations->isNotEmpty())
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size:var(--font-size-sm);">
                                <thead style="background:var(--table-header-bg);color:var(--table-header-text);">
                                    <tr>
                                        <th class="ps-3">{{ __('personnel.col_date') }}</th>
                                        <th>{{ __('personnel.col_competence') }}</th>
                                        <th>{{ __('personnel.col_type') }}</th>
                                        <th>{{ __('personnel.col_diplome') }}</th>
                                        <th>{{ __('personnel.col_lieu') }}</th>
                                        <th>{{ __('personnel.col_delivre_par') }}</th>
                                        <th>{{ __('personnel.col_commentaire') }}</th>
                                        @if(auth()->user()->hasPermission(4))<th class="noprint" style="width:72px;"></th>@endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($formations as $pf)
                                        <tr>
                                            <td class="ps-3" style="white-space:nowrap">
                                                {{ $pf->PF_DATE ? \Carbon\Carbon::parse($pf->PF_DATE)->format('d/m/Y') : '—' }}
                                            </td>
                                            <td>
                                                <strong>{{ $pf->PS_TYPE }}</strong>
                                                @if($pf->PS_DESC)
                                                    <small class="text-muted">— {{ $pf->PS_DESC }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $pf->TF_LIBELLE }}</td>
                                            <td>{{ $pf->PF_DIPLOME ?: '—' }}</td>
                                            <td>{{ $pf->PF_LIEU ?: '—' }}</td>
                                            <td>{{ $pf->PF_RESPONSABLE ?: '—' }}</td>
                                            <td>{{ $pf->PF_COMMENT ?: '—' }}</td>
                                            @if(auth()->user()->hasPermission(4))
                                                <td class="text-end pe-2 noprint">
                                                    <button type="button"
                                                            class="btn btn-xs btn-light py-0 px-1 me-1"
                                                            onclick="openEditTrainingModal({{ json_encode([
                                                                'pf_id'          => $pf->PF_ID,
                                                                'ps_id'          => $pf->PS_ID,
                                                                'tf_code'        => $pf->TF_CODE,
                                                                'pf_date'        => $pf->PF_DATE,
                                                                'pf_lieu'        => $pf->PF_LIEU ?? '',
                                                                'pf_responsable' => $pf->PF_RESPONSABLE ?? '',
                                                                'pf_diplome'     => $pf->PF_DIPLOME ?? '',
                                                                'pf_comment'     => $pf->PF_COMMENT ?? '',
                                                            ]) }})">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST"
                                                          action="{{ route('personnel.training.destroy', [$personnel, $pf->PF_ID]) }}"
                                                          class="d-inline"
                                                          onsubmit="return confirm('{{ __('personnel.confirm_del_formation') }}')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-light py-0 px-1 text-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="ob-widget-empty p-3">{{ __('personnel.empty_formations') }}</p>
                        @endif
                    </div>
                </div>
            </div>{{-- /section-formations --}}

            {{-- ▸ Cotisations ────────────────────────────────────────────── --}}
            <div id="section-cotisations" data-pers-section data-nav-icon="fas fa-euro-sign" data-nav-label="Cotisations" data-nav-badge="{{ $cotisations->count() ?: '' }}">
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-euro-sign"></i> {{ __('personnel.section_cotisations') }}
                            @if($cotisations->isNotEmpty())
                                <span class="ob-badge ob-badge-archive ms-1">{{ $cotisations->count() }}</span>
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-success noprint"
                                data-bs-toggle="modal" data-bs-target="#cotisModal"
                                onclick="openCotisModal(null)">
                            <i class="fas fa-plus me-1"></i> {{ __('common.add') }}
                        </button>
                    </div>
                    <div class="ob-widget-card-body p-0">
                        @if ($cotisations->isNotEmpty())
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size:var(--font-size-sm);">
                                <thead style="background:var(--table-header-bg);color:var(--table-header-text);">
                                    <tr>
                                        <th class="ps-3">{{ __('personnel.col_annee') }}</th>
                                        <th>{{ __('personnel.col_periode') }}</th>
                                        <th>{{ __('personnel.col_date') }}</th>
                                        <th class="text-end">{{ __('personnel.col_montant') }}</th>
                                        <th>{{ __('personnel.col_mode') }}</th>
                                        <th>{{ __('personnel.col_commentaire') }}</th>
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
                                                    <span class="badge bg-warning text-dark me-1">{{ __('personnel.label_remb') }}</span>
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
                                                      onsubmit="return confirm('{{ __('personnel.confirm_del_cotisation') }}')">
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
                                            style="font-size:var(--font-size-xs);color:var(--text-muted-soft);">{{ __('personnel.total_net') }}</td>
                                        <td class="text-end {{ $personnel->cotis_net < 0 ? 'text-danger' : '' }}">
                                            {{ number_format($personnel->cotis_net, 2, ',', ' ') }} €
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        @else
                            <p class="ob-widget-empty p-3">{{ __('personnel.empty_cotisations') }}</p>
                        @endif
                    </div>
                </div>
            </div>{{-- /section-cotisations --}}

            {{-- ▸ Participation ───────────────────────────────────────────── --}}
            <div id="section-participation" data-pers-section data-nav-icon="fas fa-calendar-check" data-nav-label="Participation" data-nav-badge="{{ $participation->count() ?: '' }}">
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-calendar-check"></i> {{ __('personnel.section_participation') }}
                            @if($participation->isNotEmpty())
                                <span class="ob-badge ob-badge-archive ms-1">{{ $participation->count() }}</span>
                            @endif
                        </div>
                        <div class="ob-widget-card-actions">
                            <a href="{{ route('personnel.export.meetings', $personnel) }}"
                               class="btn btn-sm btn-light" title="{{ __('personnel.export_meetings_title') }}">
                                <i class="fas fa-file-excel text-success"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ob-widget-card-body p-0">
                        @if ($participation->isNotEmpty())
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size:var(--font-size-sm);">
                                <thead style="background:var(--table-header-bg);color:var(--table-header-text);">
                                    <tr>
                                        <th class="ps-3">{{ __('personnel.col_activite') }}</th>
                                        <th>{{ __('personnel.col_date') }}</th>
                                        <th class="text-end">{{ __('personnel.col_duree') }}</th>
                                        <th class="text-end">{{ __('personnel.col_km') }}</th>
                                        <th>{{ __('personnel.col_presence') }}</th>
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
                                                (bool) $ep->EP_ABSENT && !(bool) $ep->EP_EXCUSE => __('personnel.presence_absent'),
                                                (bool) $ep->EP_ABSENT && (bool) $ep->EP_EXCUSE  => __('personnel.presence_excuse'),
                                                default => __('personnel.presence_present'),
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
                                    {{ __('personnel.participation_limit') }}
                                </p>
                            @endif
                        @else
                            <p class="ob-widget-empty p-3">{{ __('personnel.empty_participation') }}</p>
                        @endif
                    </div>
                </div>
            </div>{{-- /section-participation --}}

            {{-- ▸ Dotation ───────────────────────────────────────────────── --}}
            <div id="section-dotation" data-pers-section data-nav-icon="fas fa-box" data-nav-label="Dotation" data-nav-badge="{{ $tenues->count() ?: '' }}">
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-box me-1"></i> {{ __('personnel.section_dotation') }}</div>
                        <div class="ob-widget-card-actions">
                            @if(auth()->user()->hasPermission(70) || auth()->id() == $personnel->P_ID)
                            <a href="{{ route('personnel.tenues', $personnel) }}" class="btn btn-sm btn-outline-primary noprint">
                                <i class="fas fa-edit me-1"></i> {{ __('personnel.btn_gerer') }}
                            </a>
                            @endif
                        </div>
                    </div>
                    <div class="ob-widget-card-body p-0">
                        @if($tenues->isEmpty())
                            <p class="ob-widget-empty mb-0">{{ __('personnel.empty_dotation') }}</p>
                        @else
                            <table class="ob-table ob-table-sm w-100 mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('personnel.col_type_hab') }}</th>
                                        <th>{{ __('personnel.col_modele') }}</th>
                                        <th>{{ __('personnel.col_annee') }}</th>
                                        <th>{{ __('personnel.col_taille') }}</th>
                                        <th class="text-end">{{ __('personnel.col_nb') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tenues as $t)
                                    <tr>
                                        <td>{{ $t->TM_CODE }}</td>
                                        <td>{{ $t->MA_MODELE }}</td>
                                        <td>{{ $t->MA_ANNEE }}</td>
                                        <td>{{ $t->TV_NAME ?? '—' }}</td>
                                        <td class="text-end">{{ $t->MA_NB }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>{{-- /section-dotation --}}

            {{-- ▸ Documents ──────────────────────────────────────────────── --}}
            <div id="section-documents" data-pers-section data-nav-icon="fas fa-file-alt" data-nav-label="Documents">
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-file-alt"></i> {{ __('personnel.section_documents') }}</div>
                        <button class="btn btn-sm btn-success noprint" disabled title="{{ __('personnel.feature_coming') }}">
                            <i class="fas fa-plus me-1"></i> {{ __('common.add') }}
                        </button>
                    </div>
                    <div class="ob-widget-card-body">
                        <p class="ob-widget-empty mb-0">{{ __('personnel.empty_documents') }}</p>
                    </div>
                </div>
            </div>{{-- /section-documents --}}

            {{-- ▸ Notes de frais ─────────────────────────────────────────── --}}
            <div id="section-notedfrais" data-pers-section data-nav-icon="fas fa-receipt" data-nav-label="Notes de frais">
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-receipt"></i> {{ __('personnel.section_notes_frais') }}</div>
                        <button class="btn btn-sm btn-success noprint" disabled title="{{ __('personnel.feature_coming') }}">
                            <i class="fas fa-plus me-1"></i> {{ __('common.add') }}
                        </button>
                    </div>
                    <div class="ob-widget-card-body">
                        <p class="ob-widget-empty mb-0">{{ __('personnel.empty_notes_frais') }}</p>
                    </div>
                </div>
            </div>{{-- /section-notedfrais --}}

            {{-- ▸ Disponibilité ──────────────────────────────────────────── --}}
            <div id="section-disponibilite" data-pers-section data-nav-icon="fas fa-calendar-day" data-nav-label="Disponibilité">
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-calendar-day"></i> {{ __('personnel.section_disponibilite') }}</div>
                        <button class="btn btn-sm btn-success noprint" disabled title="{{ __('personnel.feature_coming') }}">
                            <i class="fas fa-plus me-1"></i> {{ __('common.add') }}
                        </button>
                    </div>
                    <div class="ob-widget-card-body">
                        <p class="ob-widget-empty mb-0">{{ __('personnel.empty_disponibilite') }}</p>
                    </div>
                </div>
            </div>{{-- /section-disponibilite --}}

            {{-- ▸ Calendrier ─────────────────────────────────────────────── --}}
            <div id="section-calendrier" data-pers-section data-nav-icon="fas fa-calendar" data-nav-label="Calendrier">
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-calendar"></i> {{ __('personnel.section_calendrier') }}</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <p class="ob-widget-empty mb-0">{{ __('personnel.empty_calendrier') }}</p>
                    </div>
                </div>
            </div>{{-- /section-calendrier --}}

            {{-- ▸ Absences ───────────────────────────────────────────────── --}}
            <div id="section-absences" data-pers-section data-nav-icon="fas fa-user-times" data-nav-label="Absences">
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-user-times"></i> {{ __('personnel.section_absences') }}</div>
                        <button class="btn btn-sm btn-success noprint" disabled title="{{ __('personnel.feature_coming') }}">
                            <i class="fas fa-plus me-1"></i> {{ __('common.add') }}
                        </button>
                    </div>
                    <div class="ob-widget-card-body">
                        <p class="ob-widget-empty mb-0">{{ __('personnel.empty_absences') }}</p>
                    </div>
                </div>
            </div>{{-- /section-absences --}}

            {{-- ▸ Géolocalisation ────────────────────────────────────────── --}}
            <div id="section-geo" data-pers-section data-nav-icon="fas fa-map-marker-alt" data-nav-label="Géolocalisation">
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-map-marker-alt"></i> {{ __('personnel.section_geo') }}</div>
                        <a href="{{ route('geolocation.index') }}" class="btn btn-sm btn-light noprint">
                            <i class="fas fa-map-marked-alt me-1"></i> {{ __('personnel.btn_carte_globale') }}
                        </a>
                    </div>
                    <div class="ob-widget-card-body">
                        @if ($gps && $gps->LAT && $gps->LNG)
                            <dl class="ob-info-grid mb-0">
                                <div class="ob-info-item">
                                    <dt>{{ __('personnel.field_coordonnees') }}</dt>
                                    <dd>{{ number_format((float)$gps->LAT, 5) }}, {{ number_format((float)$gps->LNG, 5) }}</dd>
                                </div>
                                <div class="ob-info-item">
                                    <dt>{{ __('personnel.field_adresse') }}</dt>
                                    <dd>{{ $gps->ADDRESS ?: '—' }}</dd>
                                </div>
                                <div class="ob-info-item">
                                    <dt>{{ __('personnel.field_derniere_maj') }}</dt>
                                    <dd>{{ $gps->DATE_LOC ? date('d/m/Y H:i', strtotime($gps->DATE_LOC)) : '—' }}</dd>
                                </div>
                            </dl>
                        @else
                            <p class="ob-widget-empty mb-0">{{ __('personnel.empty_gps') }}</p>
                        @endif
                    </div>
                </div>
            </div>{{-- /section-geo --}}

            {{-- ▸ Accès ─────────────────────────────────────────────────── --}}
            <div id="section-acces" data-pers-section data-nav-icon="fas fa-shield-alt" data-nav-label="Accès">
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-shield-alt"></i> {{ __('personnel.section_acces') }}</div>
                        <div class="ob-widget-card-actions d-flex gap-2">
                            @if (auth()->user()->hasPermission(9) || auth()->user()->hasPermission(25))
                                <a href="{{ route('personnel.send-credentials.show', $personnel->P_ID) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-key me-1"></i>{{ __('personnel.btn_identifiants') }}
                                </a>
                            @endif
                            @if (auth()->user()->hasPermission(9))
                                <a href="{{ route('personnel.edit', $personnel->P_ID) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-pen me-1"></i>{{ __('personnel.btn_gerer_acces') }}
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="ob-widget-card-body">
                        <dl class="ob-info-grid mb-0">
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_groupes_acces') }}</dt>
                                <dd>
                                    @forelse ($personnelGroups as $gname)
                                        <span class="ob-badge ob-badge-ext me-1">{{ $gname }}</span>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_derniere_connexion') }}</dt>
                                <dd>{{ $personnel->P_LAST_CONNECT?->format('d/m/Y H:i') ?? __('personnel.stat_never') }}</dd>
                            </div>
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_connexions') }}</dt>
                                <dd>{{ $personnel->P_NB_CONNECT ?? '0' }}</dd>
                            </div>
                            @if ($personnel->P_ACCEPT_DATE)
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_charte_acceptee') }}</dt>
                                <dd>{{ $personnel->P_ACCEPT_DATE->format('d/m/Y H:i') }}</dd>
                            </div>
                            @endif
                            @if ($personnel->P_ACCEPT_DATE2)
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_charte2_acceptee') }}</dt>
                                <dd>{{ $personnel->P_ACCEPT_DATE2->format('d/m/Y H:i') }}</dd>
                            </div>
                            @endif
                            <div class="ob-info-item">
                                <dt>{{ __('personnel.field_indicateurs') }}</dt>
                                <dd>
                                    @if ($personnel->P_HIDE)   <span class="ob-badge ob-badge-archive me-1">{{ __('personnel.badge_masque') }}</span> @endif
                                    @if ($personnel->P_NOSPAM) <span class="ob-badge ob-badge-archive me-1">{{ __('personnel.badge_nospam') }}</span> @endif
                                    @if ($personnel->NPAI)     <span class="ob-badge ob-badge-ben me-1">{{ __('personnel.field_npai') }}</span> @endif
                                    @if ($personnel->SUSPENDU) <span class="ob-badge ob-badge-bloqued me-1">{{ __('personnel.badge_suspendu') }}</span> @endif
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
                            <i class="fas fa-sitemap me-1"></i> {{ __('personnel.section_sections') }}
                            @if ($personnel->section)
                                <span class="text-muted fw-normal ms-2" style="font-size:var(--font-size-sm);">
                                    {{ __('personnel.section_principale_label') }} <strong>{{ $personnel->section->S_CODE }}</strong>
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
                            <span class="text-muted" style="font-size:var(--font-size-sm);">{{ __('personnel.no_section_attribuee') }}</span>
                        @endforelse
                    </div>
                    @endfeature

                    {{-- Section-scoped organisational roles (ob_user_assignment) — read-only;
                         edited from the member's CRUD form, "Accès" tab. --}}
                    <div class="ob-widget-card-subheader">
                        <div><i class="fas fa-user-tie me-1"></i> @feature('multi_site'){{ __('personnel.roles_par_section') }} @else {{ __('personnel.roles_organisationnels') }} @endfeature</div>
                    </div>
                    <div class="ob-widget-card-body p-0">
                        @if ($roleAssignments->isEmpty())
                            <div class="ob-widget-empty p-3">{{ __('personnel.empty_roles') }}</div>
                        @else
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>@feature('multi_site')<th>{{ __('personnel.col_section') }}</th>@endfeature<th>{{ __('personnel.col_role') }}</th></tr>
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

            {{-- ▸ Identifiants de contact ────────────────────────────────── --}}
            @if(isset($contactHandles) && $contactHandles->isNotEmpty())
            <div id="section-contacts" data-pers-section data-nav-icon="fas fa-address-card" data-nav-label="Identifiants">
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-address-card"></i> {{ __('personnel.section_identifiants') }}</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <form method="POST" action="{{ route('personnel.contacts.update', $personnel) }}">
                            @csrf
                            <div class="row g-2">
                                @foreach($contactHandles as $ct)
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm">
                                        <i class="{{ $ct->CT_ICON ?? 'fas fa-link' }} me-1"></i>
                                        {{ $ct->CONTACT_TYPE }}
                                    </label>
                                    <input type="text" name="c{{ $ct->CT_ID }}"
                                           value="{{ $ct->CONTACT_VALUE ?? '' }}"
                                           class="form-control form-control-sm"
                                           placeholder="{{ __('personnel.contact_placeholder', ['type' => $ct->CONTACT_TYPE]) }}">
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-save me-1"></i> {{ __('common.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>{{-- /section-contacts --}}
            @endif

            {{-- ▸ Salarié ────────────────────────────────────────────────── --}}
            @if (auth()->user()->hasPermission(2) && $personnel->P_STATUT === 'SAL')
            <div id="section-salarie" data-pers-section data-nav-icon="fas fa-briefcase" data-nav-label="Salarié">
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-briefcase"></i> {{ __('personnel.section_salarie') }}</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <form method="POST" action="{{ route('personnel.salarie.update', $personnel) }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.label_heures_semaine') }}</label>
                                    <input type="number" step="0.01" name="TS_HEURES" class="form-control form-control-sm"
                                           value="{{ old('TS_HEURES', $personnel->TS_HEURES) }}" min="0" max="999">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.label_heures_jour') }}</label>
                                    <input type="number" step="0.01" name="TS_HEURES_PAR_JOUR" class="form-control form-control-sm"
                                           value="{{ old('TS_HEURES_PAR_JOUR', $personnel->TS_HEURES_PAR_JOUR) }}" min="0" max="99">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.label_jours_cp') }}</label>
                                    <input type="number" step="0.01" name="TS_JOURS_CP_PAR_AN" class="form-control form-control-sm"
                                           value="{{ old('TS_JOURS_CP_PAR_AN', $personnel->TS_JOURS_CP_PAR_AN) }}" min="0" max="999">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.label_heures_an') }}</label>
                                    <input type="number" step="0.01" name="TS_HEURES_PAR_AN" class="form-control form-control-sm"
                                           value="{{ old('TS_HEURES_PAR_AN', $personnel->TS_HEURES_PAR_AN) }}" min="0" max="9999">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.label_heures_recuperer') }}</label>
                                    <input type="number" step="0.01" name="TS_HEURES_A_RECUPERER" class="form-control form-control-sm"
                                           value="{{ old('TS_HEURES_A_RECUPERER', $personnel->TS_HEURES_A_RECUPERER) }}">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.label_reliquat_cp') }}</label>
                                    <input type="number" step="0.01" name="TS_RELIQUAT_CP" class="form-control form-control-sm"
                                           value="{{ old('TS_RELIQUAT_CP', $personnel->TS_RELIQUAT_CP) }}">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.label_reliquat_rtt') }}</label>
                                    <input type="number" step="0.01" name="TS_RELIQUAT_RTT" class="form-control form-control-sm"
                                           value="{{ old('TS_RELIQUAT_RTT', $personnel->TS_RELIQUAT_RTT) }}">
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-save me-1"></i> {{ __('common.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>{{-- /section-salarie --}}
            @endif

            @if ($homonyms->isNotEmpty())
            <div id="section-homonymes" data-pers-section data-nav-icon="fas fa-user-friends" data-nav-label="Homonymes" data-nav-badge="{{ $homonyms->count() }}" data-nav-badge-class="bg-warning text-dark">
                <div class="ob-widget-card mb-3 border-warning">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-user-friends text-warning"></i> {{ __('personnel.section_homonymes') }}
                        </div>
                    </div>
                    <div class="ob-widget-card-body">
                        <p class="text-muted mb-3" style="font-size:var(--font-size-sm);">
                            {{ __('personnel.homonymes_intro') }}
                        </p>
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('personnel.col_numero') }}</th>
                                    <th>{{ __('personnel.field_nom') }}</th>
                                    <th>{{ __('personnel.field_date_naissance') }}</th>
                                    <th>{{ __('personnel.col_statut') }}</th>
                                    <th>{{ __('personnel.col_section') }}</th>
                                    @if (auth()->user()->hasPermission(2))
                                    <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($homonyms as $h)
                                @php
                                    $sameBirthdate = $personnel->P_BIRTHDATE && $h->P_BIRTHDATE
                                        && $personnel->P_BIRTHDATE === $h->P_BIRTHDATE;
                                    $differentBirthdate = $personnel->P_BIRTHDATE && $h->P_BIRTHDATE
                                        && $personnel->P_BIRTHDATE !== $h->P_BIRTHDATE;
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('personnel.show', $h->P_ID) }}">{{ $h->P_ID }}</a>
                                    </td>
                                    <td>{{ ucfirst(mb_strtolower($h->P_PRENOM)) }} {{ strtoupper($h->P_NOM) }}
                                        @if ($h->P_OLD_MEMBER) <span class="badge bg-secondary ms-1">{{ __('personnel.badge_ancien') }}</span> @endif
                                    </td>
                                    <td>
                                        @if ($h->P_BIRTHDATE)
                                            {{ \Carbon\Carbon::parse($h->P_BIRTHDATE)->format('d/m/Y') }}
                                            @if ($sameBirthdate)
                                                <span class="badge bg-warning text-dark ms-1" title="{{ __('personnel.badge_doublon_title') }}">{{ __('personnel.badge_doublon_probable') }}</span>
                                            @elseif ($differentBirthdate)
                                                <span class="badge bg-secondary ms-1" title="{{ __('personnel.badge_homonyme_title') }}">{{ __('personnel.badge_homonyme') }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $h->P_STATUT }}</td>
                                    <td>{{ $h->S_CODE }}</td>
                                    @if (auth()->user()->hasPermission(2))
                                    <td>
                                        @if (!$differentBirthdate)
                                        <a href="{{ route('personnel.merge.show', [$personnel, $h->P_ID]) }}"
                                           class="btn btn-xs btn-outline-warning"
                                           title="{{ __('personnel.badge_doublon_title_manage') }}">
                                            <i class="fas fa-code-merge me-1"></i> {{ __('personnel.btn_fusionner') }}
                                        </a>
                                        @else
                                        <span class="text-muted small">{{ __('personnel.homonyme_non_applicable') }}</span>
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>{{-- /section-homonymes --}}
            @endif

            {{-- ▸ Historique ─────────────────────────────────────────────── --}}
            <div id="section-historique" data-pers-section data-nav-icon="fas fa-history" data-nav-label="Historique">
                <div class="ob-widget-card mb-3">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-history"></i> {{ __('personnel.section_historique') }}</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <p class="ob-widget-empty mb-0">{{ __('personnel.empty_historique') }}</p>
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
                <h5 class="modal-title" id="qualModalLabel" style="font-size:var(--font-size-base);">{{ __('personnel.modal_competence_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="qualForm" method="POST">
                @csrf
                <span id="qualMethodField"></span>
                <div class="modal-body">
                    <div class="mb-2" id="qualPosteWrap">
                        <label class="form-label" style="font-size:var(--font-size-sm);">
                            {{ __('personnel.modal_competence_label') }} <span class="text-danger">*</span>
                        </label>
                        <select id="qualPosteSelect" name="PS_ID" class="form-select form-select-sm" required>
                            <option value="">{{ __('personnel.modal_choisir') }}</option>
                            @foreach ($postes as $poste)
                                <option value="{{ $poste->PS_ID }}">
                                    {{ $poste->TYPE }}{{ $poste->DESCRIPTION ? ' — ' . $poste->DESCRIPTION : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="qualPosteLabel" class="mb-2" style="display:none;">
                        <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_competence_label') }}</label>
                        <p class="mb-0" id="qualPosteLabelText" style="font-size:var(--font-size-sm);"></p>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_val_label') }}</label>
                        <input id="qualVal" name="Q_VAL" type="text" class="form-control form-control-sm"
                               placeholder="{{ __('personnel.modal_val_placeholder') }}">
                    </div>
                    <div>
                        <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_exp_label') }}</label>
                        <input id="qualExp" name="Q_EXPIRATION" type="date" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-primary">{{ __('common.save') }}</button>
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
                <h5 class="modal-title" id="cotisModalLabel" style="font-size:var(--font-size-base);">{{ __('personnel.modal_cotisation_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cotisForm" method="POST">
                @csrf
                <span id="cotisMethodField"></span>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-3">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_annee_label') }}</label>
                            <input name="ANNEE" id="cotisAnnee" type="number" class="form-control form-control-sm"
                                   min="1990" max="2100" value="{{ date('Y') }}" required>
                        </div>
                        <div class="col-3">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_periode_label') }}</label>
                            <select name="PERIODE_CODE" id="cotisPeriode" class="form-select form-select-sm">
                                @foreach ($periodes as $p)
                                    <option value="{{ $p->P_CODE }}" {{ $p->P_CODE === 'A' ? 'selected' : '' }}>
                                        {{ ucfirst($p->P_DESCRIPTION) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_date_label') }}</label>
                            <input name="PC_DATE" id="cotisDate" type="date" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-3">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_montant_label') }}</label>
                            <input name="MONTANT" id="cotisMontant" type="number" class="form-control form-control-sm"
                                   min="0" step="0.01" placeholder="0.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_mode_paiement') }}</label>
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
                                <label class="form-check-label" for="cotisRemb" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_remboursement') }}</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_commentaire_label') }}</label>
                            <input name="COMMENTAIRE" id="cotisComment" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-primary">{{ __('common.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Add training modal ──────────────────────────────────────────────── --}}
<div class="modal fade" id="addTrainingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('personnel.training.store', $personnel) }}">
                @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title">{{ __('personnel.modal_add_formation') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_competence_label') }} <span class="text-danger">*</span></label>
                            <select name="PS_ID" class="form-select form-select-sm" required>
                                <option value="">{{ __('personnel.modal_choisir') }}</option>
                                @foreach($postes->filter(fn($p) => $p->PS_DIPLOMA || $p->PS_RECYCLE) as $p)
                                    <option value="{{ $p->PS_ID }}">{{ $p->TYPE }}{{ $p->DESCRIPTION ? ' — ' . $p->DESCRIPTION : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_type_formation') }} <span class="text-danger">*</span></label>
                            <select name="TF_CODE" class="form-select form-select-sm" required>
                                @foreach($formationTypes as $ft)
                                    <option value="{{ $ft->TF_CODE }}">{{ $ft->TF_LIBELLE }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.col_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="PF_DATE" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_diplome_label') }}</label>
                            <input type="text" name="PF_DIPLOME" class="form-control form-control-sm" maxlength="50">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_lieu_label') }}</label>
                            <input type="text" name="PF_LIEU" class="form-control form-control-sm" maxlength="100">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_delivre_par') }}</label>
                            <input type="text" name="PF_RESPONSABLE" class="form-control form-control-sm" maxlength="100">
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_commentaire_label') }}</label>
                            <input type="text" name="PF_COMMENT" class="form-control form-control-sm" maxlength="255">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-primary">{{ __('common.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Edit training modal ─────────────────────────────────────────────── --}}
<div class="modal fade" id="editTrainingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="editTrainingForm">
                @csrf @method('PATCH')
                <div class="modal-header py-2">
                    <h6 class="modal-title">{{ __('personnel.modal_edit_formation') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_type_formation') }} <span class="text-danger">*</span></label>
                            <select name="TF_CODE" id="editTfCode" class="form-select form-select-sm" required>
                                @foreach($formationTypes as $ft)
                                    <option value="{{ $ft->TF_CODE }}">{{ $ft->TF_LIBELLE }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.col_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="PF_DATE" id="editPfDate" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_diplome_label') }}</label>
                            <input type="text" name="PF_DIPLOME" id="editPfDiplome" class="form-control form-control-sm" maxlength="50">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_lieu_label') }}</label>
                            <input type="text" name="PF_LIEU" id="editPfLieu" class="form-control form-control-sm" maxlength="100">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_delivre_par') }}</label>
                            <input type="text" name="PF_RESPONSABLE" id="editPfResp" class="form-control form-control-sm" maxlength="100">
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="font-size:var(--font-size-sm);">{{ __('personnel.modal_commentaire_label') }}</label>
                            <input type="text" name="PF_COMMENT" id="editPfComment" class="form-control form-control-sm" maxlength="255">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-primary">{{ __('common.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditTrainingModal(data) {
    const base = '{{ route('personnel.training.update', [$personnel, '__ID__']) }}';
    document.getElementById('editTrainingForm').action = base.replace('__ID__', data.pf_id);
    document.getElementById('editTfCode').value    = data.tf_code;
    document.getElementById('editPfDate').value    = data.pf_date ? data.pf_date.substring(0, 10) : '';
    document.getElementById('editPfDiplome').value = data.pf_diplome;
    document.getElementById('editPfLieu').value    = data.pf_lieu;
    document.getElementById('editPfResp').value    = data.pf_responsable;
    document.getElementById('editPfComment').value = data.pf_comment;
    new bootstrap.Modal(document.getElementById('editTrainingModal')).show();
}
</script>

@endsection

@push('scripts')
<script>window.PERS_SHOW_CONFIG = { cotisUrl: '{{ url('personnel/' . $personnel->P_ID . '/cotisations') }}', qualUrl: '{{ url('personnel/' . $personnel->P_ID . '/qualifications') }}' };</script>
@vite(['resources/js/ob-personnel-show.js', 'resources/js/ob-pdf-personnel.js'])
@endpush
