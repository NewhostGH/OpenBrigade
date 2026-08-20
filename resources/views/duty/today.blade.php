@extends('layout.app')

@section('title', __('duty.today_heading') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('duty.breadcrumb_duty'), 'url' => route('duty.index')],
    ['label' => __('duty.title_today')],
]"/>

<div class="mx-3 mt-3">
    <x-duty-period-nav active="day" />

    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-calendar-day me-1"></i>
                {{ __('duty.today_for_date', ['date' => ucfirst($day->locale('fr')->isoFormat('dddd D MMMM YYYY'))]) }}
                <span class="ob-badge ob-badge-archive ms-1">{{ __('duty.today_count', ['count' => $slots->count()]) }}</span>
            </div>
        </div>
        <div class="ob-widget-card-body">
            @if($slots->isEmpty())
                <p class="ob-widget-empty mb-0">{{ __('duty.today_empty') }}</p>
            @else
                @foreach($byRole as $role => $group)
                    <div class="mb-3">
                        <h6 class="text-muted mb-2" style="font-size:var(--font-size-xs);text-transform:uppercase;letter-spacing:.06em;">
                            {{ $role ?: __('duty.today_no_role') }} ({{ $group->count() }})
                        </h6>
                        <div class="row g-2">
                            @foreach($group as $s)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="ob-widget-card h-100">
                                    <div class="ob-widget-card-body d-flex align-items-center gap-2 py-2">
                                        <img src="{{ route('personnel.photo', $s->P_ID) }}"
                                             width="36" height="36"
                                             style="border-radius:8px;object-fit:cover;flex-shrink:0;"
                                             onerror="this.src='{{ asset('images/autre.png') }}'"
                                             alt="">
                                        <div style="min-width:0;">
                                            <div class="fw-semibold text-truncate" style="font-size:var(--font-size-sm)">
                                                <a href="{{ route('personnel.show', $s->P_ID) }}" class="text-decoration-none">
                                                    {{ $s->P_PRENOM }} {{ strtoupper($s->P_NOM) }}
                                                </a>
                                            </div>
                                            <div class="text-muted" style="font-size:var(--font-size-xs)">
                                                {{ __('duty.today_from') }} {{ \Carbon\Carbon::parse($s->AS_DEBUT)->format('H:i') }}
                                                {{ __('duty.today_to') }} {{ \Carbon\Carbon::parse($s->AS_FIN)->format('H:i') }}
                                                @if($s->P_PHONE)
                                                    · <a href="tel:{{ $s->P_PHONE }}" class="text-decoration-none">{{ $s->P_PHONE }}</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

</div>

@endsection
