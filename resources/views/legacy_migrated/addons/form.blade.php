@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: addons.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Addons Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.addons.update', $itemKey) : route('legacy_migrated.addons.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="tab" name="tab" value="{{ old('tab', $item?->tab) }}">

<input type="hidden" id="f$ID" name="f$ID" value="{{ old('f$ID', $item?->f$ID) }}">


        <div class="mb-3">
            <label for="f57" class="form-label">F57</label>
            <input type="text" id="f57" name="f57" class="form-control" value="{{ old('f57', $item?->f57) }}">
            @error('f57')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="f60" class="form-label">F60</label>
            <textarea id="f60" name="f60" class="form-control" rows="4">{{ old('f60', $item?->f60) }}</textarea>
            @error('f60')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="gps_provider" class="form-label">Gps Provider</label>
            <input type="text" id="gps_provider" name="gps_provider" class="form-control" value="{{ old('gps_provider', $item?->gps_provider) }}">
            @error('gps_provider')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="api_key" class="form-label">Api Key</label>
            <input type="text" id="api_key" name="api_key" class="form-control" value="{{ old('api_key', $item?->api_key) }}">
            @error('api_key')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.addons.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
