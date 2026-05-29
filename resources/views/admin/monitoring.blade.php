@extends('layout.app')

@section('title', 'Monitoring — ' . config('app.name'))

@section('content')

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Journal d'activité</h1>
    </div>

    <form method="GET" action="{{ route('admin.monitoring') }}" class="ob-filters">
        <div>
            <input type="text" name="q" value="{{ $search }}"
                   class="form-control form-control-sm" placeholder="Rechercher…">
        </div>
        <div>
            <select name="type" class="form-select form-select-sm">
                <option value="ALL" @selected($ltCode === 'ALL')>Tous les types</option>
                @foreach($logTypes as $t)
                    <option value="{{ $t->LT_CODE }}" @selected($ltCode === $t->LT_CODE)>
                        {{ $t->LT_LIBELLE }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-sm btn-secondary w-100">
                <i class="fas fa-filter me-1"></i> Filtrer
            </button>
        </div>
    </form>
</div>

<div class="mx-3 mt-3">
    @if($items->isEmpty())
        <div class="text-muted fst-italic p-3">Aucune entrée dans le journal.</div>
    @else
        <table class="table table-sm table-hover align-middle">
            <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                <tr>
                    <th>Date</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Détail</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $log)
                    <tr>
                        <td style="font-size:var(--font-size-xs);white-space:nowrap;color:var(--text-muted-soft)">
                            {{ $log->LH_STAMP ? \Carbon\Carbon::parse($log->LH_STAMP)->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td style="font-size:var(--font-size-sm)">{{ $log->actor ?? '—' }}</td>
                        <td style="font-size:var(--font-size-xs)">
                            <span class="badge bg-secondary">{{ $log->LT_LIBELLE ?? $log->LT_CODE }}</span>
                        </td>
                        <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                            {{ $log->LH_COMPLEMENT ?? '' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-2">{{ $items->links() }}</div>
    @endif
</div>

@endsection
