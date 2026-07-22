{{--
    One free-text configuration row as a table row with an inline save form,
    posting to admin.settings.save.

    Params:
      $s           configuration row (object with ->ID, ->VALUE) or null
      $label       full translation key for the label
      $hint        full translation key for the hint, or null
      $back        _back redirect target (notifications | maintenance | …)
      $type        HTML input type (text | url | password)   (default: text)
      $placeholder optional placeholder string
--}}
<tr>
    <td class="ps-3" style="width:40%;vertical-align:middle;font-size:var(--font-size-sm);">
        {{ __($label) }}
        @if (! empty($hint))
            <div class="text-muted" style="font-size:var(--font-size-xs);">{{ __($hint) }}</div>
        @endif
    </td>
    <td style="vertical-align:middle;">
        @if ($s)
            <form method="POST" action="{{ route('admin.settings.save', $s->ID) }}" class="d-flex align-items-center gap-2">
                @csrf @method('PATCH')
                <input type="hidden" name="_back" value="{{ $back }}">
                <input type="{{ $type ?? 'text' }}" name="VALUE" value="{{ $s->VALUE ?? '' }}"
                       class="form-control form-control-sm" style="min-width:280px;"
                       @if (! empty($placeholder)) placeholder="{{ $placeholder }}" @endif>
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-save"></i></button>
            </form>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
</tr>
