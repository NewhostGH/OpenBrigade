@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_type_evenement.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">TypeEvenement Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_type_evenement.update', $itemKey) : route('legacy_migrated.upd_type_evenement.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="OLD_TE_CODE" name="OLD_TE_CODE" value="{{ old('OLD_TE_CODE', $item?->OLD_TE_CODE) }}">

<input type="hidden" id="TE_LIBELLE" name="TE_LIBELLE" value="{{ old('TE_LIBELLE', $item?->TE_LIBELLE) }}">

<input type="hidden" id="CEV_CODE" name="CEV_CODE" value="{{ old('CEV_CODE', $item?->CEV_CODE) }}">

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">

<input type="hidden" id="TE_CODE" name="TE_CODE" value="{{ old('TE_CODE', $item?->TE_CODE) }}">

<input type="hidden" id="tab" name="tab" value="{{ old('tab', $item?->tab) }}">

<input type="hidden" id="child" name="child" value="{{ old('child', $item?->child) }}">

<input type="hidden" id="ope" name="ope" value="{{ old('ope', $item?->ope) }}">


        <div class="mb-3">
            <label for="icone" class="form-label">Icone</label>
            <input type="text" id="icone" name="icone" class="form-control" value="{{ old('icone', $item?->icone) }}">
            @error('icone')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="suppr" name="suppr" value="{{ old('suppr', $item?->suppr) }}">

<input type="hidden" id="iconsuppr" name="iconsuppr" value="{{ old('iconsuppr', $item?->iconsuppr) }}">


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
            <label for="TE_DOCUMENT" class="form-label">Te Document</label>
            <input type="text" id="TE_DOCUMENT" name="TE_DOCUMENT" class="form-control" value="{{ old('TE_DOCUMENT', $item?->TE_DOCUMENT) }}">
            @error('TE_DOCUMENT')
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

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_type_evenement.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
