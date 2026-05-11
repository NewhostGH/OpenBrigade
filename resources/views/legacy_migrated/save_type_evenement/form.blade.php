@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_type_evenement.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">TypeEvenement Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_type_evenement.update', $itemKey) : route('legacy_migrated.save_type_evenement.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="OLD_TE_CODE" class="form-label">Old Te Code</label>
            <input type="text" id="OLD_TE_CODE" name="OLD_TE_CODE" class="form-control" value="{{ old('OLD_TE_CODE', $item?->OLD_TE_CODE) }}">
            @error('OLD_TE_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TE_CODE" class="form-label">Te Code</label>
            <input type="text" id="TE_CODE" name="TE_CODE" class="form-control" value="{{ old('TE_CODE', $item?->TE_CODE) }}">
            @error('TE_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="CEV_CODE" class="form-label">Cev Code</label>
            <input type="text" id="CEV_CODE" name="CEV_CODE" class="form-control" value="{{ old('CEV_CODE', $item?->CEV_CODE) }}">
            @error('CEV_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TE_LIBELLE" class="form-label">Te Libelle</label>
            <input type="text" id="TE_LIBELLE" name="TE_LIBELLE" class="form-control" value="{{ old('TE_LIBELLE', $item?->TE_LIBELLE) }}">
            @error('TE_LIBELLE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="operation" class="form-label">Operation</label>
            <input type="text" id="operation" name="operation" class="form-control" value="{{ old('operation', $item?->operation) }}">
            @error('operation')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TE_MULTI_DUPLI" class="form-label">Te Multi Dupli</label>
            <input type="text" id="TE_MULTI_DUPLI" name="TE_MULTI_DUPLI" class="form-control" value="{{ old('TE_MULTI_DUPLI', $item?->TE_MULTI_DUPLI) }}">
            @error('TE_MULTI_DUPLI')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TE_MAIN_COURANTE" class="form-label">Te Main Courante</label>
            <input type="text" id="TE_MAIN_COURANTE" name="TE_MAIN_COURANTE" class="form-control" value="{{ old('TE_MAIN_COURANTE', $item?->TE_MAIN_COURANTE) }}">
            @error('TE_MAIN_COURANTE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TE_VICTIMES" class="form-label">Te Victimes</label>
            <input type="text" id="TE_VICTIMES" name="TE_VICTIMES" class="form-control" value="{{ old('TE_VICTIMES', $item?->TE_VICTIMES) }}">
            @error('TE_VICTIMES')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="ACCES_RESTREINT" class="form-label">Acces Restreint</label>
            <input type="text" id="ACCES_RESTREINT" name="ACCES_RESTREINT" class="form-control" value="{{ old('ACCES_RESTREINT', $item?->ACCES_RESTREINT) }}">
            @error('ACCES_RESTREINT')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TE_PERSONNEL" class="form-label">Te Personnel</label>
            <input type="text" id="TE_PERSONNEL" name="TE_PERSONNEL" class="form-control" value="{{ old('TE_PERSONNEL', $item?->TE_PERSONNEL) }}">
            @error('TE_PERSONNEL')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TE_VEHICULES" class="form-label">Te Vehicules</label>
            <input type="text" id="TE_VEHICULES" name="TE_VEHICULES" class="form-control" value="{{ old('TE_VEHICULES', $item?->TE_VEHICULES) }}">
            @error('TE_VEHICULES')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TE_MATERIEL" class="form-label">Te Materiel</label>
            <input type="text" id="TE_MATERIEL" name="TE_MATERIEL" class="form-control" value="{{ old('TE_MATERIEL', $item?->TE_MATERIEL) }}">
            @error('TE_MATERIEL')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TE_CONSOMMABLES" class="form-label">Te Consommables</label>
            <input type="text" id="TE_CONSOMMABLES" name="TE_CONSOMMABLES" class="form-control" value="{{ old('TE_CONSOMMABLES', $item?->TE_CONSOMMABLES) }}">
            @error('TE_CONSOMMABLES')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="COLONNE_RENFORT" class="form-label">Colonne Renfort</label>
            <input type="text" id="COLONNE_RENFORT" name="COLONNE_RENFORT" class="form-control" value="{{ old('COLONNE_RENFORT', $item?->COLONNE_RENFORT) }}">
            @error('COLONNE_RENFORT')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="REMPLACEMENT" class="form-label">Remplacement</label>
            <input type="text" id="REMPLACEMENT" name="REMPLACEMENT" class="form-control" value="{{ old('REMPLACEMENT', $item?->REMPLACEMENT) }}">
            @error('REMPLACEMENT')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PIQUET" class="form-label">Piquet</label>
            <input type="text" id="PIQUET" name="PIQUET" class="form-control" value="{{ old('PIQUET', $item?->PIQUET) }}">
            @error('PIQUET')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TE_MAP" class="form-label">Te Map</label>
            <input type="text" id="TE_MAP" name="TE_MAP" class="form-control" value="{{ old('TE_MAP', $item?->TE_MAP) }}">
            @error('TE_MAP')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="CLIENT" class="form-label">Client</label>
            <input type="text" id="CLIENT" name="CLIENT" class="form-control" value="{{ old('CLIENT', $item?->CLIENT) }}">
            @error('CLIENT')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TE_DPS" class="form-label">Te Dps</label>
            <input type="text" id="TE_DPS" name="TE_DPS" class="form-control" value="{{ old('TE_DPS', $item?->TE_DPS) }}">
            @error('TE_DPS')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TE_DOCUMENT" class="form-label">Te Document</label>
            <input type="text" id="TE_DOCUMENT" name="TE_DOCUMENT" class="form-control" value="{{ old('TE_DOCUMENT', $item?->TE_DOCUMENT) }}">
            @error('TE_DOCUMENT')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.save_type_evenement.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
