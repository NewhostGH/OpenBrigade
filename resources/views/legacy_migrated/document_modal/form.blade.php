@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: document_modal.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Document Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.document_modal.update', $itemKey) : route('legacy_migrated.document_modal.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">

<input type="hidden" id="P_ID" name="P_ID" value="{{ old('P_ID', $item?->P_ID) }}">

<input type="hidden" id="doc" name="doc" value="{{ old('doc', $item?->doc) }}">

<input type="hidden" id="action" name="action" value="{{ old('action', $item?->action) }}">

<input type="hidden" id="evenement" name="evenement" value="{{ old('evenement', $item?->evenement) }}">

<input type="hidden" id="S_ID" name="S_ID" value="{{ old('S_ID', $item?->S_ID) }}">

<input type="hidden" id="filter" name="filter" value="{{ old('filter', $item?->filter) }}">

<input type="hidden" id="docid" name="docid" value="{{ old('docid', $item?->docid) }}">

<input type="hidden" id="isfolder" name="isfolder" value="{{ old('isfolder', $item?->isfolder) }}">


        <div class="mb-3">
            <label for="foldername" class="form-label">Foldername</label>
            <input type="text" id="foldername" name="foldername" class="form-control" value="{{ old('foldername', $item?->foldername) }}">
            @error('foldername')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="type" name="type" value="{{ old('type', $item?->type) }}">


        <div class="mb-3">
            <label for="security" class="form-label">Security</label>
            <textarea id="security" name="security" class="form-control" rows="4">{{ old('security', $item?->security) }}</textarea>
            @error('security')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="parentfolder" class="form-label">Parentfolder</label>
            <textarea id="parentfolder" name="parentfolder" class="form-control" rows="4">{{ old('parentfolder', $item?->parentfolder) }}</textarea>
            @error('parentfolder')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.document_modal.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
