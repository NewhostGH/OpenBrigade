@extends('layout.app')

@section('title', ($section->S_CODE ?: 'Section') . ' — Organisation — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Organisation'],
    ['label' => 'Sections', 'url' => route('organization.sections')],
    ['label' => $section->S_CODE],
]"/>

@php $activeTab = request('tab', 'informations'); @endphp

<div class="mx-3 mt-3">

    {{-- ── Header card ─────────────────────────────────────────────────────── --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-layer-group me-2"></i>
                <span class="font-monospace fw-semibold">{{ $section->S_CODE }}</span>
                @if ($section->S_DESCRIPTION)
                    <span class="text-muted fw-normal ms-2">— {{ $section->S_DESCRIPTION }}</span>
                @endif
                @if ($section->S_INACTIVE)
                    <span class="ob-badge ob-badge-archive ms-2">Inactive</span>
                @else
                    <span class="ob-badge ob-badge-actif ms-2">Active</span>
                @endif
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('organization.sections.edit', $section->S_ID) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-edit me-1"></i>Modifier
                </a>
                <a href="{{ route('organization.sections') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
        </div>
        <div class="ob-widget-card-body pt-2 pb-2">
            <div class="d-flex gap-4" style="font-size:var(--font-size-sm); color:var(--text-muted);">
                <span><i class="fas fa-users me-1"></i>{{ $memberCount }} membre{{ $memberCount !== 1 ? 's' : '' }}</span>
                @if ($section->parent)
                    <span><i class="fas fa-sitemap me-1"></i>{{ $section->parent->S_CODE }}</span>
                @endif
                @if ($section->S_CITY)
                    <span><i class="fas fa-map-marker-alt me-1"></i>{{ $section->S_CITY }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Tabs ─────────────────────────────────────────────────────────────── --}}
    <ul class="nav nav-tabs mb-3">
        @php
            $tabs = [
                'informations'    => ['icon' => 'fas fa-info-circle',    'label' => 'Informations'],
                'organigramme'    => ['icon' => 'fas fa-project-diagram', 'label' => 'Organigramme'],
                'personalisation' => ['icon' => 'fas fa-palette',         'label' => 'Personnalisation'],
                'agrements'       => ['icon' => 'fas fa-certificate',     'label' => 'Agréments & Médailles'],
                'cotisation'      => ['icon' => 'fas fa-university',      'label' => 'Cotisation'],
            ];
        @endphp
        @foreach ($tabs as $key => $tab)
            <li class="nav-item">
                <a class="nav-link{{ $activeTab === $key ? ' active' : '' }}"
                   href="{{ route('organization.sections.show', [$section->S_ID, 'tab' => $key]) }}">
                    <i class="{{ $tab['icon'] }} me-1"></i>{{ $tab['label'] }}
                </a>
            </li>
        @endforeach
    </ul>

    {{-- ════════════════════════════════════════════════════════════════════════
         Tab 1 — Informations
    ═══════════════════════════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'informations')

        <div class="row g-3">
            <div class="col-md-6">
                <div class="ob-widget-card h-100">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-layer-group me-2"></i>Informations obligatoires</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <dl class="ob-info-grid mb-0">
                            <div class="ob-info-item"><dt>Code</dt><dd>{{ $section->S_CODE ?: '—' }}</dd></div>
                            <div class="ob-info-item"><dt>Nom</dt><dd>{{ $section->S_DESCRIPTION ?: '—' }}</dd></div>
                            <div class="ob-info-item"><dt>Ordre garde</dt><dd>{{ $section->S_ORDER ?? '—' }}</dd></div>
                            <div class="ob-info-item">
                                <dt>Section parente</dt>
                                <dd>
                                    @if ($section->parent)
                                        <a href="{{ route('organization.sections.show', $section->parent->S_ID) }}">
                                            {{ $section->parent->S_CODE }}
                                            @if ($section->parent->S_DESCRIPTION)— {{ $section->parent->S_DESCRIPTION }}@endif
                                        </a>
                                    @else —
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="ob-widget-card h-100">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-address-book me-2"></i>Contact</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <dl class="ob-info-grid mb-0">
                            @foreach ([
                                'Téléphone'          => $section->S_PHONE,
                                'Tél opérationnel'   => $section->S_PHONE2,
                                'Tél formations'     => $section->S_PHONE3,
                                'Fax'                => $section->S_FAX,
                                'Email opérationnel' => $section->S_EMAIL,
                                'Email secrétariat'  => $section->S_EMAIL2,
                                'Email formation'    => $section->S_EMAIL3,
                                'Groupe WhatsApp'    => $section->S_WHATSAPP,
                                'ID Radio'           => $section->S_ID_RADIO,
                            ] as $label => $value)
                                @if ($value)
                                    <div class="ob-info-item"><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                                @endif
                            @endforeach
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="ob-widget-card h-100">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title"><i class="fas fa-info-circle me-2"></i>Informations facultatives</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <dl class="ob-info-grid mb-0">
                            @php
                                $adresse = implode(', ', array_filter([$section->S_ADDRESS, $section->S_ADDRESS_COMPLEMENT]));
                            @endphp
                            @if ($adresse)<div class="ob-info-item"><dt>Adresse</dt><dd>{{ $adresse }}</dd></div>@endif
                            @if ($section->S_ZIP_CODE || $section->S_CITY)
                                <div class="ob-info-item"><dt>Ville</dt><dd>{{ trim($section->S_ZIP_CODE . ' ' . $section->S_CITY) }}</dd></div>
                            @endif
                            @if ($section->S_SIRET)<div class="ob-info-item"><dt>SIRET</dt><dd>{{ $section->S_SIRET }}</dd></div>@endif
                            @if ($section->S_AFFILIATION)<div class="ob-info-item"><dt>N° Affiliation</dt><dd>{{ $section->S_AFFILIATION }}</dd></div>@endif
                            @if ($section->S_URL)
                                <div class="ob-info-item">
                                    <dt>Site web</dt>
                                    <dd><a href="{{ Str::startsWith($section->S_URL, 'http') ? $section->S_URL : 'https://' . $section->S_URL }}"
                                           target="_blank" rel="noopener">{{ $section->S_URL }}</a></dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         Tab 2 — Organigramme
    ═══════════════════════════════════════════════════════════════════════════ --}}
    @elseif ($activeTab === 'organigramme')

        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title"><i class="fas fa-project-diagram me-2"></i>Rôles dans la section</div>
            </div>
            <div class="ob-widget-card-body">
                @if ($orgByRole->isEmpty())
                    <p class="text-muted mb-0" style="font-size:var(--font-size-sm);">Aucun rôle attribué dans cette section.</p>
                @else
                    <div class="row g-3">
                        @foreach ($orgByRole as $roleName => $members)
                            <div class="col-md-4 col-sm-6">
                                <div style="border:1px solid var(--component-border); border-radius:var(--radius-md); overflow:hidden;">
                                    <div style="background:var(--bg-subtle); padding:8px 12px;
                                                font-size:var(--font-size-sm); font-weight:600;
                                                border-bottom:1px solid var(--component-border);">
                                        <i class="fas fa-shield-alt me-1 text-muted"></i>{{ $roleName }}
                                        <span class="ob-badge ob-badge-int ms-1">{{ $members->count() }}</span>
                                    </div>
                                    <ul class="list-unstyled mb-0" style="padding:8px 12px; display:flex; flex-direction:column; gap:6px;">
                                        @foreach ($members as $m)
                                            <li style="font-size:var(--font-size-sm);">
                                                <a href="{{ route('personnel.show', $m->P_ID) }}" class="text-decoration-none">
                                                    <i class="fas fa-user me-1 text-muted"></i>
                                                    {{ strtoupper($m->P_NOM) }} {{ $m->P_PRENOM }}
                                                </a>
                                                @if ($m->P_CODE)
                                                    <span class="text-muted" style="font-size:var(--font-size-xs);">({{ $m->P_CODE }})</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         Tab 3 — Personnalisation
    ═══════════════════════════════════════════════════════════════════════════ --}}
    @elseif ($activeTab === 'personalisation')

        <form method="POST"
              action="{{ route('organization.sections.personalisation', $section->S_ID) }}"
              enctype="multipart/form-data">
            @csrf @method('PATCH')

            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:var(--font-size-sm);">{{ $errors->first() }}</div>
            @endif

            {{-- Papier à entête --}}
            <div class="ob-widget-card mb-3">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title"><i class="fas fa-file-alt me-2"></i>Papier à entête</div>
                </div>
                <div class="ob-widget-card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label form-label-sm">Modèle (.PDF)</label>
                            <input type="file" name="S_PDF_PAGE" accept=".pdf" class="form-control form-control-sm">
                            @if ($section->S_PDF_PAGE)
                                <div class="mt-1 d-flex align-items-center gap-2" style="font-size:var(--font-size-xs); color:var(--text-muted);">
                                    <span><i class="fas fa-file-pdf text-danger me-1"></i>{{ $section->S_PDF_PAGE }}</span>
                                    <button type="submit" form="letterhead-reset-form"
                                            class="btn btn-sm btn-outline-secondary py-0"
                                            onclick="return confirm('Réinitialiser le papier à entête ? Le modèle par défaut sera utilisé.')">
                                        <i class="fas fa-undo me-1"></i>Modèle par défaut
                                    </button>
                                </div>
                            @else
                                <div class="mt-1" style="font-size:var(--font-size-xs); color:var(--text-muted);">
                                    Modèle par défaut utilisé (pdf_page.pdf)
                                </div>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm" for="S_PDF_MARGE_TOP">Marge haut (mm)</label>
                            <input type="number" id="S_PDF_MARGE_TOP" name="S_PDF_MARGE_TOP"
                                   min="0" max="999" class="form-control form-control-sm"
                                   value="{{ old('S_PDF_MARGE_TOP', $section->S_PDF_MARGE_TOP ?? 15) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm" for="S_PDF_MARGE_LEFT">Marge gauche/droite (mm)</label>
                            <input type="number" id="S_PDF_MARGE_LEFT" name="S_PDF_MARGE_LEFT"
                                   min="0" max="999" class="form-control form-control-sm"
                                   value="{{ old('S_PDF_MARGE_LEFT', $section->S_PDF_MARGE_LEFT ?? 15) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm" for="S_PDF_TEXTE_TOP">Début zone de texte (mm)</label>
                            <input type="number" id="S_PDF_TEXTE_TOP" name="S_PDF_TEXTE_TOP"
                                   min="0" max="9999" class="form-control form-control-sm"
                                   value="{{ old('S_PDF_TEXTE_TOP', $section->S_PDF_TEXTE_TOP ?? 40) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm" for="S_PDF_TEXTE_BOTTOM">Fin zone de texte (mm)</label>
                            <input type="number" id="S_PDF_TEXTE_BOTTOM" name="S_PDF_TEXTE_BOTTOM"
                                   min="0" max="9999" class="form-control form-control-sm"
                                   value="{{ old('S_PDF_TEXTE_BOTTOM', $section->S_PDF_TEXTE_BOTTOM ?? 25) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Badge --}}
            <div class="ob-widget-card mb-3">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title"><i class="fas fa-id-badge me-2"></i>Badge</div>
                </div>
                <div class="ob-widget-card-body">
                    <div class="row g-2 align-items-start">
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">Image de fond du badge</label>
                            <input type="file" name="S_PDF_BADGE" accept="image/*" class="form-control form-control-sm">
                            @if ($section->S_PDF_BADGE)
                                <div class="mt-1 d-flex align-items-center gap-2" style="font-size:var(--font-size-xs); color:var(--text-muted);">
                                    <span><i class="fas fa-image me-1"></i>{{ $section->S_PDF_BADGE }}</span>
                                    <button type="submit" form="badge-reset-form"
                                            class="btn btn-sm btn-outline-secondary py-0"
                                            onclick="return confirm('Réinitialiser l\'image de fond du badge ?')">
                                        <i class="fas fa-undo me-1"></i>Image par défaut
                                    </button>
                                </div>
                            @else
                                <div class="mt-1" style="font-size:var(--font-size-xs); color:var(--text-muted);">
                                    Aucune image de fond (dessin par défaut)
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Interdire les modifications --}}
            <div class="ob-widget-card mb-3">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title"><i class="fas fa-lock me-2"></i>Interdire les modifications sur les activités terminées</div>
                </div>
                <div class="ob-widget-card-body">
                    @php $lockDays = (int) ($section->NB_DAYS_BEFORE_BLOCK ?? 0); @endphp
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label form-label-sm d-block">Modifications interdites</label>
                            <select id="lock_mode" class="form-select form-select-sm" style="width:auto;"
                                    onchange="document.getElementById('lock_days_wrap').style.display = this.value === 'days' ? 'inline-flex' : 'none'">
                                <option value="never" {{ $lockDays === 0 ? 'selected' : '' }}>Jamais</option>
                                <option value="days"  {{ $lockDays > 0  ? 'selected' : '' }}>Après x jours</option>
                            </select>
                        </div>
                        <div class="col-auto" id="lock_days_wrap"
                             style="display:{{ $lockDays > 0 ? 'inline-flex' : 'none' }}; align-items:center; gap:8px;">
                            <input type="number" id="NB_DAYS_BEFORE_BLOCK" name="NB_DAYS_BEFORE_BLOCK"
                                   min="1" max="9999" class="form-control form-control-sm" style="width:100px;"
                                   value="{{ old('NB_DAYS_BEFORE_BLOCK', $lockDays ?: '') }}">
                            <span style="font-size:var(--font-size-sm);">jours après la fin</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Textes par défaut --}}
            <div class="ob-widget-card mb-3">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title"><i class="fas fa-file-invoice me-2"></i>Textes par défaut pour devis et factures</div>
                </div>
                <div class="ob-widget-card-body">
                    <div class="row g-3">
                        @foreach ([
                            'S_PDF_SIGNATURE'  => 'Signature des documents',
                            'S_DEVIS_DEBUT'    => 'Début du devis',
                            'S_DEVIS_FIN'      => 'Fin du devis',
                            'S_FACTURE_DEBUT'  => 'Début de facture',
                            'S_FACTURE_FIN'    => 'Fin de facture',
                        ] as $field => $label)
                            <div class="col-md-6">
                                <label class="form-label form-label-sm" for="{{ $field }}">{{ $label }}</label>
                                <textarea id="{{ $field }}" name="{{ $field }}" rows="3"
                                          class="form-control form-control-sm">{{ old($field, $section->$field) }}</textarea>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Signature président --}}
            <div class="ob-widget-card mb-3">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title"><i class="fas fa-signature me-2"></i>Image de la signature du président</div>
                </div>
                <div class="ob-widget-card-body">
                    <div class="row g-2 align-items-start">
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">Signature scannée</label>
                            @if ($section->S_IMAGE_SIGNATURE)
                                <div class="mb-1" style="font-size:var(--font-size-xs); color:var(--text-muted);">
                                    <i class="fas fa-image me-1"></i>{{ $section->S_IMAGE_SIGNATURE }}
                                </div>
                            @endif
                            <input type="file" name="S_IMAGE_SIGNATURE" accept="image/*" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
            </div>
        </form>

        {{-- Reset letterhead to the default template (button lives in the card above) --}}
        <form id="letterhead-reset-form" method="POST"
              action="{{ route('organization.sections.letterhead.reset', $section->S_ID) }}">
            @csrf
            @method('DELETE')
        </form>

        {{-- Reset badge background image (button lives in the card above) --}}
        <form id="badge-reset-form" method="POST"
              action="{{ route('organization.sections.badge.reset', $section->S_ID) }}">
            @csrf
            @method('DELETE')
        </form>

    {{-- ════════════════════════════════════════════════════════════════════════
         Tab 4 — Agréments & Médailles
    ═══════════════════════════════════════════════════════════════════════════ --}}
    @elseif ($activeTab === 'agrements')

        <div id="agr-feedback" style="display:none;" class="mb-2"></div>

        @foreach ($agrementCategories as $cat)
            <div class="ob-widget-card mb-3">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title"><i class="fas fa-certificate me-2"></i>{{ $cat['label'] }}</div>
                </div>
                <div class="ob-widget-card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:90px;">Code</th>
                                <th>Libellé</th>
                                @if ($cat['type'] === 'medal')
                                    <th style="width:160px;">Délivrée le</th>
                                    <th style="width:200px;">Agrafe</th>
                                @else
                                    <th style="width:160px;">Début</th>
                                    <th style="width:160px;">Fin</th>
                                @endif
                                <th style="width:80px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cat['items'] as $item)
                                @php $row = $agrementsMap[$item['code']] ?? null; @endphp
                                <tr class="agr-row"
                                    data-code="{{ $item['code'] }}"
                                    data-type="{{ $cat['type'] }}">
                                    <td class="align-middle font-monospace" style="font-size:var(--font-size-sm);">{{ $item['code'] }}</td>
                                    <td class="align-middle" style="font-size:var(--font-size-sm);">{{ $item['label'] }}</td>
                                    @if ($cat['type'] === 'medal')
                                        <td class="align-middle">
                                            <input type="date" class="form-control form-control-sm agr-date-debut"
                                                   value="{{ $row?->A_DEBUT }}">
                                        </td>
                                        <td class="align-middle">
                                            <input type="text" class="form-control form-control-sm agr-agrafe"
                                                   placeholder="Agrafe…" value="{{ $row?->A_COMMENT }}">
                                        </td>
                                    @else
                                        <td class="align-middle">
                                            <input type="date" class="form-control form-control-sm agr-date-debut"
                                                   value="{{ $row?->A_DEBUT }}">
                                        </td>
                                        <td class="align-middle">
                                            <input type="date" class="form-control form-control-sm agr-date-fin"
                                                   value="{{ $row?->A_FIN }}">
                                        </td>
                                    @endif
                                    <td class="align-middle text-end">
                                        <button type="button" class="btn btn-sm btn-outline-success agr-save" title="Enregistrer">
                                            <i class="fas fa-save"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger agr-clear"
                                                title="Effacer" style="visibility:{{ $row ? 'visible' : 'hidden' }};">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <script>
        (function () {
            const csrf    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const baseUrl = '{{ rtrim(url('/organisation/sections/' . $section->S_ID . '/agrement'), '/') }}';
            const fb      = document.getElementById('agr-feedback');

            function flash(msg, ok) {
                fb.textContent = msg;
                fb.className = 'mb-2 alert py-1 px-3 ' + (ok ? 'alert-success' : 'alert-danger');
                fb.style.cssText = 'display:block; font-size:var(--font-size-sm);';
                setTimeout(() => fb.style.display = 'none', 3000);
            }

            document.querySelectorAll('.agr-row').forEach(function (row) {
                const code = row.dataset.code;
                const type = row.dataset.type;
                const url  = baseUrl + '/' + encodeURIComponent(code);

                row.querySelector('.agr-save').addEventListener('click', function () {
                    const dateDebut = row.querySelector('.agr-date-debut')?.value || null;
                    const dateFin   = type === 'medal' ? null : (row.querySelector('.agr-date-fin')?.value || null);
                    const agrafe    = type === 'medal' ? (row.querySelector('.agr-agrafe')?.value || null) : null;

                    fetch(url, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ date_debut: dateDebut, date_fin: dateFin, agrafe }),
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.ok) {
                            flash('Enregistré.', true);
                            row.querySelector('.agr-clear').style.visibility = 'visible';
                        } else {
                            flash('Erreur lors de l\'enregistrement.', false);
                        }
                    })
                    .catch(() => flash('Erreur réseau.', false));
                });

                row.querySelector('.agr-clear').addEventListener('click', function () {
                    fetch(url, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.ok) {
                            row.querySelectorAll('input').forEach(i => i.value = '');
                            this.style.visibility = 'hidden';
                            flash('Effacé.', true);
                        }
                    })
                    .catch(() => flash('Erreur réseau.', false));
                });
            });
        })();
        </script>

    {{-- ════════════════════════════════════════════════════════════════════════
         Tab 5 — Cotisation / RIB
    ═══════════════════════════════════════════════════════════════════════════ --}}
    @elseif ($activeTab === 'cotisation')

        <form method="POST" action="{{ route('organization.sections.rib', $section->S_ID) }}">
            @csrf @method('PATCH')

            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:var(--font-size-sm);">{{ $errors->first() }}</div>
            @endif

            <div class="ob-widget-card mb-3">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title"><i class="fas fa-university me-2"></i>RIB</div>
                </div>
                <div class="ob-widget-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label form-label-sm" for="IBAN">IBAN</label>
                            <input type="text" id="IBAN" name="IBAN" maxlength="34"
                                   class="form-control form-control-sm font-monospace"
                                   placeholder="FR76 XXXX XXXX XXXX XXXX XXXX XXX"
                                   value="{{ old('IBAN', $rib?->IBAN) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm" for="BIC">BIC / SWIFT</label>
                            <input type="text" id="BIC" name="BIC" maxlength="11"
                                   class="form-control form-control-sm font-monospace"
                                   value="{{ old('BIC', $rib?->BIC) }}">
                        </div>
                        @if ($rib?->UPDATE_DATE)
                            <div class="col-12">
                                <span class="text-muted" style="font-size:var(--font-size-xs);">
                                    Dernière mise à jour :
                                    {{ \Carbon\Carbon::parse($rib->UPDATE_DATE)->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
            </div>
        </form>

    @endif

</div>

@endsection
