@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_materiel_selector.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">MaterielSelector Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_materiel_selector.update', $itemKey) : route('legacy_migrated.upd_materiel_selector.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="TV_ID" class="form-label">Tv Id</label>
            <textarea id="TV_ID" name="TV_ID" class="form-control" rows="4">{{ old('TV_ID', $item?->TV_ID) }}</textarea>
            @error('TV_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_materiel_selector.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
