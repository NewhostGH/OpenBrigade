@php
    $fsLabels = [
        'ATTV' => ['label' => 'En attente', 'class' => 'badge-warning'],
        'VAL'  => ['label' => 'Validé',     'class' => 'badge-info'],
        'VAL1' => ['label' => 'Validé N1',  'class' => 'badge-info'],
        'VAL2' => ['label' => 'Validé N2',  'class' => 'badge-info'],
    ];
@endphp

@if (!empty($expenses['rows']))
<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-receipt"></i> Notes de frais
        </div>
        <a class="widget-card-link" href="{{ url('/legacy/note_frais_edit.php') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="widget-card-body">
        @foreach ($expenses['rows'] as $row)
            @php
                $fs = $fsLabels[$row->FS_CODE] ?? ['label' => $row->FS_CODE, 'class' => 'badge-warning'];
            @endphp
            <div class="alert-item-row">
                <div class="alert-item-info">
                    @if ($expenses['isManager'])
                        <div class="alert-item-label">
                            {{ $row->P_NOM }} {{ $row->P_PRENOM }}
                        </div>
                    @endif
                    <div class="alert-item-sub">
                        <a href="{{ url('/legacy/note_frais_edit.php?action=update&nfid=' . $row->NF_ID) }}"
                           style="color:inherit">Note #{{ $row->NF_ID }}</a>
                        &mdash;
                        @if(!empty($row->NF_CREATE_DATE))
                            {{ \Carbon\Carbon::parse($row->NF_CREATE_DATE)->format('d-m-Y') }}
                        @endif
                        @if(!empty($row->TOTAL_AMOUNT))
                            &mdash; {{ number_format($row->TOTAL_AMOUNT, 2) }} €
                        @endif
                    </div>
                </div>
                <span class="alert-badge {{ $fs['class'] }}">{{ $fs['label'] }}</span>
            </div>
        @endforeach
    </div>
</div>
@else
<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-receipt"></i> Notes de frais
        </div>
        <a class="widget-card-link" href="{{ url('/legacy/note_frais_edit.php') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="widget-card-body">
        <p class="widget-empty">Aucune note de frais à traiter.</p>
    </div>
</div>
@endif
