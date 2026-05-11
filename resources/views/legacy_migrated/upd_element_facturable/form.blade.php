@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_element_facturable.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">ElementFacturable Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_element_facturable.update', $itemKey) : route('legacy_migrated.upd_element_facturable.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="EF_ID" name="EF_ID" value="{{ old('EF_ID', $item?->EF_ID) }}">

<input type="hidden" id="operation" name="operation" value="{{ old('operation', $item?->operation) }}">


        <div class="mb-3">
            <label for="EF_NAME" class="form-label">Ef Name</label>
            <input type="text" id="EF_NAME" name="EF_NAME" class="form-control" value="{{ old('EF_NAME', $item?->EF_NAME) }}">
            @error('EF_NAME')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="EF_PRICE" class="form-label">Ef Price</label>
            <input type="text" id="EF_PRICE" name="EF_PRICE" class="form-control" value="{{ old('EF_PRICE', $item?->EF_PRICE) }}">
            @error('EF_PRICE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="Dupliquer" class="form-label">Dupliquer</label>
            <input type="text" id="Dupliquer" name="Dupliquer" class="form-control" value="{{ old('Dupliquer', $item?->Dupliquer) }}">
            @error('Dupliquer')
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


        <div class="mb-3">
            <label for="S_ID" class="form-label">S Id</label>
            <textarea id="S_ID" name="S_ID" class="form-control" rows="4">{{ old('S_ID', $item?->S_ID) }}</textarea>
            @error('S_ID')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="TEF_CODE" class="form-label">Tef Code</label>
            <textarea id="TEF_CODE" name="TEF_CODE" class="form-control" rows="4">{{ old('TEF_CODE', $item?->TEF_CODE) }}</textarea>
            @error('TEF_CODE')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_element_facturable.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
