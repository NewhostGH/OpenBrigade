@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_grades.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Grades Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_grades.update', $itemKey) : route('legacy_migrated.save_grades.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="OLD_G_GRADE" class="form-label">Old G Grade</label>
            <input type="text" id="OLD_G_GRADE" name="OLD_G_GRADE" class="form-control" value="{{ old('OLD_G_GRADE', $item?->OLD_G_GRADE) }}">
            @error('OLD_G_GRADE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="G_GRADE" class="form-label">G Grade</label>
            <input type="text" id="G_GRADE" name="G_GRADE" class="form-control" value="{{ old('G_GRADE', $item?->G_GRADE) }}">
            @error('G_GRADE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="G_DESCRIPTION" class="form-label">G Description</label>
            <input type="text" id="G_DESCRIPTION" name="G_DESCRIPTION" class="form-control" value="{{ old('G_DESCRIPTION', $item?->G_DESCRIPTION) }}">
            @error('G_DESCRIPTION')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="categorie" class="form-label">Categorie</label>
            <input type="text" id="categorie" name="categorie" class="form-control" value="{{ old('categorie', $item?->categorie) }}">
            @error('categorie')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="G_TYPE" class="form-label">G Type</label>
            <input type="text" id="G_TYPE" name="G_TYPE" class="form-control" value="{{ old('G_TYPE', $item?->G_TYPE) }}">
            @error('G_TYPE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="G_LEVEL" class="form-label">G Level</label>
            <input type="text" id="G_LEVEL" name="G_LEVEL" class="form-control" value="{{ old('G_LEVEL', $item?->G_LEVEL) }}">
            @error('G_LEVEL')
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


        <div class="mb-3">
            <label for="usage" class="form-label">Usage</label>
            <input type="text" id="usage" name="usage" class="form-control" value="{{ old('usage', $item?->usage) }}">
            @error('usage')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="oldCat" class="form-label">Oldcat</label>
            <input type="text" id="oldCat" name="oldCat" class="form-control" value="{{ old('oldCat', $item?->oldCat) }}">
            @error('oldCat')
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
                            <a href="{{ route('legacy_migrated.save_grades.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
