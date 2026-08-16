@extends('layout.app')

@section('title', __('personnel.qr_title') . ' — ' . strtoupper($personnel->P_NOM) . ' ' . $personnel->P_PRENOM . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('personnel.title'), 'url' => route('personnel.index')],
    ['label' => strtoupper($personnel->P_NOM) . ' ' . $personnel->P_PRENOM, 'url' => route('personnel.show', $personnel)],
    ['label' => __('personnel.qr_title')],
]"/>

<div class="mx-3 mt-3">
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-qrcode me-1 text-muted"></i>
                {{ __('personnel.qr_title') }}
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('personnel.show', $personnel) }}" class="btn btn-sm btn-outline-secondary noprint">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('personnel.qr_back') }}
                </a>
            </div>
        </div>

        <div class="ob-widget-card-body text-center">
            <p class="text-muted mb-3">
                {{ __('personnel.qr_desc', ['name' => strtoupper($personnel->P_NOM) . ' ' . $personnel->P_PRENOM]) }}
            </p>

            <div class="ob-pers-qr d-inline-block">
                {!! $qrSvg !!}
            </div>

            <p class="ob-widget-empty mt-3">{{ __('personnel.qr_hint') }}</p>
        </div>
    </div>
</div>

@endsection
