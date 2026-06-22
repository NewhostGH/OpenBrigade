@extends('layout.app')

@section('title', 'Tableau de garde — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('duty.breadcrumb_duty')],
]"/>

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>{{ __('duty.title_schedule') }}</h1>
        @if(auth()->user()->hasPermission(26))
            {{-- TODO: Migrate code --}}
            <a href="{{ url('/legacy/astreinte_edit.php') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> {{ __('duty.new_on_call') }}
            </a>
        @endif
        @if(auth()->user()->hasPermission(5))
            <a href="{{ route('duty.types.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-cog me-1"></i> {{ __('duty.guard_types_btn') }}
            </a>
        @endif
    </div>

    {{-- Week navigation --}}
    <div class="d-flex align-items-center gap-3 mt-2">
        <a href="{{ route('duty.index', ['week' => $prevWeek]) }}"
           class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-chevron-left"></i> {{ __('duty.prev_week') }}
        </a>

        <span class="fw-semibold" style="font-size:var(--font-size-sm)">
            {{ __('duty.week_label', ['from' => $monday->locale('fr')->isoFormat('D MMMM'), 'to' => $sunday->locale('fr')->isoFormat('D MMMM YYYY')]) }}
        </span>

        <a href="{{ route('duty.index', ['week' => $nextWeek]) }}"
           class="btn btn-sm btn-outline-secondary">
            {{ __('duty.next_week') }} <i class="fas fa-chevron-right"></i>
        </a>

        @if($weekOffset !== 0)
            <a href="{{ route('duty.index') }}" class="btn btn-sm btn-outline-primary">
                {{ __('duty.current_week') }}
            </a>
        @endif
    </div>
</div>

<div class="mx-3 mt-3">
    @php $hasAny = collect($days)->sum(fn($d) => count($d['slots'])) > 0; @endphp

    @if(!$hasAny)
        <div class="text-muted fst-italic p-3">{{ __('duty.no_on_call_week') }}</div>
    @else
        <div class="row g-2">
            @foreach($days as $day)
                <div class="col-xl col-lg-4 col-md-6 col-12">
                    <div class="ob-widget-card {{ $day['isToday'] ? 'border-primary' : '' }}">
                        <div class="ob-widget-card-header"
                             style="{{ $day['isToday'] ? 'background:color-mix(in srgb, var(--brand-bg) 8%, #fff)' : '' }}">
                            <div class="ob-widget-card-title">
                                {{ $day['label'] }}
                            </div>
                            @if($day['isToday'])
                                <span class="badge bg-primary" style="font-size:var(--font-size-xs)">{{ __('duty.today_badge') }}</span>
                            @endif
                        </div>
                        <div class="ob-widget-card-body p-0">
                            @if($day['slots']->isEmpty())
                                <p class="ob-widget-empty p-2">{{ __('duty.no_on_call_day') }}</p>
                            @else
                                @foreach($day['slots'] as $slot)
                                    <div class="ob-duty-row px-2">
                                        <img src="{{ route('personnel.photo', $slot->P_ID) }}"
                                             width="32" height="32"
                                             class="ob-duty-avatar"
                                             onerror="this.src='{{ asset('images/autre.png') }}'">
                                        <div class="ob-duty-info">
                                            <div class="ob-duty-name">
                                                <a href="{{ route('personnel.show', $slot->P_ID) }}"
                                                   class="text-decoration-none"
                                                   style="color:inherit">
                                                    {{ $slot->P_PRENOM }} {{ strtoupper($slot->P_NOM) }}
                                                </a>
                                            </div>
                                            <div class="ob-duty-role">
                                                {{ $slot->GP_DESCRIPTION }}
                                                &mdash;
                                                {{ substr($slot->AS_DEBUT, 11, 5) }}–{{ substr($slot->AS_FIN, 11, 5) }}
                                            </div>
                                            @if($slot->P_PHONE)
                                                <a href="tel:{{ $slot->P_PHONE }}" class="ob-duty-phone">
                                                    <i class="fas fa-phone fa-xs me-1"></i>{{ $slot->P_PHONE }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
