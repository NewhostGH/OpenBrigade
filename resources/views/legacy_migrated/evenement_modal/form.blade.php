@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_modal.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Evenement Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_modal.update', $itemKey) : route('legacy_migrated.evenement_modal.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="s" class="form-label">S</label>
            <input type="text" id="s" name="s" class="form-control" value="{{ old('s', $item?->s) }}">
            @error('s')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="evenement" name="evenement" value="{{ old('evenement', $item?->evenement) }}">


        <div class="mb-3">
            <label for="fn" class="form-label">Fn</label>
            <textarea id="fn" name="fn" class="form-control" rows="4">{{ old('fn', $item?->fn) }}</textarea>
            @error('fn')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="vfn" class="form-label">Vfn</label>
            <textarea id="vfn" name="vfn" class="form-control" rows="4">{{ old('vfn', $item?->vfn) }}</textarea>
            @error('vfn')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="pe" class="form-label">Pe</label>
            <textarea id="pe" name="pe" class="form-control" rows="4">{{ old('pe', $item?->pe) }}</textarea>
            @error('pe')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_modal.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
