@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: configuration.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Configuration Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.configuration.update', $itemKey) : route('legacy_migrated.configuration.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="f$ID" class="form-label">F$Id</label>
            <input type="text" id="f$ID" name="f$ID" class="form-control" value="{{ old('f$ID', $item?->f$ID) }}">
            @error('f$ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="tab" name="tab" value="{{ old('tab', $item?->tab) }}">


        <div class="mb-3">
            <label for="f76" class="form-label">F76</label>
            <textarea id="f76" name="f76" class="form-control" rows="4">{{ old('f76', $item?->f76) }}</textarea>
            @error('f76')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="f96" class="form-label">F96</label>
            <textarea id="f96" name="f96" class="form-control" rows="4">{{ old('f96', $item?->f96) }}</textarea>
            @error('f96')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="f97" class="form-label">F97</label>
            <textarea id="f97" name="f97" class="form-control" rows="4">{{ old('f97', $item?->f97) }}</textarea>
            @error('f97')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="f101" class="form-label">F101</label>
            <textarea id="f101" name="f101" class="form-control" rows="4">{{ old('f101', $item?->f101) }}</textarea>
            @error('f101')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.configuration.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
