@php
    $hasTrainee = !empty($training['asTrainee']);
    $hasTrainer = !empty($training['asTrainer']);
    $formatMins = fn($mins) => sprintf('%02d:%02d', (int)($mins * 60 / 60), (int)(($mins * 60) % 60)); // i18n-ignore
@endphp
@if ($hasTrainee || $hasTrainer)
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-graduation-cap"></i> {{ __('dashboard.training.title', ['year' => $training['year']]) }}
        </div>
        <a class="ob-widget-card-link"
           href="{{ route('personnel.qualifications', auth()->user()->P_ID) }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="ob-widget-card-body">
        @if ($hasTrainee)
            <p style="font-size:var(--font-size-xs);color:var(--text-muted-soft);margin-bottom:4px;">
                {{ __('dashboard.training.trainee_since', ['year' => $training['year']]) }}
            </p>
            @php $totalMins = 0; @endphp
            @foreach ($training['asTrainee'] as $row)
                @php $mins = (float)$row->TOTAL * 60; $totalMins += $mins; /* i18n-ignore */ @endphp
                <div class="ob-dash-training-row">
                    <span class="ob-dash-training-code">{{ $row->PH_CODE ?: __('dashboard.training.other_code') }}</span>
                    <span class="ob-dash-training-hours">
                        {{ sprintf('%02d:%02d', floor($mins/60), $mins%60) }} h
                    </span>
                </div>
            @endforeach
            <div class="ob-dash-training-row" style="font-weight:600;">
                <span class="ob-dash-training-code">{{ __('dashboard.training.total') }}</span>
                <span>{{ sprintf('%02d:%02d', floor($totalMins/60), $totalMins%60) }} h</span>
            </div>
        @endif

        @if ($hasTrainer)
            <p style="font-size:var(--font-size-xs);color:var(--text-muted-soft);margin:10px 0 4px;">
                {{ __('dashboard.training.trainer_since', ['year' => $training['year']]) }}
            </p>
            @php $totalMins = 0; @endphp
            @foreach ($training['asTrainer'] as $row)
                @php $mins = (float)$row->TOTAL * 60; $totalMins += $mins; /* i18n-ignore */ @endphp
                <div class="ob-dash-training-row">
                    <span class="ob-dash-training-code">{{ $row->PH_CODE ?: __('dashboard.training.other_code') }}</span>
                    <span class="ob-dash-training-hours">
                        {{ sprintf('%02d:%02d', floor($mins/60), $mins%60) }} h
                    </span>
                </div>
            @endforeach
            <div class="ob-dash-training-row" style="font-weight:600;">
                <span class="ob-dash-training-code">{{ __('dashboard.training.total_trainer') }}</span>
                <span>{{ sprintf('%02d:%02d', floor($totalMins/60), $totalMins%60) }} h</span>
            </div>
        @endif
    </div>
</div>
@endif
