@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_consommable.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Consommable Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_consommable.update', $itemKey) : route('legacy_migrated.upd_consommable.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="C_ID" name="C_ID" value="{{ old('C_ID', $item?->C_ID) }}">

<input type="hidden" id="numlot" name="numlot" value="{{ old('numlot', $item?->numlot) }}">

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">


        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="text" id="quantity" name="quantity" class="form-control" value="{{ old('quantity', $item?->quantity) }}">
            @error('quantity')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="minimum" class="form-label">Minimum</label>
            <input type="text" id="minimum" name="minimum" class="form-control" value="{{ old('minimum', $item?->minimum) }}">
            @error('minimum')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="C_DESCRIPTION" class="form-label">C Description</label>
            <input type="text" id="C_DESCRIPTION" name="C_DESCRIPTION" class="form-control" value="{{ old('C_DESCRIPTION', $item?->C_DESCRIPTION) }}">
            @error('C_DESCRIPTION')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="dc0" name="dc0" value="{{ old('dc0', $item?->dc0) }}">


        <div class="mb-3">
            <label for="C_DATE_ACHAT" class="form-label">C Date Achat</label>
            <input type="text" id="C_DATE_ACHAT" name="C_DATE_ACHAT" class="form-control" value="{{ old('C_DATE_ACHAT', $item?->C_DATE_ACHAT) }}">
            @error('C_DATE_ACHAT')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="C_DATE_PEREMPTION" class="form-label">C Date Peremption</label>
            <input type="text" id="C_DATE_PEREMPTION" name="C_DATE_PEREMPTION" class="form-control" value="{{ old('C_DATE_PEREMPTION', $item?->C_DATE_PEREMPTION) }}">
            @error('C_DATE_PEREMPTION')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="C_LIEU_STOCKAGE" class="form-label">C Lieu Stockage</label>
            <input type="text" id="C_LIEU_STOCKAGE" name="C_LIEU_STOCKAGE" class="form-control" value="{{ old('C_LIEU_STOCKAGE', $item?->C_LIEU_STOCKAGE) }}">
            @error('C_LIEU_STOCKAGE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="S_ID" name="S_ID" value="{{ old('S_ID', $item?->S_ID) }}">


        <div class="mb-3">
            <label for="annuler" class="form-label">Annuler</label>
            <input type="text" id="annuler" name="annuler" class="form-control" value="{{ old('annuler', $item?->annuler) }}">
            @error('annuler')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TC_ID" class="form-label">Tc Id</label>
            <textarea id="TC_ID" name="TC_ID" class="form-control" rows="4">{{ old('TC_ID', $item?->TC_ID) }}</textarea>
            @error('TC_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_consommable.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
