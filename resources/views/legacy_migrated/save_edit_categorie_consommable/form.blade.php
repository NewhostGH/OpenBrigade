@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_edit_categorie_consommable.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EditCategorieConsommable Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_edit_categorie_consommable.update', $itemKey) : route('legacy_migrated.save_edit_categorie_consommable.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="CC_CODE_PREV" class="form-label">Cc Code Prev</label>
            <input type="text" id="CC_CODE_PREV" name="CC_CODE_PREV" class="form-control" value="{{ old('CC_CODE_PREV', $item?->CC_CODE_PREV) }}">
            @error('CC_CODE_PREV')
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
            <label for="CC_NAME" class="form-label">Cc Name</label>
            <input type="text" id="CC_NAME" name="CC_NAME" class="form-control" value="{{ old('CC_NAME', $item?->CC_NAME) }}">
            @error('CC_NAME')
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
            <label for="CC_DESCRIPTION" class="form-label">Cc Description</label>
            <input type="text" id="CC_DESCRIPTION" name="CC_DESCRIPTION" class="form-control" value="{{ old('CC_DESCRIPTION', $item?->CC_DESCRIPTION) }}">
            @error('CC_DESCRIPTION')
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
                            <a href="{{ route('legacy_migrated.save_edit_categorie_consommable.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
