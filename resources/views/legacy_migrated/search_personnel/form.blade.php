@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: search_personnel.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">SearchPersonnel Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.search_personnel.update', $itemKey) : route('legacy_migrated.search_personnel.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="choixSection" name="choixSection" value="{{ old('choixSection', $item?->choixSection) }}">


        <div class="mb-3">
            <label for="trouve" class="form-label">Trouve</label>
            <input type="text" id="trouve" name="trouve" class="form-control" value="{{ old('trouve', $item?->trouve) }}">
            @error('trouve')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="typetri" name="typetri" value="{{ old('typetri', $item?->typetri) }}">

<input type="hidden" id="selectComp" name="selectComp" value="{{ old('selectComp', $item?->selectComp) }}">


        <div class="mb-3">
            <label for="choixStatut" class="form-label">Choixstatut</label>
            <textarea id="choixStatut" name="choixStatut" class="form-control" rows="4">{{ old('choixStatut', $item?->choixStatut) }}</textarea>
            @error('choixStatut')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="typeTri" class="form-label">Typetri</label>
            <textarea id="typeTri" name="typeTri" class="form-control" rows="4">{{ old('typeTri', $item?->typeTri) }}</textarea>
            @error('typeTri')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.search_personnel.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
