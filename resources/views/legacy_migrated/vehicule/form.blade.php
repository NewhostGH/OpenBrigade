@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: vehicule.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Vehicule Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.vehicule.update', $itemKey) : route('legacy_migrated.vehicule.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="sub" class="form-label">Sub</label>
            <input type="text" id="sub" name="sub" class="form-control" value="{{ old('sub', $item?->sub) }}">
            @error('sub')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="mad" class="form-label">Mad</label>
            <input type="text" id="mad" name="mad" class="form-control" value="{{ old('mad', $item?->mad) }}">
            @error('mad')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


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
            <label for="filter2" class="form-label">Filter2</label>
            <textarea id="filter2" name="filter2" class="form-control" rows="4">{{ old('filter2', $item?->filter2) }}</textarea>
            @error('filter2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="vehicule" class="form-label">Vehicule</label>
            <textarea id="vehicule" name="vehicule" class="form-control" rows="4">{{ old('vehicule', $item?->vehicule) }}</textarea>
            @error('vehicule')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.vehicule.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
