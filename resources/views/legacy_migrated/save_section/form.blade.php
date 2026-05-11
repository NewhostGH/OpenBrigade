@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: save_section.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Section Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.save_section.update', $itemKey) : route('legacy_migrated.save_section.store') }}">
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
            <label for="nom" class="form-label">Nom</label>
            <input type="text" id="nom" name="nom" class="form-control" value="{{ old('nom', $item?->nom) }}">
            @error('nom')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="code" class="form-label">Code</label>
            <input type="text" id="code" name="code" class="form-control" value="{{ old('code', $item?->code) }}">
            @error('code')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="parent" class="form-label">Parent</label>
            <input type="text" id="parent" name="parent" class="form-control" value="{{ old('parent', $item?->parent) }}">
            @error('parent')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="ordre" class="form-label">Ordre</label>
            <input type="text" id="ordre" name="ordre" class="form-control" value="{{ old('ordre', $item?->ordre) }}">
            @error('ordre')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" id="address" name="address" class="form-control" value="{{ old('address', $item?->address) }}">
            @error('address')
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
            <label for="hide" class="form-label">Hide</label>
            <input type="text" id="hide" name="hide" class="form-control" value="{{ old('hide', $item?->hide) }}">
            @error('hide')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="SHOW_PHONE3" class="form-label">Show Phone3</label>
            <input type="text" id="SHOW_PHONE3" name="SHOW_PHONE3" class="form-control" value="{{ old('SHOW_PHONE3', $item?->SHOW_PHONE3) }}">
            @error('SHOW_PHONE3')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="SHOW_EMAIL3" class="form-label">Show Email3</label>
            <input type="text" id="SHOW_EMAIL3" name="SHOW_EMAIL3" class="form-control" value="{{ old('SHOW_EMAIL3', $item?->SHOW_EMAIL3) }}">
            @error('SHOW_EMAIL3')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="SHOW_URL" class="form-label">Show Url</label>
            <input type="text" id="SHOW_URL" name="SHOW_URL" class="form-control" value="{{ old('SHOW_URL', $item?->SHOW_URL) }}">
            @error('SHOW_URL')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="inactive" class="form-label">Inactive</label>
            <input type="text" id="inactive" name="inactive" class="form-control" value="{{ old('inactive', $item?->inactive) }}">
            @error('inactive')
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
                            <a href="{{ route('legacy_migrated.save_section.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
