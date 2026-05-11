@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migration source: repo_events.php | This view stems from a legacy migration and requires functional verification.
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-4">RepoEvents Form</h1>

                    @php($itemKey = $item?->getKey() ?? ($item?->id ?? ($item?->P_ID ?? null)))
                    <form method="POST" action="{{ ($item && $itemKey) ? route('legacy_migrated.repo_events.update', $itemKey) : route('legacy_migrated.repo_events.store') }}">
                        @csrf
                        @if($item)
                            @method('PUT')
                        @endif


        <div class="mb-3">
            <label for="sub" class="form-label">Sub</label>
            <input type="text" id="sub" name="sub" class="form-control" value="{{ old('sub', $item?->sub) }}">
            @error('sub')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="dtdb" class="form-label">Dtdb</label>
            <input type="text" id="dtdb" name="dtdb" class="form-control" value="{{ old('dtdb', $item?->dtdb) }}">
            @error('dtdb')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="btGo" class="form-label">Btgo</label>
            <input type="text" id="btGo" name="btGo" class="form-control" value="{{ old('btGo', $item?->btGo) }}">
            @error('btGo')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="dtfn" class="form-label">Dtfn</label>
            <input type="text" id="dtfn" name="dtfn" class="form-control" value="{{ old('dtfn', $item?->dtfn) }}">
            @error('dtfn')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label for="report" class="form-label">Report</label>
            <textarea id="report" name="report" class="form-control" rows="4">{{ old('report', $item?->report) }}</textarea>
            @error('report')
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


        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <textarea id="type" name="type" class="form-control" rows="4">{{ old('type', $item?->type) }}</textarea>
            @error('type')
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
            <label for="year" class="form-label">Year</label>
            <textarea id="year" name="year" class="form-control" rows="4">{{ old('year', $item?->year) }}</textarea>
            @error('year')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('legacy_migrated.repo_events.index') }}" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
