@if (!empty($horaires['rows']))
<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-clock"></i> Horaires à valider
        </div>
    </div>
    <div class="widget-card-body">
        @foreach ($horaires['rows'] as $row)
            <a class="horaire-row alert-item-row"
               href="{{ url('/legacy/upd_personnel.php?tab=12&from=list&view=week&year=' . $row->ANNEE . '&week=' . $row->SEMAINE . '&pompier=' . $row->P_ID) }}">
                <div class="alert-item-info">
                    <div class="alert-item-label">
                        {{ strtoupper($row->P_NOM) }} {{ ucfirst(strtolower($row->P_PRENOM)) }}
                    </div>
                    <div class="alert-item-sub">Semaine {{ $row->SEMAINE }} – {{ $row->ANNEE }}</div>
                </div>
                <span class="day-label day-label-orange">À valider</span>
            </a>
        @endforeach
    </div>
</div>
@endif
