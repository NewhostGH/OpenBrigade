@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_document.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Document Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_document.update', $itemKey) : route('legacy_migrated.upd_document.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">

<input type="hidden" id="S_ID" name="S_ID" value="{{ old('S_ID', $item?->S_ID) }}">

<input type="hidden" id="P_ID" name="P_ID" value="{{ old('P_ID', $item?->P_ID) }}">

<input type="hidden" id="status" name="status" value="{{ old('status', $item?->status) }}">

<input type="hidden" id="section" name="section" value="{{ old('section', $item?->section) }}">

<input type="hidden" id="victime" name="victime" value="{{ old('victime', $item?->victime) }}">

<input type="hidden" id="evenement" name="evenement" value="{{ old('evenement', $item?->evenement) }}">

<input type="hidden" id="numinter" name="numinter" value="{{ old('numinter', $item?->numinter) }}">

<input type="hidden" id="action" name="action" value="{{ old('action', $item?->action) }}">

<input type="hidden" id="modeinter" name="modeinter" value="{{ old('modeinter', $item?->modeinter) }}">

<input type="hidden" id="vehicule" name="vehicule" value="{{ old('vehicule', $item?->vehicule) }}">

<input type="hidden" id="materiel" name="materiel" value="{{ old('materiel', $item?->materiel) }}">

<input type="hidden" id="person" name="person" value="{{ old('person', $item?->person) }}">

<input type="hidden" id="nfid" name="nfid" value="{{ old('nfid', $item?->nfid) }}">

<input type="hidden" id="dossier" name="dossier" value="{{ old('dossier', $item?->dossier) }}">

<input type="hidden" id="type" name="type" value="{{ old('type', $item?->type) }}">

<input type="hidden" id="security" name="security" value="{{ old('security', $item?->security) }}">

<input type="hidden" id="from" name="from" value="{{ old('from', $item?->from) }}">


        <div class="mb-3">
            <label for="userfile[]" class="form-label">Userfile[]</label>
            <input type="text" id="userfile[]" name="userfile[]" class="form-control" value="{{ old('userfile[]', $item?->userfile[]) }}">
            @error('userfile[]')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_document.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
