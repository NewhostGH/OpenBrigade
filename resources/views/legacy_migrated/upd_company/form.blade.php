@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_company.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Company Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_company.update', $itemKey) : route('legacy_migrated.upd_company.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">

<input type="hidden" id="C_ID" name="C_ID" value="{{ old('C_ID', $item?->C_ID) }}">

<input type="hidden" id="from" name="from" value="{{ old('from', $item?->from) }}">

<input type="hidden" id="evenement" name="evenement" value="{{ old('evenement', $item?->evenement) }}">


        <div class="mb-3">
            <label for="C_NAME" class="form-label">C Name</label>
            <input type="text" id="C_NAME" name="C_NAME" class="form-control" value="{{ old('C_NAME', $item?->C_NAME) }}">
            @error('C_NAME')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="groupe" name="groupe" value="{{ old('groupe', $item?->groupe) }}">


        <div class="mb-3">
            <label for="C_DESCRIPTION" class="form-label">C Description</label>
            <input type="text" id="C_DESCRIPTION" name="C_DESCRIPTION" class="form-control" value="{{ old('C_DESCRIPTION', $item?->C_DESCRIPTION) }}">
            @error('C_DESCRIPTION')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="C_SIRET" class="form-label">C Siret</label>
            <input type="text" id="C_SIRET" name="C_SIRET" class="form-control" value="{{ old('C_SIRET', $item?->C_SIRET) }}">
            @error('C_SIRET')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="zipcode" class="form-label">Zipcode</label>
            <input type="text" id="zipcode" name="zipcode" class="form-control" value="{{ old('zipcode', $item?->zipcode) }}">
            @error('zipcode')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="city" class="form-label">City</label>
            <input type="text" id="city" name="city" class="form-control" value="{{ old('city', $item?->city) }}">
            @error('city')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="relation_nom" class="form-label">Relation Nom</label>
            <input type="text" id="relation_nom" name="relation_nom" class="form-control" value="{{ old('relation_nom', $item?->relation_nom) }}">
            @error('relation_nom')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $item?->phone) }}">
            @error('phone')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="fax" class="form-label">Fax</label>
            <input type="text" id="fax" name="fax" class="form-control" value="{{ old('fax', $item?->fax) }}">
            @error('fax')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="text" id="email" name="email" class="form-control" value="{{ old('email', $item?->email) }}">
            @error('email')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="Annuler" class="form-label">Annuler</label>
            <input type="text" id="Annuler" name="Annuler" class="form-control" value="{{ old('Annuler', $item?->Annuler) }}">
            @error('Annuler')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea id="address" name="address" class="form-control" rows="4">{{ old('address', $item?->address) }}</textarea>
            @error('address')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TC_CODE" class="form-label">Tc Code</label>
            <textarea id="TC_CODE" name="TC_CODE" class="form-control" rows="4">{{ old('TC_CODE', $item?->TC_CODE) }}</textarea>
            @error('TC_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="parent" class="form-label">Parent</label>
            <textarea id="parent" name="parent" class="form-control" rows="4">{{ old('parent', $item?->parent) }}</textarea>
            @error('parent')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_company.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
