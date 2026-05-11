@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: ins_materiel.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Materiel Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.ins_materiel.update', $itemKey) : route('legacy_migrated.ins_materiel.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="TM_ID" name="TM_ID" value="{{ old('TM_ID', $item?->TM_ID) }}">

<input type="hidden" id="MA_NUMERO_SERIE" name="MA_NUMERO_SERIE" value="{{ old('MA_NUMERO_SERIE', $item?->MA_NUMERO_SERIE) }}">

<input type="hidden" id="MA_COMMENT" name="MA_COMMENT" value="{{ old('MA_COMMENT', $item?->MA_COMMENT) }}">

<input type="hidden" id="VP_ID" name="VP_ID" value="{{ old('VP_ID', $item?->VP_ID) }}">

<input type="hidden" id="MA_ANNEE" name="MA_ANNEE" value="{{ old('MA_ANNEE', $item?->MA_ANNEE) }}">

<input type="hidden" id="MA_INVENTAIRE" name="MA_INVENTAIRE" value="{{ old('MA_INVENTAIRE', $item?->MA_INVENTAIRE) }}">

<input type="hidden" id="MA_REV_DATE" name="MA_REV_DATE" value="{{ old('MA_REV_DATE', $item?->MA_REV_DATE) }}">

<input type="hidden" id="groupe" name="groupe" value="{{ old('groupe', $item?->groupe) }}">

<input type="hidden" id="MA_ID" name="MA_ID" value="{{ old('MA_ID', $item?->MA_ID) }}">

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">

<input type="hidden" id="from" name="from" value="{{ old('from', $item?->from) }}">


        <div class="mb-3">
            <label for="MA_MODELE" class="form-label">Ma Modele</label>
            <input type="text" id="MA_MODELE" name="MA_MODELE" class="form-control" value="{{ old('MA_MODELE', $item?->MA_MODELE) }}">
            @error('MA_MODELE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="text" id="quantity" name="quantity" class="form-control" value="{{ old('quantity', $item?->quantity) }}">
            @error('quantity')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="MA_LIEU_STOCKAGE" class="form-label">Ma Lieu Stockage</label>
            <input type="text" id="MA_LIEU_STOCKAGE" name="MA_LIEU_STOCKAGE" class="form-control" value="{{ old('MA_LIEU_STOCKAGE', $item?->MA_LIEU_STOCKAGE) }}">
            @error('MA_LIEU_STOCKAGE')
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


        <div class="mb-3">
            <label for="MA_EXTERNE" class="form-label">Ma Externe</label>
            <input type="text" id="MA_EXTERNE" name="MA_EXTERNE" class="form-control" value="{{ old('MA_EXTERNE', $item?->MA_EXTERNE) }}">
            @error('MA_EXTERNE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="annuler" class="form-label">Annuler</label>
            <input type="text" id="annuler" name="annuler" class="form-control" value="{{ old('annuler', $item?->annuler) }}">
            @error('annuler')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TM_USAGE" class="form-label">Tm Usage</label>
            <textarea id="TM_USAGE" name="TM_USAGE" class="form-control" rows="4">{{ old('TM_USAGE', $item?->TM_USAGE) }}</textarea>
            @error('TM_USAGE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="affected_to" class="form-label">Affected To</label>
            <textarea id="affected_to" name="affected_to" class="form-control" rows="4">{{ old('affected_to', $item?->affected_to) }}</textarea>
            @error('affected_to')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.ins_materiel.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
