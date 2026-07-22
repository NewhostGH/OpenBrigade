@extends('layout.app')

@section('title', ($section->S_CODE ?: 'Section') . ' — Organisation — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('organization.bc_organisation')],
    ['label' => __('organization.bc_sections'), 'url' => route('organization.sections')],
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
                    <span class="ob-badge ob-badge-archive ms-2">{{ __('organization.status_inactive') }}</span>
                @else
                    <span class="ob-badge ob-badge-actif ms-2">{{ __('organization.status_active') }}</span>
                @endif
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('organization.sections.edit', $section->S_ID) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-edit me-1"></i>{{ __('common.edit') }}
                </a>
                <a href="{{ route('organization.sections') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
        </div>
        <div class="ob-widget-card-body pt-2 pb-2">
            <div class="d-flex gap-4" style="font-size:var(--font-size-sm); color:var(--text-muted);">
                <span><i class="fas fa-users me-1"></i>{{ trans_choice('organization.member_count', $memberCount, ['count' => $memberCount]) }}</span>
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
                'informations'    => ['icon' => 'fas fa-info-circle',    'label' => __('organization.tab_informations')],
                'organigramme'    => ['icon' => 'fas fa-project-diagram', 'label' => __('organization.tab_org_chart')],
                'personalisation' => ['icon' => 'fas fa-palette',         'label' => __('organization.tab_personalisation')],
                'agrements'       => ['icon' => 'fas fa-certificate',     'label' => __('organization.tab_agrements')],
                'cotisation'      => ['icon' => 'fas fa-university',      'label' => __('organization.tab_cotisation')],
                'interdictions'   => ['icon' => 'fas fa-ban',            'label' => __('organization.tab_interdictions')],
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
                        <div class="ob-widget-card-title"><i class="fas fa-layer-group me-2"></i>{{ __('organization.card_mandatory_info') }}</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <dl class="ob-info-grid mb-0">
                            <div class="ob-info-item"><dt>{{ __('organization.field_code') }}</dt><dd>{{ $section->S_CODE ?: '—' }}</dd></div>
                            <div class="ob-info-item"><dt>{{ __('organization.field_name') }}</dt><dd>{{ $section->S_DESCRIPTION ?: '—' }}</dd></div>
                            <div class="ob-info-item"><dt>{{ __('organization.field_order') }}</dt><dd>{{ $section->S_ORDER ?? '—' }}</dd></div>
                            <div class="ob-info-item">
                                <dt>{{ __('organization.field_parent_section') }}</dt>
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
                        <div class="ob-widget-card-title"><i class="fas fa-address-book me-2"></i>{{ __('organization.card_contact') }}</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <dl class="ob-info-grid mb-0">
                            @foreach ([
                                __('organization.field_phone')          => $section->S_PHONE,
                                __('organization.field_phone_ops')      => $section->S_PHONE2,
                                __('organization.field_phone_training') => $section->S_PHONE3,
                                __('organization.field_fax')            => $section->S_FAX,
                                __('organization.field_email_ops')      => $section->S_EMAIL,
                                __('organization.field_email_secretary') => $section->S_EMAIL2,
                                __('organization.field_email_training') => $section->S_EMAIL3,
                                __('organization.field_whatsapp')       => $section->S_WHATSAPP,
                                __('organization.field_radio_id')       => $section->S_ID_RADIO,
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
                        <div class="ob-widget-card-title"><i class="fas fa-info-circle me-2"></i>{{ __('organization.card_optional_info') }}</div>
                    </div>
                    <div class="ob-widget-card-body">
                        <dl class="ob-info-grid mb-0">
                            @php
                                $adresse = implode(', ', array_filter([$section->S_ADDRESS, $section->S_ADDRESS_COMPLEMENT]));
                            @endphp
                            @if ($adresse)<div class="ob-info-item"><dt>{{ __('organization.field_address') }}</dt><dd>{{ $adresse }}</dd></div>@endif
                            @if ($section->S_ZIP_CODE || $section->S_CITY)
                                <div class="ob-info-item"><dt>{{ __('organization.field_city') }}</dt><dd>{{ trim($section->S_ZIP_CODE . ' ' . $section->S_CITY) }}</dd></div>
                            @endif
                            @if ($section->S_SIRET)<div class="ob-info-item"><dt>{{ __('organization.field_siret') }}</dt><dd>{{ $section->S_SIRET }}</dd></div>@endif
                            @if ($section->S_AFFILIATION)<div class="ob-info-item"><dt>{{ __('organization.field_affiliation') }}</dt><dd>{{ $section->S_AFFILIATION }}</dd></div>@endif
                            @if ($section->S_URL)
                                <div class="ob-info-item">
                                    <dt>{{ __('organization.field_website') }}</dt>
                                    <dd><a href="{{ Str::startsWith($section->S_URL, 'http') ? $section->S_URL : 'https://' . $section->S_URL }}"
                                           target="_blank" rel="noopener">{{ $section->S_URL }}</a></dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Danger zone: deactivate / reactivate ─────────────────────────── --}}
        @if ((int) $section->S_ID !== 0)
            <div class="ob-widget-card mt-3 border-danger-subtle">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title text-danger">
                        <i class="fas fa-triangle-exclamation me-2"></i>{{ __('organization.danger_zone') }}
                    </div>
                </div>
                <div class="ob-widget-card-body d-flex flex-wrap align-items-center gap-3">
                    @if ($section->S_INACTIVE)
                        <div class="flex-grow-1 text-muted" style="font-size:var(--font-size-sm);">
                            {{ __('organization.reactivate_hint') }}
                        </div>
                        <form method="POST" action="{{ route('organization.sections.reactivate', $section->S_ID) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-outline-success">
                                <i class="fas fa-rotate-left me-1"></i>{{ __('organization.reactivate') }}
                            </button>
                        </form>
                    @else
                        <div class="flex-grow-1 text-muted" style="font-size:var(--font-size-sm);">
                            {{ __('organization.deactivate_hint') }}
                        </div>
                        <button type="button" class="btn btn-outline-danger"
                                data-bs-toggle="modal" data-bs-target="#deactivateModal">
                            <i class="fas fa-ban me-1"></i>{{ __('organization.deactivate') }}
                        </button>
                    @endif
                </div>
            </div>

            {{-- Deactivate modal: choose deactivate-only vs deactivate+radiate --}}
            @unless ($section->S_INACTIVE)
                <div class="modal fade" id="deactivateModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('organization.sections.deactivate', $section->S_ID) }}" class="modal-content">
                            @csrf @method('PATCH')
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('organization.deactivate_modal_title') }} — {{ $section->S_CODE }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="radiate" value="0" id="radiateNo" checked>
                                    <label class="form-check-label" for="radiateNo">
                                        <strong>{{ __('organization.deactivate_choice_only') }}</strong>
                                        <div class="text-muted" style="font-size:var(--font-size-xs);">{{ __('organization.deactivate_choice_only_hint') }}</div>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="radiate" value="1" id="radiateYes">
                                    <label class="form-check-label" for="radiateYes">
                                        <strong class="text-danger">{{ __('organization.deactivate_choice_radiate') }}</strong>
                                        <div class="text-muted" style="font-size:var(--font-size-xs);">
                                            {{ __('organization.deactivate_choice_radiate_hint', ['count' => $activeMemberCount]) }}
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                                <button type="submit" class="btn btn-danger">{{ __('organization.deactivate_confirm') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endunless
        @endif

    {{-- ════════════════════════════════════════════════════════════════════════
         Tab 2 — Organigramme
    ═══════════════════════════════════════════════════════════════════════════ --}}
    @elseif ($activeTab === 'organigramme')

        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title"><i class="fas fa-project-diagram me-2"></i>{{ __('organization.card_roles') }}</div>
                <a href="{{ route('organization.org-chart', ['focus' => $section->S_ID]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-sitemap me-1"></i>{{ __('organization.view_in_org_chart') }}
                </a>
            </div>
            <div class="ob-widget-card-body">
                @if ($orgByRole->isEmpty())
                    <p class="text-muted mb-0" style="font-size:var(--font-size-sm);">{{ __('organization.no_roles') }}</p>
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
                    <div class="ob-widget-card-title"><i class="fas fa-file-alt me-2"></i>{{ __('organization.card_letterhead') }}</div>
                </div>
                <div class="ob-widget-card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label form-label-sm">{{ __('organization.label_pdf_model') }}</label>
                            <input type="file" name="S_PDF_PAGE" accept=".pdf" class="form-control form-control-sm">
                            @if ($section->S_PDF_PAGE)
                                <div class="mt-1 d-flex align-items-center gap-2" style="font-size:var(--font-size-xs); color:var(--text-muted);">
                                    <span><i class="fas fa-file-pdf text-danger me-1"></i>{{ $section->S_PDF_PAGE }}</span>
                                    <button type="submit" form="letterhead-reset-form"
                                            class="btn btn-sm btn-outline-secondary py-0"
                                            onclick="return confirm('{{ __('organization.confirm_reset_lh') }}')">
                                        <i class="fas fa-undo me-1"></i>{{ __('organization.label_reset_letterhead') }}
                                    </button>
                                </div>
                            @else
                                <div class="mt-1" style="font-size:var(--font-size-xs); color:var(--text-muted);">
                                    {{ __('organization.label_default_model') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm" for="S_PDF_MARGE_TOP">{{ __('organization.label_margin_top') }}</label>
                            <input type="number" id="S_PDF_MARGE_TOP" name="S_PDF_MARGE_TOP"
                                   min="0" max="999" class="form-control form-control-sm"
                                   value="{{ old('S_PDF_MARGE_TOP', $section->S_PDF_MARGE_TOP ?? 15) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm" for="S_PDF_MARGE_LEFT">{{ __('organization.label_margin_lr') }}</label>
                            <input type="number" id="S_PDF_MARGE_LEFT" name="S_PDF_MARGE_LEFT"
                                   min="0" max="999" class="form-control form-control-sm"
                                   value="{{ old('S_PDF_MARGE_LEFT', $section->S_PDF_MARGE_LEFT ?? 15) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm" for="S_PDF_TEXTE_TOP">{{ __('organization.label_text_top') }}</label>
                            <input type="number" id="S_PDF_TEXTE_TOP" name="S_PDF_TEXTE_TOP"
                                   min="0" max="9999" class="form-control form-control-sm"
                                   value="{{ old('S_PDF_TEXTE_TOP', $section->S_PDF_TEXTE_TOP ?? 40) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm" for="S_PDF_TEXTE_BOTTOM">{{ __('organization.label_text_bottom') }}</label>
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
                    <div class="ob-widget-card-title"><i class="fas fa-id-badge me-2"></i>{{ __('organization.card_badge') }}</div>
                </div>
                <div class="ob-widget-card-body">
                    <div class="row g-2 align-items-start">
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">{{ __('organization.label_badge_bg') }}</label>
                            <input type="file" name="S_PDF_BADGE" accept="image/*" class="form-control form-control-sm">
                            @if ($section->S_PDF_BADGE)
                                <div class="mt-1 d-flex align-items-center gap-2" style="font-size:var(--font-size-xs); color:var(--text-muted);">
                                    <span><i class="fas fa-image me-1"></i>{{ $section->S_PDF_BADGE }}</span>
                                    <button type="submit" form="badge-reset-form"
                                            class="btn btn-sm btn-outline-secondary py-0"
                                            onclick="return confirm('{{ __('organization.confirm_reset_badge') }}')">
                                        <i class="fas fa-undo me-1"></i>{{ __('organization.label_reset_badge') }}
                                    </button>
                                </div>
                            @else
                                <div class="mt-1" style="font-size:var(--font-size-xs); color:var(--text-muted);">
                                    {{ __('organization.label_no_badge_bg') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Interdire les modifications --}}
            <div class="ob-widget-card mb-3">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title"><i class="fas fa-lock me-2"></i>{{ __('organization.card_lock') }}</div>
                </div>
                <div class="ob-widget-card-body">
                    @php $lockDays = (int) ($section->NB_DAYS_BEFORE_BLOCK ?? 0); @endphp
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label form-label-sm d-block">{{ __('organization.label_lock_mode') }}</label>
                            <select id="lock_mode" class="form-select form-select-sm" style="width:auto;"
                                    onchange="document.getElementById('lock_days_wrap').style.display = this.value === 'days' ? 'inline-flex' : 'none'">
                                <option value="never" {{ $lockDays === 0 ? 'selected' : '' }}>{{ __('organization.lock_never') }}</option>
                                <option value="days"  {{ $lockDays > 0  ? 'selected' : '' }}>{{ __('organization.lock_after_days') }}</option>
                            </select>
                        </div>
                        <div class="col-auto" id="lock_days_wrap"
                             style="display:{{ $lockDays > 0 ? 'inline-flex' : 'none' }}; align-items:center; gap:8px;">
                            <input type="number" id="NB_DAYS_BEFORE_BLOCK" name="NB_DAYS_BEFORE_BLOCK"
                                   min="1" max="9999" class="form-control form-control-sm" style="width:100px;"
                                   value="{{ old('NB_DAYS_BEFORE_BLOCK', $lockDays ?: '') }}">
                            <span style="font-size:var(--font-size-sm);">{{ __('organization.lock_days_after') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Textes par défaut --}}
            <div class="ob-widget-card mb-3">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title"><i class="fas fa-file-invoice me-2"></i>{{ __('organization.card_default_texts') }}</div>
                </div>
                <div class="ob-widget-card-body">
                    <div class="row g-3">
                        @foreach ([
                            'S_PDF_SIGNATURE'  => __('organization.label_pdf_signature'),
                            'S_DEVIS_DEBUT'    => __('organization.label_devis_debut'),
                            'S_DEVIS_FIN'      => __('organization.label_devis_fin'),
                            'S_FACTURE_DEBUT'  => __('organization.label_facture_debut'),
                            'S_FACTURE_FIN'    => __('organization.label_facture_fin'),
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
                    <div class="ob-widget-card-title"><i class="fas fa-signature me-2"></i>{{ __('organization.card_president_sig') }}</div>
                </div>
                <div class="ob-widget-card-body">
                    <div class="row g-2 align-items-start">
                        <div class="col-md-4">
                            <label class="form-label form-label-sm">{{ __('organization.label_scanned_sig') }}</label>
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
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>{{ __('common.save') }}</button>
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
                                <th style="width:90px;">{{ __('organization.col_agr_code') }}</th>
                                <th>{{ __('organization.col_agr_label') }}</th>
                                @if ($cat['type'] === 'medal')
                                    <th style="width:160px;">{{ __('organization.col_agr_delivered') }}</th>
                                    <th style="width:200px;">{{ __('organization.col_agr_clasp') }}</th>
                                @else
                                    <th style="width:160px;">{{ __('organization.col_agr_start') }}</th>
                                    <th style="width:160px;">{{ __('organization.col_agr_end') }}</th>
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
                                                   placeholder="{{ __('organization.agr_clasp_placeholder') }}" value="{{ $row?->A_COMMENT }}">
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
                                        <button type="button" class="btn btn-sm btn-outline-success agr-save" title="{{ __('organization.agr_save_title') }}">
                                            <i class="fas fa-save"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger agr-clear"
                                                title="{{ __('organization.agr_clear_title') }}" style="visibility:{{ $row ? 'visible' : 'hidden' }};">
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
            const baseUrl = '{{ rtrim(url('/organization/sections/' . $section->S_ID . '/agrement'), '/') }}';
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

        <form method="POST" action="{{ route('organization.sections.rib', $section->S_ID) }}"
              enctype="multipart/form-data">
            @csrf @method('PATCH')

            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:var(--font-size-sm);">{{ $errors->first() }}</div>
            @endif

            {{-- IBAN / BIC --}}
            <div class="ob-widget-card mb-3">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title"><i class="fas fa-university me-2"></i>{{ __('organization.card_bank') }}</div>
                </div>
                <div class="ob-widget-card-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label form-label-sm" for="IBAN">{{ __('organization.label_iban') }}</label>
                            <input type="text" id="IBAN" name="IBAN" maxlength="34"
                                   class="form-control form-control-sm font-monospace"
                                   placeholder="{{ __('organization.iban_placeholder') }}"
                                   value="{{ old('IBAN', $rib?->IBAN) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm" for="BIC">{{ __('organization.label_bic') }}</label>
                            <input type="text" id="BIC" name="BIC" maxlength="11"
                                   class="form-control form-control-sm font-monospace"
                                   value="{{ old('BIC', $rib?->BIC) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Legacy RIB fields --}}
            <div class="ob-widget-card mb-3">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title"><i class="fas fa-hashtag me-2"></i>{{ __('organization.card_rib') }}</div>
                    <div class="ob-widget-card-actions" style="font-size:var(--font-size-xs);color:var(--text-muted-soft);">
                        {{ __('organization.rib_used_for') }}
                    </div>
                </div>
                <div class="ob-widget-card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label form-label-sm" for="CODE_BANQUE">{{ __('organization.label_code_banque') }}</label>
                            <input type="text" id="CODE_BANQUE" name="CODE_BANQUE" maxlength="30"
                                   class="form-control form-control-sm font-monospace"
                                   placeholder="12345"
                                   value="{{ old('CODE_BANQUE', $rib?->CODE_BANQUE) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm" for="ETABLISSEMENT">{{ __('organization.label_etablissement') }}</label>
                            <input type="text" id="ETABLISSEMENT" name="ETABLISSEMENT" maxlength="5"
                                   class="form-control form-control-sm font-monospace"
                                   placeholder="12345"
                                   value="{{ old('ETABLISSEMENT', $rib?->ETABLISSEMENT) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm" for="GUICHET">{{ __('organization.label_guichet') }}</label>
                            <input type="text" id="GUICHET" name="GUICHET" maxlength="5"
                                   class="form-control form-control-sm font-monospace"
                                   placeholder="12345"
                                   value="{{ old('GUICHET', $rib?->GUICHET) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm" for="COMPTE">{{ __('organization.label_compte') }}</label>
                            <input type="text" id="COMPTE" name="COMPTE" maxlength="11"
                                   class="form-control form-control-sm font-monospace"
                                   placeholder="00123456789"
                                   value="{{ old('COMPTE', $rib?->COMPTE) }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label form-label-sm" for="CLE_RIB">{{ __('organization.label_cle_rib') }}</label>
                            <input type="text" id="CLE_RIB" name="CLE_RIB" maxlength="2"
                                   class="form-control form-control-sm font-monospace"
                                   placeholder="42"
                                   value="{{ old('CLE_RIB', $rib?->CLE_RIB) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIB file upload --}}
            <div class="ob-widget-card mb-3">
                <div class="ob-widget-card-header">
                    <div class="ob-widget-card-title"><i class="fas fa-file-pdf me-2"></i>{{ __('organization.card_rib_doc') }}</div>
                </div>
                <div class="ob-widget-card-body">
                    @if ($rib?->CB_FILE)
                        <div class="mb-2 d-flex align-items-center gap-2">
                            <i class="fas fa-paperclip text-muted"></i>
                            <a href="{{ route('organization.sections.rib.download', $section->S_ID) }}"
                               class="text-decoration-none" style="font-size:var(--font-size-sm);">
                                {{ __('organization.label_rib_download') }}
                            </a>
                            <span class="text-muted" style="font-size:var(--font-size-xs);">
                                ({{ strtoupper(pathinfo($rib->CB_FILE, PATHINFO_EXTENSION)) }})
                            </span>
                        </div>
                    @endif
                    <div>
                        <label class="form-label form-label-sm" for="rib_file">
                            {{ $rib?->CB_FILE ? __('organization.label_rib_replace') : __('organization.label_rib_upload') }}
                        </label>
                        <input type="file" id="rib_file" name="rib_file"
                               class="form-control form-control-sm"
                               accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text">{{ __('organization.rib_file_hint') }}</div>
                    </div>
                </div>
            </div>

            @if ($rib?->UPDATE_DATE)
                <p class="text-muted mb-3" style="font-size:var(--font-size-xs);">
                    {{ __('organization.rib_updated_at', ['date' => \Carbon\Carbon::parse($rib->UPDATE_DATE)->format('d/m/Y H:i')]) }}
                </p>
            @endif

            <div class="mb-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>{{ __('common.save') }}</button>
            </div>
        </form>

    {{-- ════════════════════════════════════════════════════════════════════════
         Tab 6 — Interdictions (section_stop_evenement)
    ═══════════════════════════════════════════════════════════════════════════ --}}
    @elseif ($activeTab === 'interdictions')

        <div class="ob-widget-card">
            <div class="ob-widget-card-header d-flex justify-content-between align-items-center">
                <div class="ob-widget-card-title"><i class="fas fa-ban me-2"></i>{{ __('organization.interdictions_title') }}</div>
                <button type="button" class="btn btn-sm btn-success"
                        data-bs-toggle="modal" data-bs-target="#interdictionModal"
                        onclick="obInterdictionModal(null)">
                    <i class="fas fa-plus me-1"></i>{{ __('organization.interdiction_add') }}
                </button>
            </div>
            <div class="ob-widget-card-body">
                <p class="text-muted" style="font-size:var(--font-size-sm);">{{ __('organization.interdictions_hint') }}</p>

                @if ($interdictions->isEmpty())
                    <p class="text-muted mb-0">{{ __('organization.interdiction_none') }}</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('organization.interdiction_col_type') }}</th>
                                    <th>{{ __('organization.interdiction_col_period') }}</th>
                                    <th>{{ __('organization.interdiction_col_comment') }}</th>
                                    <th class="text-center">{{ __('organization.interdiction_col_status') }}</th>
                                    <th class="text-center">{{ __('common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($interdictions as $it)
                                    <tr>
                                        <td>{{ $it->TE_CODE === 'ALL' ? __('organization.interdiction_all_types') : ($it->TE_LIBELLE ?: $it->TE_CODE) }}</td>
                                        <td class="text-nowrap">
                                            {{ \Carbon\Carbon::parse($it->START_DATE)->format('d/m/Y') }}
                                            → {{ \Carbon\Carbon::parse($it->END_DATE)->format('d/m/Y') }}
                                        </td>
                                        <td>{{ $it->SSE_COMMENT }}</td>
                                        <td class="text-center">
                                            @if ($it->SSE_ACTIVE)
                                                <span class="ob-badge ob-badge-actif">{{ __('common.yes') }}</span>
                                            @else
                                                <span class="ob-badge ob-badge-archive">{{ __('common.no') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center text-nowrap">
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    data-bs-toggle="modal" data-bs-target="#interdictionModal"
                                                    onclick='obInterdictionModal(@json($it))'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" class="d-inline"
                                                  action="{{ route('organization.sections.interdictions.destroy', [$section->S_ID, $it->SSE_ID]) }}"
                                                  onsubmit="return confirm(@js(__('organization.interdiction_delete_confirm')))">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Add/edit interdiction modal --}}
        <div class="modal fade" id="interdictionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" id="interdictionForm" class="modal-content"
                      action="{{ route('organization.sections.interdictions.store', $section->S_ID) }}">
                    @csrf
                    <input type="hidden" name="_method" id="interdictionMethod" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="interdictionModalTitle">{{ __('organization.interdiction_add') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="TE_CODE" class="form-label">{{ __('organization.interdiction_field_type') }}</label>
                            <select name="TE_CODE" id="TE_CODE" class="form-select">
                                <option value="ALL">{{ __('organization.interdiction_all_types') }}</option>
                                @foreach ($eventTypes as $category => $types)
                                    <optgroup label="{{ $category }}">
                                        @foreach ($types as $t)
                                            <option value="{{ $t->TE_CODE }}">{{ $t->TE_LIBELLE }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-6 mb-3">
                                <label for="START_DATE" class="form-label">{{ __('organization.interdiction_field_start') }}</label>
                                <input type="date" name="START_DATE" id="START_DATE" class="form-control" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="END_DATE" class="form-label">{{ __('organization.interdiction_field_end') }}</label>
                                <input type="date" name="END_DATE" id="END_DATE" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="SSE_COMMENT" class="form-label">{{ __('organization.interdiction_field_comment') }}</label>
                            <textarea name="SSE_COMMENT" id="SSE_COMMENT" rows="2" maxlength="255" class="form-control"></textarea>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="SSE_ACTIVE" id="SSE_ACTIVE" value="1" checked>
                            <label class="form-check-label" for="SSE_ACTIVE">{{ __('organization.interdiction_field_active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                        <button type="submit" class="btn btn-success">{{ __('common.save') }}</button>
                    </div>
                </form>
            </div>
        </div>

        @push('scripts')
        <script>
            function obInterdictionModal(it) {
                var form = document.getElementById('interdictionForm');
                var title = document.getElementById('interdictionModalTitle');
                var method = document.getElementById('interdictionMethod');
                var storeUrl = @json(route('organization.sections.interdictions.store', $section->S_ID));
                var updateBase = @json(url('/organization/sections/'.$section->S_ID.'/interdictions'));
                if (it) {
                    title.textContent = @json(__('organization.interdiction_edit'));
                    method.value = 'PATCH';
                    form.action = updateBase + '/' + it.SSE_ID;
                    document.getElementById('TE_CODE').value = it.TE_CODE;
                    document.getElementById('START_DATE').value = (it.START_DATE || '').substring(0, 10);
                    document.getElementById('END_DATE').value = (it.END_DATE || '').substring(0, 10);
                    document.getElementById('SSE_COMMENT').value = it.SSE_COMMENT || '';
                    document.getElementById('SSE_ACTIVE').checked = String(it.SSE_ACTIVE) === '1';
                } else {
                    title.textContent = @json(__('organization.interdiction_add'));
                    method.value = 'POST';
                    form.action = storeUrl;
                    form.reset();
                    document.getElementById('SSE_ACTIVE').checked = true;
                }
            }
        </script>
        @endpush

    @endif

</div>

@endsection
