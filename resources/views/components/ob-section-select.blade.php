{{-- Scoped section dropdown — see App\View\Components\ObSectionSelect. --}}
@if($multiSite)
<select {{ $attributes->merge(['class' => 'form-select form-select-sm']) }}
        name="{{ $name }}"
        @if($autoSubmit) onchange="this.form.submit()" @endif
        @if($required) required @endif>

    @if($allLabel !== null)
        {{-- "All" sentinel is empty (parsed to SectionScopeService::ALL); 0 is the real root section --}}
        <option value="" @selected($selected === null)>{{ $allLabel }}</option>
    @endif

    @foreach($options as $opt)
        <option value="{{ $opt['id'] }}" style="{{ $opt['style'] }}" @selected($selected === $opt['id'])>
            {{ $opt['label'] }}
        </option>
    @endforeach
</select>
@endif
