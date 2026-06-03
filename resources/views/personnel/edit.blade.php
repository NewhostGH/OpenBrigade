@extends('layout.app')

@section('title', 'Édition — ' . $personnel->P_NOM . ' ' . $personnel->P_PRENOM . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Personnel', 'url' => route('personnel.index')],
    ['label' => $personnel->P_NOM . ' ' . $personnel->P_PRENOM, 'url' => route('personnel.show', $personnel)],
    ['label' => 'Modifier'],
]"/>

<div class="container-fluid px-3 py-3" style="max-width: 960px;">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('personnel.update', $personnel) }}"
          enctype="multipart/form-data" id="editForm">
        @csrf
        @method('PATCH')

        <div class="d-flex gap-4 align-items-flex-start flex-wrap">

            {{-- ── Photo column ──────────────────────────────────── --}}
            <div style="flex: 0 0 130px;">
                <div style="width:120px; height:120px; border-radius:var(--radius-md); overflow:hidden;
                            border:2px solid var(--component-border); cursor:pointer; position:relative;
                            background:var(--table-odd-row);"
                     onclick="document.getElementById('photo_upload').click()">
                    <img id="photoPreview"
                         src="{{ route('personnel.photo', $personnel) }}"
                         alt="Photo"
                         style="width:100%; height:100%; object-fit:cover; display:block;">
                    <div id="photoOverlay"
                         style="position:absolute; inset:0; background:rgba(0,0,0,.45); color:#fff;
                                font-size:11px; display:flex; flex-direction:column;
                                align-items:center; justify-content:center; gap:4px;
                                opacity:0; transition:opacity .15s;">
                        <i class="fas fa-camera fa-lg"></i><span>Changer</span>
                    </div>
                </div>
                <input type="file" id="photo_upload" name="photo_upload" accept="image/*" class="d-none"
                       onchange="previewPhoto(this)">
                <p class="text-center mt-1" style="font-size:0.7rem; color:var(--text-muted-soft);">
                    Cliquer pour changer<br>JPG/PNG · max 4 Mo
                </p>
                @error('photo_upload')
                    <p class="text-danger" style="font-size:0.75rem;">{{ $message }}</p>
                @enderror
            </div>

            {{-- ── Form column ───────────────────────────────────── --}}
            <div style="flex: 1 1 500px; min-width: 0;">

                {{-- Tabs --}}
                <ul class="nav nav-tabs" id="editTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab"
                                data-bs-target="#tab-identite" type="button">
                            <i class="fas fa-user me-1"></i> Identité
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab"
                                data-bs-target="#tab-contact" type="button">
                            <i class="fas fa-address-card me-1"></i> Contact
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab"
                                data-bs-target="#tab-urgence" type="button">
                            <i class="fas fa-phone-alt me-1"></i> Urgence
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab"
                                data-bs-target="#tab-autres" type="button">
                            <i class="fas fa-info-circle me-1"></i> Autres
                        </button>
                    </li>
                </ul>

                <div class="tab-content pt-3" id="editTabContent">

                    {{-- ── Tab: Identité ─────────────────────────── --}}
                    <div class="tab-pane fade show active" id="tab-identite">
                        <div class="row g-2">

                            <div class="col-md-2">
                                <label class="form-label form-label-sm" for="P_CIVILITE">Civilité</label>
                                <select id="P_CIVILITE" name="P_CIVILITE"
                                        class="form-select form-select-sm @error('P_CIVILITE') is-invalid @enderror">
                                    <option value="">—</option>
                                    <option value="1" @selected((string)old('P_CIVILITE',$personnel->P_CIVILITE)==='1')>M.</option>
                                    <option value="2" @selected((string)old('P_CIVILITE',$personnel->P_CIVILITE)==='2')>Mme</option>
                                    <option value="3" @selected((string)old('P_CIVILITE',$personnel->P_CIVILITE)==='3')>Dr.</option>
                                    <option value="4" @selected((string)old('P_CIVILITE',$personnel->P_CIVILITE)==='4')>Pr.</option>
                                </select>
                                @error('P_CIVILITE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label form-label-sm" for="P_PRENOM">Prénom *</label>
                                <input id="P_PRENOM" name="P_PRENOM" type="text"
                                       class="form-control form-control-sm @error('P_PRENOM') is-invalid @enderror"
                                       value="{{ old('P_PRENOM', $personnel->P_PRENOM) }}" required>
                                @error('P_PRENOM')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_PRENOM2">Prénom 2</label>
                                <input id="P_PRENOM2" name="P_PRENOM2" type="text"
                                       class="form-control form-control-sm @error('P_PRENOM2') is-invalid @enderror"
                                       value="{{ old('P_PRENOM2', $personnel->P_PRENOM2) }}">
                                @error('P_PRENOM2')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_SEXE">Sexe</label>
                                <select id="P_SEXE" name="P_SEXE"
                                        class="form-select form-select-sm @error('P_SEXE') is-invalid @enderror">
                                    <option value="">—</option>
                                    <option value="M" @selected(old('P_SEXE',$personnel->P_SEXE)==='M')>Masculin</option>
                                    <option value="F" @selected(old('P_SEXE',$personnel->P_SEXE)==='F')>Féminin</option>
                                </select>
                                @error('P_SEXE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label form-label-sm" for="P_NOM">Nom *</label>
                                <input id="P_NOM" name="P_NOM" type="text"
                                       class="form-control form-control-sm @error('P_NOM') is-invalid @enderror"
                                       value="{{ old('P_NOM', $personnel->P_NOM) }}" required>
                                @error('P_NOM')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label form-label-sm" for="P_NOM_NAISSANCE">Nom de naissance</label>
                                <input id="P_NOM_NAISSANCE" name="P_NOM_NAISSANCE" type="text"
                                       class="form-control form-control-sm @error('P_NOM_NAISSANCE') is-invalid @enderror"
                                       value="{{ old('P_NOM_NAISSANCE', $personnel->P_NOM_NAISSANCE) }}">
                                @error('P_NOM_NAISSANCE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_CODE">Matricule *</label>
                                <input id="P_CODE" name="P_CODE" type="text"
                                       class="form-control form-control-sm @error('P_CODE') is-invalid @enderror"
                                       value="{{ old('P_CODE', $personnel->P_CODE) }}" required>
                                @error('P_CODE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_GRADE">Grade</label>
                                <input id="P_GRADE" name="P_GRADE" type="text"
                                       class="form-control form-control-sm @error('P_GRADE') is-invalid @enderror"
                                       value="{{ old('P_GRADE', $personnel->P_GRADE) }}">
                                @error('P_GRADE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_STATUT">Statut *</label>
                                <select id="P_STATUT" name="P_STATUT"
                                        class="form-select form-select-sm @error('P_STATUT') is-invalid @enderror" required>
                                    @foreach (['BEN' => 'Bénévole', 'EXT' => 'Externe', 'PRES' => 'Prestataire', 'SAL' => 'Salarié', 'ADH' => 'Adhérent'] as $val => $lbl)
                                        <option value="{{ $val }}" @selected(old('P_STATUT',$personnel->P_STATUT)===$val)>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                                @error('P_STATUT')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_PROFESSION">Profession</label>
                                <input id="P_PROFESSION" name="P_PROFESSION" type="text"
                                       class="form-control form-control-sm @error('P_PROFESSION') is-invalid @enderror"
                                       value="{{ old('P_PROFESSION', $personnel->P_PROFESSION) }}">
                                @error('P_PROFESSION')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label form-label-sm" for="P_SECTION">Section</label>
                                <select id="P_SECTION" name="P_SECTION"
                                        class="form-select form-select-sm @error('P_SECTION') is-invalid @enderror">
                                    <option value="">—</option>
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->S_ID }}"
                                                @selected((string)old('P_SECTION',$personnel->P_SECTION)===(string)$section->S_ID)>
                                            {{ $section->S_CODE }}{{ $section->S_DESCRIPTION ? ' — '.$section->S_DESCRIPTION : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('P_SECTION')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_DATE_ENGAGEMENT">Date d'entrée</label>
                                <input id="P_DATE_ENGAGEMENT" name="P_DATE_ENGAGEMENT" type="date"
                                       class="form-control form-control-sm @error('P_DATE_ENGAGEMENT') is-invalid @enderror"
                                       value="{{ old('P_DATE_ENGAGEMENT', $personnel->P_DATE_ENGAGEMENT?->format('Y-m-d')) }}">
                                @error('P_DATE_ENGAGEMENT')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_FIN">Date de fin</label>
                                <input id="P_FIN" name="P_FIN" type="date"
                                       class="form-control form-control-sm @error('P_FIN') is-invalid @enderror"
                                       value="{{ old('P_FIN', $personnel->P_FIN?->format('Y-m-d')) }}">
                                @error('P_FIN')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                        </div>
                    </div>

                    {{-- ── Tab: Contact ───────────────────────────── --}}
                    <div class="tab-pane fade" id="tab-contact">
                        <div class="row g-2">

                            <div class="col-md-6">
                                <label class="form-label form-label-sm" for="P_EMAIL">Email</label>
                                <input id="P_EMAIL" name="P_EMAIL" type="email"
                                       class="form-control form-control-sm @error('P_EMAIL') is-invalid @enderror"
                                       value="{{ old('P_EMAIL', $personnel->P_EMAIL) }}">
                                @error('P_EMAIL')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_PHONE">Téléphone</label>
                                <input id="P_PHONE" name="P_PHONE" type="text"
                                       class="form-control form-control-sm @error('P_PHONE') is-invalid @enderror"
                                       value="{{ old('P_PHONE', $personnel->P_PHONE) }}">
                                @error('P_PHONE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_PHONE2">Portable</label>
                                <input id="P_PHONE2" name="P_PHONE2" type="text"
                                       class="form-control form-control-sm @error('P_PHONE2') is-invalid @enderror"
                                       value="{{ old('P_PHONE2', $personnel->P_PHONE2) }}">
                                @error('P_PHONE2')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label form-label-sm" for="P_ADDRESS">Adresse</label>
                                <input id="P_ADDRESS" name="P_ADDRESS" type="text"
                                       class="form-control form-control-sm @error('P_ADDRESS') is-invalid @enderror"
                                       value="{{ old('P_ADDRESS', $personnel->P_ADDRESS) }}">
                                @error('P_ADDRESS')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label form-label-sm" for="P_ZIP_CODE">Code postal</label>
                                <input id="P_ZIP_CODE" name="P_ZIP_CODE" type="text"
                                       class="form-control form-control-sm @error('P_ZIP_CODE') is-invalid @enderror"
                                       value="{{ old('P_ZIP_CODE', $personnel->P_ZIP_CODE) }}">
                                @error('P_ZIP_CODE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label form-label-sm" for="P_CITY">Ville</label>
                                <input id="P_CITY" name="P_CITY" type="text"
                                       class="form-control form-control-sm @error('P_CITY') is-invalid @enderror"
                                       value="{{ old('P_CITY', $personnel->P_CITY) }}">
                                @error('P_CITY')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label form-label-sm" for="P_PAYS">Pays</label>
                                <input id="P_PAYS" name="P_PAYS" type="text"
                                       class="form-control form-control-sm @error('P_PAYS') is-invalid @enderror"
                                       placeholder="France"
                                       value="{{ old('P_PAYS', $personnel->P_PAYS) }}">
                                @error('P_PAYS')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                        </div>
                    </div>

                    {{-- ── Tab: Urgence ────────────────────────────── --}}
                    <div class="tab-pane fade" id="tab-urgence">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Personne à contacter en cas d'urgence.
                        </p>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label form-label-sm" for="P_RELATION_PRENOM">Prénom</label>
                                <input id="P_RELATION_PRENOM" name="P_RELATION_PRENOM" type="text"
                                       class="form-control form-control-sm @error('P_RELATION_PRENOM') is-invalid @enderror"
                                       value="{{ old('P_RELATION_PRENOM', $personnel->P_RELATION_PRENOM) }}">
                                @error('P_RELATION_PRENOM')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-sm" for="P_RELATION_NOM">Nom</label>
                                <input id="P_RELATION_NOM" name="P_RELATION_NOM" type="text"
                                       class="form-control form-control-sm @error('P_RELATION_NOM') is-invalid @enderror"
                                       value="{{ old('P_RELATION_NOM', $personnel->P_RELATION_NOM) }}">
                                @error('P_RELATION_NOM')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-sm" for="P_RELATION_PHONE">Téléphone</label>
                                <input id="P_RELATION_PHONE" name="P_RELATION_PHONE" type="text"
                                       class="form-control form-control-sm @error('P_RELATION_PHONE') is-invalid @enderror"
                                       value="{{ old('P_RELATION_PHONE', $personnel->P_RELATION_PHONE) }}">
                                @error('P_RELATION_PHONE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label form-label-sm" for="P_RELATION_MAIL">Email</label>
                                <input id="P_RELATION_MAIL" name="P_RELATION_MAIL" type="email"
                                       class="form-control form-control-sm @error('P_RELATION_MAIL') is-invalid @enderror"
                                       value="{{ old('P_RELATION_MAIL', $personnel->P_RELATION_MAIL) }}">
                                @error('P_RELATION_MAIL')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- ── Tab: Autres ─────────────────────────────── --}}
                    <div class="tab-pane fade" id="tab-autres">
                        <div class="row g-2">

                            <div class="col-md-4">
                                <label class="form-label form-label-sm" for="P_BIRTHDATE">Date de naissance</label>
                                <input id="P_BIRTHDATE" name="P_BIRTHDATE" type="date"
                                       class="form-control form-control-sm @error('P_BIRTHDATE') is-invalid @enderror"
                                       value="{{ old('P_BIRTHDATE', $personnel->P_BIRTHDATE?->format('Y-m-d')) }}">
                                @error('P_BIRTHDATE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label form-label-sm" for="P_BIRTHPLACE">Lieu de naissance</label>
                                <input id="P_BIRTHPLACE" name="P_BIRTHPLACE" type="text"
                                       class="form-control form-control-sm @error('P_BIRTHPLACE') is-invalid @enderror"
                                       value="{{ old('P_BIRTHPLACE', $personnel->P_BIRTHPLACE) }}">
                                @error('P_BIRTHPLACE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_BIRTH_DEP">Département</label>
                                <input id="P_BIRTH_DEP" name="P_BIRTH_DEP" type="text"
                                       class="form-control form-control-sm @error('P_BIRTH_DEP') is-invalid @enderror"
                                       maxlength="3" placeholder="67"
                                       value="{{ old('P_BIRTH_DEP', $personnel->P_BIRTH_DEP) }}">
                                @error('P_BIRTH_DEP')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <p class="ob-section-title mb-2">Licence</p>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label form-label-sm" for="P_LICENCE">N° Licence</label>
                                <input id="P_LICENCE" name="P_LICENCE" type="text"
                                       class="form-control form-control-sm @error('P_LICENCE') is-invalid @enderror"
                                       value="{{ old('P_LICENCE', $personnel->P_LICENCE) }}">
                                @error('P_LICENCE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-sm" for="P_LICENCE_DATE">Date début</label>
                                <input id="P_LICENCE_DATE" name="P_LICENCE_DATE" type="date"
                                       class="form-control form-control-sm @error('P_LICENCE_DATE') is-invalid @enderror"
                                       value="{{ old('P_LICENCE_DATE', $personnel->P_LICENCE_DATE?->format('Y-m-d')) }}">
                                @error('P_LICENCE_DATE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-sm" for="P_LICENCE_EXPIRY">Date expiration</label>
                                <input id="P_LICENCE_EXPIRY" name="P_LICENCE_EXPIRY" type="date"
                                       class="form-control form-control-sm @error('P_LICENCE_EXPIRY') is-invalid @enderror"
                                       value="{{ old('P_LICENCE_EXPIRY', $personnel->P_LICENCE_EXPIRY?->format('Y-m-d')) }}">
                                @error('P_LICENCE_EXPIRY')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <p class="ob-section-title mb-2">Notes</p>
                            </div>

                            <div class="col-12">
                                <label class="form-label form-label-sm" for="OBSERVATION">Observations</label>
                                <textarea id="OBSERVATION" name="OBSERVATION" rows="3"
                                          class="form-control form-control-sm @error('OBSERVATION') is-invalid @enderror"
                                          >{{ old('OBSERVATION', $personnel->OBSERVATION) }}</textarea>
                                @error('OBSERVATION')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <p class="ob-section-title mb-2">Paramètres</p>
                            </div>

                            <div class="col-6 col-md-3">
                                <div class="form-check">
                                    <input id="P_HIDE" name="P_HIDE" type="checkbox" value="1"
                                           class="form-check-input"
                                           @checked((bool)old('P_HIDE',$personnel->P_HIDE))>
                                    <label class="form-check-label form-label-sm" for="P_HIDE">Masqué des listes</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="form-check">
                                    <input id="P_NOSPAM" name="P_NOSPAM" type="checkbox" value="1"
                                           class="form-check-input"
                                           @checked((bool)old('P_NOSPAM',$personnel->P_NOSPAM))>
                                    <label class="form-check-label form-label-sm" for="P_NOSPAM">No spam</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="form-check">
                                    <input id="NPAI" name="NPAI" type="checkbox" value="1"
                                           class="form-check-input"
                                           @checked((bool)old('NPAI',$personnel->NPAI))>
                                    <label class="form-check-label form-label-sm" for="NPAI">
                                        NPAI <small class="text-muted">(adresse invalide)</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="form-check">
                                    <input id="SUSPENDU" name="SUSPENDU" type="checkbox" value="1"
                                           class="form-check-input"
                                           @checked((bool)old('SUSPENDU',$personnel->SUSPENDU))>
                                    <label class="form-check-label form-label-sm" for="SUSPENDU">Suspendu</label>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>{{-- /tab-content --}}

                {{-- Action bar --}}
                <div class="d-flex gap-2 mt-3 pt-2 border-top align-items-center">
                    <button class="btn btn-primary btn-sm" type="submit">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                    <a href="{{ route('personnel.show', $personnel) }}"
                       class="btn btn-outline-secondary btn-sm">Annuler</a>
                    <span class="ms-auto text-muted" style="font-size:var(--font-size-xs);">
                        ID : {{ $personnel->P_ID }}
                        &nbsp;·&nbsp;
                        Dernière connexion :
                        {{ $personnel->P_LAST_CONNECT?->format('d/m/Y H:i') ?? 'jamais' }}
                    </span>
                </div>

            </div>{{-- /form column --}}
        </div>{{-- /flex row --}}
    </form>
</div>

@endsection

@push('scripts')
<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('photoPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Photo overlay hover
(function () {
    var wrap    = document.querySelector('[onclick*="photo_upload"]');
    var overlay = document.getElementById('photoOverlay');
    if (wrap && overlay) {
        wrap.addEventListener('mouseenter', function () { overlay.style.opacity = '1'; });
        wrap.addEventListener('mouseleave', function () { overlay.style.opacity = '0'; });
    }

    // Restore active tab from sessionStorage
    var saved = sessionStorage.getItem('personnelEditTab');
    if (saved) {
        var btn = document.querySelector('[data-bs-target="' + saved + '"]');
        if (btn) new bootstrap.Tab(btn).show();
    }
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function (e) {
            sessionStorage.setItem('personnelEditTab', e.target.dataset.bsTarget);
        });
    });

    // Highlight tabs that contain validation errors
    document.querySelectorAll('.is-invalid').forEach(function (el) {
        var pane = el.closest('.tab-pane');
        if (pane) {
            var tab = document.querySelector('[data-bs-target="#' + pane.id + '"]');
            if (tab) tab.classList.add('text-danger');
        }
    });
}());
</script>
@endpush
