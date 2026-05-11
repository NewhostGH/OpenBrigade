@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_type_vehicule.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">TypeVehicule Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_type_vehicule.update', $itemKey) : route('legacy_migrated.upd_type_vehicule.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">

<input type="hidden" id="OLD_TV_CODE" name="OLD_TV_CODE" value="{{ old('OLD_TV_CODE', $item?->OLD_TV_CODE) }}">


        <div class="mb-3">
            <label for="TV_CODE" class="form-label">Tv Code</label>
            <input type="text" id="TV_CODE" name="TV_CODE" class="form-control" value="{{ old('TV_CODE', $item?->TV_CODE) }}">
            @error('TV_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TV_LIBELLE" class="form-label">Tv Libelle</label>
            <input type="text" id="TV_LIBELLE" name="TV_LIBELLE" class="form-control" value="{{ old('TV_LIBELLE', $item?->TV_LIBELLE) }}">
            @error('TV_LIBELLE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="tab" name="tab" value="{{ old('tab', $item?->tab) }}">

<input type="hidden" id="child" name="child" value="{{ old('child', $item?->child) }}">

<input type="hidden" id="upd" name="upd" value="{{ old('upd', $item?->upd) }}">


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
            <label for="ROLE_$i" class="form-label">Role $I</label>
            <input type="text" id="ROLE_$i" name="ROLE_$i" class="form-control" value="{{ old('ROLE_$i', $item?->ROLE_$i) }}">
            @error('ROLE_$i')
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
            <label for="TV_USAGE" class="form-label">Tv Usage</label>
            <textarea id="TV_USAGE" name="TV_USAGE" class="form-control" rows="4">{{ old('TV_USAGE', $item?->TV_USAGE) }}</textarea>
            @error('TV_USAGE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TV_NB" class="form-label">Tv Nb</label>
            <textarea id="TV_NB" name="TV_NB" class="form-control" rows="4">{{ old('TV_NB', $item?->TV_NB) }}</textarea>
            @error('TV_NB')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PS_$i" class="form-label">Ps $I</label>
            <textarea id="PS_$i" name="PS_$i" class="form-control" rows="4">{{ old('PS_$i', $item?->PS_$i) }}</textarea>
            @error('PS_$i')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_type_vehicule.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
