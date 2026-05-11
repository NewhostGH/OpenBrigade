@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: demande_renfort.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">DemandeRenfort Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.demande_renfort.update', $itemKey) : route('legacy_migrated.demande_renfort.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="evenement" name="evenement" value="{{ old('evenement', $item?->evenement) }}">


        <div class="mb-3">
            <label for="vehicule" class="form-label">Vehicule</label>
            <input type="text" id="vehicule" name="vehicule" class="form-control" value="{{ old('vehicule', $item?->vehicule) }}">
            @error('vehicule')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="type_" class="form-label">Type </label>
            <input type="text" id="type_" name="type_" class="form-control" value="{{ old('type_', $item?->type_) }}">
            @error('type_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="$TM_USAGE" class="form-label">$Tm Usage</label>
            <input type="text" id="$TM_USAGE" name="$TM_USAGE" class="form-control" value="{{ old('$TM_USAGE', $item?->$TM_USAGE) }}">
            @error('$TM_USAGE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="point" class="form-label">Point</label>
            <input type="text" id="point" name="point" class="form-control" value="{{ old('point', $item?->point) }}">
            @error('point')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="specifique" class="form-label">Specifique</label>
            <textarea id="specifique" name="specifique" class="form-control" rows="4">{{ old('specifique', $item?->specifique) }}</textarea>
            @error('specifique')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="new_type_vehicule" class="form-label">New Type Vehicule</label>
            <textarea id="new_type_vehicule" name="new_type_vehicule" class="form-control" rows="4">{{ old('new_type_vehicule', $item?->new_type_vehicule) }}</textarea>
            @error('new_type_vehicule')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="new_type_materiel" class="form-label">New Type Materiel</label>
            <textarea id="new_type_materiel" name="new_type_materiel" class="form-control" rows="4">{{ old('new_type_materiel', $item?->new_type_materiel) }}</textarea>
            @error('new_type_materiel')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.demande_renfort.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
