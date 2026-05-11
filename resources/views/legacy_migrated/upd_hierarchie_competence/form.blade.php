@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_hierarchie_competence.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">HierarchieCompetence Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_hierarchie_competence.update', $itemKey) : route('legacy_migrated.upd_hierarchie_competence.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="OLD_PH_CODE" name="OLD_PH_CODE" value="{{ old('OLD_PH_CODE', $item?->OLD_PH_CODE) }}">

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">


        <div class="mb-3">
            <label for="PH_CODE" class="form-label">Ph Code</label>
            <input type="text" id="PH_CODE" name="PH_CODE" class="form-control" value="{{ old('PH_CODE', $item?->PH_CODE) }}">
            @error('PH_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PH_NAME" class="form-label">Ph Name</label>
            <input type="text" id="PH_NAME" name="PH_NAME" class="form-control" value="{{ old('PH_NAME', $item?->PH_NAME) }}">
            @error('PH_NAME')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PH_HIDE_LOWER" class="form-label">Ph Hide Lower</label>
            <input type="text" id="PH_HIDE_LOWER" name="PH_HIDE_LOWER" class="form-control" value="{{ old('PH_HIDE_LOWER', $item?->PH_HIDE_LOWER) }}">
            @error('PH_HIDE_LOWER')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PH_UPDATE_LOWER_EXPIRY" class="form-label">Ph Update Lower Expiry</label>
            <input type="text" id="PH_UPDATE_LOWER_EXPIRY" name="PH_UPDATE_LOWER_EXPIRY" class="form-control" value="{{ old('PH_UPDATE_LOWER_EXPIRY', $item?->PH_UPDATE_LOWER_EXPIRY) }}">
            @error('PH_UPDATE_LOWER_EXPIRY')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="PH_UPDATE_MANDATORY" class="form-label">Ph Update Mandatory</label>
            <input type="text" id="PH_UPDATE_MANDATORY" name="PH_UPDATE_MANDATORY" class="form-control" value="{{ old('PH_UPDATE_MANDATORY', $item?->PH_UPDATE_MANDATORY) }}">
            @error('PH_UPDATE_MANDATORY')
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

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_hierarchie_competence.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
