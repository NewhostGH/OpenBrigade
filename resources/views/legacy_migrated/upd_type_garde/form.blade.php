@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_type_garde.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">TypeGarde Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_type_garde.update', $itemKey) : route('legacy_migrated.upd_type_garde.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="EQ_ID" name="EQ_ID" value="{{ old('EQ_ID', $item?->EQ_ID) }}">

<input type="hidden" id="groupe" name="groupe" value="{{ old('groupe', $item?->groupe) }}">


        <div class="mb-3">
            <label for="EQ_NOM" class="form-label">Eq Nom</label>
            <input type="text" id="EQ_NOM" name="EQ_NOM" class="form-control" value="{{ old('EQ_NOM', $item?->EQ_NOM) }}">
            @error('EQ_NOM')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EQ_LIEU" class="form-label">Eq Lieu</label>
            <input type="text" id="EQ_LIEU" name="EQ_LIEU" class="form-control" value="{{ old('EQ_LIEU', $item?->EQ_LIEU) }}">
            @error('EQ_LIEU')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EQ_ADDRESS" class="form-label">Eq Address</label>
            <input type="text" id="EQ_ADDRESS" name="EQ_ADDRESS" class="form-control" value="{{ old('EQ_ADDRESS', $item?->EQ_ADDRESS) }}">
            @error('EQ_ADDRESS')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="date1" name="date1" value="{{ old('date1', $item?->date1) }}">

<input type="hidden" id="date2" name="date2" value="{{ old('date2', $item?->date2) }}">


        <div class="mb-3">
            <label for="EQ_JOUR" class="form-label">Eq Jour</label>
            <input type="text" id="EQ_JOUR" name="EQ_JOUR" class="form-control" value="{{ old('EQ_JOUR', $item?->EQ_JOUR) }}">
            @error('EQ_JOUR')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EQ_PERSONNEL1" class="form-label">Eq Personnel1</label>
            <input type="text" id="EQ_PERSONNEL1" name="EQ_PERSONNEL1" class="form-control" value="{{ old('EQ_PERSONNEL1', $item?->EQ_PERSONNEL1) }}">
            @error('EQ_PERSONNEL1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EQ_NUIT" class="form-label">Eq Nuit</label>
            <input type="text" id="EQ_NUIT" name="EQ_NUIT" class="form-control" value="{{ old('EQ_NUIT', $item?->EQ_NUIT) }}">
            @error('EQ_NUIT')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EQ_PERSONNEL2" class="form-label">Eq Personnel2</label>
            <input type="text" id="EQ_PERSONNEL2" name="EQ_PERSONNEL2" class="form-control" value="{{ old('EQ_PERSONNEL2', $item?->EQ_PERSONNEL2) }}">
            @error('EQ_PERSONNEL2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EQ_VEHICULES" class="form-label">Eq Vehicules</label>
            <input type="text" id="EQ_VEHICULES" name="EQ_VEHICULES" class="form-control" value="{{ old('EQ_VEHICULES', $item?->EQ_VEHICULES) }}">
            @error('EQ_VEHICULES')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EQ_SPP" class="form-label">Eq Spp</label>
            <input type="text" id="EQ_SPP" name="EQ_SPP" class="form-control" value="{{ old('EQ_SPP', $item?->EQ_SPP) }}">
            @error('EQ_SPP')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EQ_DEFAULT" class="form-label">Eq Default</label>
            <input type="text" id="EQ_DEFAULT" name="EQ_DEFAULT" class="form-control" value="{{ old('EQ_DEFAULT', $item?->EQ_DEFAULT) }}">
            @error('EQ_DEFAULT')
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
            <label for="annuler" class="form-label">Annuler</label>
            <input type="text" id="annuler" name="annuler" class="form-control" value="{{ old('annuler', $item?->annuler) }}">
            @error('annuler')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EQ_REGIME_TRAVAIL" class="form-label">Eq Regime Travail</label>
            <textarea id="EQ_REGIME_TRAVAIL" name="EQ_REGIME_TRAVAIL" class="form-control" rows="4">{{ old('EQ_REGIME_TRAVAIL', $item?->EQ_REGIME_TRAVAIL) }}</textarea>
            @error('EQ_REGIME_TRAVAIL')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="debut1" class="form-label">Debut1</label>
            <textarea id="debut1" name="debut1" class="form-control" rows="4">{{ old('debut1', $item?->debut1) }}</textarea>
            @error('debut1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="fin1" class="form-label">Fin1</label>
            <textarea id="fin1" name="fin1" class="form-control" rows="4">{{ old('fin1', $item?->fin1) }}</textarea>
            @error('fin1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="duree1" class="form-label">Duree1</label>
            <textarea id="duree1" name="duree1" class="form-control" rows="4">{{ old('duree1', $item?->duree1) }}</textarea>
            @error('duree1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_type_garde.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
