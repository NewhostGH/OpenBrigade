@extends('layout.app')

@section('title', 'Disponibilités — ' . config('app.name'))

@section('content')

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Mes disponibilités</h1>
    </div>
</div>

<div class="mx-3 mt-3 row g-3">

    {{-- ── 4-week availability grid ─────────────────────────────────────── --}}
    <div class="col-lg-8">
        <div class="widget-card">
            <div class="widget-card-header">
                <div class="widget-card-title">
                    <i class="fas fa-calendar-check"></i> Planning 4 semaines
                </div>
                <div style="font-size:var(--font-size-xs)">
                    @foreach($periods as $period)
                        <span class="me-2">
                            <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:var(--brand-bg);opacity:{{ 0.3 + ($loop->index * 0.2) }}"></span>
                            {{ $period->P_LIBELLE }}
                        </span>
                    @endforeach
                </div>
            </div>
            <div class="widget-card-body p-0">
                <table class="table table-sm mb-0" style="table-layout:fixed">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            @foreach(['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'] as $l)
                                <th class="text-center" style="font-size:var(--font-size-xs)">{{ $l }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weeks as $week)
                            <tr>
                                @foreach($week as $cell)
                                    <td class="text-center p-1 {{ $cell['isToday'] ? 'table-primary' : '' }}"
                                        style="font-size:var(--font-size-xs)">
                                        <div style="font-weight:{{ $cell['isToday'] ? '700' : '400' }}">
                                            {{ $cell['date']->format('j') }}
                                        </div>
                                        @if($cell['periodId'])
                                            @php $pi = $periods->firstWhere('PERIOD_ID', $cell['periodId']); @endphp
                                            <div style="font-size:9px;color:#27ae60;font-weight:600">
                                                {{ $pi?->P_LIBELLE ?? '✓' }}
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Absences / indisponibilités ─────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="widget-card">
            <div class="widget-card-header">
                <div class="widget-card-title">
                    <i class="fas fa-user-times"></i> Mes absences à venir
                </div>
                <a href="{{ route('indispo.index', ['tab' => 'mine']) }}"
                   class="widget-card-link">Toutes</a>
            </div>
            <div class="widget-card-body p-0">
                @if($absences->isEmpty())
                    <p class="widget-empty p-3">Aucune absence.</p>
                @else
                    @foreach($absences as $abs)
                        <div class="duty-row px-3">
                            <div class="duty-info">
                                <div class="duty-name">{{ $abs->TI_LIBELLE ?? 'Absence' }}</div>
                                <div class="duty-role">
                                    {{ $abs->I_DEBUT ? \Carbon\Carbon::parse($abs->I_DEBUT)->format('d/m/Y') : '?' }}
                                    —
                                    {{ $abs->I_FIN ? \Carbon\Carbon::parse($abs->I_FIN)->format('d/m/Y') : '?' }}
                                </div>
                            </div>
                            @if($abs->I_ACCEPT == 1)
                                <span class="badge bg-success">OK</span>
                            @else
                                <span class="badge bg-warning text-dark">En attente</span>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="mt-2">
            <a href="{{ url('/legacy/indispo_choice.php') }}" class="btn btn-sm btn-outline-secondary w-100">
                <i class="fas fa-plus me-1"></i> Déclarer une absence
            </a>
        </div>
    </div>

</div>

@endsection
