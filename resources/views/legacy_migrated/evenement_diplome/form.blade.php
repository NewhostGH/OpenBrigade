@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_diplome.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementDiplome Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_diplome.update', $itemKey) : route('legacy_migrated.evenement_diplome.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="evenement" class="form-label">Evenement</label>
            <input type="text" id="evenement" name="evenement" class="form-control" value="{{ old('evenement', $item?->evenement) }}">
            @error('evenement')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="expiration" class="form-label">Expiration</label>
            <input type="text" id="expiration" name="expiration" class="form-control" value="{{ old('expiration', $item?->expiration) }}">
            @error('expiration')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="comment" class="form-label">Comment</label>
            <input type="text" id="comment" name="comment" class="form-control" value="{{ old('comment', $item?->comment) }}">
            @error('comment')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="update_hierarchy" class="form-label">Update Hierarchy</label>
            <input type="text" id="update_hierarchy" name="update_hierarchy" class="form-control" value="{{ old('update_hierarchy', $item?->update_hierarchy) }}">
            @error('update_hierarchy')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_diplome.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
