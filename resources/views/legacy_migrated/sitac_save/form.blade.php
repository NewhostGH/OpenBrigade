@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: sitac_save.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Sitac Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.sitac_save.update', $itemKey) : route('legacy_migrated.sitac_save.store') }}">
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
            <label for="centerlat" class="form-label">Centerlat</label>
            <input type="text" id="centerlat" name="centerlat" class="form-control" value="{{ old('centerlat', $item?->centerlat) }}">
            @error('centerlat')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="centerlng" class="form-label">Centerlng</label>
            <input type="text" id="centerlng" name="centerlng" class="form-control" value="{{ old('centerlng', $item?->centerlng) }}">
            @error('centerlng')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="zoomlevel" class="form-label">Zoomlevel</label>
            <input type="text" id="zoomlevel" name="zoomlevel" class="form-control" value="{{ old('zoomlevel', $item?->zoomlevel) }}">
            @error('zoomlevel')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="maptypeid" class="form-label">Maptypeid</label>
            <input type="text" id="maptypeid" name="maptypeid" class="form-control" value="{{ old('maptypeid', $item?->maptypeid) }}">
            @error('maptypeid')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="custom" class="form-label">Custom</label>
            <input type="text" id="custom" name="custom" class="form-control" value="{{ old('custom', $item?->custom) }}">
            @error('custom')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="latitude" class="form-label">Latitude</label>
            <input type="text" id="latitude" name="latitude" class="form-control" value="{{ old('latitude', $item?->latitude) }}">
            @error('latitude')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="longitude" class="form-label">Longitude</label>
            <input type="text" id="longitude" name="longitude" class="form-control" value="{{ old('longitude', $item?->longitude) }}">
            @error('longitude')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="flag" class="form-label">Flag</label>
            <input type="text" id="flag" name="flag" class="form-control" value="{{ old('flag', $item?->flag) }}">
            @error('flag')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="equipe" class="form-label">Equipe</label>
            <input type="text" id="equipe" name="equipe" class="form-control" value="{{ old('equipe', $item?->equipe) }}">
            @error('equipe')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" id="address" name="address" class="form-control" value="{{ old('address', $item?->address) }}">
            @error('address')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <input type="text" id="status" name="status" class="form-control" value="{{ old('status', $item?->status) }}">
            @error('status')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="intervention" class="form-label">Intervention</label>
            <input type="text" id="intervention" name="intervention" class="form-control" value="{{ old('intervention', $item?->intervention) }}">
            @error('intervention')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="cav" class="form-label">Cav</label>
            <input type="text" id="cav" name="cav" class="form-control" value="{{ old('cav', $item?->cav) }}">
            @error('cav')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.sitac_save.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
