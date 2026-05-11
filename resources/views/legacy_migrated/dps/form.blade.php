@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: dps.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Dps Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.dps.update', $itemKey) : route('legacy_migrated.dps.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="dimNbISActeurs" class="form-label">Dimnbisacteurs</label>
            <input type="text" id="dimNbISActeurs" name="dimNbISActeurs" class="form-control" value="{{ old('dimNbISActeurs', $item?->dimNbISActeurs) }}">
            @error('dimNbISActeurs')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="P1" class="form-label">P1</label>
            <input type="text" id="P1" name="P1" class="form-control" value="{{ old('P1', $item?->P1) }}">
            @error('P1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="P" class="form-label">P</label>
            <input type="text" id="P" name="P" class="form-control" value="{{ old('P', $item?->P) }}">
            @error('P')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="P2" class="form-label">P2</label>
            <input type="text" id="P2" name="P2" class="form-control" value="{{ old('P2', $item?->P2) }}">
            @error('P2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="E1" class="form-label">E1</label>
            <input type="text" id="E1" name="E1" class="form-control" value="{{ old('E1', $item?->E1) }}">
            @error('E1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="E2" class="form-label">E2</label>
            <input type="text" id="E2" name="E2" class="form-control" value="{{ old('E2', $item?->E2) }}">
            @error('E2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="evenement" name="evenement" value="{{ old('evenement', $item?->evenement) }}">


        <div class="mb-3">
            <label for="dimNbISActeursCom" class="form-label">Dimnbisacteurscom</label>
            <textarea id="dimNbISActeursCom" name="dimNbISActeursCom" class="form-control" rows="4">{{ old('dimNbISActeursCom', $item?->dimNbISActeursCom) }}</textarea>
            @error('dimNbISActeursCom')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="action" class="form-label">Action</label>
            <input type="text" id="action" name="action" class="form-control" value="{{ old('action', $item?->action) }}">
            @error('action')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="i" class="form-label">I</label>
            <input type="text" id="i" name="i" class="form-control" value="{{ old('i', $item?->i) }}">
            @error('i')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="RIS" class="form-label">Ris</label>
            <input type="text" id="RIS" name="RIS" class="form-control" value="{{ old('RIS', $item?->RIS) }}">
            @error('RIS')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="RISCalc" class="form-label">Riscalc</label>
            <input type="text" id="RISCalc" name="RISCalc" class="form-control" value="{{ old('RISCalc', $item?->RISCalc) }}">
            @error('RISCalc')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="NbIS" class="form-label">Nbis</label>
            <input type="text" id="NbIS" name="NbIS" class="form-control" value="{{ old('NbIS', $item?->NbIS) }}">
            @error('NbIS')
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
            <label for="commentaire" class="form-label">Commentaire</label>
            <input type="text" id="commentaire" name="commentaire" class="form-control" value="{{ old('commentaire', $item?->commentaire) }}">
            @error('commentaire')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="secteurs" class="form-label">Secteurs</label>
            <input type="text" id="secteurs" name="secteurs" class="form-control" value="{{ old('secteurs', $item?->secteurs) }}">
            @error('secteurs')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="postes" class="form-label">Postes</label>
            <input type="text" id="postes" name="postes" class="form-control" value="{{ old('postes', $item?->postes) }}">
            @error('postes')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="equipes" class="form-label">Equipes</label>
            <input type="text" id="equipes" name="equipes" class="form-control" value="{{ old('equipes', $item?->equipes) }}">
            @error('equipes')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="binomes" class="form-label">Binomes</label>
            <input type="text" id="binomes" name="binomes" class="form-control" value="{{ old('binomes', $item?->binomes) }}">
            @error('binomes')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.dps.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
