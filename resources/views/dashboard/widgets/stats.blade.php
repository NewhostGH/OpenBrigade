<div class="dash-stats">

    {{-- My participations --}}
    <a class="stat-tile"
       href="{{ url('/legacy/upd_personnel.php?from=inscriptions&tab=4&pompier=' . $stats['pid'] . '&type_evenement=ALL') }}">
        <div class="stat-tile-header">
            <div class="stat-tile-icon stat-icon-blue">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <div class="stat-tile-title">Mes participations</div>
                <div class="stat-tile-subtitle">{{ $stats['year'] }}</div>
            </div>
        </div>
        <div class="stat-tile-numbers">
            <div class="stat-number-item">
                <span class="stat-number-label">Total</span>
                <span class="stat-number-value">{{ $stats['partiDone'] }}</span>
            </div>
            <div class="stat-number-item">
                <span class="stat-number-label">À venir</span>
                <span class="stat-number-value">{{ $stats['partiIncoming'] }}</span>
            </div>
        </div>
    </a>

    {{-- Section activities --}}
    <a class="stat-tile"
       href="{{ url('/legacy/evenement_choice.php?ec_mode=default&page=1') }}">
        <div class="stat-tile-header">
            <div class="stat-tile-icon stat-icon-green">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <div class="stat-tile-title">Activités</div>
                <div class="stat-tile-subtitle">{{ $stats['sectionName'] }}</div>
            </div>
        </div>
        <div class="stat-tile-numbers">
            <div class="stat-number-item">
                <span class="stat-number-label">Ce mois</span>
                <span class="stat-number-value">{{ $stats['actMonth'] }}</span>
            </div>
            <div class="stat-number-item">
                <span class="stat-number-label">Trimestre</span>
                <span class="stat-number-value">{{ $stats['actQuarter'] }}</span>
            </div>
        </div>
    </a>

    {{-- New members --}}
    <a class="stat-tile"
       href="{{ url('/legacy/personnel.php?position=actif&category=INT&order=P_DATE_ENGAGEMENT') }}">
        <div class="stat-tile-header">
            <div class="stat-tile-icon stat-icon-orange">
                <i class="fas fa-user-plus"></i>
            </div>
            <div>
                <div class="stat-tile-title">Nouveaux membres</div>
                <div class="stat-tile-subtitle">{{ $stats['sectionName'] }}</div>
            </div>
        </div>
        <div class="stat-tile-numbers">
            <div class="stat-number-item">
                <span class="stat-number-label">Ce mois</span>
                <span class="stat-number-value">{{ $stats['newMonth'] }}</span>
            </div>
            <div class="stat-number-item">
                <span class="stat-number-label">Trimestre</span>
                <span class="stat-number-value">{{ $stats['newQuarter'] }}</span>
            </div>
        </div>
    </a>

    {{-- Total alerts --}}
    <div class="stat-tile">
        <div class="stat-tile-header">
            <div class="stat-tile-icon stat-icon-red">
                <i class="fas fa-bell"></i>
            </div>
            <div>
                <div class="stat-tile-title">Tâches</div>
                <div class="stat-tile-subtitle">Mes alarmes</div>
            </div>
        </div>
        <div class="stat-tile-numbers">
            <div class="stat-number-item">
                <span class="stat-number-label">Total</span>
                <span class="stat-number-value {{ $stats['alerts'] > 0 ? 'text-danger' : '' }}">
                    {{ $stats['alerts'] }}
                </span>
            </div>
        </div>
    </div>

</div>
