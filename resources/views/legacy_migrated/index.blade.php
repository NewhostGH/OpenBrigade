@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header">
                    Legacy migrated area
                </div>
                <div class="card-body">
                    <h1 class="h4 mb-3">Legacy Views</h1>
                    <p class="text-muted mb-4">
                        You are in the migrated legacy section. Use the quick links below to open migrated pages.
                    </p>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <a class="btn btn-outline-primary w-100" href="{{ route('legacy_migrated.personnel.index') }}">Personnel</a>
                        </div>
                        <div class="col-md-4">
                            <a class="btn btn-outline-primary w-100" href="{{ route('legacy_migrated.evenement_detail.index') }}">Evenement Detail</a>
                        </div>
                        <div class="col-md-4">
                            <a class="btn btn-outline-primary w-100" href="{{ route('legacy_migrated.astreintes.index') }}">Astreintes</a>
                        </div>
                        <div class="col-md-4">
                            <a class="btn btn-outline-primary w-100" href="{{ route('legacy_migrated.materiel.index') }}">Materiel</a>
                        </div>
                        <div class="col-md-4">
                            <a class="btn btn-outline-primary w-100" href="{{ route('legacy_migrated.configuration.index') }}">Configuration</a>
                        </div>
                        <div class="col-md-4">
                            <a class="btn btn-outline-primary w-100" href="{{ route('legacy_migrated.company.index') }}">Company</a>
                        </div>
                    </div>

                    <hr class="my-4">
                    <a class="btn btn-secondary" href="{{ route('dashboard') }}">Back to dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
