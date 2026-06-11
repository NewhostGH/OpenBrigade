{{-- Scoped section dropdown — see App\View\Components\ObSectionSelect. --}}
@if($multiSite)
<select {{ $attributes->merge(['class' => 'form-select form-select-sm']) }}
        name="{{ $name }}"
        @if($autoSubmit) onchange="this.form.submit()" @endif
        @if($required) required @endif>

    @if($allLabel !== null)
        <option value="0" @selected($selected === null || $selected === 0)>{{ $allLabel }}</option>
    @endif

    @foreach($options as $opt)
        <option value="{{ $opt['id'] }}" style="{{ $opt['style'] }}" @selected($selected === $opt['id'])>
            {{ $opt['label'] }}
        </option>
    @endforeach
</select>
@endif
