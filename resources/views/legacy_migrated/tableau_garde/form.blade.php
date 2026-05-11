@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: tableau_garde.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">TableauGarde Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.tableau_garde.update', $itemKey) : route('legacy_migrated.tableau_garde.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="chk-masque" class="form-label">Chk-Masque</label>
            <input type="text" id="chk-masque" name="chk-masque" class="form-control" value="{{ old('chk-masque', $item?->chk-masque) }}">
            @error('chk-masque')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="delete" class="form-label">Delete</label>
            <input type="text" id="delete" name="delete" class="form-control" value="{{ old('delete', $item?->delete) }}">
            @error('delete')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="filter" class="form-label">Filter</label>
            <textarea id="filter" name="filter" class="form-control" rows="4">{{ old('filter', $item?->filter) }}</textarea>
            @error('filter')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="equipe" class="form-label">Equipe</label>
            <textarea id="equipe" name="equipe" class="form-control" rows="4">{{ old('equipe', $item?->equipe) }}</textarea>
            @error('equipe')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="tableau_garde_display_mode" class="form-label">Tableau Garde Display Mode</label>
            <textarea id="tableau_garde_display_mode" name="tableau_garde_display_mode" class="form-control" rows="4">{{ old('tableau_garde_display_mode', $item?->tableau_garde_display_mode) }}</textarea>
            @error('tableau_garde_display_mode')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="month" class="form-label">Month</label>
            <textarea id="month" name="month" class="form-control" rows="4">{{ old('month', $item?->month) }}</textarea>
            @error('month')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="week" class="form-label">Week</label>
            <textarea id="week" name="week" class="form-control" rows="4">{{ old('week', $item?->week) }}</textarea>
            @error('week')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="year" class="form-label">Year</label>
            <textarea id="year" name="year" class="form-control" rows="4">{{ old('year', $item?->year) }}</textarea>
            @error('year')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="person" class="form-label">Person</label>
            <textarea id="person" name="person" class="form-control" rows="4">{{ old('person', $item?->person) }}</textarea>
            @error('person')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.tableau_garde.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
