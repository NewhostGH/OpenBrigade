@extends('layout.app')

@section('title', 'Edition personnel - ' . config('app.name'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">Edition personnel</h1>

                    <form method="POST" action="{{ route('personnel.update', $personnel) }}" class="row g-3">
                        @csrf
                        @method('PATCH')

                        <div class="col-md-4">
                            <label class="form-label" for="P_CODE">Code</label>
                            <input id="P_CODE" name="P_CODE" type="text"
                                class="form-control @error('P_CODE') is-invalid @enderror"
                                value="{{ old('P_CODE', $personnel->P_CODE) }}" required>
                            @error('P_CODE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-mds-4">
                            <label class="form-label" for="P_PRENOM">Prenom</label>
                            <input id="P_PRENOM" name="P_PRENOM" type="text"
                                class="form-control @error('P_PRENOM') is-invalid @enderror"
                                value="{{ old('P_PRENOM', $personnel->P_PRENOM) }}" required>
                            @error('P_PRENOM')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="P_NOM">Nom</label>
                            <input id="P_NOM" name="P_NOM" type="text"
                                class="form-control @error('P_NOM') is-invalid @enderror"
                                value="{{ old('P_NOM', $personnel->P_NOM) }}" required>
                            @error('P_NOM')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="P_STATUT">Statut</label>
                            <input id="P_STATUT" name="P_STATUT" type="text"
                                class="form-control @error('P_STATUT') is-invalid @enderror"
                                value="{{ old('P_STATUT', $personnel->P_STATUT) }}" required>
                            @error('P_STATUT')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="P_GRADE">Grade</label>
                            <input id="P_GRADE" name="P_GRADE" type="text"
                                class="form-control @error('P_GRADE') is-invalid @enderror"
                                value="{{ old('P_GRADE', $personnel->P_GRADE) }}" required>
                            @error('P_GRADE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="P_PROFESSION">Profession</label>
                            <input id="P_PROFESSION" name="P_PROFESSION" type="text"
                                class="form-control @error('P_PROFESSION') is-invalid @enderror"
                                value="{{ old('P_PROFESSION', $personnel->P_PROFESSION) }}" required>
                            @error('P_PROFESSION')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="P_EMAIL">Email</label>
                            <input id="P_EMAIL" name="P_EMAIL" type="email"
                                class="form-control @error('P_EMAIL') is-invalid @enderror"
                                value="{{ old('P_EMAIL', $personnel->P_EMAIL) }}">
                            @error('P_EMAIL')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="P_PHONE">Telephone</label>
                            <input id="P_PHONE" name="P_PHONE" type="text"
                                class="form-control @error('P_PHONE') is-invalid @enderror"
                                value="{{ old('P_PHONE', $personnel->P_PHONE) }}">
                            @error('P_PHONE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="P_PHONE2">Portable</label>
                            <input id="P_PHONE2" name="P_PHONE2" type="text"
                                class="form-control @error('P_PHONE2') is-invalid @enderror"
                                value="{{ old('P_PHONE2', $personnel->P_PHONE2) }}">
                            @error('P_PHONE2')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="P_SECTION">Section</label>
                            <select id="P_SECTION" name="P_SECTION"
                                class="form-select @error('P_SECTION') is-invalid @enderror">
                                <option value="">--</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->S_ID }}" @selected((string) old('P_SECTION', $personnel->P_SECTION) === (string) $section->S_ID)>
                                        {{ $section->S_CODE }} - {{ $section->S_DESCRIPTION }}
                                    </option>
                                @endforeach
                            </select>
                            @error('P_SECTION')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="P_BIRTHDATE">Date naissance</label>
                            <input id="P_BIRTHDATE" name="P_BIRTHDATE" type="date"
                                class="form-control @error('P_BIRTHDATE') is-invalid @enderror"
                                value="{{ old('P_BIRTHDATE', $personnel->P_BIRTHDATE?->format('Y-m-d')) }}">
                            @error('P_BIRTHDATE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="P_DATE_ENGAGEMENT">Date engagement</label>
                            <input id="P_DATE_ENGAGEMENT" name="P_DATE_ENGAGEMENT" type="date"
                                class="form-control @error('P_DATE_ENGAGEMENT') is-invalid @enderror"
                                value="{{ old('P_DATE_ENGAGEMENT', $personnel->P_DATE_ENGAGEMENT?->format('Y-m-d')) }}">
                            @error('P_DATE_ENGAGEMENT')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="P_FIN">Date fin</label>
                            <input id="P_FIN" name="P_FIN" type="date"
                                class="form-control @error('P_FIN') is-invalid @enderror"
                                value="{{ old('P_FIN', $personnel->P_FIN?->format('Y-m-d')) }}">
                            @error('P_FIN')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="P_ZIP_CODE">Code postal</label>
                            <input id="P_ZIP_CODE" name="P_ZIP_CODE" type="text"
                                class="form-control @error('P_ZIP_CODE') is-invalid @enderror"
                                value="{{ old('P_ZIP_CODE', $personnel->P_ZIP_CODE) }}">
                            @error('P_ZIP_CODE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="P_CITY">Ville</label>
                            <input id="P_CITY" name="P_CITY" type="text"
                                class="form-control @error('P_CITY') is-invalid @enderror"
                                value="{{ old('P_CITY', $personnel->P_CITY) }}">
                            @error('P_CITY')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="P_ADDRESS">Adresse</label>
                            <input id="P_ADDRESS" name="P_ADDRESS" type="text"
                                class="form-control @error('P_ADDRESS') is-invalid @enderror"
                                value="{{ old('P_ADDRESS', $personnel->P_ADDRESS) }}">
                            @error('P_ADDRESS')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3 form-check mt-2 ms-2">
                            <input id="P_HIDE" name="P_HIDE" type="checkbox" value="1" class="form-check-input"
                                @checked((bool) old('P_HIDE', $personnel->P_HIDE))>
                            <label class="form-check-label" for="P_HIDE">Afficher dans listes</label>
                        </div>

                        <div class="col-md-3 form-check mt-2 ms-2">
                            <input id="P_NOSPAM" name="P_NOSPAM" type="checkbox" value="1" class="form-check-input"
                                @checked((bool) old('P_NOSPAM', $personnel->P_NOSPAM))>
                            <label class="form-check-label" for="P_NOSPAM">No spam</label>
                        </div>

                        <div class="col-12 d-flex gap-2 mt-3">
                            <button class="btn btn-primary" type="submit">Enregistrer</button>
                            <a href="{{ route('personnel.show', $personnel) }}"
                                class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection