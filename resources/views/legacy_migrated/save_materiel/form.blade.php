@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_materiel.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Materiel Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_materiel.update', $itemKey) : route('legacy_migrated.save_materiel.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="section" class="form-label">Section</label>
            <input type="text" id="section" name="section" class="form-control" value="{{ old('section', $item?->section) }}">
            @error('section')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="materiel" class="form-label">Materiel</label>
            <input type="text" id="materiel" name="materiel" class="form-control" value="{{ old('materiel', $item?->materiel) }}">
            @error('materiel')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <input type="text" id="type" name="type" class="form-control" value="{{ old('type', $item?->type) }}">
            @error('type')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="security" class="form-label">Security</label>
            <input type="text" id="security" name="security" class="form-control" value="{{ old('security', $item?->security) }}">
            @error('security')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="MA_NUMERO_SERIE" class="form-label">Ma Numero Serie</label>
            <input type="text" id="MA_NUMERO_SERIE" name="MA_NUMERO_SERIE" class="form-control" value="{{ old('MA_NUMERO_SERIE', $item?->MA_NUMERO_SERIE) }}">
            @error('MA_NUMERO_SERIE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="MA_COMMENT" class="form-label">Ma Comment</label>
            <input type="text" id="MA_COMMENT" name="MA_COMMENT" class="form-control" value="{{ old('MA_COMMENT', $item?->MA_COMMENT) }}">
            @error('MA_COMMENT')
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
            <label for="MA_INVENTAIRE2" class="form-label">Ma Inventaire2</label>
            <input type="text" id="MA_INVENTAIRE2" name="MA_INVENTAIRE2" class="form-control" value="{{ old('MA_INVENTAIRE2', $item?->MA_INVENTAIRE2) }}">
            @error('MA_INVENTAIRE2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="MA_MODELE" class="form-label">Ma Modele</label>
            <input type="text" id="MA_MODELE" name="MA_MODELE" class="form-control" value="{{ old('MA_MODELE', $item?->MA_MODELE) }}">
            @error('MA_MODELE')
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


        <div class="mb-3">
            <label for="TM_ID" class="form-label">Tm Id</label>
            <input type="text" id="TM_ID" name="TM_ID" class="form-control" value="{{ old('TM_ID', $item?->TM_ID) }}">
            @error('TM_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TM_USAGE" class="form-label">Tm Usage</label>
            <input type="text" id="TM_USAGE" name="TM_USAGE" class="form-control" value="{{ old('TM_USAGE', $item?->TM_USAGE) }}">
            @error('TM_USAGE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="MA_ID" class="form-label">Ma Id</label>
            <input type="text" id="MA_ID" name="MA_ID" class="form-control" value="{{ old('MA_ID', $item?->MA_ID) }}">
            @error('MA_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TV_ID" class="form-label">Tv Id</label>
            <input type="text" id="TV_ID" name="TV_ID" class="form-control" value="{{ old('TV_ID', $item?->TV_ID) }}">
            @error('TV_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="MA_ANNEE" class="form-label">Ma Annee</label>
            <input type="text" id="MA_ANNEE" name="MA_ANNEE" class="form-control" value="{{ old('MA_ANNEE', $item?->MA_ANNEE) }}">
            @error('MA_ANNEE')
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
            <label for="VP_ID" class="form-label">Vp Id</label>
            <input type="text" id="VP_ID" name="VP_ID" class="form-control" value="{{ old('VP_ID', $item?->VP_ID) }}">
            @error('VP_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="groupe" class="form-label">Groupe</label>
            <input type="text" id="groupe" name="groupe" class="form-control" value="{{ old('groupe', $item?->groupe) }}">
            @error('groupe')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


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

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.save_materiel.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
