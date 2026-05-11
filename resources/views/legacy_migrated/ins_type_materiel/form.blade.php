@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: ins_type_materiel.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">TypeMateriel Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.ins_type_materiel.update', $itemKey) : route('legacy_migrated.ins_type_materiel.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">

<input type="hidden" id="TM_ID" name="TM_ID" value="{{ old('TM_ID', $item?->TM_ID) }}">

<input type="hidden" id="TM_LOT" name="TM_LOT" value="{{ old('TM_LOT', $item?->TM_LOT) }}">


        <div class="mb-3">
            <label for="TM_CODE" class="form-label">Tm Code</label>
            <input type="text" id="TM_CODE" name="TM_CODE" class="form-control" value="{{ old('TM_CODE', $item?->TM_CODE) }}">
            @error('TM_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TM_DESCRIPTION" class="form-label">Tm Description</label>
            <input type="text" id="TM_DESCRIPTION" name="TM_DESCRIPTION" class="form-control" value="{{ old('TM_DESCRIPTION', $item?->TM_DESCRIPTION) }}">
            @error('TM_DESCRIPTION')
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
            <label for="TM_USAGE" class="form-label">Tm Usage</label>
            <textarea id="TM_USAGE" name="TM_USAGE" class="form-control" rows="4">{{ old('TM_USAGE', $item?->TM_USAGE) }}</textarea>
            @error('TM_USAGE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TT_CODE" class="form-label">Tt Code</label>
            <textarea id="TT_CODE" name="TT_CODE" class="form-control" rows="4">{{ old('TT_CODE', $item?->TT_CODE) }}</textarea>
            @error('TT_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.ins_type_materiel.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
