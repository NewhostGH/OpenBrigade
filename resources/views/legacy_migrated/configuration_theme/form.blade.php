@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: configuration_theme.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">ConfigurationTheme Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.configuration_theme.update', $itemKey) : route('legacy_migrated.configuration_theme.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="image" name="image" value="{{ old('image', $item?->image) }}">

<input type="hidden" id="action" name="action" value="{{ old('action', $item?->action) }}">


        <div class="mb-3">
            <label for="upload" class="form-label">Upload</label>
            <input type="text" id="upload" name="upload" class="form-control" value="{{ old('upload', $item?->upload) }}">
            @error('upload')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.configuration_theme.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
