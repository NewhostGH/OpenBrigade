{{--
    One free-text observability setting as a table row with an inline save form,
    posting to admin.settings.save with _back=monitoring (Paramètres tab).

    Params:
      $s         configuration row (object with ->ID, ->VALUE) or null
      $label     admin.monitoring.settings.<label> translation key
      $hint      admin.monitoring.settings.<hint> translation key, or null
      $default   fallback value when the row has no stored value yet
      $type      HTML input type (text | url | password)   (default: text)
      $placeholder optional placeholder string
--}}
<tr>
    <td class="ps-3" style="width:40%;vertical-align:middle;font-size:var(--font-size-sm);">
        {{ __('admin.monitoring.settings.'.$label) }}
        @if (! empty($hint))
            <div class="text-muted" style="font-size:var(--font-size-xs);">{{ __('admin.monitoring.settings.'.$hint) }}</div>
        @endif
    </td>
    <td style="vertical-align:middle;">
        <form method="POST" action="{{ route('admin.settings.save', $s->ID) }}" class="d-flex align-items-center gap-2">
            @csrf @method('PATCH')
            <input type="hidden" name="_back" value="monitoring">
            <input type="hidden" name="_tab" value="settings">
            <input type="{{ $type ?? 'text' }}" name="VALUE" value="{{ $s?->VALUE ?? ($default ?? '') }}"
                   class="form-control form-control-sm" style="min-width:280px;"
                   @if (! empty($placeholder)) placeholder="{{ $placeholder }}" @endif>
            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-save"></i></button>
        </form>
    </td>
</tr>
