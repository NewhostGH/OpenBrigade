@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_type_consommable.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">TypeConsommable Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_type_consommable.update', $itemKey) : route('legacy_migrated.upd_type_consommable.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="TC_ID" name="TC_ID" value="{{ old('TC_ID', $item?->TC_ID) }}">


        <div class="mb-3">
            <label for="TC_DESCRIPTION" class="form-label">Tc Description</label>
            <input type="text" id="TC_DESCRIPTION" name="TC_DESCRIPTION" class="form-control" value="{{ old('TC_DESCRIPTION', $item?->TC_DESCRIPTION) }}">
            @error('TC_DESCRIPTION')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TC_QUANTITE_PAR_UNITE" class="form-label">Tc Quantite Par Unite</label>
            <input type="text" id="TC_QUANTITE_PAR_UNITE" name="TC_QUANTITE_PAR_UNITE" class="form-control" value="{{ old('TC_QUANTITE_PAR_UNITE', $item?->TC_QUANTITE_PAR_UNITE) }}">
            @error('TC_QUANTITE_PAR_UNITE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TC_PEREMPTION" class="form-label">Tc Peremption</label>
            <input type="text" id="TC_PEREMPTION" name="TC_PEREMPTION" class="form-control" value="{{ old('TC_PEREMPTION', $item?->TC_PEREMPTION) }}">
            @error('TC_PEREMPTION')
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
            <label for="CC_CODE" class="form-label">Cc Code</label>
            <textarea id="CC_CODE" name="CC_CODE" class="form-control" rows="4">{{ old('CC_CODE', $item?->CC_CODE) }}</textarea>
            @error('CC_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TCO_CODE" class="form-label">Tco Code</label>
            <textarea id="TCO_CODE" name="TCO_CODE" class="form-control" rows="4">{{ old('TCO_CODE', $item?->TCO_CODE) }}</textarea>
            @error('TCO_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TUM_CODE" class="form-label">Tum Code</label>
            <textarea id="TUM_CODE" name="TUM_CODE" class="form-control" rows="4">{{ old('TUM_CODE', $item?->TUM_CODE) }}</textarea>
            @error('TUM_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_type_consommable.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
