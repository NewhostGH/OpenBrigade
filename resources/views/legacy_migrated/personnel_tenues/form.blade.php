@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: personnel_tenues.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">PersonnelTenues Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.personnel_tenues.update', $itemKey) : route('legacy_migrated.personnel_tenues.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="pompier" name="pompier" value="{{ old('pompier', $item?->pompier) }}">

<input type="hidden" id="TYPE_" name="TYPE_" value="{{ old('TYPE_', $item?->TYPE_) }}">


        <div class="mb-3">
            <label for="MODELE_" class="form-label">Modele </label>
            <input type="text" id="MODELE_" name="MODELE_" class="form-control" value="{{ old('MODELE_', $item?->MODELE_) }}">
            @error('MODELE_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="ANNEE_" class="form-label">Annee </label>
            <input type="text" id="ANNEE_" name="ANNEE_" class="form-control" value="{{ old('ANNEE_', $item?->ANNEE_) }}">
            @error('ANNEE_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="NB_" class="form-label">Nb </label>
            <input type="text" id="NB_" name="NB_" class="form-control" value="{{ old('NB_', $item?->NB_) }}">
            @error('NB_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="NEW_" name="NEW_" value="{{ old('NEW_', $item?->NEW_) }}">


        <div class="mb-3">
            <label for="annuler" class="form-label">Annuler</label>
            <input type="text" id="annuler" name="annuler" class="form-control" value="{{ old('annuler', $item?->annuler) }}">
            @error('annuler')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TAILLE_" class="form-label">Taille </label>
            <textarea id="TAILLE_" name="TAILLE_" class="form-control" rows="4">{{ old('TAILLE_', $item?->TAILLE_) }}</textarea>
            @error('TAILLE_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.personnel_tenues.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
