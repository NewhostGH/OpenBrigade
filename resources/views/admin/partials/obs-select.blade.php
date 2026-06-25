{{--
    One enumerated observability setting as a table row with an auto-submitting
    <select>, posting to admin.settings.save with _back=monitoring.

    Params:
      $s        configuration row (object with ->ID, ->VALUE) or null
      $label    admin.monitoring.settings.<label> translation key
      $hint     admin.monitoring.settings.<hint> translation key, or null
      $default  fallback value when the row has no stored value yet
      $options  array<string,string> value => display label (already translated)
--}}
<tr>
    <td class="ps-3" style="width:60%;vertical-align:middle;font-size:var(--font-size-sm);">
        {{ __('admin.monitoring.settings.'.$label) }}
        @if (! empty($hint))
            <div class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.monitoring.settings.'.$hint) }}</div>
        @endif
    </td>
    <td style="vertical-align:middle;">
        <form method="POST" action="{{ route('admin.settings.save', $s->ID) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="_back" value="monitoring">
            <input type="hidden" name="_tab" value="settings">
            <select name="VALUE" class="form-select form-select-sm ob-obs-select" style="max-width:220px;">
                @foreach ($options as $value => $label)
                    <option value="{{ $value }}" {{ ($s?->VALUE ?? $default) === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </td>
</tr>
