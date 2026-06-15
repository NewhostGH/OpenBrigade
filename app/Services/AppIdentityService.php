<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Resolves the organisation identity values stored in the `configuration` table
 * (IDs 6, 8, 39, 40, 71, 75) and exposes them for use in views and reports.
 *
 * All values are memoised per-request so the table is read at most once.
 */
class AppIdentityService
{
    private const IDS = [
        6 => 'cisname',
        8 => 'admin_email',
        39 => 'organisation_name',
        40 => 'association_dept_name',
        71 => 'logo',
        75 => 'splash_screen',
    ];

    /** @var array<string,string> */
    private array $values = [];

    private bool $loaded = false;

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }
        $this->loaded = true;

        try {
            $rows = DB::table('configuration')
                ->whereIn('ID', array_keys(self::IDS))
                ->pluck('VALUE', 'ID');

            foreach (self::IDS as $id => $key) {
                $this->values[$key] = (string) ($rows[$id] ?? '');
            }
        } catch (\Throwable) {
            // During install or migration the table may not exist yet.
            foreach (array_values(self::IDS) as $key) {
                $this->values[$key] = '';
            }
        }
    }

    /** Short organisation name (e.g. "CIS Dupont"). Falls back to config('app.name'). */
    public function shortName(): string
    {
        $this->load();

        return $this->values['cisname'] ?: config('app.name', 'OpenBrigade');
    }

    /** Long organisation name (e.g. "Croix-Rouge Française — UL Dupont"). */
    public function longName(): string
    {
        $this->load();

        return $this->values['organisation_name'] ?: $this->shortName();
    }

    /** Organisation description / departmental name. */
    public function description(): string
    {
        $this->load();

        return $this->values['association_dept_name'];
    }

    /** Contact / admin email address. */
    public function adminEmail(): string
    {
        $this->load();

        return $this->values['admin_email'];
    }

    /**
     * Public URL for the organisation logo stored in public storage,
     * or null when none has been configured.
     */
    public function logoUrl(): ?string
    {
        $this->load();
        $path = $this->values['logo'];

        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return null;
    }

    /**
     * Public URL for the login-page background image,
     * or null when none has been configured.
     */
    public function splashUrl(): ?string
    {
        $this->load();
        $path = $this->values['splash_screen'];

        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return null;
    }
}
