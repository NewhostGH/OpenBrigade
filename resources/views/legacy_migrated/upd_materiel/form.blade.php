@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_materiel.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Materiel Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_materiel.update', $itemKey) : route('legacy_migrated.upd_materiel.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="MA_ID" name="MA_ID" value="{{ old('MA_ID', $item?->MA_ID) }}">

<input type="hidden" id="MA_NUMERO_SERIE" name="MA_NUMERO_SERIE" value="{{ old('MA_NUMERO_SERIE', $item?->MA_NUMERO_SERIE) }}">

<input type="hidden" id="MA_COMMENT" name="MA_COMMENT" value="{{ old('MA_COMMENT', $item?->MA_COMMENT) }}">

<input type="hidden" id="VP_ID" name="VP_ID" value="{{ old('VP_ID', $item?->VP_ID) }}">

<input type="hidden" id="MA_MODELE" name="MA_MODELE" value="{{ old('MA_MODELE', $item?->MA_MODELE) }}">

<input type="hidden" id="MA_ANNEE" name="MA_ANNEE" value="{{ old('MA_ANNEE', $item?->MA_ANNEE) }}">

<input type="hidden" id="MA_REV_DATE" name="MA_REV_DATE" value="{{ old('MA_REV_DATE', $item?->MA_REV_DATE) }}">

<input type="hidden" id="TM_USAGE" name="TM_USAGE" value="{{ old('TM_USAGE', $item?->TM_USAGE) }}">

<input type="hidden" id="TV_ID" name="TV_ID" value="{{ old('TV_ID', $item?->TV_ID) }}">

<input type="hidden" id="numlot" name="numlot" value="{{ old('numlot', $item?->numlot) }}">

<input type="hidden" id="from" name="from" value="{{ old('from', $item?->from) }}">


        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="text" id="quantity" name="quantity" class="form-control" value="{{ old('quantity', $item?->quantity) }}">
            @error('quantity')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="MA_INVENTAIRE" class="form-label">Ma Inventaire</label>
            <input type="text" id="MA_INVENTAIRE" name="MA_INVENTAIRE" class="form-control" value="{{ old('MA_INVENTAIRE', $item?->MA_INVENTAIRE) }}">
            @error('MA_INVENTAIRE')
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

<input type="hidden" id="TM_CODE" name="TM_CODE" value="{{ old('TM_CODE', $item?->TM_CODE) }}">

<input type="hidden" id="groupe" name="groupe" value="{{ old('groupe', $item?->groupe) }}">

<input type="hidden" id="MA_INVENTAIRE2" name="MA_INVENTAIRE2" value="{{ old('MA_INVENTAIRE2', $item?->MA_INVENTAIRE2) }}">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_materiel.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
