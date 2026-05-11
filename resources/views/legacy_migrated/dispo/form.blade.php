@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: dispo.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Dispo Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.dispo.update', $itemKey) : route('legacy_migrated.dispo.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="ouvrir" class="form-label">Ouvrir</label>
            <input type="text" id="ouvrir" name="ouvrir" class="form-control" value="{{ old('ouvrir', $item?->ouvrir) }}">
            @error('ouvrir')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="fermer" class="form-label">Fermer</label>
            <input type="text" id="fermer" name="fermer" class="form-control" value="{{ old('fermer', $item?->fermer) }}">
            @error('fermer')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="CheckAll" class="form-label">Checkall</label>
            <input type="text" id="CheckAll" name="CheckAll" class="form-control" value="{{ old('CheckAll', $item?->CheckAll) }}">
            @error('CheckAll')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="nbjours" name="nbjours" value="{{ old('nbjours', $item?->nbjours) }}">

<input type="hidden" id="person" name="person" value="{{ old('person', $item?->person) }}">

<input type="hidden" id="month" name="month" value="{{ old('month', $item?->month) }}">

<input type="hidden" id="year" name="year" value="{{ old('year', $item?->year) }}">


        <div class="mb-3">
            <label for="1_" class="form-label">1 </label>
            <input type="text" id="1_" name="1_" class="form-control" value="{{ old('1_', $item?->1_) }}">
            @error('1_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="4_" class="form-label">4 </label>
            <input type="text" id="4_" name="4_" class="form-control" value="{{ old('4_', $item?->4_) }}">
            @error('4_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="2_" class="form-label">2 </label>
            <input type="text" id="2_" name="2_" class="form-control" value="{{ old('2_', $item?->2_) }}">
            @error('2_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="3_" class="form-label">3 </label>
            <input type="text" id="3_" name="3_" class="form-control" value="{{ old('3_', $item?->3_) }}">
            @error('3_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="msg" class="form-label">Msg</label>
            <textarea id="msg" name="msg" class="form-control" rows="4">{{ old('msg', $item?->msg) }}</textarea>
            @error('msg')
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


        <div class="mb-3">
            <label for="menu1" class="form-label">Menu1</label>
            <textarea id="menu1" name="menu1" class="form-control" rows="4">{{ old('menu1', $item?->menu1) }}</textarea>
            @error('menu1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="filtre" class="form-label">Filtre</label>
            <textarea id="filtre" name="filtre" class="form-control" rows="4">{{ old('filtre', $item?->filtre) }}</textarea>
            @error('filtre')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="menu4" class="form-label">Menu4</label>
            <textarea id="menu4" name="menu4" class="form-control" rows="4">{{ old('menu4', $item?->menu4) }}</textarea>
            @error('menu4')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="menu5" class="form-label">Menu5</label>
            <textarea id="menu5" name="menu5" class="form-control" rows="4">{{ old('menu5', $item?->menu5) }}</textarea>
            @error('menu5')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="menu3" class="form-label">Menu3</label>
            <textarea id="menu3" name="menu3" class="form-control" rows="4">{{ old('menu3', $item?->menu3) }}</textarea>
            @error('menu3')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="section" class="form-label">Section</label>
            <textarea id="section" name="section" class="form-control" rows="4">{{ old('section', $item?->section) }}</textarea>
            @error('section')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.dispo.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
