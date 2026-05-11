@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: bilan_participation.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">BilanParticipation Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.bilan_participation.update', $itemKey) : route('legacy_migrated.bilan_participation.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="groupJN" class="form-label">Groupjn</label>
            <input type="text" id="groupJN" name="groupJN" class="form-control" value="{{ old('groupJN', $item?->groupJN) }}">
            @error('groupJN')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="c$i" class="form-label">C$I</label>
            <input type="text" id="c$i" name="c$i" class="form-control" value="{{ old('c$i', $item?->c$i) }}">
            @error('c$i')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="c" class="form-label">C</label>
            <input type="text" id="c" name="c" class="form-control" value="{{ old('c', $item?->c) }}">
            @error('c')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="c1" name="c1" value="{{ old('c1', $item?->c1) }}">

<input type="hidden" id="c2" name="c2" value="{{ old('c2', $item?->c2) }}">

<input type="hidden" id="c3" name="c3" value="{{ old('c3', $item?->c3) }}">


        <div class="mb-3">
            <label for="section" class="form-label">Section</label>
            <textarea id="section" name="section" class="form-control" rows="4">{{ old('section', $item?->section) }}</textarea>
            @error('section')
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
            <label for="year" class="form-label">Year</label>
            <textarea id="year" name="year" class="form-control" rows="4">{{ old('year', $item?->year) }}</textarea>
            @error('year')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.bilan_participation.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
