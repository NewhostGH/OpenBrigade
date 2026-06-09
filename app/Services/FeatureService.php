<?php

namespace App\Services;

use App\Models\ObFeature;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for feature/module flags (the `ob_feature` table).
 *
 * Reads are memoised per-request so the sidebar, the `feature:` middleware and
 * any controller share one query. Writes keep the legacy `configuration` row in
 * sync so code that has not yet been migrated off `configuration` keeps working.
 */
class FeatureService
{
    /** @var array<string,bool>|null */
    private ?array $map = null;

    /**
     * Whether a feature is enabled. Unknown keys default to enabled so a missing
     * registry row never hides an existing screen.
     */
    public function isEnabled(string $key): bool
    {
        return $this->enabledMap()[$key] ?? true;
    }

    /** All features ordered for display (group, then ordering, then name). */
    public function all(): Collection
    {
        return ObFeature::orderBy('group')
            ->orderBy('ordering')
            ->orderBy('name')
            ->get();
    }

    /**
     * Toggle a feature on/off, propagating to the legacy configuration row.
     */
    public function setEnabled(ObFeature $feature, bool $enabled): void
    {
        $feature->update(['enabled' => $enabled]);

        if ($feature->legacy_config_id !== null) {
            DB::table('configuration')
                ->where('ID', $feature->legacy_config_id)
                ->update(['VALUE' => $enabled ? '1' : '0']);
        }

        $this->map = null;
    }

    /** @return array<string,bool> */
    private function enabledMap(): array
    {
        if ($this->map === null) {
            $this->map = ObFeature::query()
                ->pluck('enabled', 'key')
                ->map(fn ($v) => (bool) $v)
                ->all();
        }

        return $this->map;
    }
}
