@extends('layout.app')

@section('title', 'Trombinoscope — ' . config('app.name'))

@push('styles')
<style>
.trombi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 12px;
}
.trombi-card {
    background: var(--component-bg);
    border: 1px solid var(--component-border);
    border-radius: var(--radius-md);
    overflow: hidden;
    text-align: center;
    padding: 12px 8px 10px;
    text-decoration: none;
    color: inherit;
    transition: box-shadow var(--transition-fast);
}
.trombi-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-decoration: none; color: inherit; }
.trombi-photo {
    width: 80px; height: 80px; border-radius: 50%;
    object-fit: cover; border: 2px solid var(--component-border);
    margin-bottom: 8px;
}
.trombi-name {
    font-size: var(--font-size-sm); font-weight: 600; color: var(--page-text);
    line-height: 1.2; margin-bottom: 2px;
}
.trombi-grade {
    font-size: var(--font-size-xs); color: var(--text-muted-soft);
}
</style>
@endpush

@section('content')

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Trombinoscope</h1>
    </div>

    <form method="GET" action="{{ route('personnel.trombinoscope') }}" class="ob-filters">
        <div>
            <input type="text" name="q" value="{{ $search }}"
                   class="form-control form-control-sm" placeholder="Rechercher un nom…">
        </div>
        <div>
            <select name="section" class="form-select form-select-sm">
                <option value="0" @selected($sectionId === 0)>Ma section</option>
                @foreach($sections as $s)
                    <option value="{{ $s->S_ID }}" @selected($sectionId === $s->S_ID)>
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
        <div class="text-muted fst-italic p-3">Aucun personnel trouvé.</div>
    @else
        <div class="trombi-grid">
            @foreach($items as $p)
                <a href="{{ route('personnel.show', $p->P_ID) }}" class="trombi-card">
                    <img src="{{ route('personnel.photo', $p->P_ID) }}"
                         class="trombi-photo"
                         alt="{{ $p->P_PRENOM }} {{ $p->P_NOM }}"
                         onerror="this.src='{{ asset('images/autre.png') }}'">
                    <div class="trombi-name">
                        {{ ucfirst(strtolower($p->P_PRENOM)) }}<br>
                        {{ strtoupper($p->P_NOM) }}
                    </div>
                    @if($p->P_GRADE)
                        <div class="trombi-grade">{{ $p->P_GRADE }}</div>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="mt-3">{{ $items->links() }}</div>
    @endif
</div>

@endsection
