@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: zipcode.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Zipcode Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.zipcode.update', $itemKey) : route('legacy_migrated.zipcode.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="maxRows" class="form-label">Maxrows</label>
            <input type="text" id="maxRows" name="maxRows" class="form-control" value="{{ old('maxRows', $item?->maxRows) }}">
            @error('maxRows')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="ZipCode" class="form-label">Zipcode</label>
            <input type="text" id="ZipCode" name="ZipCode" class="form-control" value="{{ old('ZipCode', $item?->ZipCode) }}">
            @error('ZipCode')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="City" class="form-label">City</label>
            <input type="text" id="City" name="City" class="form-control" value="{{ old('City', $item?->City) }}">
            @error('City')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.zipcode.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
