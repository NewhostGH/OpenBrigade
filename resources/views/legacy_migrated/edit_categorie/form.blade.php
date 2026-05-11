@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: edit_categorie.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EditCategorie Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.edit_categorie.update', $itemKey) : route('legacy_migrated.edit_categorie.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="TM_USAGE_PREV" name="TM_USAGE_PREV" value="{{ old('TM_USAGE_PREV', $item?->TM_USAGE_PREV) }}">


        <div class="mb-3">
            <label for="TM_USAGE" class="form-label">Tm Usage</label>
            <input type="text" id="TM_USAGE" name="TM_USAGE" class="form-control" value="{{ old('TM_USAGE', $item?->TM_USAGE) }}">
            @error('TM_USAGE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="CM_DESCRIPTION" class="form-label">Cm Description</label>
            <input type="text" id="CM_DESCRIPTION" name="CM_DESCRIPTION" class="form-control" value="{{ old('CM_DESCRIPTION', $item?->CM_DESCRIPTION) }}">
            @error('CM_DESCRIPTION')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="logo" class="form-label">Logo</label>
            <input type="text" id="logo" name="logo" class="form-control" value="{{ old('logo', $item?->logo) }}">
            @error('logo')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="Delete" class="form-label">Delete</label>
            <input type="text" id="Delete" name="Delete" class="form-control" value="{{ old('Delete', $item?->Delete) }}">
            @error('Delete')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.edit_categorie.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
