@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_options.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementOptions Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_options.update', $itemKey) : route('legacy_migrated.evenement_options.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="choix_texte_" class="form-label">Choix Texte </label>
            <input type="text" id="choix_texte_" name="choix_texte_" class="form-control" value="{{ old('choix_texte_', $item?->choix_texte_) }}">
            @error('choix_texte_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="newtexte" class="form-label">Newtexte</label>
            <input type="text" id="newtexte" name="newtexte" class="form-control" value="{{ old('newtexte', $item?->newtexte) }}">
            @error('newtexte')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="choix_value_" class="form-label">Choix Value </label>
            <textarea id="choix_value_" name="choix_value_" class="form-control" rows="4">{{ old('choix_value_', $item?->choix_value_) }}</textarea>
            @error('choix_value_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="newvalue" class="form-label">Newvalue</label>
            <textarea id="newvalue" name="newvalue" class="form-control" rows="4">{{ old('newvalue', $item?->newvalue) }}</textarea>
            @error('newvalue')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="evenement" class="form-label">Evenement</label>
            <input type="text" id="evenement" name="evenement" class="form-control" value="{{ old('evenement', $item?->evenement) }}">
            @error('evenement')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="option" class="form-label">Option</label>
            <input type="text" id="option" name="option" class="form-control" value="{{ old('option', $item?->option) }}">
            @error('option')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="groupe" class="form-label">Groupe</label>
            <input type="text" id="groupe" name="groupe" class="form-control" value="{{ old('groupe', $item?->groupe) }}">
            @error('groupe')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="action" class="form-label">Action</label>
            <input type="text" id="action" name="action" class="form-control" value="{{ old('action', $item?->action) }}">
            @error('action')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EO_TITLE" class="form-label">Eo Title</label>
            <input type="text" id="EO_TITLE" name="EO_TITLE" class="form-control" value="{{ old('EO_TITLE', $item?->EO_TITLE) }}">
            @error('EO_TITLE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EOG_TITLE" class="form-label">Eog Title</label>
            <input type="text" id="EOG_TITLE" name="EOG_TITLE" class="form-control" value="{{ old('EOG_TITLE', $item?->EOG_TITLE) }}">
            @error('EOG_TITLE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EO_TYPE" class="form-label">Eo Type</label>
            <input type="text" id="EO_TYPE" name="EO_TYPE" class="form-control" value="{{ old('EO_TYPE', $item?->EO_TYPE) }}">
            @error('EO_TYPE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EO_COMMENT" class="form-label">Eo Comment</label>
            <input type="text" id="EO_COMMENT" name="EO_COMMENT" class="form-control" value="{{ old('EO_COMMENT', $item?->EO_COMMENT) }}">
            @error('EO_COMMENT')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EO_ORDER" class="form-label">Eo Order</label>
            <input type="text" id="EO_ORDER" name="EO_ORDER" class="form-control" value="{{ old('EO_ORDER', $item?->EO_ORDER) }}">
            @error('EO_ORDER')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EOG_ORDER" class="form-label">Eog Order</label>
            <input type="text" id="EOG_ORDER" name="EOG_ORDER" class="form-control" value="{{ old('EOG_ORDER', $item?->EOG_ORDER) }}">
            @error('EOG_ORDER')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_options.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
