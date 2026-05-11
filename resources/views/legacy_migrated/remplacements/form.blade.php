@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: remplacements.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Remplacements Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.remplacements.update', $itemKey) : route('legacy_migrated.remplacements.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="dtdb" class="form-label">Dtdb</label>
            <input type="text" id="dtdb" name="dtdb" class="form-control" value="{{ old('dtdb', $item?->dtdb) }}">
            @error('dtdb')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="dtfn" class="form-label">Dtfn</label>
            <input type="text" id="dtfn" name="dtfn" class="form-control" value="{{ old('dtfn', $item?->dtfn) }}">
            @error('dtfn')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="filter" class="form-label">Filter</label>
            <textarea id="filter" name="filter" class="form-control" rows="4">{{ old('filter', $item?->filter) }}</textarea>
            @error('filter')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="substitute" class="form-label">Substitute</label>
            <textarea id="substitute" name="substitute" class="form-control" rows="4">{{ old('substitute', $item?->substitute) }}</textarea>
            @error('substitute')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="replaced" class="form-label">Replaced</label>
            <textarea id="replaced" name="replaced" class="form-control" rows="4">{{ old('replaced', $item?->replaced) }}</textarea>
            @error('replaced')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <textarea id="status" name="status" class="form-control" rows="4">{{ old('status', $item?->status) }}</textarea>
            @error('status')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.remplacements.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
