@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: diplome_edit.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">DiplomeEdit Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.diplome_edit.update', $itemKey) : route('legacy_migrated.diplome_edit.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="annexe[" class="form-label">Annexe[</label>
            <input type="text" id="annexe[" name="annexe[" class="form-control" value="{{ old('annexe[', $item?->annexe[) }}">
            @error('annexe[')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="userfile" class="form-label">Userfile</label>
            <input type="text" id="userfile" name="userfile" class="form-control" value="{{ old('userfile', $item?->userfile) }}">
            @error('userfile')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="actif[" name="actif[" value="{{ old('actif[', $item?->actif[) }}">

<input type="hidden" id="affichage[" name="affichage[" value="{{ old('affichage[', $item?->affichage[) }}">

<input type="hidden" id="action" name="action" value="{{ old('action', $item?->action) }}">

<input type="hidden" id="psid" name="psid" value="{{ old('psid', $item?->psid) }}">


        <div class="mb-3">
            <label for="filter" class="form-label">Filter</label>
            <textarea id="filter" name="filter" class="form-control" rows="4">{{ old('filter', $item?->filter) }}</textarea>
            @error('filter')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="selectdiplome" class="form-label">Selectdiplome</label>
            <textarea id="selectdiplome" name="selectdiplome" class="form-control" rows="4">{{ old('selectdiplome', $item?->selectdiplome) }}</textarea>
            @error('selectdiplome')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="aff_taille[" class="form-label">Aff Taille[</label>
            <textarea id="aff_taille[" name="aff_taille[" class="form-control" rows="4">{{ old('aff_taille[', $item?->aff_taille[) }}</textarea>
            @error('aff_taille[')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="aff_style[" class="form-label">Aff Style[</label>
            <textarea id="aff_style[" name="aff_style[" class="form-control" rows="4">{{ old('aff_style[', $item?->aff_style[) }}</textarea>
            @error('aff_style[')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="aff_police[" class="form-label">Aff Police[</label>
            <textarea id="aff_police[" name="aff_police[" class="form-control" rows="4">{{ old('aff_police[', $item?->aff_police[) }}</textarea>
            @error('aff_police[')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.diplome_edit.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
