@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_type_consommable.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">TypeConsommable Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_type_consommable.update', $itemKey) : route('legacy_migrated.save_type_consommable.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="TC_ID" class="form-label">Tc Id</label>
            <input type="text" id="TC_ID" name="TC_ID" class="form-control" value="{{ old('TC_ID', $item?->TC_ID) }}">
            @error('TC_ID')
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
            <label for="CC_CODE" class="form-label">Cc Code</label>
            <input type="text" id="CC_CODE" name="CC_CODE" class="form-control" value="{{ old('CC_CODE', $item?->CC_CODE) }}">
            @error('CC_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TC_DESCRIPTION" class="form-label">Tc Description</label>
            <input type="text" id="TC_DESCRIPTION" name="TC_DESCRIPTION" class="form-control" value="{{ old('TC_DESCRIPTION', $item?->TC_DESCRIPTION) }}">
            @error('TC_DESCRIPTION')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TCO_CODE" class="form-label">Tco Code</label>
            <input type="text" id="TCO_CODE" name="TCO_CODE" class="form-control" value="{{ old('TCO_CODE', $item?->TCO_CODE) }}">
            @error('TCO_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TUM_CODE" class="form-label">Tum Code</label>
            <input type="text" id="TUM_CODE" name="TUM_CODE" class="form-control" value="{{ old('TUM_CODE', $item?->TUM_CODE) }}">
            @error('TUM_CODE')
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
            <label for="from" class="form-label">From</label>
            <input type="text" id="from" name="from" class="form-control" value="{{ old('from', $item?->from) }}">
            @error('from')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.save_type_consommable.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
