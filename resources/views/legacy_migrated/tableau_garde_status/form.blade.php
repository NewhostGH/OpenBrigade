@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: tableau_garde_status.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">TableauGardeStatus Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.tableau_garde_status.update', $itemKey) : route('legacy_migrated.tableau_garde_status.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="mail" class="form-label">Mail</label>
            <input type="text" id="mail" name="mail" class="form-control" value="{{ old('mail', $item?->mail) }}">
            @error('mail')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

<input type="hidden" id="confirmed" name="confirmed" value="{{ old('confirmed', $item?->confirmed) }}">

<input type="hidden" id="action" name="action" value="{{ old('action', $item?->action) }}">

<input type="hidden" id="month" name="month" value="{{ old('month', $item?->month) }}">

<input type="hidden" id="year" name="year" value="{{ old('year', $item?->year) }}">

<input type="hidden" id="equipe" name="equipe" value="{{ old('equipe', $item?->equipe) }}">

<input type="hidden" id="filter" name="filter" value="{{ old('filter', $item?->filter) }}">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.tableau_garde_status.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
