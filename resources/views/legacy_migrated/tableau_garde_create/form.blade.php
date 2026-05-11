@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: tableau_garde_create.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">TableauGardeCreate Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.tableau_garde_create.update', $itemKey) : route('legacy_migrated.tableau_garde_create.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="g2p" class="form-label">G2P</label>
            <input type="text" id="g2p" name="g2p" class="form-control" value="{{ old('g2p', $item?->g2p) }}">
            @error('g2p')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="defaultpart" name="defaultpart" value="{{ old('defaultpart', $item?->defaultpart) }}">

<input type="hidden" id="month" name="month" value="{{ old('month', $item?->month) }}">

<input type="hidden" id="year" name="year" value="{{ old('year', $item?->year) }}">

<input type="hidden" id="equipe" name="equipe" value="{{ old('equipe', $item?->equipe) }}">

<input type="hidden" id="filter" name="filter" value="{{ old('filter', $item?->filter) }}">

<input type="hidden" id="date1" name="date1" value="{{ old('date1', $item?->date1) }}">

<input type="hidden" id="date2" name="date2" value="{{ old('date2', $item?->date2) }}">


        <div class="mb-3">
            <label for="alldays" class="form-label">Alldays</label>
            <input type="text" id="alldays" name="alldays" class="form-control" value="{{ old('alldays', $item?->alldays) }}">
            @error('alldays')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="V" class="form-label">V</label>
            <input type="text" id="V" name="V" class="form-control" value="{{ old('V', $item?->V) }}">
            @error('V')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="SPP" class="form-label">Spp</label>
            <input type="text" id="SPP" name="SPP" class="form-control" value="{{ old('SPP', $item?->SPP) }}">
            @error('SPP')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="SPV" class="form-label">Spv</label>
            <input type="text" id="SPV" name="SPV" class="form-control" value="{{ old('SPV', $item?->SPV) }}">
            @error('SPV')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="day_choice" name="day_choice" value="{{ old('day_choice', $item?->day_choice) }}">

<input type="hidden" id="lieu" name="lieu" value="{{ old('lieu', $item?->lieu) }}">

<input type="hidden" id="address" name="address" value="{{ old('address', $item?->address) }}">

<input type="hidden" id="debut1" name="debut1" value="{{ old('debut1', $item?->debut1) }}">

<input type="hidden" id="fin1" name="fin1" value="{{ old('fin1', $item?->fin1) }}">

<input type="hidden" id="duree1" name="duree1" value="{{ old('duree1', $item?->duree1) }}">

<input type="hidden" id="nb1" name="nb1" value="{{ old('nb1', $item?->nb1) }}">

<input type="hidden" id="debut2" name="debut2" value="{{ old('debut2', $item?->debut2) }}">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.tableau_garde_create.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
