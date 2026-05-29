@extends('layout.app')

@section('title', 'Messages — ' . config('app.name'))

@section('content')

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Tableau d'affichage</h1>
        @if(auth()->user()->hasPermission(44))
            <a href="{{ url('/legacy/mail_create.php') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Nouveau message
            </a>
        @endif
    </div>

    {{-- Category tabs --}}
    <div class="d-flex gap-2 mt-2">
        <a href="{{ route('message.index', ['category' => 'consigne']) }}"
           class="btn btn-sm {{ $category === 'consigne' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="fas fa-clipboard-list me-1"></i> Consignes
        </a>
        <a href="{{ route('message.index', ['category' => 'amicale']) }}"
           class="btn btn-sm {{ $category === 'amicale' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="fas fa-newspaper me-1"></i> Actualités
        </a>
        <a href="{{ route('message.index', ['category' => 'all']) }}"
           class="btn btn-sm {{ $category === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
            Tous
        </a>
    </div>
</div>

<div class="mx-3 mt-3">
    @if($items->isEmpty())
        <div class="text-muted fst-italic p-3">Aucun message.</div>
    @else
        @foreach($items as $msg)
            <div class="widget-card mb-3">
                <div class="widget-card-header">
                    <div class="widget-card-title">
                        <i class="fas {{ $msg->M_TYPE === 'consigne' ? 'fa-clipboard-list' : 'fa-newspaper' }} fa-sm"></i>
                        {{ $msg->M_OBJET ?: '(sans objet)' }}
                    </div>
                    <div style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                        {{ $msg->author ?? '—' }}
                        &mdash;
                        {{ $msg->M_DATE ? \Carbon\Carbon::parse($msg->M_DATE)->locale('fr')->isoFormat('D MMM YYYY') : '' }}
                    </div>
                </div>
                @if($msg->M_TEXTE)
                    <div class="widget-card-body" style="font-size:var(--font-size-sm)">
                        {!! nl2br(e($msg->M_TEXTE)) !!}
                    </div>
                @endif
            </div>
        @endforeach

        <div class="mt-2">{{ $items->links() }}</div>
    @endif
</div>

@endsection
