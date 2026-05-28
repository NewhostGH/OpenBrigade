@if (!empty($infos['consignes']))
<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-bullhorn"></i> Consignes opérationnelles
        </div>
        <a class="widget-card-link" href="{{ url('/legacy/tableau_garde.php?tab=2&mode_garde=1') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="widget-card-body">
        @foreach ($infos['consignes'] as $msg)
            <div class="info-row">
                <div class="info-date">{{ $msg->FORMDATE }}</div>
                <div class="info-content">
                    <div class="info-title">
                        <i class="far fa-circle" style="color:{{ $msg->TM_COLOR ?? '#666' }}"></i>
                        {{ $msg->M_OBJET }}
                    </div>
                    <div class="info-body">{!! nl2br(e($msg->M_TEXTE)) !!}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

@if (!empty($infos['actualites']))
<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-newspaper"></i> Actualités
        </div>
        <a class="widget-card-link" href="{{ url('/legacy/message.php?catmessage=amicale') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="widget-card-body">
        @foreach ($infos['actualites'] as $msg)
            <div class="info-row">
                <div class="info-date">{{ $msg->FORMDATE }}</div>
                <div class="info-content">
                    <div class="info-title">
                        <i class="far fa-circle" style="color:{{ $msg->TM_COLOR ?? '#666' }}"></i>
                        {{ $msg->M_OBJET }}
                    </div>
                    <div class="info-body">{!! nl2br(e($msg->M_TEXTE)) !!}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

@if (empty($infos['consignes']) && empty($infos['actualites']))
<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-newspaper"></i> Informations
        </div>
    </div>
    <div class="widget-card-body">
        <p class="widget-empty">Aucune information en cours.</p>
    </div>
</div>
@endif
