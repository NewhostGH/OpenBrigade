@if (!empty($infos['consignes']))
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-bullhorn"></i> Consignes opérationnelles
        </div>
        <a class="ob-widget-card-link" href="{{ url('/legacy/tableau_garde.php?tab=2&mode_garde=1') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="ob-widget-card-body">
        @foreach ($infos['consignes'] as $msg)
            <div class="ob-dash-info-row">
                <div class="ob-dash-info-date">{{ $msg->FORMDATE }}</div>
                <div class="ob-dash-info-content">
                    <div class="ob-dash-info-title">
                        <i class="far fa-circle" style="color:{{ $msg->TM_COLOR ?? '#666' }}"></i>
                        {{ $msg->M_OBJET }}
                    </div>
                    <div class="ob-dash-info-body">{!! nl2br(e($msg->M_TEXTE)) !!}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

@if (!empty($infos['actualites']))
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-newspaper"></i> Actualités
        </div>
        <a class="ob-widget-card-link" href="{{ url('/legacy/message.php?catmessage=amicale') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="ob-widget-card-body">
        @foreach ($infos['actualites'] as $msg)
            <div class="ob-dash-info-row">
                <div class="ob-dash-info-date">{{ $msg->FORMDATE }}</div>
                <div class="ob-dash-info-content">
                    <div class="ob-dash-info-title">
                        <i class="far fa-circle" style="color:{{ $msg->TM_COLOR ?? '#666' }}"></i>
                        {{ $msg->M_OBJET }}
                    </div>
                    <div class="ob-dash-info-body">{!! nl2br(e($msg->M_TEXTE)) !!}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

@if (empty($infos['consignes']) && empty($infos['actualites']))
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-newspaper"></i> Informations
        </div>
    </div>
    <div class="ob-widget-card-body">
        <p class="ob-widget-empty">Aucune information en cours.</p>
    </div>
</div>
@endif
