@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: gps_save2.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Gps2 Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.gps_save2.update', $itemKey) : route('legacy_migrated.gps_save2.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


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

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.gps_save2.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
