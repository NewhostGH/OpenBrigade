{{-- Shared form body for create/edit guard type modals --}}
@php
    $isEdit = $item !== null;
    $f = fn(string $k, $default = null) => old($k, $isEdit ? ($item->$k ?? $default) : $default);
@endphp

<div class="row g-3">

    {{-- Name + order --}}
    <div class="col-8">
        <label class="form-label form-label-sm">{{ __('duty.form_name') }} <span class="text-danger">*</span></label>
        <input type="text" name="EQ_NOM" value="{{ $f('EQ_NOM', '') }}"
               class="form-control form-control-sm" maxlength="60" required>
    </div>
    <div class="col-4">
        <label class="form-label form-label-sm">{{ __('duty.form_order') }}</label>
        <input type="number" name="EQ_ORDER" value="{{ $f('EQ_ORDER', $nextOrder) }}"
               class="form-control form-control-sm" min="1">
    </div>

    {{-- Location --}}
    <div class="col-6">
        <label class="form-label form-label-sm">{{ __('duty.form_location') }}</label>
        <input type="text" name="EQ_LIEU" value="{{ $f('EQ_LIEU', '') }}"
               class="form-control form-control-sm" maxlength="100" placeholder="{{ __('duty.form_location_ph') }}">
    </div>
    <div class="col-6">
        <label class="form-label form-label-sm">{{ __('duty.form_address') }}</label>
        <input type="text" name="EQ_ADDRESS" value="{{ $f('EQ_ADDRESS', '') }}"
               class="form-control form-control-sm" maxlength="255">
    </div>

    {{-- Day shift --}}
    <div class="col-12">
        <hr class="my-1">
        <div class="form-check form-check-inline mb-2">
            <input class="form-check-input" type="checkbox" name="EQ_JOUR" id="eq_jour_{{ $isEdit ? $item->EQ_ID : 'new' }}"
                   value="1" {{ $f('EQ_JOUR', 0) ? 'checked' : '' }}>
            <label class="form-check-label" for="eq_jour_{{ $isEdit ? $item->EQ_ID : 'new' }}">
                <i class="fas fa-sun text-warning me-1"></i> {{ __('duty.active_day') }}
            </label>
        </div>
        <div class="row g-2">
            <div class="col-4">
                <label class="form-label form-label-sm">{{ __('duty.form_start_day') }}</label>
                <input type="time" name="EQ_DEBUT1" value="{{ substr($f('EQ_DEBUT1', '07:30') ?? '07:30', 0, 5) }}"
                       class="form-control form-control-sm">
            </div>
            <div class="col-4">
                <label class="form-label form-label-sm">{{ __('duty.form_end_day') }}</label>
                <input type="time" name="EQ_FIN1" value="{{ substr($f('EQ_FIN1', '19:30') ?? '19:30', 0, 5) }}"
                       class="form-control form-control-sm">
            </div>
            <div class="col-4">
                <label class="form-label form-label-sm">{{ __('duty.form_persons_day') }}</label>
                <input type="number" name="EQ_PERSONNEL1" value="{{ $f('EQ_PERSONNEL1', 4) }}"
                       class="form-control form-control-sm" min="0" max="999">
            </div>
        </div>
    </div>

    {{-- Night shift --}}
    <div class="col-12">
        <hr class="my-1">
        <div class="form-check form-check-inline mb-2">
            <input class="form-check-input" type="checkbox" name="EQ_NUIT" id="eq_nuit_{{ $isEdit ? $item->EQ_ID : 'new' }}"
                   value="1" {{ $f('EQ_NUIT', 0) ? 'checked' : '' }}>
            <label class="form-check-label" for="eq_nuit_{{ $isEdit ? $item->EQ_ID : 'new' }}">
                <i class="fas fa-moon text-primary me-1"></i> {{ __('duty.active_night') }}
            </label>
        </div>
        <div class="row g-2">
            <div class="col-4">
                <label class="form-label form-label-sm">{{ __('duty.form_start_night') }}</label>
                <input type="time" name="EQ_DEBUT2" value="{{ substr($f('EQ_DEBUT2', '19:30') ?? '19:30', 0, 5) }}"
                       class="form-control form-control-sm">
            </div>
            <div class="col-4">
                <label class="form-label form-label-sm">{{ __('duty.form_end_night') }}</label>
                <input type="time" name="EQ_FIN2" value="{{ substr($f('EQ_FIN2', '07:30') ?? '07:30', 0, 5) }}"
                       class="form-control form-control-sm">
            </div>
            <div class="col-4">
                <label class="form-label form-label-sm">{{ __('duty.form_persons_night') }}</label>
                <input type="number" name="EQ_PERSONNEL2" value="{{ $f('EQ_PERSONNEL2', 4) }}"
                       class="form-control form-control-sm" min="0" max="999">
            </div>
        </div>
    </div>

    {{-- Options --}}
    <div class="col-12">
        <hr class="my-1">
        <div class="d-flex gap-4 flex-wrap">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="EQ_VEHICULES"
                       id="eq_veh_{{ $isEdit ? $item->EQ_ID : 'new' }}"
                       value="1" {{ $f('EQ_VEHICULES', 0) ? 'checked' : '' }}>
                <label class="form-check-label" for="eq_veh_{{ $isEdit ? $item->EQ_ID : 'new' }}"
                       style="font-size:var(--font-size-sm);">
                    <i class="fas fa-truck me-1"></i> {{ __('duty.form_vehicles_default') }}
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="EQ_DEFAULT"
                       id="eq_def_{{ $isEdit ? $item->EQ_ID : 'new' }}"
                       value="1" {{ $f('EQ_DEFAULT', 0) ? 'checked' : '' }}>
                <label class="form-check-label" for="eq_def_{{ $isEdit ? $item->EQ_ID : 'new' }}"
                       style="font-size:var(--font-size-sm);">
                    <i class="fas fa-star me-1 text-warning"></i> {{ __('duty.form_is_default') }}
                </label>
            </div>
        </div>
    </div>

</div>
