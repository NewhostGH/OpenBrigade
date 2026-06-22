@if (!empty($infos['consignes']))
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-bullhorn"></i> {{ __('dashboard.infos.consignes_title') }}
        </div>
        <a class="ob-widget-card-link" href="{{ route('duty.index') }}">
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
            <i class="fas fa-newspaper"></i> {{ __('dashboard.infos.actualites_title') }}
        </div>
        <a class="ob-widget-card-link" href="{{ route('message.index') }}">
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
            <i class="fas fa-newspaper"></i> {{ __('dashboard.infos.fallback_title') }}
        </div>
    </div>
    <div class="ob-widget-card-body">
        <p class="ob-widget-empty">{{ __('dashboard.infos.empty') }}</p>
    </div>
</div>
@endif
