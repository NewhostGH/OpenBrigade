@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_detail.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementDetail Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_detail.update', $itemKey) : route('legacy_migrated.evenement_detail.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="sub" class="form-label">Sub</label>
            <input type="text" id="sub" name="sub" class="form-control" value="{{ old('sub', $item?->sub) }}">
            @error('sub')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="autorefresh" class="form-label">Autorefresh</label>
            <input type="text" id="autorefresh" name="autorefresh" class="form-control" value="{{ old('autorefresh', $item?->autorefresh) }}">
            @error('autorefresh')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="sectioninscription" class="form-label">Sectioninscription</label>
            <textarea id="sectioninscription" name="sectioninscription" class="form-control" rows="4">{{ old('sectioninscription', $item?->sectioninscription) }}</textarea>
            @error('sectioninscription')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="company" class="form-label">Company</label>
            <textarea id="company" name="company" class="form-control" rows="4">{{ old('company', $item?->company) }}</textarea>
            @error('company')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="add" class="form-label">Add</label>
            <textarea id="add" name="add" class="form-control" rows="4">{{ old('add', $item?->add) }}</textarea>
            @error('add')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="newchef" class="form-label">Newchef</label>
            <textarea id="newchef" name="newchef" class="form-control" rows="4">{{ old('newchef', $item?->newchef) }}</textarea>
            @error('newchef')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="addvehicule" class="form-label">Addvehicule</label>
            <textarea id="addvehicule" name="addvehicule" class="form-control" rows="4">{{ old('addvehicule', $item?->addvehicule) }}</textarea>
            @error('addvehicule')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <textarea id="type" name="type" class="form-control" rows="4">{{ old('type', $item?->type) }}</textarea>
            @error('type')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="addmateriel" class="form-label">Addmateriel</label>
            <textarea id="addmateriel" name="addmateriel" class="form-control" rows="4">{{ old('addmateriel', $item?->addmateriel) }}</textarea>
            @error('addmateriel')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="addconso" class="form-label">Addconso</label>
            <textarea id="addconso" name="addconso" class="form-control" rows="4">{{ old('addconso', $item?->addconso) }}</textarea>
            @error('addconso')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_detail.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
