@php
    $hasTrainee = !empty($training['asTrainee']);
    $hasTrainer = !empty($training['asTrainer']);
    $formatMins = fn($mins) => sprintf('%02d:%02d', (int)($mins * 60 / 60), (int)(($mins * 60) % 60));
@endphp
@if ($hasTrainee || $hasTrainer)
<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-graduation-cap"></i> Formations {{ $training['year'] }}
        </div>
        <a class="widget-card-link"
           href="{{ url('/legacy/upd_personnel.php?pompier=' . auth()->user()->P_ID . '&tab=2&child=2') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="widget-card-body">
        @if ($hasTrainee)
            <p style="font-size:var(--font-size-xs);color:var(--text-muted-soft);margin-bottom:4px;">
                Suivies depuis le 1er janvier {{ $training['year'] }}
            </p>
            @php $totalMins = 0; @endphp
            @foreach ($training['asTrainee'] as $row)
                @php $mins = (float)$row->TOTAL * 60; $totalMins += $mins; @endphp
                <div class="training-row">
                    <span class="training-code">{{ $row->PH_CODE ?: 'Autres' }}</span>
                    <span class="training-hours">
                        {{ sprintf('%02d:%02d', floor($mins/60), $mins%60) }} h
                    </span>
                </div>
            @endforeach
            <div class="training-row" style="font-weight:600;">
                <span class="training-code">TOTAL</span>
                <span>{{ sprintf('%02d:%02d', floor($totalMins/60), $totalMins%60) }} h</span>
            </div>
        @endif

        @if ($hasTrainer)
            <p style="font-size:var(--font-size-xs);color:var(--text-muted-soft);margin:10px 0 4px;">
                Données depuis le 1er janvier {{ $training['year'] }}
            </p>
            @php $totalMins = 0; @endphp
            @foreach ($training['asTrainer'] as $row)
                @php $mins = (float)$row->TOTAL * 60; $totalMins += $mins; @endphp
                <div class="training-row">
                    <span class="training-code">{{ $row->PH_CODE ?: 'Autres' }}</span>
                    <span class="training-hours">
                        {{ sprintf('%02d:%02d', floor($mins/60), $mins%60) }} h
                    </span>
                </div>
            @endforeach
            <div class="training-row" style="font-weight:600;">
                <span class="training-code">TOTAL formateur</span>
                <span>{{ sprintf('%02d:%02d', floor($totalMins/60), $totalMins%60) }} h</span>
            </div>
        @endif
    </div>
</div>
@endif
