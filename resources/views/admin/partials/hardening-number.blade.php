{{--
    One numeric hardening setting as a table row with an inline save form, mirroring
    the "days audit" row of the Sessions & audit tab.

    Params:
      $s       configuration row (object with ->ID, ->VALUE) or null
      $label   admin.security.<label> translation key for the row title
      $unit    admin.security.<unit> translation key shown after the title, or null
      $default fallback value when the row has no stored value yet
      $min     HTML min attribute
      $max     HTML max attribute
--}}
<tr>
    <td class="ps-3" style="width:60%;vertical-align:middle;font-size:var(--font-size-sm);">
        {{ __('admin.security.'.$label) }}
        @if ($unit)<span class="text-muted">{{ __('admin.security.'.$unit) }}</span>@endif
    </td>
    <td style="vertical-align:middle;">
        <form method="POST" action="{{ route('admin.settings.save', $s->ID) }}" class="d-flex align-items-center gap-2">
            @csrf @method('PATCH')
            <input type="hidden" name="_back" value="security">
            <input type="hidden" name="_tab" value="hardening">
            <input type="number" name="VALUE" min="{{ $min }}" max="{{ $max }}" value="{{ $s?->VALUE ?? $default }}"
                   class="form-control form-control-sm" style="max-width:140px;">
            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-save"></i></button>
        </form>
    </td>
</tr>
