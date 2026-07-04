<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Http\Controllers;

use App\Http\Middleware\RequireSetup;
use App\Services\OrganisationSetupService;
use App\Services\UploadSecurityService;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * First-run setup wizard (successor to legacy `wizard.php`) and the admin
 * screen to change the organisation type afterwards.
 *
 * Guarded by permission 14 (application settings). The wizard is reached via
 * {@see RequireSetup} while `already_configured` is unset.
 */
class SetupController extends Controller
{
    public function __construct(private readonly OrganisationSetupService $setup) {}

    /** First-run wizard form. */
    public function show(): View|RedirectResponse
    {
        if ($this->setup->isCompleted()) {
            return redirect()->route('dashboard');
        }

        return view('setup.wizard', [
            'types' => $this->setup->types(),
            'identity' => $this->setup->identity(),
            'orgType' => $this->setup->orgType(),
        ]);
    }

    /** Persist the wizard, mark the install configured, and enter the app. */
    public function store(Request $request): RedirectResponse
    {
        if ($this->setup->isCompleted()) {
            return redirect()->route('dashboard');
        }

        $data = $this->validateWizard($request);

        $this->setup->saveIdentity([
            'cisname' => $data['cisname'],
            'organisation_name' => $data['organisation_name'],
            'cisurl' => $data['cisurl'],
            'admin_email' => $data['admin_email'],
            'application_title' => $data['application_title'],
            'association_dept_name' => $data['description'] ?? '',
        ]);

        if ($request->hasFile('logo')) {
            app(UploadSecurityService::class)->assertSafe(
                $request->file('logo'),
                ['jpeg', 'jpg', 'png', 'gif', 'ico', 'webp'],
                4096,
                'logo',
            );
            $this->setup->saveLogo($request->file('logo'));
        }

        $this->setup->setOrgType((int) $data['type_organisation']);
        $this->setup->markCompleted();

        Audit::activity('setup.completed', [
            'org_type' => (int) $data['type_organisation'],
            'cisname' => $data['cisname'],
        ]);

        return redirect()->route('dashboard')->with('success', __('setup.completed'));
    }

    /** Admin: change the organisation type after first-run. */
    public function editOrgType(): View
    {
        return view('setup.org-type', [
            'types' => $this->setup->types(),
            'orgType' => $this->setup->orgType(),
            'currentLabel' => $this->setup->typeLabel(),
            'customRoles' => $this->setup->customRoles(),
            'presetRoles' => $this->setup->presetRoles(),
        ]);
    }

    /** Admin: persist a new organisation type (non-destructive). */
    public function updateOrgType(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type_organisation' => ['required', Rule::in(array_keys($this->setup->types()))],
        ]);

        $this->setup->setOrgType((int) $data['type_organisation']);

        Audit::activity('setup.org_type_changed', ['org_type' => (int) $data['type_organisation']]);

        return redirect()->route('setup.org-type')->with('success', __('setup.type_saved'));
    }

    /** Admin: destructive reset of the given type's preset roles to defaults. */
    public function resetRoles(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type_organisation' => ['required', Rule::in(array_keys($this->setup->types()))],
        ]);

        $orgType = (int) $data['type_organisation'];
        $this->setup->resetRoles($orgType);

        Audit::security('setup.roles_reset', ['org_type' => $orgType], 'warning');

        return redirect()->route('setup.org-type')
            ->with('success', __('setup.reset_roles_done', ['type' => $this->setup->typeLabel($orgType)]));
    }

    /**
     * Admin: delete selected custom roles, remapping their member assignments to
     * a chosen preset role (or dropping them). Destructive; explicit opt-in.
     */
    public function deleteCustomRoles(Request $request): RedirectResponse
    {
        $presetIds = $this->setup->presetRoles()->pluck('id')->all();

        $data = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['integer'],
            'remap' => ['array'],
            'remap.*' => ['nullable', Rule::in($presetIds)],
        ]);

        $selected = $data['roles'] ?? [];
        if ($selected === []) {
            return redirect()->route('setup.org-type')->with('error', __('setup.delete_roles_none'));
        }

        // Build customRoleId => targetPresetRoleId|null (empty string = drop).
        $map = [];
        foreach ($selected as $roleId) {
            $target = $data['remap'][$roleId] ?? null;
            $map[(int) $roleId] = ($target === null || $target === '') ? null : (int) $target;
        }

        $this->setup->deleteCustomRoles($map);

        Audit::security('setup.custom_roles_deleted', [
            'roles' => array_keys($map),
            'remap' => $map,
        ], 'warning');

        return redirect()->route('setup.org-type')->with('success', __('setup.delete_roles_done'));
    }

    /**
     * @return array<string,mixed>
     */
    private function validateWizard(Request $request): array
    {
        return $request->validate([
            'type_organisation' => ['required', Rule::in(array_keys($this->setup->types()))],
            'cisname' => ['required', 'string', 'max:25'],
            'organisation_name' => ['required', 'string', 'max:60'],
            'cisurl' => ['required', 'string', 'max:60', 'url'],
            'admin_email' => ['required', 'string', 'max:60', 'email'],
            'application_title' => ['required', 'string', 'max:25'],
            'description' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'mimes:jpeg,png,gif,ico,webp', 'max:4096'],
        ]);
    }
}
