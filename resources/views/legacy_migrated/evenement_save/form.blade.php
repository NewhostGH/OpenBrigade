@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_save.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Evenement Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_save.update', $itemKey) : route('legacy_migrated.evenement_save.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="action" class="form-label">Action</label>
            <input type="text" id="action" name="action" class="form-control" value="{{ old('action', $item?->action) }}">
            @error('action')
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
            <label for="copycheffrom" class="form-label">Copycheffrom</label>
            <input type="text" id="copycheffrom" name="copycheffrom" class="form-control" value="{{ old('copycheffrom', $item?->copycheffrom) }}">
            @error('copycheffrom')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="copydetailsfrom" class="form-label">Copydetailsfrom</label>
            <input type="text" id="copydetailsfrom" name="copydetailsfrom" class="form-control" value="{{ old('copydetailsfrom', $item?->copydetailsfrom) }}">
            @error('copydetailsfrom')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="copymode" class="form-label">Copymode</label>
            <input type="text" id="copymode" name="copymode" class="form-control" value="{{ old('copymode', $item?->copymode) }}">
            @error('copymode')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="closed" class="form-label">Closed</label>
            <input type="text" id="closed" name="closed" class="form-control" value="{{ old('closed', $item?->closed) }}">
            @error('closed')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="open_to_ext" class="form-label">Open To Ext</label>
            <input type="text" id="open_to_ext" name="open_to_ext" class="form-control" value="{{ old('open_to_ext', $item?->open_to_ext) }}">
            @error('open_to_ext')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="allow_reinforcement" class="form-label">Allow Reinforcement</label>
            <input type="text" id="allow_reinforcement" name="allow_reinforcement" class="form-control" value="{{ old('allow_reinforcement', $item?->allow_reinforcement) }}">
            @error('allow_reinforcement')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="section" class="form-label">Section</label>
            <input type="text" id="section" name="section" class="form-control" value="{{ old('section', $item?->section) }}">
            @error('section')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="nb_vpsp" class="form-label">Nb Vpsp</label>
            <input type="text" id="nb_vpsp" name="nb_vpsp" class="form-control" value="{{ old('nb_vpsp', $item?->nb_vpsp) }}">
            @error('nb_vpsp')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="nb_autres_vehicules" class="form-label">Nb Autres Vehicules</label>
            <input type="text" id="nb_autres_vehicules" name="nb_autres_vehicules" class="form-control" value="{{ old('nb_autres_vehicules', $item?->nb_autres_vehicules) }}">
            @error('nb_autres_vehicules')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="canceled" class="form-label">Canceled</label>
            <input type="text" id="canceled" name="canceled" class="form-control" value="{{ old('canceled', $item?->canceled) }}">
            @error('canceled')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="flag1" class="form-label">Flag1</label>
            <input type="text" id="flag1" name="flag1" class="form-control" value="{{ old('flag1', $item?->flag1) }}">
            @error('flag1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="colonne" class="form-label">Colonne</label>
            <input type="text" id="colonne" name="colonne" class="form-control" value="{{ old('colonne', $item?->colonne) }}">
            @error('colonne')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="visible_outside" class="form-label">Visible Outside</label>
            <input type="text" id="visible_outside" name="visible_outside" class="form-control" value="{{ old('visible_outside', $item?->visible_outside) }}">
            @error('visible_outside')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="mail1" class="form-label">Mail1</label>
            <input type="text" id="mail1" name="mail1" class="form-control" value="{{ old('mail1', $item?->mail1) }}">
            @error('mail1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="mail2" class="form-label">Mail2</label>
            <input type="text" id="mail2" name="mail2" class="form-control" value="{{ old('mail2', $item?->mail2) }}">
            @error('mail2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="mail3" class="form-label">Mail3</label>
            <input type="text" id="mail3" name="mail3" class="form-control" value="{{ old('mail3', $item?->mail3) }}">
            @error('mail3')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="company" class="form-label">Company</label>
            <input type="text" id="company" name="company" class="form-control" value="{{ old('company', $item?->company) }}">
            @error('company')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="contact_name" class="form-label">Contact Name</label>
            <input type="text" id="contact_name" name="contact_name" class="form-control" value="{{ old('contact_name', $item?->contact_name) }}">
            @error('contact_name')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_save.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
