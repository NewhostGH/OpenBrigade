{{--
    Error card shared by both the standalone shell and the in-app layout.
    Expects $code (int). Pulls metadata from config via App\Support\ErrorPage.
--}}
@php
    $meta = \App\Support\ErrorPage::meta($code);
    $relogin = $meta['relogin'] ?? false;

    // 503 may carry a custom maintenance message (php artisan down --message).
    $message = ($code === 503 && isset($exception) && $exception->getMessage())
        ? $exception->getMessage()
        : $meta['message'];
@endphp
<div class="ob-error-wrap">
    <main class="ob-error-card" role="main">
        <div class="ob-error-diagram">
            @include('errors.partials.diagram', ['node' => $meta['node']])
        </div>
        <div class="ob-error-body">
            <div class="ob-error-code">{{ $code }}</div>
            <h1 class="ob-error-title">{{ $meta['title'] }}</h1>
            <p class="ob-error-message">{{ $message }}</p>
            <div class="ob-error-actions">
                @if ($relogin)
                    <a href="{{ url('/login') }}" class="ob-error-btn ob-error-btn-primary">{{ __('errors.btn_login') }}</a>
                    <a href="{{ url('/') }}" class="ob-error-btn ob-error-btn-secondary">{{ __('errors.btn_home') }}</a>
                @elseif ($code === 503)
                    <a href="javascript:location.reload()" class="ob-error-btn ob-error-btn-primary">{{ __('errors.btn_retry') }}</a>
                @else
                    <a href="{{ url('/') }}" class="ob-error-btn ob-error-btn-primary">{{ __('errors.btn_back_home') }}</a>
                    <a href="javascript:history.back()" class="ob-error-btn ob-error-btn-secondary">{{ __('errors.btn_prev_page') }}</a>
                @endif
            </div>
        </div>
        <div class="ob-error-foot">
            {{ date('Y') }} — {{ config('app.name') }}
        </div>
    </main>
</div>
