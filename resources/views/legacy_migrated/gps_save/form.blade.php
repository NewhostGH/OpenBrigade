@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: gps_save.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Gps Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.gps_save.update', $itemKey) : route('legacy_migrated.gps_save.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="pid" class="form-label">Pid</label>
            <input type="text" id="pid" name="pid" class="form-control" value="{{ old('pid', $item?->pid) }}">
            @error('pid')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="lat" class="form-label">Lat</label>
            <input type="text" id="lat" name="lat" class="form-control" value="{{ old('lat', $item?->lat) }}">
            @error('lat')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="lng" class="form-label">Lng</label>
            <input type="text" id="lng" name="lng" class="form-control" value="{{ old('lng', $item?->lng) }}">
            @error('lng')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="findAddress" class="form-label">Findaddress</label>
            <input type="text" id="findAddress" name="findAddress" class="form-control" value="{{ old('findAddress', $item?->findAddress) }}">
            @error('findAddress')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="GPSAddress" class="form-label">Gpsaddress</label>
            <input type="text" id="GPSAddress" name="GPSAddress" class="form-control" value="{{ old('GPSAddress', $item?->GPSAddress) }}">
            @error('GPSAddress')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.gps_save.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
