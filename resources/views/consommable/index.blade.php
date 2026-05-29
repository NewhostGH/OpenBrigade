@extends('layout.app')

@section('title', 'Consommables — ' . config('app.name'))

@section('content')

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Consommables</h1>
        @if(auth()->user()->hasPermission(71))
            <a href="{{ url('/legacy/upd_consommable.php?action=insert') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Nouveau consommable
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('consommable.index') }}" class="ob-filters">
        <div>
            <input type="text" name="q" value="{{ $search }}"
                   class="form-control form-control-sm" placeholder="Description, type…">
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
            <div class="form-check mt-1">
                <input type="checkbox" class="form-check-input" id="alertOnly" name="alert"
                       value="1" @checked($alert)
                       onchange="this.form.submit()">
                <label class="form-check-label" for="alertOnly" style="font-size:var(--font-size-sm)">
                    Alertes seulement
                </label>
            </div>
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
        <div class="text-muted fst-italic p-3">Aucun consommable trouvé.</div>
    @else
        <table class="table table-sm table-hover align-middle">
            <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                <tr>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Qté / Min</th>
                    <th>Lieu</th>
                    <th>Péremption</th>
                    <th style="width:80px">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $c)
                    <tr>
                        <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                            {{ $c->TC_LIBELLE ?? '—' }}
                        </td>
                        <td style="font-size:var(--font-size-sm);font-weight:600">
                            {{ $c->C_DESCRIPTION ?: '—' }}
                        </td>
                        <td style="font-size:var(--font-size-sm)">
                            @php $low = $c->C_MINIMUM > 0 && $c->C_NOMBRE < $c->C_MINIMUM; @endphp
                            <span class="{{ $low ? 'text-danger fw-semibold' : '' }}">
                                {{ $c->C_NOMBRE }}
                            </span>
                            @if($c->C_MINIMUM > 0)
                                <span class="text-muted">/ {{ $c->C_MINIMUM }}</span>
                            @endif
                        </td>
                        <td style="font-size:var(--font-size-xs)">{{ $c->C_LIEU_STOCKAGE ?: '—' }}</td>
                        <td style="font-size:var(--font-size-xs){{ $c->alert_level === 'expired' ? ';color:#c0392b;font-weight:600' : ($c->alert_level === 'expiring' ? ';color:#e67e22;font-weight:600' : '') }}">
                            {{ $c->C_DATE_PEREMPTION ? \Carbon\Carbon::parse($c->C_DATE_PEREMPTION)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            @if($c->alert_level === 'expired')
                                <span class="badge bg-danger">Périmé</span>
                            @elseif($c->alert_level === 'expiring')
                                <span class="badge bg-warning text-dark">Bientôt</span>
                            @elseif($c->alert_level === 'low')
                                <span class="badge bg-warning text-dark">Stock bas</span>
                            @else
                                <span class="badge bg-success">OK</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-2">{{ $items->links() }}</div>
    @endif
</div>

@endsection
