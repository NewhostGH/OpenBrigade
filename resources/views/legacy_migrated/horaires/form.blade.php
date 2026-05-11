@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: horaires.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Horaires Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.horaires.update', $itemKey) : route('legacy_migrated.horaires.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="person" name="person" value="{{ old('person', $item?->person) }}">

<input type="hidden" id="week" name="week" value="{{ old('week', $item?->week) }}">

<input type="hidden" id="year" name="year" value="{{ old('year', $item?->year) }}">

<input type="hidden" id="from" name="from" value="{{ old('from', $item?->from) }}">


        <div class="mb-3">
            <label for="debut1" class="form-label">Debut1</label>
            <input type="text" id="debut1" name="debut1" class="form-control" value="{{ old('debut1', $item?->debut1) }}">
            @error('debut1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="fin1" class="form-label">Fin1</label>
            <input type="text" id="fin1" name="fin1" class="form-control" value="{{ old('fin1', $item?->fin1) }}">
            @error('fin1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="debut2" class="form-label">Debut2</label>
            <input type="text" id="debut2" name="debut2" class="form-control" value="{{ old('debut2', $item?->debut2) }}">
            @error('debut2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="fin2" class="form-label">Fin2</label>
            <input type="text" id="fin2" name="fin2" class="form-control" value="{{ old('fin2', $item?->fin2) }}">
            @error('fin2')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="asa_" class="form-label">Asa </label>
            <input type="text" id="asa_" name="asa_" class="form-control" value="{{ old('asa_', $item?->asa_) }}">
            @error('asa_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="forma_" class="form-label">Forma </label>
            <input type="text" id="forma_" name="forma_" class="form-control" value="{{ old('forma_', $item?->forma_) }}">
            @error('forma_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="formas_" class="form-label">Formas </label>
            <input type="text" id="formas_" name="formas_" class="form-control" value="{{ old('formas_', $item?->formas_) }}">
            @error('formas_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="duree2_" class="form-label">Duree2 </label>
            <input type="text" id="duree2_" name="duree2_" class="form-control" value="{{ old('duree2_', $item?->duree2_) }}">
            @error('duree2_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="duree" class="form-label">Duree</label>
            <input type="text" id="duree" name="duree" class="form-control" value="{{ old('duree', $item?->duree) }}">
            @error('duree')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="total" class="form-label">Total</label>
            <input type="text" id="total" name="total" class="form-control" value="{{ old('total', $item?->total) }}">
            @error('total')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="total1" class="form-label">Total1</label>
            <input type="text" id="total1" name="total1" class="form-control" value="{{ old('total1', $item?->total1) }}">
            @error('total1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="total2" class="form-label">Total2</label>
            <input type="text" id="total2" name="total2" class="form-control" value="{{ old('total2', $item?->total2) }}">
            @error('total2')
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
            <label for="status" class="form-label">Status</label>
            <textarea id="status" name="status" class="form-control" rows="4">{{ old('status', $item?->status) }}</textarea>
            @error('status')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.horaires.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
