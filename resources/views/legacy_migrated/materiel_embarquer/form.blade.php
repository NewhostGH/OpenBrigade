@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: materiel_embarquer.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">MaterielEmbarquer Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.materiel_embarquer.update', $itemKey) : route('legacy_migrated.materiel_embarquer.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <textarea id="type" name="type" class="form-control" rows="4">{{ old('type', $item?->type) }}</textarea>
            @error('type')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="addmateriel" class="form-label">Addmateriel</label>
            <textarea id="addmateriel" name="addmateriel" class="form-control" rows="4">{{ old('addmateriel', $item?->addmateriel) }}</textarea>
            @error('addmateriel')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="addconsommable" class="form-label">Addconsommable</label>
            <textarea id="addconsommable" name="addconsommable" class="form-control" rows="4">{{ old('addconsommable', $item?->addconsommable) }}</textarea>
            @error('addconsommable')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.materiel_embarquer.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
