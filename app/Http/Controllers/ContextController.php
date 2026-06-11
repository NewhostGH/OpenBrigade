<?php

namespace App\Http\Controllers;

use App\Services\PermissionResolver;
use App\Services\SectionScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Switches the active section / role context used by the section-scoped
 * permission resolver ({@see PermissionResolver}) and the data isolation
 * layer ({@see SectionScopeService}). Stored in the session.
 */
class ContextController extends Controller
{
    public function __construct(
        private readonly PermissionResolver $resolver,
        private readonly SectionScopeService $sectionScope,
    ) {}

    /** Set the active section; '' or 'home' clears it back to the home section. */
    public function section(Request $request): RedirectResponse
    {
        $sId = $request->string('s')->toString();

        if ($sId === '' || $sId === 'home' || $sId === 'all') {
            $request->session()->forget('hab.section');
        } else {
            abort_unless($this->sectionScope->canChoose((int) $sId), 403);
            $request->session()->put('hab.section', (int) $sId);
        }

        // Changing the section invalidates a role filter scoped to the old one.
        $request->session()->forget('hab.role');

        return back();
    }

    /** Set the active role filter; '' or 'all' clears it (all roles). */
    public function role(Request $request): RedirectResponse
    {
        $user = $request->user();
        $rId = $request->string('r')->toString();

        if ($rId === '' || $rId === 'all') {
            $request->session()->forget('hab.role');
        } else {
            $allowed = $this->resolver->userRoles($user, $this->resolver->activeSectionId($user))
                ->pluck('id')->map(fn ($v) => (int) $v)->all();
            abort_unless(in_array((int) $rId, $allowed, true), 403);
            $request->session()->put('hab.role', (int) $rId);
        }

        return back();
    }
}
