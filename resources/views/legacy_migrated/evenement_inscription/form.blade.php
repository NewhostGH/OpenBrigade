@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_inscription.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementInscription Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_inscription.update', $itemKey) : route('legacy_migrated.evenement_inscription.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="evenement" name="evenement" value="{{ old('evenement', $item?->evenement) }}">

<input type="hidden" id="P_ID" name="P_ID" value="{{ old('P_ID', $item?->P_ID) }}">

<input type="hidden" id="accept" name="accept" value="{{ old('accept', $item?->accept) }}">

<input type="hidden" id="action" name="action" value="{{ old('action', $item?->action) }}">


        <div class="mb-3">
            <label for="chien_" class="form-label">Chien </label>
            <input type="text" id="chien_" name="chien_" class="form-control" value="{{ old('chien_', $item?->chien_) }}">
            @error('chien_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="vehic_" class="form-label">Vehic </label>
            <input type="text" id="vehic_" name="vehic_" class="form-control" value="{{ old('vehic_', $item?->vehic_) }}">
            @error('vehic_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="statut" class="form-label">Statut</label>
            <input type="text" id="statut" name="statut" class="form-control" value="{{ old('statut', $item?->statut) }}">
            @error('statut')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="inscription[]" class="form-label">Inscription[]</label>
            <input type="text" id="inscription[]" name="inscription[]" class="form-control" value="{{ old('inscription[]', $item?->inscription[]) }}">
            @error('inscription[]')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="value" class="form-label">Value</label>
            <input type="text" id="value" name="value" class="form-control" value="{{ old('value', $item?->value) }}">
            @error('value')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EP_FLAG1" class="form-label">Ep Flag1</label>
            <input type="text" id="EP_FLAG1" name="EP_FLAG1" class="form-control" value="{{ old('EP_FLAG1', $item?->EP_FLAG1) }}">
            @error('EP_FLAG1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="inscription" class="form-label">Inscription</label>
            <input type="text" id="inscription" name="inscription" class="form-control" value="{{ old('inscription', $item?->inscription) }}">
            @error('inscription')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_inscription.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
