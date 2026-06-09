@extends('layout.app')

@php $isEdit = $section !== null; @endphp

@section('title', ($isEdit ? 'Modifier' : 'Nouvelle') . ' section — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Organisation'],
    ['label' => 'Sections', 'url' => route('organisation.sections')],
    ['label' => $isEdit ? 'Modifier' : 'Nouvelle'],
]"/>

@php $val = fn ($key, $default = '') => old($key, $isEdit ? ($section->$key ?? $default) : $default); @endphp

<div class="mx-3 mt-3">
    <form method="POST"
          action="{{ $isEdit ? route('organisation.sections.update', $section->S_ID) : route('organisation.sections.store') }}">
        @csrf
        @if ($isEdit) @method('PATCH') @endif

        <div class="ob-widget-card mb-3">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-layer-group me-2"></i>{{ $isEdit ? 'Modifier la section' : 'Nouvelle section' }}
                </div>
            </div>
            <div class="ob-widget-card-body">
                @if ($errors->any())
                    <div class="alert alert-danger py-2 px-3" style="font-size:var(--font-size-sm);">{{ $errors->first() }}</div>
                @endif
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label form-label-sm" for="S_CODE">Code *</label>
                        <input type="text" id="S_CODE" name="S_CODE" maxlength="25" required
                               class="form-control form-control-sm" value="{{ $val('S_CODE') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm" for="S_DESCRIPTION">Nom</label>
                        <input type="text" id="S_DESCRIPTION" name="S_DESCRIPTION" maxlength="80"
                               class="form-control form-control-sm" value="{{ $val('S_DESCRIPTION') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-label-sm" for="S_ORDER">Ordre</label>
                        <input type="number" id="S_ORDER" name="S_ORDER" min="0" max="255"
                               class="form-control form-control-sm" value="{{ $val('S_ORDER', 50) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label form-label-sm" for="S_PARENT">Section parente</label>
                        <select id="S_PARENT" name="S_PARENT" class="form-select form-select-sm">
                            <option value="0">— racine —</option>
                            @foreach ($parents as $p)
                                <option value="{{ $p->S_ID }}" @selected((string) $val('S_PARENT') === (string) $p->S_ID)>
                                    {{ $p->S_CODE }} — {{ $p->S_DESCRIPTION }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" id="S_INACTIVE" name="S_INACTIVE" value="1"
                                   class="form-check-input" @checked($val('S_INACTIVE'))>
                            <label class="form-check-label" for="S_INACTIVE" style="font-size:var(--font-size-sm);">Section inactive</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ob-widget-card mb-3">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title"><i class="fas fa-address-book me-2"></i>Coordonnées</div>
            </div>
            <div class="ob-widget-card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label form-label-sm" for="S_PHONE">Téléphone</label>
                        <input type="text" id="S_PHONE" name="S_PHONE" maxlength="20" class="form-control form-control-sm" value="{{ $val('S_PHONE') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm" for="S_FAX">Fax</label>
                        <input type="text" id="S_FAX" name="S_FAX" maxlength="20" class="form-control form-control-sm" value="{{ $val('S_FAX') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm" for="S_EMAIL">Courriel</label>
                        <input type="email" id="S_EMAIL" name="S_EMAIL" maxlength="60" class="form-control form-control-sm" value="{{ $val('S_EMAIL') }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label form-label-sm" for="S_URL">Site web</label>
                        <input type="text" id="S_URL" name="S_URL" maxlength="60" class="form-control form-control-sm" value="{{ $val('S_URL') }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label form-label-sm" for="S_ADDRESS">Adresse</label>
                        <input type="text" id="S_ADDRESS" name="S_ADDRESS" maxlength="150" class="form-control form-control-sm" value="{{ $val('S_ADDRESS') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm" for="S_ADDRESS_COMPLEMENT">Complément</label>
                        <input type="text" id="S_ADDRESS_COMPLEMENT" name="S_ADDRESS_COMPLEMENT" maxlength="150" class="form-control form-control-sm" value="{{ $val('S_ADDRESS_COMPLEMENT') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-label-sm" for="S_ZIP_CODE">Code postal</label>
                        <input type="text" id="S_ZIP_CODE" name="S_ZIP_CODE" maxlength="6" class="form-control form-control-sm" value="{{ $val('S_ZIP_CODE') }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label form-label-sm" for="S_CITY">Ville</label>
                        <input type="text" id="S_CITY" name="S_CITY" maxlength="30" class="form-control form-control-sm" value="{{ $val('S_CITY') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
            <a href="{{ route('organisation.sections') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>

@endsection
