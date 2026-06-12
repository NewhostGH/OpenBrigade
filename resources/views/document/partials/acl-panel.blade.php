{{-- ACL management panel — rendered standalone (full page) and injected into the
     in-page "Partager" modal via AJAX. Forms are marked data-acl-form so the
     modal can submit them without leaving the page.
     Vars: $type, $id, $name, $aces, $groups, $roles, $people, $groupNames,
           $peopleNames, $rightLabels --}}
<div data-acl-panel>
    <p class="text-muted mb-2" style="font-size:var(--font-size-sm);">
        <i class="fas fa-{{ $type === 'folder' ? 'folder' : 'file' }} me-1 text-secondary"></i>
        <strong>{{ $name }}</strong> — les dossiers transmettent leurs autorisations à leur contenu ;
        un <strong>refus</strong> l'emporte toujours.
    </p>

    @if (session('success'))
        <div class="alert alert-success py-1 px-2 mb-2" style="font-size:var(--font-size-sm);">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger py-1 px-2 mb-2" style="font-size:var(--font-size-sm);">{{ session('error') }}</div>
    @endif

    {{-- Current ACEs --}}
    @if ($aces->isEmpty())
        <div class="text-muted mb-3" style="font-size:var(--font-size-sm);">
            Aucune autorisation propre — la sécurité de section / type s'applique.
        </div>
    @else
        <table class="table table-sm align-middle mb-3" style="font-size:var(--font-size-sm);">
            <thead>
                <tr><th>Bénéficiaire</th><th>Effet</th><th>Droits</th><th style="width:40px;"></th></tr>
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
                            <form method="POST" action="{{ route('document.acl.destroy', $ace->id) }}" data-acl-form data-confirm="Retirer cette autorisation ?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Retirer"><i class="fas fa-trash fa-xs"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Add an ACE --}}
    <form method="POST" action="{{ route('document.acl.store', [$type, $id]) }}" class="row g-2" data-acl-form data-acl-add>
        @csrf
        <div class="col-md-4">
            <label class="form-label" for="aclPrincipalType">Bénéficiaire</label>
            <select id="aclPrincipalType" name="principal_type" class="form-select form-select-sm" data-acl-principal-type>
                <option value="user">Utilisateur</option>
                <option value="group">Groupe</option>
                <option value="role">Rôle</option>
                <option value="everyone">Tout le monde</option>
            </select>
        </div>
        <div class="col-md-5">
            <div data-acl-pp="user">
                <label class="form-label" for="aclUser">Personne</label>
                <select id="aclUser" name="user_id" class="form-select form-select-sm">
                    @foreach ($people as $p)
                        <option value="{{ $p->P_ID }}">{{ $p->P_NOM }} {{ $p->P_PRENOM }}</option>
                    @endforeach
                </select>
            </div>
            <div data-acl-pp="group" class="d-none">
                <label class="form-label" for="aclGroup">Groupe</label>
                <select id="aclGroup" name="group_id" class="form-select form-select-sm">
                    @foreach ($groups as $g)<option value="{{ $g->id }}">{{ $g->name }}</option>@endforeach
                </select>
            </div>
            <div data-acl-pp="role" class="d-none">
                <label class="form-label" for="aclRole">Rôle</label>
                <select id="aclRole" name="role_id" class="form-select form-select-sm">
                    @foreach ($roles as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach
                </select>
            </div>
            <div data-acl-pp="everyone" class="d-none">
                <label class="form-label">&nbsp;</label>
                <p class="form-text mb-0">Toute personne ayant accès à la bibliothèque.</p>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="aclEffect">Effet</label>
            <select id="aclEffect" name="effect" class="form-select form-select-sm">
                <option value="allow">Autorise</option>
                <option value="deny">Refuse</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label d-block mb-1">Droits</label>
            @foreach ($rightLabels as $bit => $rl)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="rights[]" value="{{ $bit }}" id="aclRight{{ $bit }}">
                    <label class="form-check-label" for="aclRight{{ $bit }}">{{ $rl }}</label>
                </div>
            @endforeach
        </div>
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-sm btn-primary">Ajouter l'autorisation</button>
        </div>
    </form>
</div>
