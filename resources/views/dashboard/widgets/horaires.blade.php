@if (!empty($horaires['rows']))
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-clock"></i> {{ __('dashboard.horaires.title') }}
        </div>
    </div>
    <div class="ob-widget-card-body">
        @foreach ($horaires['rows'] as $row)
            <a class="ob-dash-horaire-row ob-dash-alert-item-row"
               {{-- TODO: Migrate code --}}
               href="{{ url('/legacy/upd_personnel.php?tab=12&from=list&view=week&year=' . $row->ANNEE . '&week=' . $row->SEMAINE . '&pompier=' . $row->P_ID) }}">
                <div class="ob-dash-alert-item-info">
                    <div class="ob-dash-alert-item-label">
                        {{ strtoupper($row->P_NOM) }} {{ ucfirst(strtolower($row->P_PRENOM)) }}
                    </div>
                    <div class="ob-dash-alert-item-sub">{{ __('dashboard.horaires.week_label', ['week' => $row->SEMAINE, 'year' => $row->ANNEE]) }}</div>
                </div>
                <span class="ob-dash-day-label ob-dash-day-label-orange">{{ __('dashboard.horaires.to_validate') }}</span>
            </a>
        @endforeach
    </div>
</div>
@endif
