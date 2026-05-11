@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_edit_categorie_grades.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EditCategorieGrades Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_edit_categorie_grades.update', $itemKey) : route('legacy_migrated.save_edit_categorie_grades.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="code_cat" class="form-label">Code Cat</label>
            <input type="text" id="code_cat" name="code_cat" class="form-control" value="{{ old('code_cat', $item?->code_cat) }}">
            @error('code_cat')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="description_cat" class="form-label">Description Cat</label>
            <input type="text" id="description_cat" name="description_cat" class="form-control" value="{{ old('description_cat', $item?->description_cat) }}">
            @error('description_cat')
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
                            <a href="{{ route('legacy_migrated.save_edit_categorie_grades.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
