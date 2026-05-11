@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_documents.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Documents Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_documents.update', $itemKey) : route('legacy_migrated.save_documents.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="S_ID" class="form-label">S Id</label>
            <input type="text" id="S_ID" name="S_ID" class="form-control" value="{{ old('S_ID', $item?->S_ID) }}">
            @error('S_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="operation" class="form-label">Operation</label>
            <input type="text" id="operation" name="operation" class="form-control" value="{{ old('operation', $item?->operation) }}">
            @error('operation')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <input type="text" id="type" name="type" class="form-control" value="{{ old('type', $item?->type) }}">
            @error('type')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="url" class="form-label">Url</label>
            <input type="text" id="url" name="url" class="form-control" value="{{ old('url', $item?->url) }}">
            @error('url')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="security" class="form-label">Security</label>
            <input type="text" id="security" name="security" class="form-control" value="{{ old('security', $item?->security) }}">
            @error('security')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="dossier" class="form-label">Dossier</label>
            <input type="text" id="dossier" name="dossier" class="form-control" value="{{ old('dossier', $item?->dossier) }}">
            @error('dossier')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="from" class="form-label">From</label>
            <input type="text" id="from" name="from" class="form-control" value="{{ old('from', $item?->from) }}">
            @error('from')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="vehicule" class="form-label">Vehicule</label>
            <input type="text" id="vehicule" name="vehicule" class="form-control" value="{{ old('vehicule', $item?->vehicule) }}">
            @error('vehicule')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="materiel" class="form-label">Materiel</label>
            <input type="text" id="materiel" name="materiel" class="form-control" value="{{ old('materiel', $item?->materiel) }}">
            @error('materiel')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="docid" class="form-label">Docid</label>
            <input type="text" id="docid" name="docid" class="form-control" value="{{ old('docid', $item?->docid) }}">
            @error('docid')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="isfolder" class="form-label">Isfolder</label>
            <input type="text" id="isfolder" name="isfolder" class="form-control" value="{{ old('isfolder', $item?->isfolder) }}">
            @error('isfolder')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="parentfolder" class="form-label">Parentfolder</label>
            <input type="text" id="parentfolder" name="parentfolder" class="form-control" value="{{ old('parentfolder', $item?->parentfolder) }}">
            @error('parentfolder')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="foldername" class="form-label">Foldername</label>
            <input type="text" id="foldername" name="foldername" class="form-control" value="{{ old('foldername', $item?->foldername) }}">
            @error('foldername')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.save_documents.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
