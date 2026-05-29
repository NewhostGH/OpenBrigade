@extends('layout.app')

@section('title', 'Clients — ' . config('app.name'))

@section('content')

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Clients / Sociétés</h1>
        @if(auth()->user()->hasPermission(29))
            <a href="{{ url('/legacy/ins_company.php') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Nouveau client
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('company.index') }}" class="ob-filters">
        <div>
            <input type="text" name="q" value="{{ $search }}"
                   class="form-control form-control-sm" placeholder="Nom, contact, email…">
        </div>
        <div>
            <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="ALL" @selected($type === 'ALL')>Tous les types</option>
                @foreach($types as $t)
                    <option value="{{ $t->TC_CODE }}" @selected($type === $t->TC_CODE)>
                        {{ $t->TC_LIBELLE }}
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
        <div class="text-muted fst-italic p-3">Aucun client trouvé.</div>
    @else
        <table class="table table-sm table-hover align-middle">
            <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                <tr>
                    <th>Nom</th>
                    <th>Type</th>
                    <th>Ville</th>
                    <th>Téléphone</th>
                    <th>E-mail</th>
                    <th style="width:50px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $c)
                    <tr>
                        <td style="font-size:var(--font-size-sm);font-weight:600">{{ $c->C_NAME }}</td>
                        <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                            {{ $c->TC_LIBELLE ?? $c->TC_CODE ?? '—' }}
                        </td>
                        <td style="font-size:var(--font-size-sm)">
                            {{ $c->C_CITY ? $c->C_CITY . ($c->C_ZIP_CODE ? ' (' . $c->C_ZIP_CODE . ')' : '') : '—' }}
                        </td>
                        <td style="font-size:var(--font-size-sm)">
                            @if($c->C_PHONE)
                                <a href="tel:{{ $c->C_PHONE }}" class="text-decoration-none">{{ $c->C_PHONE }}</a>
                            @else —
                            @endif
                        </td>
                        <td style="font-size:var(--font-size-sm)">
                            @if($c->C_EMAIL)
                                <a href="mailto:{{ $c->C_EMAIL }}" class="text-decoration-none">{{ $c->C_EMAIL }}</a>
                            @else —
                            @endif
                        </td>
                        <td>
                            <a href="{{ url('/legacy/upd_company.php?company=' . $c->C_ID) }}"
                               class="btn btn-sm btn-outline-secondary" title="Modifier">
                                <i class="fas fa-edit fa-xs"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-2">{{ $items->links() }}</div>
    @endif
</div>

@endsection
