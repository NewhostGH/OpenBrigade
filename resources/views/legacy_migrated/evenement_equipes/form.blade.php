@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_equipes.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementEquipes Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_equipes.update', $itemKey) : route('legacy_migrated.evenement_equipes.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="EE_ORDER" class="form-label">Ee Order</label>
            <textarea id="EE_ORDER" name="EE_ORDER" class="form-control" rows="4">{{ old('EE_ORDER', $item?->EE_ORDER) }}</textarea>
            @error('EE_ORDER')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="evenement" class="form-label">Evenement</label>
            <input type="text" id="evenement" name="evenement" class="form-control" value="{{ old('evenement', $item?->evenement) }}">
            @error('evenement')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="equipe" class="form-label">Equipe</label>
            <input type="text" id="equipe" name="equipe" class="form-control" value="{{ old('equipe', $item?->equipe) }}">
            @error('equipe')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EE_NAME" class="form-label">Ee Name</label>
            <input type="text" id="EE_NAME" name="EE_NAME" class="form-control" value="{{ old('EE_NAME', $item?->EE_NAME) }}">
            @error('EE_NAME')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EE_DESCRIPTION" class="form-label">Ee Description</label>
            <input type="text" id="EE_DESCRIPTION" name="EE_DESCRIPTION" class="form-control" value="{{ old('EE_DESCRIPTION', $item?->EE_DESCRIPTION) }}">
            @error('EE_DESCRIPTION')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EE_ID_RADIO" class="form-label">Ee Id Radio</label>
            <input type="text" id="EE_ID_RADIO" name="EE_ID_RADIO" class="form-control" value="{{ old('EE_ID_RADIO', $item?->EE_ID_RADIO) }}">
            @error('EE_ID_RADIO')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EE_SIGNATURE" class="form-label">Ee Signature</label>
            <input type="text" id="EE_SIGNATURE" name="EE_SIGNATURE" class="form-control" value="{{ old('EE_SIGNATURE', $item?->EE_SIGNATURE) }}">
            @error('EE_SIGNATURE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="icon" class="form-label">Icon</label>
            <input type="text" id="icon" name="icon" class="form-control" value="{{ old('icon', $item?->icon) }}">
            @error('icon')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_equipes.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
