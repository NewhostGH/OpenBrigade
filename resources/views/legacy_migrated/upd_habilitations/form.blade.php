@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: upd_habilitations.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">Habilitations Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.upd_habilitations.update', $itemKey) : route('legacy_migrated.upd_habilitations.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="GP_ID" name="GP_ID" value="{{ old('GP_ID', $item?->GP_ID) }}">

<input type="hidden" id="GP_DESCRIPTION" name="GP_DESCRIPTION" value="{{ old('GP_DESCRIPTION', $item?->GP_DESCRIPTION) }}">

<input type="hidden" id="sub_possible" name="sub_possible" value="{{ old('sub_possible', $item?->sub_possible) }}">

<input type="hidden" id="all_possible" name="all_possible" value="{{ old('all_possible', $item?->all_possible) }}">

<input type="hidden" id="gp_usage" name="gp_usage" value="{{ old('gp_usage', $item?->gp_usage) }}">

<input type="hidden" id="gp_astreinte" name="gp_astreinte" value="{{ old('gp_astreinte', $item?->gp_astreinte) }}">

<input type="hidden" id="gp_order" name="gp_order" value="{{ old('gp_order', $item?->gp_order) }}">

<input type="hidden" id="category" name="category" value="{{ old('category', $item?->category) }}">


        <div class="mb-3">
            <label for="tr_widget" class="form-label">Tr Widget</label>
            <input type="text" id="tr_widget" name="tr_widget" class="form-control" value="{{ old('tr_widget', $item?->tr_widget) }}">
            @error('tr_widget')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="$F_ID" class="form-label">$F Id</label>
            <input type="text" id="$F_ID" name="$F_ID" class="form-control" value="{{ old('$F_ID', $item?->$F_ID) }}">
            @error('$F_ID')
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

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.upd_habilitations.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
