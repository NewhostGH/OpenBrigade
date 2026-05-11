@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: repos_saisie.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">ReposSaisie Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.repos_saisie.update', $itemKey) : route('legacy_migrated.repos_saisie.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="nbjours" name="nbjours" value="{{ old('nbjours', $item?->nbjours) }}">

<input type="hidden" id="person" name="person" value="{{ old('person', $item?->person) }}">

<input type="hidden" id="month" name="month" value="{{ old('month', $item?->month) }}">

<input type="hidden" id="year" name="year" value="{{ old('year', $item?->year) }}">


        <div class="mb-3">
            <label for="2_" class="form-label">2 </label>
            <input type="text" id="2_" name="2_" class="form-control" value="{{ old('2_', $item?->2_) }}">
            @error('2_')
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
            <label for="filter" class="form-label">Filter</label>
            <textarea id="filter" name="filter" class="form-control" rows="4">{{ old('filter', $item?->filter) }}</textarea>
            @error('filter')
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
            <label for="menu1" class="form-label">Menu1</label>
            <textarea id="menu1" name="menu1" class="form-control" rows="4">{{ old('menu1', $item?->menu1) }}</textarea>
            @error('menu1')
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
                            <a href="{{ route('legacy_migrated.repos_saisie.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
