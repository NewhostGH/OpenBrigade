@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: evenement_display.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">EvenementDisplay Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.evenement_display.update', $itemKey) : route('legacy_migrated.evenement_display.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif

<input type="hidden" id="Messagesubject" name="Messagesubject" value="{{ old('Messagesubject', $item?->Messagesubject) }}">

<input type="hidden" id="Messagebody" name="Messagebody" value="{{ old('Messagebody', $item?->Messagebody) }}">

<input type="hidden" id="SelectionMail" name="SelectionMail" value="{{ old('SelectionMail', $item?->SelectionMail) }}">


        <div class="mb-3">
            <label for="exp_" class="form-label">Exp </label>
            <input type="text" id="exp_" name="exp_" class="form-control" value="{{ old('exp_', $item?->exp_) }}">
            @error('exp_')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="update_hierarchy" class="form-label">Update Hierarchy</label>
            <input type="text" id="update_hierarchy" name="update_hierarchy" class="form-control" value="{{ old('update_hierarchy', $item?->update_hierarchy) }}">
            @error('update_hierarchy')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="evenement_show_absents" class="form-label">Evenement Show Absents</label>
            <input type="text" id="evenement_show_absents" name="evenement_show_absents" class="form-control" value="{{ old('evenement_show_absents', $item?->evenement_show_absents) }}">
            @error('evenement_show_absents')
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
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" id="nombre" name="nombre" class="form-control" value="{{ old('nombre', $item?->nombre) }}">
            @error('nombre')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="evenement_periode" class="form-label">Evenement Periode</label>
            <textarea id="evenement_periode" name="evenement_periode" class="form-control" rows="4">{{ old('evenement_periode', $item?->evenement_periode) }}</textarea>
            @error('evenement_periode')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="evenement_date" class="form-label">Evenement Date</label>
            <textarea id="evenement_date" name="evenement_date" class="form-control" rows="4">{{ old('evenement_date', $item?->evenement_date) }}</textarea>
            @error('evenement_date')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.evenement_display.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
