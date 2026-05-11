@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_consommable.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Consommable Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_consommable.update', $itemKey) : route('legacy_migrated.save_consommable.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="C_ID" class="form-label">C Id</label>
            <input type="text" id="C_ID" name="C_ID" class="form-control" value="{{ old('C_ID', $item?->C_ID) }}">
            @error('C_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TC_ID" class="form-label">Tc Id</label>
            <input type="text" id="TC_ID" name="TC_ID" class="form-control" value="{{ old('TC_ID', $item?->TC_ID) }}">
            @error('TC_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="S_ID" class="form-label">S Id</label>
            <input type="text" id="S_ID" name="S_ID" class="form-control" value="{{ old('S_ID', $item?->S_ID) }}">
            @error('S_ID')
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
            <label for="minimum" class="form-label">Minimum</label>
            <input type="text" id="minimum" name="minimum" class="form-control" value="{{ old('minimum', $item?->minimum) }}">
            @error('minimum')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


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
            <label for="C_DESCRIPTION" class="form-label">C Description</label>
            <input type="text" id="C_DESCRIPTION" name="C_DESCRIPTION" class="form-control" value="{{ old('C_DESCRIPTION', $item?->C_DESCRIPTION) }}">
            @error('C_DESCRIPTION')
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


        <div class="mb-3">
            <label for="numlot" class="form-label">Numlot</label>
            <input type="text" id="numlot" name="numlot" class="form-control" value="{{ old('numlot', $item?->numlot) }}">
            @error('numlot')
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

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.save_consommable.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
