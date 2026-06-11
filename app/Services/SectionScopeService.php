<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Single authority for section-based data isolation.
 *
 * Every listing query, section dropdown and write-coercion that scopes rows
 * by section goes through this service, so the isolation rule lives in
 * exactly one place:
 *
 *   visible = (the user's sections — P_SECTION, ob_personnel_section
 *              memberships and ob_user_assignment role sections — each
 *              expanded to its descendants: a section always sees below
 *              itself, never above)
 *           ∩ (the navbar-chosen section + its descendants, when chosen)
 *
 * Global groups (ob_personnel_group) and global role assignments gate
 * PERMISSIONS through PermissionResolver; they never widen DATA visibility.
 * Only membership of a section (or an ancestor) grants access to its rows.
 *
 * "Unrestricted" (null) applies only when the multi_site feature is off.
 */
class SectionScopeService implements ServiceInterface
{
    /** @var Collection|null all section rows, memoized per request */
    private ?Collection $sections = null;

    /** @var array<int,int[]>|null parent id => child section ids, memoized */
    private ?array $children = null;

    /** @var int[]|null|false memoized visible set; false = not yet computed */
    private array|null|false $visible = false;

    /** @var int[]|null|false memoized base set (no navbar narrowing) */
    private array|null|false $baseVisible = false;

    public function __construct(
        private readonly FeatureService $features,
        private readonly PermissionResolver $resolver,
    ) {}

    /**
     * Section IDs the user may EVER see — memberships + descendants, before
     * the navbar choice narrows anything. This is also the set of sections
     * the user may choose in the switcher (otherwise, once narrowed, they
     * could never switch sideways). Null = unrestricted (multi_site off).
     *
     * @return int[]|null
     */
    public function baseVisibleSectionIds(): ?array
    {
        if ($this->baseVisible !== false) {
            return $this->baseVisible;
        }

        if (! $this->features->isEnabled('multi_site')) {
            return $this->baseVisible = null;
        }

        $user = auth()->user();
        if ($user === null) {
            return $this->baseVisible = [];
        }

        return $this->baseVisible = $this->expand($this->memberSectionIds($user));
    }

    /**
     * Section IDs whose data the authenticated user may see, or null when
     * unrestricted (multi_site off). Can be empty — the caller's whereIn()
     * then matches nothing, which is the safe default.
     *
     * @return int[]|null
     */
    public function visibleSectionIds(): ?array
    {
        if ($this->visible !== false) {
            return $this->visible;
        }

        $base = $this->baseVisibleSectionIds();
        if ($base === null) {
            return $this->visible = null;
        }

        $chosen = $this->resolver->chosenSectionId();
        if ($chosen !== null) {
            $base = array_values(array_intersect($base, $this->expand([$chosen])));
        }

        return $this->visible = $base;
    }

    /** May the user pick this section in the navbar switcher? */
    public function canChoose(int $sectionId): bool
    {
        $base = $this->baseVisibleSectionIds();

        return $base === null || in_array($sectionId, $base, true);
    }

    /**
     * Final scope for a listing: an explicit `?section=` filter intersected
     * with the visible set — a request parameter can narrow the scope but
     * never widen it. Null = unrestricted.
     *
     * @return int[]|null
     */
    public function resolveScope(int $requestedSectionId = 0, bool $subsections = true): ?array
    {
        $visible = $this->visibleSectionIds();

        if ($requestedSectionId <= 0) {
            return $visible;
        }

        $requested = $subsections ? $this->expand([$requestedSectionId]) : [$requestedSectionId];

        return $visible === null
            ? $requested
            : array_values(array_intersect($requested, $visible));
    }

    /**
     * Apply the resolved scope to a query (Eloquent or Query Builder).
     *
     * @template TQuery of \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Contracts\Database\Eloquent\Builder
     *
     * @param  TQuery  $query
     * @return TQuery
     */
    public function apply($query, string $column, int $requestedSectionId = 0, bool $subsections = true)
    {
        $scope = $this->resolveScope($requestedSectionId, $subsections);
        if ($scope !== null) {
            $query->whereIn($column, $scope);
        }

        return $query;
    }

    /** May the user attach data to / read data from this section? */
    public function allows(int $sectionId): bool
    {
        $visible = $this->visibleSectionIds();

        return $visible === null || in_array($sectionId, $visible, true);
    }

    /**
     * A submitted section id, forced back inside the visible set: returned
     * unchanged when allowed, otherwise replaced by the navbar choice, the
     * user's home section, or the first visible section.
     */
    public function coerce(?int $sectionId): ?int
    {
        if ($sectionId !== null && $sectionId > 0 && $this->allows($sectionId)) {
            return $sectionId;
        }

        $visible = $this->visibleSectionIds();
        if ($visible === null) {
            return $sectionId;
        }

        $chosen = $this->resolver->chosenSectionId();
        if ($chosen !== null && in_array($chosen, $visible, true)) {
            return $chosen;
        }

        $home = auth()->user()?->P_SECTION;
        if ($home !== null && in_array((int) $home, $visible, true)) {
            return (int) $home;
        }

        return $visible[0] ?? null;
    }

    /** Default section for new records: navbar choice, else home section. */
    public function defaultSectionId(): ?int
    {
        return $this->coerce(
            $this->resolver->chosenSectionId()
                ?? (auth()->user()?->P_SECTION !== null ? (int) auth()->user()->P_SECTION : null)
        );
    }

    /**
     * Visible sections as a depth-annotated list in DFS (tree) order, for
     * the <x-ob-section-select> component and the navbar switcher. Siblings
     * follow the org-chart order (S_ORDER, S_CODE), so the root site always
     * comes first by structure. Depth only advances under visible sections
     * so a hidden intermediate level leaves no dangling indent.
     *
     * @param  bool  $ignoreChosen  list the full base set instead of the
     *                              choice-narrowed one (navbar switcher).
     * @return array<int,array{S_ID:int,S_CODE:?string,S_DESCRIPTION:?string,depth:int}>
     */
    public function options(bool $ignoreChosen = false): array
    {
        if (! $this->features->isEnabled('multi_site')) {
            return [];
        }

        $visible = $ignoreChosen ? $this->baseVisibleSectionIds() : $this->visibleSectionIds();
        $allowed = $visible !== null ? array_flip($visible) : null;

        $out = [];
        $visited = [];
        $walk = function (int $parent, int $depth) use (&$walk, &$visited, $allowed, &$out): void {
            foreach ($this->childrenOf($parent) as $s) {
                $sId = (int) $s->S_ID;
                if (isset($visited[$sId])) {
                    continue; // guard against parent-link cycles in legacy data
                }
                $visited[$sId] = true;

                $show = $allowed === null || isset($allowed[$sId]);
                if ($show) {
                    $out[] = [
                        'S_ID' => $sId,
                        'S_PARENT' => (int) $s->S_PARENT,
                        'S_CODE' => $s->S_CODE,
                        'S_DESCRIPTION' => $s->S_DESCRIPTION,
                        'depth' => $depth,
                    ];
                }
                $walk($sId, $show ? $depth + 1 : $depth);
            }
        };
        $walk(0, 0);

        return $out;
    }

    /**
     * Sections for the navbar switcher, as depth-annotated objects in
     * org-chart tree order. Always the full base set — never narrowed by
     * the current choice.
     */
    public function switcherSections(): Collection
    {
        return collect($this->options(ignoreChosen: true))
            ->map(fn (array $o) => (object) array_merge($o, [
                'S_DESCRIPTION' => $o['S_DESCRIPTION'] ?: 'Section '.$o['S_ID'],
            ]));
    }

    /**
     * IDs of a section and all its descendants (org-tree subtree).
     *
     * @return int[]
     */
    public function descendantIds(int $sectionId): array
    {
        return $this->expand([$sectionId]);
    }

    // ── internals ─────────────────────────────────────────────────────────────

    /**
     * Sections the user belongs to or holds a section-scoped role in.
     *
     * @return int[]
     */
    private function memberSectionIds($user): array
    {
        $pid = (int) $user->P_ID;

        $ids = DB::table('ob_personnel_section')
            ->where('person_id', $pid)
            ->pluck('section_id')
            ->merge(
                DB::table('ob_user_assignment')
                    ->where('person_id', $pid)
                    ->whereNotNull('section_id')
                    ->pluck('section_id')
            );

        if ($user->P_SECTION !== null) {
            $ids->push($user->P_SECTION);
        }

        return $ids->map(fn ($v) => (int) $v)
            ->unique()
            ->filter(fn ($v) => $v > 0)
            ->values()
            ->all();
    }

    /**
     * The given section ids plus all their descendants.
     *
     * @param  int[]  $ids
     * @return int[]
     */
    private function expand(array $ids): array
    {
        $out = [];
        $queue = array_values(array_unique($ids));
        while ($queue !== []) {
            $id = array_shift($queue);
            if (isset($out[$id])) {
                continue;
            }
            $out[$id] = true;
            foreach ($this->childrenOf($id) as $child) {
                $queue[] = (int) $child->S_ID;
            }
        }

        return array_map('intval', array_keys($out));
    }

    /** @return Collection direct children of a section, in display order */
    private function childrenOf(int $parentId): Collection
    {
        if ($this->children === null) {
            $this->children = [];
            foreach ($this->allSections() as $s) {
                $this->children[(int) $s->S_PARENT][] = $s;
            }
        }

        return collect($this->children[$parentId] ?? []);
    }

    private function allSections(): Collection
    {
        // Canonical org-chart sibling order (same as the Organisation pages).
        return $this->sections ??= DB::table('section')
            ->orderBy('S_ORDER')
            ->orderBy('S_CODE')
            ->get(['S_ID', 'S_PARENT', 'S_CODE', 'S_DESCRIPTION']);
    }
}
