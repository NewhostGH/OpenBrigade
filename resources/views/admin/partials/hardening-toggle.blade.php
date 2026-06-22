{{--
    One boolean hardening setting as a table row, mirroring the Sessions & audit
    tab: an auto-submitting toggle (.ob-sec-toggle) posting to admin.settings.save.

    Params:
      $s       configuration row (object with ->ID, ->VALUE) or null
      $label   admin.security.<label> translation key for the row title
      $hint    admin.security.<hint> translation key for the help text
      $default '1' | '0' fallback when the row has no stored value yet
--}}
<tr>
    <td class="ps-3" style="width:60%;vertical-align:middle;font-size:var(--font-size-sm);">
        {{ __('admin.security.'.$label) }}
        <div class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.security.'.$hint) }}</div>
    </td>
    <td style="vertical-align:middle;">
        <form method="POST" action="{{ route('admin.settings.save', $s->ID) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="_back" value="security">
            <input type="hidden" name="_tab" value="hardening">
            <input type="hidden" name="toggle" value="1">
            <div class="form-check form-switch">
                <input class="form-check-input ob-sec-toggle" type="checkbox" name="VALUE" value="1"
                       {{ ($s?->VALUE ?? $default) == '1' ? 'checked' : '' }}>
            </div>
        </form>
    </td>
</tr>
