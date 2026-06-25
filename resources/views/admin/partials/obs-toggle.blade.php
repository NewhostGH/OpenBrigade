{{--
    One boolean observability setting as a table row — an auto-submitting toggle
    posting to admin.settings.save with _back=monitoring (Paramètres tab).

    Params:
      $s       configuration row (object with ->ID, ->VALUE) or null
      $label   admin.monitoring.settings.<label> translation key
      $hint    admin.monitoring.settings.<hint> translation key, or null
      $default '1' | '0' fallback when the row has no stored value yet
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
            <input type="hidden" name="toggle" value="1">
            <div class="form-check form-switch">
                <input class="form-check-input ob-obs-toggle" type="checkbox" name="VALUE" value="1"
                       {{ ($s?->VALUE ?? $default) == '1' ? 'checked' : '' }}>
            </div>
        </form>
    </td>
</tr>
