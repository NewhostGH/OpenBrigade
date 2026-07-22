{{--
    One boolean configuration row as a table row — an auto-submitting toggle
    posting to admin.settings.save.

    Params:
      $s       configuration row (object with ->ID, ->VALUE) or null
      $label   full translation key for the label
      $hint    full translation key for the hint, or null
      $default '1' | '0' fallback when the row has no stored value yet
      $back    _back redirect target (notifications | maintenance | …)
--}}
<tr>
    <td class="ps-3" style="width:60%;vertical-align:middle;font-size:var(--font-size-sm);">
        {{ __($label) }}
        @if (! empty($hint))
            <div class="text-muted" style="font-size:var(--font-size-xs);">{{ __($hint) }}</div>
        @endif
    </td>
    <td style="vertical-align:middle;">
        @if ($s)
            <form method="POST" action="{{ route('admin.settings.save', $s->ID) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="_back" value="{{ $back }}">
                <input type="hidden" name="toggle" value="1">
                <div class="form-check form-switch">
                    <input class="form-check-input ob-setting-row-toggle" type="checkbox" name="VALUE" value="1"
                           {{ ($s->VALUE ?? $default) == '1' ? 'checked' : '' }}>
                </div>
            </form>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
</tr>
