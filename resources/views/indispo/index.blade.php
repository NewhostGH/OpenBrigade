@extends('layout.app')

@section('title', 'Indisponibilités — ' . config('app.name'))

@section('content')

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Absences / Indisponibilités</h1>
        <a href="{{ url('/legacy/indispo_choice.php') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Déclarer une absence
        </a>
    </div>

    {{-- Tab: section vs mine --}}
    <div class="d-flex gap-2 mt-2">
        <a href="{{ route('indispo.index', ['tab' => 'section', 'status' => $status]) }}"
           class="btn btn-sm {{ $tab === 'section' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="fas fa-users me-1"></i> Ma section
        </a>
        <a href="{{ route('indispo.index', ['tab' => 'mine', 'status' => $status]) }}"
           class="btn btn-sm {{ $tab === 'mine' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="fas fa-user me-1"></i> Mes absences
        </a>

        <span class="ms-3">
            <a href="{{ route('indispo.index', ['tab' => $tab, 'status' => 'pending']) }}"
               class="btn btn-sm {{ $status === 'pending' ? 'btn-warning' : 'btn-outline-secondary' }}">En attente</a>
            <a href="{{ route('indispo.index', ['tab' => $tab, 'status' => 'accepted']) }}"
               class="btn btn-sm {{ $status === 'accepted' ? 'btn-success' : 'btn-outline-secondary' }}">Acceptées</a>
            <a href="{{ route('indispo.index', ['tab' => $tab, 'status' => 'all']) }}"
               class="btn btn-sm {{ $status === 'all' ? 'btn-secondary' : 'btn-outline-secondary' }}">Toutes</a>
        </span>
    </div>
</div>

<div class="mx-3 mt-3">
    @if($items->isEmpty())
        <div class="text-muted fst-italic p-3">Aucune indisponibilité.</div>
    @else
        <table class="table table-sm table-hover align-middle">
            <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                <tr>
                    <th>Personnel</th>
                    <th>Type</th>
                    <th>Début</th>
                    <th>Fin</th>
                    <th>Statut</th>
                    <th>Commentaire</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i)
                    <tr>
                        <td style="font-size:var(--font-size-sm)">{{ $i->person_name ?? '—' }}</td>
                        <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                            {{ $i->TI_LIBELLE ?? '—' }}
                        </td>
                        <td style="font-size:var(--font-size-sm)">
                            {{ $i->I_DEBUT ? \Carbon\Carbon::parse($i->I_DEBUT)->format('d/m/Y') : '—' }}
                        </td>
                        <td style="font-size:var(--font-size-sm)">
                            {{ $i->I_FIN ? \Carbon\Carbon::parse($i->I_FIN)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            @if($i->I_ACCEPT == 1)
                                <span class="badge bg-success">Acceptée</span>
                            @elseif($i->I_ACCEPT === null || $i->I_ACCEPT == 0)
                                <span class="badge bg-warning text-dark">En attente</span>
                            @else
                                <span class="badge bg-danger">Refusée</span>
                            @endif
                        </td>
                        <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                            {{ $i->I_COMMENT ?: '' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-2">{{ $items->links() }}</div>
    @endif
</div>

@endsection
