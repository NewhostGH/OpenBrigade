<div class="ob-dash-stats">

    {{-- My participations --}}
    <a class="ob-dash-stat-tile" href="{{ route('personnel.show', $stats['pid']) }}">
        <div class="ob-dash-stat-tile-header">
            <div class="ob-dash-stat-tile-icon ob-dash-stat-icon-blue">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <div class="ob-dash-stat-tile-title">Mes participations</div>
                <div class="ob-dash-stat-tile-subtitle">{{ $stats['year'] }}</div>
            </div>
        </div>
        <div class="ob-dash-stat-tile-numbers">
            <div class="ob-dash-stat-number-item">
                <span class="ob-dash-stat-number-label">Total</span>
                <span class="ob-dash-stat-number-value">{{ $stats['partiDone'] }}</span>
            </div>
            <div class="ob-dash-stat-number-item">
                <span class="ob-dash-stat-number-label">À venir</span>
                <span class="ob-dash-stat-number-value">{{ $stats['partiIncoming'] }}</span>
            </div>
        </div>
    </a>

    {{-- Section activities --}}
    <a class="ob-dash-stat-tile" href="{{ route('event.index') }}">
        <div class="ob-dash-stat-tile-header">
            <div class="ob-dash-stat-tile-icon ob-dash-stat-icon-green">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <div class="ob-dash-stat-tile-title">Activités</div>
                <div class="ob-dash-stat-tile-subtitle">{{ $stats['sectionName'] }}</div>
            </div>
        </div>
        <div class="ob-dash-stat-tile-numbers">
            <div class="ob-dash-stat-number-item">
                <span class="ob-dash-stat-number-label">Ce mois</span>
                <span class="ob-dash-stat-number-value">{{ $stats['actMonth'] }}</span>
            </div>
            <div class="ob-dash-stat-number-item">
                <span class="ob-dash-stat-number-label">Trimestre</span>
                <span class="ob-dash-stat-number-value">{{ $stats['actQuarter'] }}</span>
            </div>
        </div>
    </a>

    {{-- New members --}}
    <a class="ob-dash-stat-tile" href="{{ route('personnel.index') }}">
        <div class="ob-dash-stat-tile-header">
            <div class="ob-dash-stat-tile-icon ob-dash-stat-icon-orange">
                <i class="fas fa-user-plus"></i>
            </div>
            <div>
                <div class="ob-dash-stat-tile-title">Nouveaux membres</div>
                <div class="ob-dash-stat-tile-subtitle">{{ $stats['sectionName'] }}</div>
            </div>
        </div>
        <div class="ob-dash-stat-tile-numbers">
            <div class="ob-dash-stat-number-item">
                <span class="ob-dash-stat-number-label">Ce mois</span>
                <span class="ob-dash-stat-number-value">{{ $stats['newMonth'] }}</span>
            </div>
            <div class="ob-dash-stat-number-item">
                <span class="ob-dash-stat-number-label">Trimestre</span>
                <span class="ob-dash-stat-number-value">{{ $stats['newQuarter'] }}</span>
            </div>
        </div>
    </a>

    {{-- Total alerts --}}
    <div class="ob-dash-stat-tile">
        <div class="ob-dash-stat-tile-header">
            <div class="ob-dash-stat-tile-icon ob-dash-stat-icon-red">
                <i class="fas fa-bell"></i>
            </div>
            <div>
                <div class="ob-dash-stat-tile-title">Tâches</div>
                <div class="ob-dash-stat-tile-subtitle">Mes alarmes</div>
            </div>
        </div>
        <div class="ob-dash-stat-tile-numbers">
            <div class="ob-dash-stat-number-item">
                <span class="ob-dash-stat-number-label">Total</span>
                <span class="ob-dash-stat-number-value {{ $stats['alerts'] > 0 ? 'text-danger' : '' }}">
                    {{ $stats['alerts'] }}
                </span>
            </div>
        </div>
    </div>

</div>