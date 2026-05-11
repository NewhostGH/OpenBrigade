@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: message.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Message Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.message.update', $itemKey) : route('legacy_migrated.message.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="catmessage" name="catmessage" value="{{ old('catmessage', $item?->catmessage) }}">


        <div class="mb-3">
            <label for="mail" class="form-label">Mail</label>
            <input type="text" id="mail" name="mail" class="form-control" value="{{ old('mail', $item?->mail) }}">
            @error('mail')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="section" name="section" value="{{ old('section', $item?->section) }}">


        <div class="mb-3">
            <label for="objet" class="form-label">Objet</label>
            <input type="text" id="objet" name="objet" class="form-control" value="{{ old('objet', $item?->objet) }}">
            @error('objet')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="userfile[]" class="form-label">Userfile[]</label>
            <input type="text" id="userfile[]" name="userfile[]" class="form-control" value="{{ old('userfile[]', $item?->userfile[]) }}">
            @error('userfile[]')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="annuler" class="form-label">Annuler</label>
            <input type="text" id="annuler" name="annuler" class="form-control" value="{{ old('annuler', $item?->annuler) }}">
            @error('annuler')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="tab" name="tab" value="{{ old('tab', $item?->tab) }}">

<input type="hidden" id="filter" name="filter" value="{{ old('filter', $item?->filter) }}">


        <div class="mb-3">
            <label for="search" class="form-label">Search</label>
            <input type="text" id="search" name="search" class="form-control" value="{{ old('search', $item?->search) }}">
            @error('search')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea id="message" name="message" class="form-control" rows="4">{{ old('message', $item?->message) }}</textarea>
            @error('message')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TM_ID" class="form-label">Tm Id</label>
            <textarea id="TM_ID" name="TM_ID" class="form-control" rows="4">{{ old('TM_ID', $item?->TM_ID) }}</textarea>
            @error('TM_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="duree" class="form-label">Duree</label>
            <textarea id="duree" name="duree" class="form-control" rows="4">{{ old('duree', $item?->duree) }}</textarea>
            @error('duree')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.message.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
