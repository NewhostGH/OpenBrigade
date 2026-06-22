@extends('layout.app')

@php $isEdit = $section !== null; @endphp

@section('title', ($isEdit ? __('organization.bc_edit') : __('organization.form_title_new')) . ' — ' . config('app.name'))

@section('content')

@php
$breadcrumb = [
    ['label' => __('organization.bc_organisation')],
    ['label' => __('organization.bc_sections'), 'url' => route('organization.sections')],
];
if ($isEdit) {
    $breadcrumb[] = ['label' => $section->S_CODE, 'url' => route('organization.sections.show', $section->S_ID)];
    $breadcrumb[] = ['label' => __('organization.bc_edit')];
} else {
    $breadcrumb[] = ['label' => __('organization.bc_new')];
}
@endphp
<x-ob-breadcrumb :items="$breadcrumb" />

@php $val = fn ($key, $default = '') => old($key, $isEdit ? ($section->$key ?? $default) : $default); @endphp

<div class="mx-3 mt-3">
    <form id="section-form" method="POST"
          action="{{ $isEdit ? route('organization.sections.update', $section->S_ID) : route('organization.sections.store') }}">
        @csrf
        @if ($isEdit) @method('PATCH') @endif

        @if ($errors->any())
            <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:var(--font-size-sm);">{{ $errors->first() }}</div>
        @endif

        {{-- ── Informations obligatoires ──────────────────────────────────── --}}
        <div class="ob-widget-card mb-3">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title"><i class="fas fa-layer-group me-2"></i>{{ __('organization.card_mandatory_info') }}</div>
            </div>
            <div class="ob-widget-card-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label form-label-sm" for="S_CODE">{{ __('organization.label_code_required') }}</label>
                        <input type="text" id="S_CODE" name="S_CODE" maxlength="25" required
                               class="form-control form-control-sm" value="{{ $val('S_CODE') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm" for="S_DESCRIPTION">{{ __('organization.field_name') }}</label>
                        <input type="text" id="S_DESCRIPTION" name="S_DESCRIPTION" maxlength="80"
                               class="form-control form-control-sm" value="{{ $val('S_DESCRIPTION') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-label-sm" for="S_ORDER">{{ __('organization.field_order') }}</label>
                        <input type="number" id="S_ORDER" name="S_ORDER" min="0" max="255"
                               class="form-control form-control-sm" value="{{ $val('S_ORDER', 50) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm" for="S_PARENT">{{ __('organization.field_parent_section') }}</label>
                        <select id="S_PARENT" name="S_PARENT" required
                                class="form-select form-select-sm @error('S_PARENT') is-invalid @enderror">
                            {{-- value 0 = directement sous l'organisation (site de premier niveau). --}}
                            @if ($canBeRoot)
                                <option value="0" @selected((string) $val('S_PARENT') === '0')>{{ __('organization.label_root_option') }}</option>
                            @endif
                            @foreach ($parents as $p)
                                <option value="{{ $p->S_ID }}" @selected((string) $val('S_PARENT') === (string) $p->S_ID)>
                                    {{ $p->S_CODE }} — {{ $p->S_DESCRIPTION }}
                                </option>
                            @endforeach
                        </select>
                        @error('S_PARENT')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" id="S_INACTIVE" name="S_INACTIVE" value="1"
                                   class="form-check-input" @checked($val('S_INACTIVE'))>
                            <label class="form-check-label" for="S_INACTIVE" style="font-size:var(--font-size-sm);">{{ __('organization.label_section_inactive') }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Contact ────────────────────────────────────────────────────── --}}
        <div class="ob-widget-card mb-3">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title"><i class="fas fa-address-book me-2"></i>{{ __('organization.card_contact') }}</div>
            </div>
            <div class="ob-widget-card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label form-label-sm" for="S_PHONE">{{ __('organization.field_phone') }}</label>
                        <input type="text" id="S_PHONE" name="S_PHONE" maxlength="20"
                               class="form-control form-control-sm" value="{{ $val('S_PHONE') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm" for="S_PHONE2">{{ __('organization.field_phone_ops') }}</label>
                        <input type="text" id="S_PHONE2" name="S_PHONE2" maxlength="20"
                               class="form-control form-control-sm" value="{{ $val('S_PHONE2') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm" for="S_PHONE3">{{ __('organization.field_phone_training') }}</label>
                        <input type="text" id="S_PHONE3" name="S_PHONE3" maxlength="20"
                               class="form-control form-control-sm" value="{{ $val('S_PHONE3') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm" for="S_FAX">{{ __('organization.field_fax') }}</label>
                        <input type="text" id="S_FAX" name="S_FAX" maxlength="20"
                               class="form-control form-control-sm" value="{{ $val('S_FAX') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm" for="S_EMAIL">{{ __('organization.field_email_ops') }}</label>
                        <input type="email" id="S_EMAIL" name="S_EMAIL" maxlength="60"
                               class="form-control form-control-sm" value="{{ $val('S_EMAIL') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm" for="S_EMAIL2">{{ __('organization.field_email_secretary') }}</label>
                        <input type="email" id="S_EMAIL2" name="S_EMAIL2" maxlength="60"
                               class="form-control form-control-sm" value="{{ $val('S_EMAIL2') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm" for="S_EMAIL3">{{ __('organization.field_email_training') }}</label>
                        <input type="email" id="S_EMAIL3" name="S_EMAIL3" maxlength="60"
                               class="form-control form-control-sm" value="{{ $val('S_EMAIL3') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm" for="S_WHATSAPP">{{ __('organization.field_whatsapp') }}</label>
                        <input type="text" id="S_WHATSAPP" name="S_WHATSAPP" maxlength="30"
                               class="form-control form-control-sm" value="{{ $val('S_WHATSAPP') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm" for="S_ID_RADIO">{{ __('organization.field_radio_id') }}</label>
                        <input type="text" id="S_ID_RADIO" name="S_ID_RADIO" maxlength="5"
                               class="form-control form-control-sm font-monospace" value="{{ $val('S_ID_RADIO') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Informations facultatives ───────────────────────────────────── --}}
        <div class="ob-widget-card mb-3">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title"><i class="fas fa-info-circle me-2"></i>{{ __('organization.card_optional_info') }}</div>
            </div>
            <div class="ob-widget-card-body">
                <div class="row g-2">
                    <div class="col-md-8">
                        <label class="form-label form-label-sm" for="S_ADDRESS">{{ __('organization.field_address') }}</label>
                        <input type="text" id="S_ADDRESS" name="S_ADDRESS" maxlength="150"
                               class="form-control form-control-sm" value="{{ $val('S_ADDRESS') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm" for="S_ADDRESS_COMPLEMENT">{{ __('organization.field_address_complement') }}</label>
                        <input type="text" id="S_ADDRESS_COMPLEMENT" name="S_ADDRESS_COMPLEMENT" maxlength="150"
                               class="form-control form-control-sm" value="{{ $val('S_ADDRESS_COMPLEMENT') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm" for="S_ZIP_CODE">{{ __('organization.field_zip_code') }}</label>
                        <input type="text" id="S_ZIP_CODE" name="S_ZIP_CODE" maxlength="6"
                               class="form-control form-control-sm" value="{{ $val('S_ZIP_CODE') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm" for="S_CITY">{{ __('organization.field_city') }}</label>
                        <input type="text" id="S_CITY" name="S_CITY" maxlength="30"
                               class="form-control form-control-sm" value="{{ $val('S_CITY') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-label-sm" for="S_SIRET">{{ __('organization.field_siret') }}</label>
                        <input type="text" id="S_SIRET" name="S_SIRET" maxlength="20"
                               class="form-control form-control-sm" value="{{ $val('S_SIRET') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-label-sm" for="S_AFFILIATION">{{ __('organization.field_affiliation') }}</label>
                        <input type="text" id="S_AFFILIATION" name="S_AFFILIATION" maxlength="20"
                               class="form-control form-control-sm" value="{{ $val('S_AFFILIATION') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm" for="S_URL">{{ __('organization.field_website') }}</label>
                        <input type="text" id="S_URL" name="S_URL" maxlength="60"
                               class="form-control form-control-sm" value="{{ $val('S_URL') }}">
                    </div>
                </div>
            </div>
        </div>

    </form>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" form="section-form" class="btn btn-primary"><i class="fas fa-save me-1"></i>{{ __('common.save') }}</button>
        <a href="{{ $isEdit ? route('organization.sections.show', $section->S_ID) : route('organization.sections') }}"
           class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
        @if ($isEdit)
            <div class="ms-auto">
                <form method="POST" action="{{ route('organization.sections.destroy', $section->S_ID) }}"
                      onsubmit="return confirm('{{ __('organization.confirm_delete', ['code' => addslashes($section->S_CODE)]) }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fas fa-trash me-1"></i>{{ __('common.delete') }}
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>

@endsection
