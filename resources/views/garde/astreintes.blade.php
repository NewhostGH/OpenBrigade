@extends('layout.app')

@section('title', 'Astreintes — ' . config('app.name'))

@section('content')

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Gestion des astreintes</h1>
        @if(auth()->user()->hasPermission(26))
            <a href="{{ url('/legacy/astreinte_edit.php') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Nouvelle astreinte
            </a>
        @endif
    </div>

    <div class="d-flex align-items-center gap-3 mt-2">
        <a href="{{ route('garde.astreintes', ['month' => $prevMonth, 'year' => $prevYear]) }}"
           class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-chevron-left"></i>
        </a>
        <span class="fw-semibold" style="font-size:var(--font-size-sm)">
            {{ ucfirst($first->locale('fr')->isoFormat('MMMM YYYY')) }}
        </span>
        <a href="{{ route('garde.astreintes', ['month' => $nextMonth, 'year' => $nextYear]) }}"
           class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-chevron-right"></i>
        </a>
    </div>
</div>

<div class="mx-3 mt-3">
    @if($slots->isEmpty())
        <div class="text-muted fst-italic p-3">Aucune astreinte ce mois-ci.</div>
    @else
        <table class="table table-sm table-hover align-middle">
            <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                <tr>
                    <th>Début</th>
                    <th>Fin</th>
                    <th>Personnel</th>
                    <th>Rôle</th>
                    <th style="width:60px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($slots as $slot)
                    <tr>
                        <td style="font-size:var(--font-size-sm)">
                            {{ \Carbon\Carbon::parse($slot->AS_DEBUT)->locale('fr')->isoFormat('ddd D MMM, HH:mm') }}
                        </td>
                        <td style="font-size:var(--font-size-sm)">
                            {{ \Carbon\Carbon::parse($slot->AS_FIN)->locale('fr')->isoFormat('ddd D MMM, HH:mm') }}
                        </td>
                        <td>
                            <a href="{{ route('personnel.show', $slot->P_ID) }}"
                               class="text-decoration-none" style="font-size:var(--font-size-sm)">
                                {{ $slot->P_PRENOM }} {{ strtoupper($slot->P_NOM) }}
                            </a>
                        </td>
                        <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                            {{ $slot->GP_DESCRIPTION }}
                        </td>
                        <td>
                            <a href="{{ url('/legacy/astreinte_edit.php?astreinte=' . $slot->AS_ID) }}"
                               class="btn btn-sm btn-outline-secondary" title="Modifier">
                                <i class="fas fa-edit fa-xs"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-2">{{ $slots->links() }}</div>
    @endif
</div>

@endsection
