@extends('layout.app')

@section('title', __('dashboard.index.title') . ' – ' . config('app.name'))

@section('content')
<div class="ob-dash-wrap">

    {{-- ── Stats KPI bar ─────────────────────────────────────────────────── --}}
    @include('dashboard.widgets.stats')

    {{-- ── Alert banners ────────────────────────────────────────────────── --}}
    @if ($passwordExpiry)
        <div class="ob-dash-alert {{ $passwordExpiry['expired'] ? 'ob-dash-alert-danger' : 'ob-dash-alert-warning' }}">
            <i class="fas fa-key"></i>
            @if ($passwordExpiry['expired'])
                {{ __('dashboard.index.password_expired') }} <strong>{{ __('dashboard.index.password_expired_tag') }}</strong>.
            @else
                {{ __('dashboard.index.password_expiry_soon') }} <strong>{{ __('dashboard.index.password_days', ['count' => $passwordExpiry['days']]) }}</strong>
                {{ __('dashboard.index.password_on', ['date' => $passwordExpiry['expiry']]) }}
            @endif
            <a href="{{ route('account.auth', ['tab' => 'password']) }}" class="ms-2">{{ __('dashboard.index.password_change_now') }}</a>
        </div>
    @endif

    @if (!empty($competenceAlerts))
        <div class="ob-dash-alert ob-dash-alert-warning">
            <i class="fas fa-certificate"></i>
            {{ __('dashboard.index.competence_expiry') }}
            @foreach ($competenceAlerts as $c)
                <strong>{{ $c->TYPE }}</strong> {{ __('dashboard.index.competence_days', ['count' => $c->NB]) }}
                ({{ $c->Q_EXPIRATION }})@if (!$loop->last), @endif
            @endforeach
            &mdash;
            <a href="{{ route('personnel.qualifications', auth()->user()->P_ID) }}">
                {{ __('dashboard.index.competence_see') }}
            </a>
        </div>
    @endif

    {{-- ── 3-column widget grid ──────────────────────────────────────────── --}}
    <div class="ob-dash-columns"
         id="ob-dash-columns"
         data-save-url="{{ route('dashboard.layout.save') }}">

        @foreach([1, 2, 3] as $col)
        <div class="ob-dash-column" data-col="{{ $col }}">

            {{-- Drop hint — only visible in edit mode when the column has no visible widgets --}}
            <div class="ob-col-drop-hint" aria-hidden="true">
                <i class="fas fa-plus-circle"></i> {{ __('dashboard.index.drop_hint') }}
            </div>

            @foreach($widgetsByColumn[$col] as $widget)
                <div class="ob-widget-wrapper{{ $widget['visible'] ? '' : ' ob-widget-hidden' }}"
                     data-widget="{{ $widget['key'] }}"
                     data-label="{{ $widget['label'] }}"
                     draggable="true">
                    @include('dashboard.widgets.' . $widget['key'])
                </div>
            @endforeach
        </div>
        @endforeach

    </div>

    {{-- ── Hidden widgets tray (edit mode only) ────────────────────────── --}}
    <div id="ob-hidden-tray" class="ob-hidden-tray" style="display:none">
        <div class="ob-hidden-tray-header">
            <i class="fas fa-eye-slash"></i> {{ __('dashboard.index.hidden_tray_title') }}
        </div>
        <div id="ob-hidden-tray-items" class="ob-hidden-tray-items">
            @forelse($hiddenWidgets as $w)
                <div class="ob-hidden-widget-pill" data-widget="{{ $w['key'] }}">
                    <span class="ob-hidden-widget-name">{{ $w['label'] }}</span>
                    <button class="ob-add-back-btn" type="button" title="{{ __('dashboard.index.btn_show_widget') }}">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            @empty
                <span id="ob-tray-empty" class="ob-hidden-tray-empty">{{ __('dashboard.index.tray_all_visible') }}</span>
            @endforelse
        </div>
    </div>
</div>

{{-- Fixed edit-mode toggle — position:fixed, takes no layout space --}}
<button id="ob-dash-edit-toggle" class="ob-btn-edit-mode" type="button">
    <i class="fas fa-sliders-h"></i> {{ __('dashboard.index.btn_customize') }}
</button>

@endsection

@push('scripts')
<script type="module" src="{{ Vite::asset('resources/js/ob-dashboard.js') }}"></script>
@endpush
