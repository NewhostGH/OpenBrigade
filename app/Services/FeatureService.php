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
     *
     * Side-effects on specific keys:
     *  - multi_site ON → assign every user without a section to the root section.
     */
    public function setEnabled(ObFeature $feature, bool $enabled): void
    {
        $feature->update(['enabled' => $enabled]);

        if ($feature->legacy_config_id !== null) {
            DB::table('configuration')
                ->where('ID', $feature->legacy_config_id)
                ->update(['VALUE' => $enabled ? '1' : '0']);
        }

        if ($feature->key === 'multi_site' && $enabled) {
            $this->backfillSectionlessUsers();
        }

        $this->map = null;
    }

    /**
     * When multi_site is switched on, every user whose P_SECTION is NULL
     * is assigned to the root section (S_PARENT = 0 or NULL).
     */
    private function backfillSectionlessUsers(): void
    {
        $rootId = DB::table('section')
            ->where(fn ($q) => $q->where('S_PARENT', 0)->orWhereNull('S_PARENT'))
            ->value('S_ID');

        if ($rootId === null) {
            return;
        }

        DB::table('personnel')
            ->whereNull('P_SECTION')
            ->update(['P_SECTION' => $rootId]);

        // Also insert ob_personnel_section rows for those users (they had none).
        $missing = DB::table('personnel as p')
            ->where('p.P_SECTION', $rootId)
            ->whereNotExists(function ($q) use ($rootId): void {
                $q->from('ob_personnel_section')
                    ->whereColumn('person_id', 'p.P_ID')
                    ->where('section_id', $rootId);
            })
            ->pluck('p.P_ID');

        foreach ($missing as $personId) {
            DB::table('ob_personnel_section')->insertOrIgnore([
                'person_id' => $personId,
                'section_id' => $rootId,
            ]);
        }
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
