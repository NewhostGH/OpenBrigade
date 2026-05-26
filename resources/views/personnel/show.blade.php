@extends('layout.app')

@section('title', 'Profil personnel - ' . config('app.name'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h1 class="h4 mb-1">{{ $personnel->P_PRENOM }} {{ $personnel->P_NOM }}</h1>
                            <div class="text-muted">{{ $personnel->P_CODE }} - {{ $personnel->P_STATUT }}</div>
                        </div>
                        <a href="{{ route('personnel.edit', $personnel) }}" class="btn btn-primary">Editer</a>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6"><strong>Grade:</strong> {{ $personnel->P_GRADE }}</div>
                        <div class="col-md-6"><strong>Profession:</strong> {{ $personnel->P_PROFESSION }}</div>
                        <div class="col-md-6"><strong>Email:</strong> {{ $personnel->P_EMAIL ?: '-' }}</div>
                        <div class="col-md-6"><strong>Telephone:</strong> {{ $personnel->P_PHONE ?: '-' }}</div>
                        <div class="col-md-6"><strong>Portable:</strong> {{ $personnel->P_PHONE2 ?: '-' }}</div>
                        <div class="col-md-6"><strong>Section:</strong> {{ $personnel->section?->S_CODE ?: '-' }}</div>
                        <div class="col-md-6"><strong>Naissance:</strong>
                            {{ $personnel->P_BIRTHDATE?->format('d/m/Y') ?: '-' }}</div>
                        <div class="col-md-6"><strong>Engagement:</strong>
                            {{ $personnel->P_DATE_ENGAGEMENT?->format('d/m/Y') ?: '-' }}</div>
                        <div class="col-md-6"><strong>Fin:</strong> {{ $personnel->P_FIN?->format('d/m/Y') ?: '-' }}</div>
                        <div class="col-md-6"><strong>Adresse:</strong> {{ $personnel->P_ADDRESS ?: '-' }}</div>
                        <div class="col-md-6"><strong>Ville:</strong> {{ $personnel->P_ZIP_CODE }} {{ $personnel->P_CITY }}
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('personnel.index') }}" class="btn btn-outline-secondary">Retour liste</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection