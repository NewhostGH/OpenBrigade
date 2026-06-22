@extends('layout.app')

@section('title', "Modifier la charte — " . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('admin.administration')], {{-- i18n-ignore --}}
    ['label' => __('admin.security.title'), 'url' => route('admin.security', ['tab' => 'charter'])],
    ['label' => __('admin.security.tab_charter')],
]"/>

<div class="mx-3 mt-3">
<div class="row g-3">

    <div class="col-lg-8">
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-file-contract me-1"></i> {{ __('admin.charter.title') }}
                </div>
                <a href="{{ route('account.charter') }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                    <i class="fas fa-eye me-1"></i> {{ __('admin.preview') }}
                </a>
            </div>
            <div class="ob-widget-card-body">

                <form method="POST" action="{{ route('admin.security.charter.save') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="charte_text" class="form-label">{{ __('admin.charter.content_label') }}</label>
                        <textarea id="charte_text" name="charte_text" rows="24"
                                  class="form-control font-monospace"
                                  style="font-size:var(--font-size-xs); resize:vertical;"
                                  placeholder="{{ __('admin.charter.content_ph') }}"
                        >{{ old('charte_text', $charteText) }}</textarea>
                        <div class="form-text">
                            {!! __('admin.charter.allowed_tags') !!}
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" id="force_reaccept" name="force_reaccept"
                                   value="1" class="form-check-input"
                                   {{ old('force_reaccept') ? 'checked' : '' }}>
                            <label for="force_reaccept" class="form-check-label fw-semibold">
                                {{ __('admin.charter.force_reaccept') }}
                            </label>
                        </div>
                        <div class="form-text ms-4">
                            {{ __('admin.charter.force_reaccept_hint') }}
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> {{ __('common.save') }}
                        </button>
                        <a href="{{ route('admin.security', ['tab' => 'charter']) }}" class="btn btn-outline-secondary">
                            {{ __('common.cancel') }}
                        </a>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-info-circle me-1"></i> {{ __('admin.charter.about_title') }}
                </div>
            </div>
            <div class="ob-widget-card-body" style="font-size:var(--font-size-sm);">

                @if ($updatedAt)
                    <p>
                        <i class="fas fa-history me-1 text-muted"></i>
                        {!! __('admin.charter.last_published') !!}
                        <strong>{{ \Carbon\Carbon::parse($updatedAt)->format('d/m/Y à H:i') }}</strong>
                    </p>
                    <hr>
                @endif

                <p class="text-muted mb-2">
                    <i class="fas fa-lightbulb me-1"></i>
                    {{ __('admin.charter.hint_auto') }}
                </p>
                <p class="text-muted mb-0">
                    <i class="fas fa-shield-alt me-1"></i>
                    {!! __('admin.charter.hint_reaccept') !!}
                </p>

            </div>
        </div>
    </div>

</div>
</div>

@endsection
