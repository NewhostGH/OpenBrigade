@extends($layout)

@section('title', 'Partage — ' . config('app.name'))

@section('content')

@unless ($isWindow)
    <x-ob-breadcrumb :items="[
        ['label' => __('document.title'), 'url' => route('document.index')],
        ['label' => __('document.acl_page_title')],
    ]"/>
@endunless

<div class="{{ $isWindow ? '' : 'mx-3 mt-2' }}" style="max-width:900px;">

    <h1 class="ob-toolbar-heading mb-1">
        <i class="fas fa-{{ $type === 'folder' ? 'folder' : 'file' }} me-2 text-secondary"></i>{{ $name }}
    </h1>
    <p class="text-muted" style="font-size:var(--font-size-sm);">
        {{ __('document.acl_desc', ['target' => $type === 'folder' ? __('document.acl_desc_folder') : __('document.acl_desc_doc')]) }}
    </p>

    {{-- Current ACEs --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-user-lock me-2"></i>{{ __('document.acl_card_title') }}</div>
        </div>
        <div class="ob-widget-card-body p-0">
            @if ($aces->isEmpty())
                <div class="p-3 text-muted" style="font-size:var(--font-size-sm);">
                    {{ __('document.acl_empty') }}
                </div>
            @else
                <table class="table table-sm align-middle mb-0" style="font-size:var(--font-size-sm);">
                    <thead>
                        <tr>
                            <th>{{ __('document.acl_th_beneficiary') }}</th>
                            <th>{{ __('document.acl_th_effect') }}</th>
                            <th>{{ __('document.acl_th_rights') }}</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($aces as $ace)
                            @php
                                $label = match ($ace->principal_type) {
                                    'everyone' => __('document.acl_opt_everyone'),
                                    'user' => $peopleNames[$ace->principal_id] ?? __('document.acl_label_person_id', ['id' => $ace->principal_id]),
                                    default => $groupNames[$ace->principal_id] ?? ('#'.$ace->principal_id),
                                };
                                $icon = ['user' => 'user', 'group' => 'users', 'role' => 'user-tie', 'everyone' => 'globe'][$ace->principal_type] ?? 'user';
                            @endphp
                            <tr>
                                <td><i class="fas fa-{{ $icon }} fa-fw me-1 text-muted"></i>{{ $label }}</td>
                                <td>
                                    <span class="ob-badge {{ $ace->effect === 'deny' ? 'ob-badge-bloqued' : 'ob-badge-actif' }}">
                                        {{ $ace->effect === 'deny' ? __('document.acl_effect_deny') : __('document.acl_effect_allow') }}
                                    </span>
                                </td>
                                <td>
                                    @foreach ($rightLabels as $bit => $rl)
                                        @if ($ace->rights & $bit)
                                            <span class="ob-badge ob-badge-archive me-1" style="font-size:9px;">{{ $rl }}</span>
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('document.acl.destroy', $ace->id) }}" data-acl-delete>
                                        @csrf
                                        @method('DELETE')
                                        @if ($isWindow)<input type="hidden" name="window" value="1">@endif
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="{{ __('document.acl_btn_remove') }}">
                                            <i class="fas fa-trash fa-xs"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Add an ACE --}}
    <div class="ob-widget-card">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>{{ __('document.acl_add_card_title') }}</div>
        </div>
        <div class="ob-widget-card-body">
            <form method="POST" action="{{ route('document.acl.store', [$type, $id]) }}" class="row g-3">
                @csrf
                @if ($isWindow)<input type="hidden" name="window" value="1">@endif

                <div class="col-md-4">
                    <label class="form-label" for="aclPrincipalType">{{ __('document.acl_label_beneficiary') }}</label>
                    <select id="aclPrincipalType" name="principal_type" class="form-select" data-acl-principal-type>
                        <option value="user">{{ __('document.acl_opt_user') }}</option>
                        <option value="group">{{ __('document.acl_opt_group') }}</option>
                        <option value="role">{{ __('document.acl_opt_role') }}</option>
                        <option value="everyone">{{ __('document.acl_opt_everyone') }}</option>
                    </select>
                </div>

                <div class="col-md-5">
                    <div data-acl-pp="user">
                        <label class="form-label" for="aclUser">{{ __('document.acl_label_person') }}</label>
                        <select id="aclUser" name="user_id" class="form-select">
                            @foreach ($people as $p)
                                <option value="{{ $p->P_ID }}">{{ $p->P_NOM }} {{ $p->P_PRENOM }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div data-acl-pp="group" class="d-none">
                        <label class="form-label" for="aclGroup">{{ __('document.acl_label_group') }}</label>
                        <select id="aclGroup" name="group_id" class="form-select">
                            @foreach ($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div data-acl-pp="role" class="d-none">
                        <label class="form-label" for="aclRole">{{ __('document.acl_label_role') }}</label>
                        <select id="aclRole" name="role_id" class="form-select">
                            @foreach ($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div data-acl-pp="everyone" class="d-none">
                        <label class="form-label">&nbsp;</label>
                        <p class="form-text mb-0">{{ __('document.acl_everyone_note') }}</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="aclEffect">{{ __('document.acl_label_effect') }}</label>
                    <select id="aclEffect" name="effect" class="form-select">
                        <option value="allow">{{ __('document.acl_opt_allow') }}</option>
                        <option value="deny">{{ __('document.acl_opt_deny') }}</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label d-block">{{ __('document.acl_label_rights') }}</label>
                    @foreach ($rightLabels as $bit => $rl)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="rights[]" value="{{ $bit }}" id="aclRight{{ $bit }}">
                            <label class="form-check-label" for="aclRight{{ $bit }}">{{ $rl }}</label>
                        </div>
                    @endforeach
                </div>

                <div class="col-12">
                    @if ($isWindow)
                        <button type="button" class="btn btn-sm btn-secondary"
                                onclick="window.parent.postMessage('acl:close', window.location.origin)">{{ __('common.close') }}</button>
                    @else
                        <a href="{{ route('document.index') }}" class="btn btn-sm btn-secondary">{{ __('common.back') }}</a>
                    @endif
                    <button type="submit" class="btn btn-sm btn-primary">{{ __('document.acl_btn_add') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var sel = document.querySelector('[data-acl-principal-type]');
    var blocks = document.querySelectorAll('[data-acl-pp]');
    function sync() {
        blocks.forEach(function (b) { b.classList.toggle('d-none', b.dataset.aclPp !== sel.value); });
    }
    if (sel) { sel.addEventListener('change', sync); sync(); }

    document.querySelectorAll('[data-acl-delete]').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            if (!window.confirm('{{ __('document.acl_confirm_remove') }}')) { e.preventDefault(); }
        });
    });
});
</script>
@endpush
