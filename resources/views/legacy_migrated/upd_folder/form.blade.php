@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_folder.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Folder Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_folder.update', $itemKey) : route('legacy_migrated.upd_folder.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">

<input type="hidden" id="S_ID" name="S_ID" value="{{ old('S_ID', $item?->S_ID) }}">

<input type="hidden" id="dossier_parent" name="dossier_parent" value="{{ old('dossier_parent', $item?->dossier_parent) }}">

<input type="hidden" id="type" name="type" value="{{ old('type', $item?->type) }}">


        <div class="mb-3">
            <label for="folder" class="form-label">Folder</label>
            <input type="text" id="folder" name="folder" class="form-control" value="{{ old('folder', $item?->folder) }}">
            @error('folder')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_folder.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
