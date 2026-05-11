@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: download_package.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">DownloadPackage Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.download_package.update', $itemKey) : route('legacy_migrated.download_package.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="package" class="form-label">Package</label>
            <input type="text" id="package" name="package" class="form-control" value="{{ old('package', $item?->package) }}">
            @error('package')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="md5sum" class="form-label">Md5Sum</label>
            <input type="text" id="md5sum" name="md5sum" class="form-control" value="{{ old('md5sum', $item?->md5sum) }}">
            @error('md5sum')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="reason" class="form-label">Reason</label>
            <input type="text" id="reason" name="reason" class="form-control" value="{{ old('reason', $item?->reason) }}">
            @error('reason')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.download_package.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
