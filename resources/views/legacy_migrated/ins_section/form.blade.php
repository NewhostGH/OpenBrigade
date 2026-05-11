@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: ins_section.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Section Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.ins_section.update', $itemKey) : route('legacy_migrated.ins_section.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="S_ID" name="S_ID" value="{{ old('S_ID', $item?->S_ID) }}">

<input type="hidden" id="chef" name="chef" value="{{ old('chef', $item?->chef) }}">

<input type="hidden" id="adjoint" name="adjoint" value="{{ old('adjoint', $item?->adjoint) }}">

<input type="hidden" id="cadre" name="cadre" value="{{ old('cadre', $item?->cadre) }}">

<input type="hidden" id="description" name="description" value="{{ old('description', $item?->description) }}">

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">


        <div class="mb-3">
            <label for="code" class="form-label">Code</label>
            <input type="text" id="code" name="code" class="form-control" value="{{ old('code', $item?->code) }}">
            @error('code')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" id="nom" name="nom" class="form-control" value="{{ old('nom', $item?->nom) }}">
            @error('nom')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="address_complement" class="form-label">Address Complement</label>
            <input type="text" id="address_complement" name="address_complement" class="form-control" value="{{ old('address_complement', $item?->address_complement) }}">
            @error('address_complement')
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
            <label for="phone" class="form-label">Phone</label>
            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $item?->phone) }}">
            @error('phone')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="phone2" class="form-label">Phone2</label>
            <input type="text" id="phone2" name="phone2" class="form-control" value="{{ old('phone2', $item?->phone2) }}">
            @error('phone2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="phone3" class="form-label">Phone3</label>
            <input type="text" id="phone3" name="phone3" class="form-control" value="{{ old('phone3', $item?->phone3) }}">
            @error('phone3')
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
            <label for="email2" class="form-label">Email2</label>
            <input type="text" id="email2" name="email2" class="form-control" value="{{ old('email2', $item?->email2) }}">
            @error('email2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="email3" class="form-label">Email3</label>
            <input type="text" id="email3" name="email3" class="form-control" value="{{ old('email3', $item?->email3) }}">
            @error('email3')
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
            <label for="siret" class="form-label">Siret</label>
            <input type="text" id="siret" name="siret" class="form-control" value="{{ old('siret', $item?->siret) }}">
            @error('siret')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.ins_section.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
