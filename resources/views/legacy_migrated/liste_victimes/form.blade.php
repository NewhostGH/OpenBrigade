@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: liste_victimes.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">ListeVictimes Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.liste_victimes.update', $itemKey) : route('legacy_migrated.liste_victimes.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="ajouter" class="form-label">Ajouter</label>
            <input type="text" id="ajouter" name="ajouter" class="form-control" value="{{ old('ajouter', $item?->ajouter) }}">
            @error('ajouter')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="in_cav" class="form-label">In Cav</label>
            <input type="text" id="in_cav" name="in_cav" class="form-control" value="{{ old('in_cav', $item?->in_cav) }}">
            @error('in_cav')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="a_reguler" class="form-label">A Reguler</label>
            <input type="text" id="a_reguler" name="a_reguler" class="form-control" value="{{ old('a_reguler', $item?->a_reguler) }}">
            @error('a_reguler')
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
            <label for="type_victime" class="form-label">Type Victime</label>
            <textarea id="type_victime" name="type_victime" class="form-control" rows="4">{{ old('type_victime', $item?->type_victime) }}</textarea>
            @error('type_victime')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.liste_victimes.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
