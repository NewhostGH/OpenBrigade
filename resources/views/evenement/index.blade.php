@extends('layout.app')

@section('title', 'Activités — ' . config('app.name'))

@section('content')

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Activités</h1>
        @if(auth()->user()->hasPermission(15))
            <a href="{{ url('/legacy/evenement_edit.php?action=create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Nouvelle activité
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('evenement.index') }}" class="ob-filters">
        {{-- Search --}}
        <div>
            <input type="text" name="q" value="{{ $search }}"
                   class="form-control form-control-sm"
                   placeholder="Rechercher…">
        </div>

        {{-- Period --}}
        <div>
            <select name="period" class="form-select form-select-sm">
                <option value="upcoming" @selected($period === 'upcoming')>À venir</option>
                <option value="past"     @selected($period === 'past')>Passées</option>
                <option value="all"      @selected($period === 'all')>Toutes</option>
            </select>
        </div>

        {{-- Event type --}}
        <div>
            <select name="type" class="form-select form-select-sm">
                <option value="ALL" @selected($type === 'ALL')>Tous les types</option>
                @foreach($types as $t)
                    <option value="{{ $t->TE_CODE }}" @selected($type === $t->TE_CODE)>
                        {{ $t->TE_LIBELLE }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Section --}}
        <div>
            <select name="section" class="form-select form-select-sm">
                <option value="0" @selected($filtSect === 0)>Ma section</option>
                @foreach($sections as $s)
                    <option value="{{ $s->S_ID }}" @selected($filtSect === $s->S_ID)>
                        {{ $s->S_CODE }} — {{ $s->S_DESCRIPTION }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Submit --}}
        <div>
            <button type="submit" class="btn btn-sm btn-secondary w-100">
                <i class="fas fa-filter me-1"></i> Filtrer
            </button>
        </div>
    </form>
</div>

<div class="mx-3 mt-3">
    @if($items->isEmpty())
        <div class="text-muted fst-italic p-3">Aucune activité trouvée.</div>
    @else
        <table class="table table-sm table-hover align-middle">
            <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                <tr>
                    <th style="width:36px"></th>
                    <th>Activité</th>
                    <th>Lieu</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th style="width:60px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $event)
                    <tr>
                        <td class="text-center">
                            <i class="fas fa-{{ $event->TE_ICON ?? 'calendar' }} fa-fw"
                               style="color:var(--text-muted-soft)"
                               title="{{ $event->TE_LIBELLE }}"></i>
                        </td>
                        <td>
                            <a href="{{ route('evenement.show', $event->E_CODE) }}"
                               class="fw-semibold text-decoration-none">
                                {{ $event->E_LIBELLE ?? $event->E_CODE }}
                            </a>
                            <div style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                {{ $event->TE_LIBELLE }}
                            </div>
                        </td>
                        <td style="font-size:var(--font-size-sm)">
                            {{ $event->E_LIEU ?? '—' }}
                        </td>
                        <td style="font-size:var(--font-size-sm);white-space:nowrap">
                            @if($event->first_date)
                                {{ \Carbon\Carbon::parse($event->first_date)->locale('fr')->isoFormat('D MMM YYYY') }}
                                @if($event->first_time)
                                    <span class="text-muted">{{ substr($event->first_time, 0, 5) }}</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($event->E_CANCELED)
                                <span class="badge bg-danger">Annulé</span>
                            @elseif($event->E_CLOSED)
                                <span class="badge bg-secondary">Clôturé</span>
                            @else
                                <span class="badge bg-success">Ouvert</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('evenement.show', $event->E_CODE) }}"
                               class="btn btn-xs btn-outline-secondary btn-sm"
                               title="Voir le détail">
                                <i class="fas fa-eye fa-xs"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-2">
            {{ $items->links() }}
        </div>
    @endif
</div>

@endsection
