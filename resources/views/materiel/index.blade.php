@extends('layout.app')

@section('title', 'Matériels — ' . config('app.name'))

@section('content')

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Matériels</h1>
        @if(auth()->user()->hasPermission(70))
            <a href="{{ url('/legacy/ins_materiel.php') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Nouveau matériel
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('materiel.index') }}" class="ob-filters">
        <div>
            <input type="text" name="q" value="{{ $search }}"
                   class="form-control form-control-sm" placeholder="Modèle, n° série…">
        </div>
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
        <div>
            <button type="submit" class="btn btn-sm btn-secondary w-100">
                <i class="fas fa-filter me-1"></i> Filtrer
            </button>
        </div>
    </form>
</div>

<div class="mx-3 mt-3">
    @if($items->isEmpty())
        <div class="text-muted fst-italic p-3">Aucun matériel trouvé.</div>
    @else
        <table class="table table-sm table-hover align-middle">
            <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                <tr>
                    <th>Type</th>
                    <th>Modèle</th>
                    <th>N° série</th>
                    <th>Lieu</th>
                    <th>Révision</th>
                    <th>Qté</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $m)
                    @php $revWarn = $m->MA_REV_DATE && $m->MA_REV_DATE <= now()->addDays(30)->toDateString(); @endphp
                    <tr>
                        <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                            {{ $m->TM_LIBELLE ?? '—' }}
                        </td>
                        <td style="font-size:var(--font-size-sm);font-weight:600">
                            {{ $m->MA_MODELE ?: '—' }}
                        </td>
                        <td style="font-size:var(--font-size-xs)">{{ $m->MA_NUMERO_SERIE ?: '—' }}</td>
                        <td style="font-size:var(--font-size-xs)">{{ $m->MA_LIEU_STOCKAGE ?: '—' }}</td>
                        <td style="font-size:var(--font-size-xs){{ $revWarn ? ';color:#c0392b;font-weight:600' : '' }}">
                            {{ $m->MA_REV_DATE ? \Carbon\Carbon::parse($m->MA_REV_DATE)->format('d/m/Y') : '—' }}
                            @if($revWarn) <i class="fas fa-exclamation-circle ms-1"></i> @endif
                        </td>
                        <td style="font-size:var(--font-size-sm)">{{ $m->MA_NB ?? 1 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-2">{{ $items->links() }}</div>
    @endif
</div>

@endsection
