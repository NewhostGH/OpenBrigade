{{-- Tri-state allow / deny / neutral selector (auto-submits).
     Vars: $action (post URL), $hidden (name => value map), $current ('allow'|'deny'|null) --}}
<form method="POST" action="{{ $action }}" style="margin:0;">
    @csrf
    @foreach ($hidden as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
    <select name="effect" class="ob-hab-effect ob-hab-effect-{{ $current ?: 'none' }} ob-hab-auto" aria-label="{{ __('admin.permissions.select_aria') }}">
        <option value="" {{ $current === null ? 'selected' : '' }}>·</option>
        <option value="allow" {{ $current === 'allow' ? 'selected' : '' }}>{{ __('admin.permissions.opt_allow') }}</option>
        <option value="deny" {{ $current === 'deny' ? 'selected' : '' }}>{{ __('admin.permissions.opt_deny') }}</option>
    </select>
</form>
