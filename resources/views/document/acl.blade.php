@extends('layout.app')

@section('title', 'Partage — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Documents', 'url' => route('document.index')],
    ['label' => 'Partage'],
]"/>

<div class="mx-3 mt-2" style="max-width:900px;">

    <h1 class="ob-toolbar-heading mb-1">
        <i class="fas fa-{{ $type === 'folder' ? 'folder' : 'file' }} me-2 text-secondary"></i>{{ $name }}
    </h1>
    <p class="text-muted" style="font-size:var(--font-size-sm);">
        Autorisations propres à {{ $type === 'folder' ? 'ce dossier' : 'ce document' }}. Les dossiers
        transmettent leurs autorisations à leur contenu ; un <strong>refus</strong> l'emporte toujours.
    </p>

    {{-- Current ACEs --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-user-lock me-2"></i>Autorisations</div>
        </div>
        <div class="ob-widget-card-body p-0">
            @if ($aces->isEmpty())
                <div class="p-3 text-muted" style="font-size:var(--font-size-sm);">
                    Aucune autorisation propre — la sécurité de section / type s'applique.
                </div>
            @else
                <table class="table table-sm align-middle mb-0" style="font-size:var(--font-size-sm);">
                    <thead>
                        <tr>
                            <th>Bénéficiaire</th>
                            <th>Effet</th>
                            <th>Droits</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($aces as $ace)
                            @php
                                $label = match ($ace->principal_type) {
                                    'everyone' => 'Tout le monde',
                                    'user' => $peopleNames[$ace->principal_id] ?? ('Personne #'.$ace->principal_id),
                                    default => $groupNames[$ace->principal_id] ?? ('#'.$ace->principal_id),
                                };
                                $icon = ['user' => 'user', 'group' => 'users', 'role' => 'user-tie', 'everyone' => 'globe'][$ace->principal_type] ?? 'user';
                            @endphp
                            <tr>
                                <td><i class="fas fa-{{ $icon }} fa-fw me-1 text-muted"></i>{{ $label }}</td>
                                <td>
                                    <span class="ob-badge {{ $ace->effect === 'deny' ? 'ob-badge-bloqued' : 'ob-badge-actif' }}">
                                        {{ $ace->effect === 'deny' ? 'Refus' : 'Autorise' }}
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
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Retirer">
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
            <div class="ob-widget-card-title"><i class="fas fa-plus me-2"></i>Ajouter une autorisation</div>
        </div>
        <div class="ob-widget-card-body">
            <form method="POST" action="{{ route('document.acl.store', [$type, $id]) }}" class="row g-3">
                @csrf

                <div class="col-md-4">
                    <label class="form-label" for="aclPrincipalType">Bénéficiaire</label>
                    <select id="aclPrincipalType" name="principal_type" class="form-select" data-acl-principal-type>
                        <option value="user">Utilisateur</option>
                        <option value="group">Groupe</option>
                        <option value="role">Rôle</option>
                        <option value="everyone">Tout le monde</option>
                    </select>
                </div>

                <div class="col-md-5">
                    <div data-acl-pp="user">
                        <label class="form-label" for="aclUser">Personne</label>
                        <select id="aclUser" name="user_id" class="form-select">
                            @foreach ($people as $p)
                                <option value="{{ $p->P_ID }}">{{ $p->P_NOM }} {{ $p->P_PRENOM }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div data-acl-pp="group" class="d-none">
                        <label class="form-label" for="aclGroup">Groupe</label>
                        <select id="aclGroup" name="group_id" class="form-select">
                            @foreach ($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div data-acl-pp="role" class="d-none">
                        <label class="form-label" for="aclRole">Rôle</label>
                        <select id="aclRole" name="role_id" class="form-select">
                            @foreach ($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div data-acl-pp="everyone" class="d-none">
                        <label class="form-label">&nbsp;</label>
                        <p class="form-text mb-0">S'applique à toute personne ayant accès à la bibliothèque.</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="aclEffect">Effet</label>
                    <select id="aclEffect" name="effect" class="form-select">
                        <option value="allow">Autorise</option>
                        <option value="deny">Refuse</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label d-block">Droits</label>
                    @foreach ($rightLabels as $bit => $rl)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="rights[]" value="{{ $bit }}" id="aclRight{{ $bit }}">
                            <label class="form-check-label" for="aclRight{{ $bit }}">{{ $rl }}</label>
                        </div>
                    @endforeach
                </div>

                <div class="col-12">
                    <a href="{{ route('document.index') }}" class="btn btn-sm btn-secondary">Retour</a>
                    <button type="submit" class="btn btn-sm btn-primary">Ajouter</button>
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
            if (!window.confirm('Retirer cette autorisation ?')) { e.preventDefault(); }
        });
    });
});
</script>
@endpush
