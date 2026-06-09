@extends('layout.app')

@section('title', ($personnel ? 'Modifier — ' . $personnel->P_NOM . ' ' . $personnel->P_PRENOM : 'Nouveau personnel') . ' — ' . config('app.name'))

@section('content')

@php
    $isEdit = $personnel !== null;

    // Safe field reader: returns old() input, then model value for edit, then default for create.
    $val     = fn(string $f, $default = null) => old($f, $isEdit ? ($personnel->$f ?? $default) : $default);
    // Date fields return Carbon objects on the model; need Y-m-d string for HTML input.
    $dateVal = fn(string $f) => old($f, $isEdit ? ($personnel->$f?->format('Y-m-d') ?? null) : null);

    $breadcrumb = [['label' => 'Personnel', 'url' => route('personnel.index')]];
    if ($isEdit) {
        $breadcrumb[] = ['label' => $personnel->P_NOM . ' ' . $personnel->P_PRENOM,
                         'url'   => route('personnel.show', $personnel)];
        $breadcrumb[] = ['label' => 'Modifier'];
    } else {
        $breadcrumb[] = ['label' => 'Nouveau personnel'];
    }
@endphp

<x-ob-breadcrumb :items="$breadcrumb"/>

<div class="mx-3 mt-3">
<div class="ob-widget-card">

    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-{{ $isEdit ? 'edit' : 'user-plus' }}"></i>
            {{ $isEdit
                ? 'Modifier — ' . strtoupper($personnel->P_NOM) . ' ' . $personnel->P_PRENOM
                : 'Nouveau personnel' }}
        </div>
        @if ($isEdit)
            <a href="{{ route('personnel.show', $personnel) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-eye me-1"></i> Voir la fiche
            </a>
        @endif
    </div>

    <div class="ob-widget-card-body">

    <form method="POST"
          action="{{ $isEdit ? route('personnel.update', $personnel) : route('personnel.store') }}"
          enctype="multipart/form-data" id="editForm">
        @csrf
        @if ($isEdit) @method('PATCH') @endif

        <div class="d-flex gap-4 align-items-flex-start flex-wrap">

            {{-- ── Photo column ──────────────────────────────────── --}}
            <div style="flex: 0 0 130px;">
                <div style="width:120px; height:120px; border-radius:var(--radius-md); overflow:hidden;
                            border:2px solid var(--component-border); cursor:pointer; position:relative;
                            background:var(--table-odd-row);"
                     onclick="document.getElementById('photo_upload').click()">
                    <img id="photoPreview"
                         src="{{ $isEdit ? $personnel->getAvatarUrl() : asset('images/autre.png') }}"
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
                <nav class="ob-subnav" role="tablist">
                    <button class="ob-subnav-tab active" data-bs-toggle="tab"
                            data-bs-target="#tab-identite" type="button" role="tab">
                        <i class="fas fa-user me-1"></i> Identité
                    </button>
                    <button class="ob-subnav-tab" data-bs-toggle="tab"
                            data-bs-target="#tab-contact" type="button" role="tab">
                        <i class="fas fa-address-card me-1"></i> Contact
                    </button>
                    <button class="ob-subnav-tab" data-bs-toggle="tab"
                            data-bs-target="#tab-urgence" type="button" role="tab">
                        <i class="fas fa-phone-alt me-1"></i> Urgence
                    </button>
                    <button class="ob-subnav-tab" data-bs-toggle="tab"
                            data-bs-target="#tab-autres" type="button" role="tab">
                        <i class="fas fa-info-circle me-1"></i> Autres
                    </button>
                    <button class="ob-subnav-tab" data-bs-toggle="tab"
                            data-bs-target="#tab-acces" type="button" role="tab">
                        <i class="fas fa-shield-alt me-1"></i> Accès
                    </button>
                </nav>

                <div class="tab-content pt-3">

                    {{-- ── Tab: Identité ─────────────────────────── --}}
                    <div class="tab-pane fade show active" id="tab-identite">
                        <div class="row g-2">

                            <div class="col-md-1">
                                <label class="form-label form-label-sm" for="P_CIVILITE">Civilité</label>
                                <select id="P_CIVILITE" name="P_CIVILITE"
                                        class="form-select form-select-sm @error('P_CIVILITE') is-invalid @enderror">
                                    <option value="">—</option>
                                    @foreach (config('personnel.civilites') as $code => $label)
                                        <option value="{{ $code }}" @selected((string)$val('P_CIVILITE')===(string)$code)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('P_CIVILITE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label form-label-sm" for="P_NOM">Nom *</label>
                                <input id="P_NOM" name="P_NOM" type="text"
                                       class="form-control form-control-sm @error('P_NOM') is-invalid @enderror"
                                       value="{{ $val('P_NOM') }}" required>
                                @error('P_NOM')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label form-label-sm" for="P_NOM_NAISSANCE">Nom de naissance</label>
                                <input id="P_NOM_NAISSANCE" name="P_NOM_NAISSANCE" type="text"
                                       class="form-control form-control-sm @error('P_NOM_NAISSANCE') is-invalid @enderror"
                                       value="{{ $val('P_NOM_NAISSANCE') }}">
                                @error('P_NOM_NAISSANCE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label form-label-sm" for="P_PRENOM">Prénom *</label>
                                <input id="P_PRENOM" name="P_PRENOM" type="text"
                                       class="form-control form-control-sm @error('P_PRENOM') is-invalid @enderror"
                                       value="{{ $val('P_PRENOM') }}" required>
                                @error('P_PRENOM')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label form-label-sm" for="P_PRENOM2">Prénom 2</label>
                                <input id="P_PRENOM2" name="P_PRENOM2" type="text"
                                       class="form-control form-control-sm @error('P_PRENOM2') is-invalid @enderror"
                                       value="{{ $val('P_PRENOM2') }}">
                                @error('P_PRENOM2')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_SEXE">Sexe</label>
                                <select id="P_SEXE" name="P_SEXE"
                                        class="form-select form-select-sm @error('P_SEXE') is-invalid @enderror">
                                    <option value="">—</option>
                                    <option value="M" @selected($val('P_SEXE')==='M')>Masculin</option>
                                    <option value="F" @selected($val('P_SEXE')==='F')>Féminin</option>
                                </select>
                                @error('P_SEXE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_CODE">Matricule *</label>
                                <input id="P_CODE" name="P_CODE" type="text"
                                       class="form-control form-control-sm @error('P_CODE') is-invalid @enderror"
                                       value="{{ $val('P_CODE') }}" required>
                                @error('P_CODE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label form-label-sm" for="P_ABBREGE">
                                    Abrégé
                                    <span class="text-muted fw-normal" style="font-size:0.7rem;">(indicatif radio)</span>
                                </label>
                                <input id="P_ABBREGE" name="P_ABBREGE" type="text"
                                       class="form-control form-control-sm @error('P_ABBREGE') is-invalid @enderror"
                                       maxlength="20" placeholder="ex. SP123"
                                       value="{{ $val('P_ABBREGE') }}">
                                @error('P_ABBREGE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label form-label-sm" for="P_GRADE">Grade</label>
                                @php
                                    $gradeList = ['ADC','ADJ','AMB','AS','ASP','CCH','CD','CDT','CE','CG1',
                                                  'COL','CPL','CPT','CS','CSAN1','CSAN2','CSANSU','EQ',
                                                  'INF','ISP','ISPC','ISPE','ISPP','JSP1','JSP2','JSP3',
                                                  'JSP4','JSPB','LCL','LTN','MAJ','MASP','MCDT','MCOL',
                                                  'MCPT','MED','MLCL','MLTN','NR','PHCDT','PHCOL','PHCPT',
                                                  'PHLCL','SAP1','SAP2','SCH','SGT','SLT','SP',
                                                  'VETCDT','VETCOL','VETCPT','VETLCL'];
                                    $curGrade = $val('P_GRADE');
                                    if ($curGrade && !in_array($curGrade, $gradeList)) $gradeList[] = $curGrade;
                                    sort($gradeList);
                                @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <select id="P_GRADE" name="P_GRADE"
                                            class="form-select form-select-sm flex-grow-1 @error('P_GRADE') is-invalid @enderror"
                                            onchange="updateGradePreview(this.value)">
                                        <option value="">— aucun —</option>
                                        @foreach ($gradeList as $g)
                                            <option value="{{ $g }}" @selected($curGrade === $g)>{{ $g }}</option>
                                        @endforeach
                                    </select>
                                    <img id="gradePreview"
                                         src="{{ $curGrade ? route('personnel.grade_image', ['grade' => $curGrade]) : '' }}"
                                         alt="" style="height:28px; {{ $curGrade ? '' : 'display:none;' }}"
                                         onerror="this.style.display='none'">
                                </div>
                                @error('P_GRADE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label form-label-sm" for="P_STATUT">Statut *</label>
                                <select id="P_STATUT" name="P_STATUT"
                                        class="form-select form-select-sm @error('P_STATUT') is-invalid @enderror" required>
                                    @foreach (config('personnel.statuts_assignable') as $statut)
                                        <option value="{{ $statut }}" @selected($val('P_STATUT', 'INT')===$statut)>{{ config('personnel.statuts')[$statut] }}</option>
                                    @endforeach
                                </select>
                                @error('P_STATUT')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_PROFESSION">Profession</label>
                                <input id="P_PROFESSION" name="P_PROFESSION" type="text"
                                       class="form-control form-control-sm @error('P_PROFESSION') is-invalid @enderror"
                                       value="{{ $val('P_PROFESSION') }}">
                                @error('P_PROFESSION')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_SECTION">Section par défaut</label>
                                <select id="P_SECTION" name="P_SECTION"
                                        class="form-select form-select-sm @error('P_SECTION') is-invalid @enderror">
                                    <option value="">—</option>
                                    @foreach ($accessibleSections as $section)
                                        <option value="{{ $section->S_ID }}"
                                                @selected((string)$val('P_SECTION')===(string)$section->S_ID)>
                                            {{ $section->S_CODE }}{{ $section->S_DESCRIPTION ? ' — '.$section->S_DESCRIPTION : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('P_SECTION')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label form-label-sm" for="P_DATE_ENGAGEMENT">Date d'entrée</label>
                                <input id="P_DATE_ENGAGEMENT" name="P_DATE_ENGAGEMENT" type="date"
                                       class="form-control form-control-sm @error('P_DATE_ENGAGEMENT') is-invalid @enderror"
                                       value="{{ $dateVal('P_DATE_ENGAGEMENT') }}">
                                @error('P_DATE_ENGAGEMENT')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label form-label-sm" for="P_FIN">Date de fin</label>
                                <input id="P_FIN" name="P_FIN" type="date"
                                       class="form-control form-control-sm @error('P_FIN') is-invalid @enderror"
                                       value="{{ $dateVal('P_FIN') }}">
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
                                       value="{{ $val('P_EMAIL') }}">
                                @error('P_EMAIL')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_PHONE">Téléphone</label>
                                <input id="P_PHONE" name="P_PHONE" type="text"
                                       class="form-control form-control-sm @error('P_PHONE') is-invalid @enderror"
                                       value="{{ $val('P_PHONE') }}">
                                @error('P_PHONE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_PHONE2">Portable</label>
                                <input id="P_PHONE2" name="P_PHONE2" type="text"
                                       class="form-control form-control-sm @error('P_PHONE2') is-invalid @enderror"
                                       value="{{ $val('P_PHONE2') }}">
                                @error('P_PHONE2')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label form-label-sm" for="P_ADDRESS">Adresse</label>
                                <input id="P_ADDRESS" name="P_ADDRESS" type="text"
                                       class="form-control form-control-sm @error('P_ADDRESS') is-invalid @enderror"
                                       value="{{ $val('P_ADDRESS') }}">
                                @error('P_ADDRESS')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label form-label-sm" for="P_ZIP_CODE">Code postal</label>
                                <input id="P_ZIP_CODE" name="P_ZIP_CODE" type="text"
                                       class="form-control form-control-sm @error('P_ZIP_CODE') is-invalid @enderror"
                                       value="{{ $val('P_ZIP_CODE') }}">
                                @error('P_ZIP_CODE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label form-label-sm" for="P_CITY">Ville</label>
                                <input id="P_CITY" name="P_CITY" type="text"
                                       class="form-control form-control-sm @error('P_CITY') is-invalid @enderror"
                                       value="{{ $val('P_CITY') }}">
                                @error('P_CITY')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label form-label-sm" for="P_PAYS">Pays</label>
                                <input id="P_PAYS" name="P_PAYS" type="text"
                                       class="form-control form-control-sm @error('P_PAYS') is-invalid @enderror"
                                       placeholder="France"
                                       value="{{ $val('P_PAYS') }}">
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
                        <div class="mb-3">
                            <label class="form-label form-label-sm" for="P_URGENCE_PERSON_ID">
                                Lier à un membre
                                <span class="text-muted fw-normal" style="font-size:0.7rem;">— les coordonnées sont synchronisées à chaque enregistrement</span>
                            </label>
                            <select id="P_URGENCE_PERSON_ID" name="P_URGENCE_PERSON_ID"
                                    class="form-select form-select-sm @error('P_URGENCE_PERSON_ID') is-invalid @enderror">
                                <option value="">— saisie manuelle —</option>
                                @foreach ($allPersonnel as $p)
                                    <option value="{{ $p->P_ID }}"
                                            data-prenom="{{ $p->P_PRENOM }}"
                                            data-nom="{{ $p->P_NOM }}"
                                            data-phone="{{ $p->P_PHONE }}"
                                            data-email="{{ $p->P_EMAIL }}"
                                            @selected((string)$val('P_URGENCE_PERSON_ID')===(string)$p->P_ID)>
                                        {{ strtoupper($p->P_NOM) }} {{ $p->P_PRENOM }}
                                    </option>
                                @endforeach
                            </select>
                            @error('P_URGENCE_PERSON_ID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label form-label-sm" for="P_RELATION_PRENOM">Prénom</label>
                                <input id="P_RELATION_PRENOM" name="P_RELATION_PRENOM" type="text"
                                       class="form-control form-control-sm @error('P_RELATION_PRENOM') is-invalid @enderror"
                                       value="{{ $val('P_RELATION_PRENOM') }}">
                                @error('P_RELATION_PRENOM')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-sm" for="P_RELATION_NOM">Nom</label>
                                <input id="P_RELATION_NOM" name="P_RELATION_NOM" type="text"
                                       class="form-control form-control-sm @error('P_RELATION_NOM') is-invalid @enderror"
                                       value="{{ $val('P_RELATION_NOM') }}">
                                @error('P_RELATION_NOM')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-sm" for="P_RELATION_PHONE">Téléphone</label>
                                <input id="P_RELATION_PHONE" name="P_RELATION_PHONE" type="text"
                                       class="form-control form-control-sm @error('P_RELATION_PHONE') is-invalid @enderror"
                                       value="{{ $val('P_RELATION_PHONE') }}">
                                @error('P_RELATION_PHONE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label form-label-sm" for="P_RELATION_MAIL">Email</label>
                                <input id="P_RELATION_MAIL" name="P_RELATION_MAIL" type="email"
                                       class="form-control form-control-sm @error('P_RELATION_MAIL') is-invalid @enderror"
                                       value="{{ $val('P_RELATION_MAIL') }}">
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
                                       value="{{ $dateVal('P_BIRTHDATE') }}">
                                @error('P_BIRTHDATE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label form-label-sm" for="P_BIRTHPLACE">Lieu de naissance</label>
                                <input id="P_BIRTHPLACE" name="P_BIRTHPLACE" type="text"
                                       class="form-control form-control-sm @error('P_BIRTHPLACE') is-invalid @enderror"
                                       value="{{ $val('P_BIRTHPLACE') }}">
                                @error('P_BIRTHPLACE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label form-label-sm" for="P_BIRTH_DEP">Département</label>
                                <input id="P_BIRTH_DEP" name="P_BIRTH_DEP" type="text"
                                       class="form-control form-control-sm @error('P_BIRTH_DEP') is-invalid @enderror"
                                       maxlength="3" placeholder="67"
                                       value="{{ $val('P_BIRTH_DEP') }}">
                                @error('P_BIRTH_DEP')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <p class="ob-form-label">Licence</p>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label form-label-sm" for="P_LICENCE">N° Licence</label>
                                <input id="P_LICENCE" name="P_LICENCE" type="text"
                                       class="form-control form-control-sm @error('P_LICENCE') is-invalid @enderror"
                                       value="{{ $val('P_LICENCE') }}">
                                @error('P_LICENCE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-sm" for="P_LICENCE_DATE">Date début</label>
                                <input id="P_LICENCE_DATE" name="P_LICENCE_DATE" type="date"
                                       class="form-control form-control-sm @error('P_LICENCE_DATE') is-invalid @enderror"
                                       value="{{ $dateVal('P_LICENCE_DATE') }}">
                                @error('P_LICENCE_DATE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-sm" for="P_LICENCE_EXPIRY">Date expiration</label>
                                <input id="P_LICENCE_EXPIRY" name="P_LICENCE_EXPIRY" type="date"
                                       class="form-control form-control-sm @error('P_LICENCE_EXPIRY') is-invalid @enderror"
                                       value="{{ $dateVal('P_LICENCE_EXPIRY') }}">
                                @error('P_LICENCE_EXPIRY')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <p class="ob-form-label">Notes</p>
                            </div>

                            <div class="col-12">
                                <label class="form-label form-label-sm" for="OBSERVATION">Observations</label>
                                <textarea id="OBSERVATION" name="OBSERVATION" rows="3"
                                          class="form-control form-control-sm @error('OBSERVATION') is-invalid @enderror"
                                          >{{ $val('OBSERVATION') }}</textarea>
                                @error('OBSERVATION')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <p class="ob-form-label">Paramètres</p>
                            </div>

                            <div class="col-6 col-md-3">
                                <div class="form-check">
                                    <input id="P_HIDE" name="P_HIDE" type="checkbox" value="1"
                                           class="form-check-input"
                                           @checked((bool)$val('P_HIDE', false))>
                                    <label class="form-check-label form-label-sm" for="P_HIDE">Masqué des listes</label>
                                </div>
                                <small class="text-muted d-block ps-4" style="font-size:0.7rem;">N'apparaît pas dans les listes publiques et les recherches.</small>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="form-check">
                                    <input id="P_NOSPAM" name="P_NOSPAM" type="checkbox" value="1"
                                           class="form-check-input"
                                           @checked((bool)$val('P_NOSPAM', false))>
                                    <label class="form-check-label form-label-sm" for="P_NOSPAM">No spam</label>
                                </div>
                                <small class="text-muted d-block ps-4" style="font-size:0.7rem;">Exclu des envois d'emails groupés et communications automatiques.</small>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="form-check">
                                    <input id="NPAI" name="NPAI" type="checkbox" value="1"
                                           class="form-check-input"
                                           @checked((bool)$val('NPAI', false))
                                           onchange="document.getElementById('npaiDateWrap').style.display=this.checked?'':'none'">
                                    <label class="form-check-label form-label-sm" for="NPAI">
                                        NPAI <small class="text-muted">(adresse invalide)</small>
                                    </label>
                                </div>
                                <small class="text-muted d-block ps-4" style="font-size:0.7rem;">N'habite Plus À l'Adresse Indiquée — adresse postale réputée invalide.</small>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="form-check">
                                    <input id="SUSPENDU" name="SUSPENDU" type="checkbox" value="1"
                                           class="form-check-input"
                                           @checked((bool)$val('SUSPENDU', false))>
                                    <label class="form-check-label form-label-sm" for="SUSPENDU">Suspendu</label>
                                </div>
                                <small class="text-muted d-block ps-4" style="font-size:0.7rem;">Compte temporairement suspendu ; accès à l'application bloqué.</small>
                            </div>
                            <div class="col-12" id="npaiDateWrap" style="{{ (bool)$val('NPAI', false) ? '' : 'display:none;' }}">
                                <div style="max-width:200px;">
                                    <label class="form-label form-label-sm" for="DATE_NPAI">Date NPAI</label>
                                    <input id="DATE_NPAI" name="DATE_NPAI" type="date"
                                           class="form-control form-control-sm @error('DATE_NPAI') is-invalid @enderror"
                                           value="{{ old('DATE_NPAI', $isEdit && isset($personnel->DATE_NPAI) ? \Carbon\Carbon::parse($personnel->DATE_NPAI)->format('Y-m-d') : '') }}">
                                    @error('DATE_NPAI')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ── Tab: Accès ──────────────────────────────── --}}
                    <div class="tab-pane fade" id="tab-acces">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-shield-alt me-1"></i>
                            Droits d'accès et affiliation organisationnelle.
                        </p>
                        <div class="row g-3">

                            @if (auth()->user()->hasPermission(9))

                                {{-- ── Sections ───────────────────────────── --}}
                                <div class="col-12">
                                    <label class="form-label form-label-sm">Sections</label>
                                    <p class="text-muted mb-2" style="font-size:var(--font-size-xs);">
                                        Sections auxquelles ce membre appartient (en plus de la section par défaut définie dans Identité).
                                    </p>
                                    <input type="text" class="form-control form-control-sm mb-2 ob-multiselect-search"
                                           data-ob-target="sections-wrap"
                                           placeholder="Rechercher une section…"
                                           autocomplete="off">
                                    <div class="ob-multiselect-wrap" id="sections-wrap" data-ob-multiselect>
                                        @foreach ($sections as $s)
                                            @php $sid = (int) $s->S_ID; @endphp
                                            <label class="ob-multiselect-item @if(in_array($sid, $currentSectionIds)) ob-selected @endif">
                                                <input type="checkbox" name="sections[]" value="{{ $sid }}"
                                                       @checked(in_array($sid, $currentSectionIds))
                                                       class="ob-multiselect-cb">
                                                <span class="ob-multiselect-label">
                                                    <span class="fw-semibold">{{ $s->S_CODE }}</span>
                                                    @if($s->S_DESCRIPTION)
                                                        <span class="text-muted ms-1">— {{ $s->S_DESCRIPTION }}</span>
                                                    @endif
                                                </span>
                                                <i class="fas fa-check ob-multiselect-check"></i>
                                            </label>
                                        @endforeach
                                        @if ($sections->isEmpty())
                                            <span class="text-muted" style="font-size:var(--font-size-xs);">Aucune section disponible.</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- ── Rôles ──────────────────────────────── --}}
                                <div class="col-12">
                                    <label class="form-label form-label-sm">Rôles organisationnels</label>
                                    <p class="text-muted mb-2" style="font-size:var(--font-size-xs);">
                                        Rôles que ce membre exerce au sein de l'organisation. Chaque rôle peut être
                                        limité à une section ou s'appliquer globalement.
                                    </p>

                                    @if ($allRoles->isEmpty())
                                        <p class="text-muted" style="font-size:var(--font-size-xs);">Aucun rôle défini.</p>
                                    @else
                                        <div id="ob-role-assignments-wrap">
                                            @foreach ($currentRoleAssignments as $i => $ra)
                                            <div class="ob-role-assignment-row d-flex gap-2 align-items-center mb-2 flex-wrap">
                                                <select name="role_assignments[{{ $i }}][group_id]"
                                                        class="form-select form-select-sm" style="flex:1 1 180px; max-width:220px;" required>
                                                    @foreach ($allRoles as $r)
                                                        <option value="{{ $r->id }}" @selected($r->id === $ra['group_id'])>{{ $r->name }}</option>
                                                    @endforeach
                                                </select>
                                                <select name="role_assignments[{{ $i }}][section_id]"
                                                        class="form-select form-select-sm" style="flex:1 1 180px; max-width:240px;">
                                                    <option value="0">— global —</option>
                                                    @foreach ($sections as $s)
                                                        <option value="{{ $s->S_ID }}"
                                                                @selected($ra['section_id'] === (int)$s->S_ID)>
                                                            {{ $s->S_CODE }}{{ $s->S_DESCRIPTION ? ' — '.$s->S_DESCRIPTION : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger ob-role-remove"
                                                        title="Supprimer ce rôle">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            @endforeach
                                        </div>

                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1"
                                                id="ob-add-role-btn">
                                            <i class="fas fa-plus me-1"></i> Ajouter un rôle
                                        </button>

                                        {{-- Template for new rows (rendered server-side, cloned by JS) --}}
                                        <template id="ob-role-row-tpl">
                                            <div class="ob-role-assignment-row d-flex gap-2 align-items-center mb-2 flex-wrap">
                                                <select name="role_assignments[__OB_IDX__][group_id]"
                                                        class="form-select form-select-sm" style="flex:1 1 180px; max-width:220px;" required>
                                                    @foreach ($allRoles as $r)
                                                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                                                    @endforeach
                                                </select>
                                                <select name="role_assignments[__OB_IDX__][section_id]"
                                                        class="form-select form-select-sm" style="flex:1 1 180px; max-width:240px;">
                                                    <option value="0">— global —</option>
                                                    @foreach ($sections as $s)
                                                        <option value="{{ $s->S_ID }}">
                                                            {{ $s->S_CODE }}{{ $s->S_DESCRIPTION ? ' — '.$s->S_DESCRIPTION : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger ob-role-remove"
                                                        title="Supprimer ce rôle">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </template>
                                    @endif
                                </div>

                                {{-- ── Groupes d'accès ────────────────────── --}}
                                <div class="col-12">
                                    <label class="form-label form-label-sm">Groupes d'accès</label>
                                    <p class="text-muted mb-2" style="font-size:var(--font-size-xs);">
                                        Groupes d'accès attribués à ce membre. Les groupes accordent des droits
                                        globaux indépendamment de la section active.
                                    </p>
                                    <input type="text" class="form-control form-control-sm mb-2 ob-multiselect-search"
                                           data-ob-target="groups-wrap"
                                           placeholder="Rechercher un groupe…"
                                           autocomplete="off">
                                    <div class="ob-multiselect-wrap" id="groups-wrap" data-ob-multiselect>
                                        @foreach ($allGroups as $g)
                                            @php $gid = (int) $g->id; @endphp
                                            <label class="ob-multiselect-item @if(in_array($gid, $currentGroupIds)) ob-selected @endif">
                                                <input type="checkbox" name="groups[]" value="{{ $gid }}"
                                                       @checked(in_array($gid, $currentGroupIds))
                                                       class="ob-multiselect-cb">
                                                <span class="ob-multiselect-label">{{ $g->name }}</span>
                                                <i class="fas fa-check ob-multiselect-check"></i>
                                            </label>
                                        @endforeach
                                        @if ($allGroups->isEmpty())
                                            <span class="text-muted" style="font-size:var(--font-size-xs);">Aucun groupe défini.</span>
                                        @endif
                                    </div>
                                </div>

                            @endif

                            @if ($isEdit && ($personnel->P_ACCEPT_DATE || $personnel->P_ACCEPT_DATE2))
                                <div class="col-12">
                                    <p class="ob-section-title mb-1 mt-2">Charte d'utilisation</p>
                                </div>
                                @if ($personnel->P_ACCEPT_DATE)
                                    <div class="col-md-5">
                                        <label class="form-label form-label-sm text-muted">Charte acceptée le</label>
                                        <p class="mb-0" style="font-size:var(--font-size-sm);">
                                            {{ $personnel->P_ACCEPT_DATE->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                @endif
                                @if ($personnel->P_ACCEPT_DATE2)
                                    <div class="col-md-5">
                                        <label class="form-label form-label-sm text-muted">Charte 2 acceptée le</label>
                                        <p class="mb-0" style="font-size:var(--font-size-sm);">
                                            {{ $personnel->P_ACCEPT_DATE2->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                @endif
                            @endif

                        </div>
                    </div>

                </div>{{-- /tab-content --}}

                {{-- Action bar --}}
                <div class="d-flex gap-2 mt-3 pt-2 border-top align-items-center">
                    <button class="btn btn-primary btn-sm" type="submit">
                        <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Enregistrer' : 'Créer' }}
                    </button>
                    <a href="{{ $isEdit ? route('personnel.show', $personnel) : route('personnel.index') }}"
                       class="btn btn-outline-secondary btn-sm">Annuler</a>
                    @if ($isEdit)
                    <span class="ms-auto text-muted" style="font-size:var(--font-size-xs);">
                        ID : {{ $personnel->P_ID }}
                        &nbsp;·&nbsp;
                        Dernière connexion :
                        {{ $personnel->P_LAST_CONNECT?->format('d/m/Y H:i') ?? 'jamais' }}
                    </span>
                    @endif
                </div>

            </div>{{-- /form column --}}
        </div>{{-- /flex row --}}
    </form>

    </div>{{-- /ob-widget-card-body --}}
</div>{{-- /ob-widget-card --}}
</div>{{-- /mx-3 mt-3 --}}

@endsection

@push('scripts')
<script>window.PERS_FORM_GRADE_URL = '{{ route('personnel.grade_image', ['grade' => 'PLACEHOLDER']) }}';</script>
@vite('resources/js/ob-personnel-form.js')
@endpush
