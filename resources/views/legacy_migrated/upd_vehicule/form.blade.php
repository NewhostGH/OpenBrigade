@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_vehicule.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Vehicule Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_vehicule.update', $itemKey) : route('legacy_migrated.upd_vehicule.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="V_ID" name="V_ID" value="{{ old('V_ID', $item?->V_ID) }}">

<input type="hidden" id="V_IMMATRICULATION" name="V_IMMATRICULATION" value="{{ old('V_IMMATRICULATION', $item?->V_IMMATRICULATION) }}">

<input type="hidden" id="V_COMMENT" name="V_COMMENT" value="{{ old('V_COMMENT', $item?->V_COMMENT) }}">

<input type="hidden" id="VP_ID" name="VP_ID" value="{{ old('VP_ID', $item?->VP_ID) }}">

<input type="hidden" id="V_KM" name="V_KM" value="{{ old('V_KM', $item?->V_KM) }}">

<input type="hidden" id="V_KM_REVISION" name="V_KM_REVISION" value="{{ old('V_KM_REVISION', $item?->V_KM_REVISION) }}">

<input type="hidden" id="EQ_ID" name="EQ_ID" value="{{ old('EQ_ID', $item?->EQ_ID) }}">

<input type="hidden" id="V_MODELE" name="V_MODELE" value="{{ old('V_MODELE', $item?->V_MODELE) }}">

<input type="hidden" id="V_ANNEE" name="V_ANNEE" value="{{ old('V_ANNEE', $item?->V_ANNEE) }}">

<input type="hidden" id="V_ASS_DATE" name="V_ASS_DATE" value="{{ old('V_ASS_DATE', $item?->V_ASS_DATE) }}">

<input type="hidden" id="V_CT_DATE" name="V_CT_DATE" value="{{ old('V_CT_DATE', $item?->V_CT_DATE) }}">

<input type="hidden" id="V_REV_DATE" name="V_REV_DATE" value="{{ old('V_REV_DATE', $item?->V_REV_DATE) }}">

<input type="hidden" id="V_TITRE_DATE" name="V_TITRE_DATE" value="{{ old('V_TITRE_DATE', $item?->V_TITRE_DATE) }}">

<input type="hidden" id="V_INVENTAIRE" name="V_INVENTAIRE" value="{{ old('V_INVENTAIRE', $item?->V_INVENTAIRE) }}">

<input type="hidden" id="V_INDICATIF" name="V_INDICATIF" value="{{ old('V_INDICATIF', $item?->V_INDICATIF) }}">

<input type="hidden" id="from" name="from" value="{{ old('from', $item?->from) }}">

<input type="hidden" id="P" name="P" value="{{ old('P', $item?->P) }}">


        <div class="mb-3">
            <label for="V_EXTERNE" class="form-label">V Externe</label>
            <input type="text" id="V_EXTERNE" name="V_EXTERNE" class="form-control" value="{{ old('V_EXTERNE', $item?->V_EXTERNE) }}">
            @error('V_EXTERNE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="dc0" name="dc0" value="{{ old('dc0', $item?->dc0) }}">


        <div class="mb-3">
            <label for="dc1" class="form-label">Dc1</label>
            <input type="text" id="dc1" name="dc1" class="form-control" value="{{ old('dc1', $item?->dc1) }}">
            @error('dc1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_vehicule.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
