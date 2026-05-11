@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: alerte_create.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">AlerteCreate Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.alerte_create.update', $itemKey) : route('legacy_migrated.alerte_create.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="menu1" name="menu1" value="{{ old('menu1', $item?->menu1) }}">

<input type="hidden" id="menu3" name="menu3" value="{{ old('menu3', $item?->menu3) }}">


        <div class="mb-3">
            <label for="comptage" class="form-label">Comptage</label>
            <input type="text" id="comptage" name="comptage" class="form-control" value="{{ old('comptage', $item?->comptage) }}">
            @error('comptage')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="maxchar" class="form-label">Maxchar</label>
            <input type="text" id="maxchar" name="maxchar" class="form-control" value="{{ old('maxchar', $item?->maxchar) }}">
            @error('maxchar')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="mode" class="form-label">Mode</label>
            <input type="text" id="mode" name="mode" class="form-control" value="{{ old('mode', $item?->mode) }}">
            @error('mode')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="mymessage" class="form-label">Mymessage</label>
            <textarea id="mymessage" name="mymessage" class="form-control" rows="4">{{ old('mymessage', $item?->mymessage) }}</textarea>
            @error('mymessage')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="menu2" class="form-label">Menu2</label>
            <textarea id="menu2" name="menu2" class="form-control" rows="4">{{ old('menu2', $item?->menu2) }}</textarea>
            @error('menu2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.alerte_create.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
