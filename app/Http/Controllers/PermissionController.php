<?php

namespace App\Http\Controllers;

use App\Models\ObGroup;
use App\Services\FeatureService;
use App\Services\PermissionResolver;
use App\Services\SectionScopeService;
use App\Services\TableExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Section-scoped habilitation administration (full ACL with groups): four tabs —
 *   1. Plafonds par section  — per-section deny-list (ob_section_permission)
 *   2. Groups d'accès        — global groups × feature grants, allow/deny (ob_group_permission)
 *   3. Rôles organisationnels — roles × feature grants, allow/deny, section-filtered
 *   4. Dérogations            — per-person allow/deny overrides (ob_user_permission)
 *
 * The resolution precedence lives in {@see PermissionResolver}: user deny >
 * user allow > section deny > group/role deny > group/role allow > default deny.
 */
class PermissionController extends Controller
{
    public function __construct(private readonly PermissionResolver $resolver) {}

    public function index(Request $request): View
    {
        // Without multi-site there is one section: per-section ceilings are
        // hidden and the screen opens on the groups matrix instead.
        $defaultTab = app(FeatureService::class)->isEnabled('multi_site') ? 'ceiling' : 'groups';
        $tab = in_array($request->string('tab')->toString(), ['ceiling', 'groups', 'roles', 'overrides'], true)
            ? $request->string('tab')->toString()
            : $defaultTab;

        $features = DB::table('fonctionnalite as f')
            ->leftJoin('type_fonctionnalite as tf', 'tf.TF_ID', '=', 'f.TF_ID')
            ->orderBy('f.TF_ID')
            ->orderBy('f.F_ID')
            ->get(['f.F_ID', 'f.F_LIBELLE', 'f.F_DESCRIPTION', 'f.F_FLAG', 'tf.TF_DESCRIPTION as category']);
        $featuresByCategory = $features->groupBy('category');

        $sections = DB::table('section')
            ->orderBy('S_PARENT')
            ->orderBy('S_DESCRIPTION')
            ->get(['S_ID', 'S_PARENT', 'S_DESCRIPTION'])
            ->each(fn ($s) => $s->S_DESCRIPTION = $s->S_DESCRIPTION ?: 'Section '.$s->S_ID);

        $sectionId = (int) $request->integer('section') ?: (int) ($sections->first()->S_ID ?? 0);
        $selected = $sections->firstWhere('S_ID', $sectionId);

        $groups = ObGroup::query()->where('kind', ObGroup::KIND_GROUP)
            ->orderBy('ordering')->orderBy('id')->get();
        $roles = ObGroup::query()->where('kind', ObGroup::KIND_ROLE)
            ->orderBy('ordering')->orderBy('id')->get();

        // "group_id|feature_id" => effect ('allow'|'deny')
        $grants = DB::table('ob_group_permission')->get()
            ->mapWithKeys(fn ($r) => ["{$r->group_id}|{$r->feature_id}" => $r->effect]);

        // Dérogations tab: personnel picker + the selected person's overrides at
        // the chosen scope. section_id = -1 (SectionScopeService::ALL) means
        // "toutes les sections / global"; 0 is the real root section.
        $rawScope = $request->input('section');
        $scopeId = ($rawScope === null || $rawScope === '') ? SectionScopeService::ALL : (int) $rawScope;
        $personId = (int) $request->integer('person') ?: null;
        [$people, $person, $userGrants] = $this->overridesData($tab, $request, $personId, $scopeId);

        // Ceiling tab: features denied at the selected section, and those locked
        // by a parent (cannot be re-allowed here).
        $ownDenied = DB::table('ob_section_permission')
            ->where('section_id', $sectionId)
            ->pluck('feature_id')->map(fn ($v) => (int) $v)->all();
        $parentDenied = $selected
            ? $this->resolver->effectiveDenied((int) $selected->S_PARENT ?: null)
            : [];

        // Group/role grant tabs: features capped by the previewed section.
        $sectionDenied = $this->resolver->effectiveDenied($sectionId);

        return view('admin.permissions.index', [
            'tab' => $tab,
            'featuresByCategory' => $featuresByCategory,
            'sections' => $sections,
            'sectionId' => $sectionId,
            'selected' => $selected,
            'groups' => $groups,
            'roles' => $roles,
            'grants' => $grants,
            'ownDenied' => $ownDenied,
            'parentDenied' => $parentDenied,
            'sectionDenied' => $sectionDenied,
            'people' => $people,
            'person' => $person,
            'scopeId' => $scopeId,
            'userGrants' => $userGrants,
            'obsolete' => array_map('intval', config('habilitations.obsolete_features', [])),
        ]);
    }

    /**
     * Personnel search + the selected person's overrides at the chosen scope,
     * loaded only on the Dérogations tab.
     *
     * @return array{0:Collection<int,object>,1:?object,2:Collection<int,string>}
     */
    private function overridesData(string $tab, Request $request, ?int $personId, int $scopeId): array
    {
        $people = collect();
        $person = null;
        $userGrants = collect();

        if ($tab !== 'overrides') {
            return [$people, $person, $userGrants];
        }

        $q = trim($request->string('q')->toString());
        if ($q !== '') {
            $people = DB::table('pompier')
                ->whereNull('P_FIN')
                ->where(fn ($w) => $w->where('P_NOM', 'like', "%{$q}%")->orWhere('P_PRENOM', 'like', "%{$q}%"))
                ->orderBy('P_NOM')->orderBy('P_PRENOM')
                ->limit(30)
                ->get(['P_ID', 'P_NOM', 'P_PRENOM']);
        }

        if ($personId !== null) {
            $person = DB::table('pompier')->where('P_ID', $personId)->first(['P_ID', 'P_NOM', 'P_PRENOM']);
            $userGrants = DB::table('ob_user_permission')
                ->where('person_id', $personId)
                ->where('section_id', $scopeId)
                ->get()
                ->mapWithKeys(fn ($r) => [(int) $r->feature_id => $r->effect]);
        }

        return [$people, $person, $userGrants];
    }

    /**
     * Set a group/role -> feature grant (ob_group_permission). effect = allow
     * grants, deny refuses, empty removes the entry (neutral).
     */
    public function setGrant(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'group_id' => ['required', 'integer'],
            'feature_id' => ['required', 'integer'],
            'effect' => ['nullable', 'in:allow,deny'],
            'tab' => ['nullable', 'string'],
            'section' => ['nullable', 'integer'],
        ]);

        $group = ObGroup::find((int) $v['group_id']);
        abort_if($group === null, 404);

        // System groups are protected against rename/delete, but their grants
        // are meant to be tuned freely. Only the "blocked" sentinel rejects them.
        if ((int) $group->id === (int) config('habilitations.block_group_id', -1)) {
            return $this->backToTab($v, 'error', 'Ce groupe est réservé et ne peut pas être modifié.');
        }

        $effect = $v['effect'] ?? null;
        if ($effect === null) {
            DB::table('ob_group_permission')
                ->where('group_id', (int) $v['group_id'])
                ->where('feature_id', (int) $v['feature_id'])
                ->delete();
        } else {
            DB::table('ob_group_permission')->updateOrInsert(
                ['group_id' => (int) $v['group_id'], 'feature_id' => (int) $v['feature_id']],
                ['effect' => $effect, 'updated_at' => now(), 'created_at' => now()],
            );
        }

        return $this->backToTab($v, 'success', 'Permission mise à jour.');
    }

    /**
     * Set a per-person override (ob_user_permission) at a section scope
     * (section_id -1 = global; 0 = root section). effect = allow|deny; empty removes it.
     */
    public function setUserGrant(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'person_id' => ['required', 'integer'],
            'feature_id' => ['required', 'integer'],
            'section_id' => ['required', 'integer'],
            'effect' => ['nullable', 'in:allow,deny'],
        ]);

        abort_if(DB::table('pompier')->where('P_ID', (int) $v['person_id'])->doesntExist(), 404);

        $key = [
            'person_id' => (int) $v['person_id'],
            'section_id' => (int) $v['section_id'],
            'feature_id' => (int) $v['feature_id'],
        ];
        $effect = $v['effect'] ?? null;
        if ($effect === null) {
            DB::table('ob_user_permission')->where($key)->delete();
        } else {
            DB::table('ob_user_permission')->updateOrInsert(
                $key,
                ['effect' => $effect, 'updated_at' => now(), 'created_at' => now()],
            );
        }

        // Keep the scope explicitly (incl. 0 = root and -1 = global) so the
        // reload lands on the same scope instead of falling back to global.
        return redirect()->route('admin.permissions', [
            'tab' => 'overrides',
            'person' => (int) $v['person_id'],
            'section' => (int) $v['section_id'],
        ])->with('success', 'Dérogation mise à jour.');
    }

    /** Toggle a section ceiling entry. allow=1 removes the deny row; allow=0 adds it. */
    public function toggleCeiling(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'section_id' => ['required', 'integer'],
            'feature_id' => ['required', 'integer'],
            'allow' => ['required', 'boolean'],
        ]);

        // A feature denied by a parent section cannot be re-allowed here.
        $section = DB::table('section')->where('S_ID', (int) $v['section_id'])->first();
        abort_if($section === null, 404);
        $parentDenied = $this->resolver->effectiveDenied((int) ($section->S_PARENT ?? 0) ?: null);
        if ($v['allow'] && in_array((int) $v['feature_id'], $parentDenied, true)) {
            return redirect()->route('admin.permissions', ['tab' => 'ceiling', 'section' => $v['section_id']])
                ->with('error', 'Cette fonctionnalité est refusée par une section parente.');
        }

        if ($v['allow']) {
            DB::table('ob_section_permission')
                ->where('section_id', (int) $v['section_id'])
                ->where('feature_id', (int) $v['feature_id'])
                ->delete();
        } else {
            DB::table('ob_section_permission')->insertOrIgnore([
                'section_id' => (int) $v['section_id'],
                'feature_id' => (int) $v['feature_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('admin.permissions', ['tab' => 'ceiling', 'section' => $v['section_id']])
            ->with('success', 'Plafond mis à jour.');
    }

    public function groupStore(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'kind' => ['required', 'in:group,role'],
            'usage' => ['required', 'in:internes,externes,all'],
        ]);

        $maxId = (int) DB::table('ob_group')->max('id');
        $newId = $v['kind'] === ObGroup::KIND_ROLE ? max($maxId + 1, 100) : $maxId + 1;

        ObGroup::create([
            'id' => $newId,
            'name' => $v['name'],
            'kind' => $v['kind'],
            'usage' => $v['usage'],
            'ordering' => 50,
            'is_system' => false,
        ]);

        $tab = $v['kind'] === ObGroup::KIND_ROLE ? 'roles' : 'groups';

        return redirect()->route('admin.permissions', ['tab' => $tab])
            ->with('success', "« {$v['name']} » créé.");
    }

    public function groupUpdate(Request $request, int $gpId): RedirectResponse
    {
        $v = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'usage' => ['required', 'in:internes,externes,all'],
            'ordering' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $group = ObGroup::find($gpId);
        abort_if($group === null, 404);
        if ($group->isProtected()) {
            return redirect()->route('admin.permissions')->with('error', 'Group système protégé.');
        }

        $group->update($v);
        $tab = $group->kind === ObGroup::KIND_ROLE ? 'roles' : 'groups';

        return redirect()->route('admin.permissions', ['tab' => $tab])->with('success', 'Mis à jour.');
    }

    public function groupDestroy(int $gpId): RedirectResponse
    {
        $group = ObGroup::find($gpId);
        abort_if($group === null, 404);
        if ($group->isProtected()) {
            return redirect()->route('admin.permissions')->with('error', 'Group système protégé.');
        }

        $inUse = DB::table('ob_personnel_group')->where('group_id', $gpId)->exists()
            || DB::table('ob_user_assignment')->where('group_id', $gpId)->exists();
        if ($inUse) {
            return redirect()->route('admin.permissions')
                ->with('error', 'Affecté à du personnel : suppression impossible.');
        }

        DB::table('ob_group_permission')->where('group_id', $gpId)->delete();
        $tab = $group->kind === ObGroup::KIND_ROLE ? 'roles' : 'groups';
        $group->delete();

        return redirect()->route('admin.permissions', ['tab' => $tab])->with('success', 'Supprimé.');
    }

    /** Export members of a group/role as XLS or CSV. */
    public function exportGroup(Request $request, int $gpId): mixed
    {
        $group = ObGroup::findOrFail($gpId);
        $format = $request->string('format', 'xlsx')->toString();
        $sectionId = (int) $request->integer('section', 0);

        if ($group->kind === ObGroup::KIND_ROLE) {
            $query = DB::table('ob_user_assignment as ua')
                ->join('pompier as p', 'p.P_ID', '=', 'ua.person_id')
                ->join('section as s', 's.S_ID', '=', 'p.P_SECTION')
                ->where('ua.group_id', $gpId);
            if ($sectionId > 0) {
                $query->where('ua.section_id', $sectionId);
            }
            $query->select(
                'p.P_NOM', 'p.P_PRENOM', 'p.P_STATUT', 'p.P_EMAIL', 'p.P_PHONE',
                's.S_CODE', 's.S_DESCRIPTION',
                DB::raw('(SELECT s2.S_CODE FROM section s2 WHERE s2.S_ID = ua.section_id) as ROLE_SECTION')
            )->orderBy('p.P_NOM')->orderBy('p.P_PRENOM');
        } else {
            $query = DB::table('ob_personnel_group as pg')
                ->join('pompier as p', 'p.P_ID', '=', 'pg.person_id')
                ->join('section as s', 's.S_ID', '=', 'p.P_SECTION')
                ->where('pg.group_id', $gpId)
                ->select(
                    'p.P_NOM', 'p.P_PRENOM', 'p.P_STATUT', 'p.P_EMAIL', 'p.P_PHONE',
                    's.S_CODE', 's.S_DESCRIPTION',
                    DB::raw('NULL as ROLE_SECTION')
                )
                ->orderBy('p.P_NOM')->orderBy('p.P_PRENOM');
        }

        $members = $query->get();

        $columns = [
            ['Nom',     fn ($r) => strtoupper($r->P_NOM ?? '')],
            ['Prénom',  fn ($r) => ucfirst(mb_strtolower($r->P_PRENOM ?? ''))],
            ['Statut',  fn ($r) => $r->P_STATUT ?? ''],
            ['Email',   fn ($r) => $r->P_EMAIL ?? ''],
            ['Téléphone', fn ($r) => $r->P_PHONE ?? ''],
            ['Section', fn ($r) => trim(($r->S_CODE ?? '').' '.($r->S_DESCRIPTION ?? ''))],
        ];

        if ($group->kind === ObGroup::KIND_ROLE) {
            $columns[] = ['Section du rôle', fn ($r) => $r->ROLE_SECTION ?? ''];
        }

        $filename = 'Habilitations_'.preg_replace('/[^A-Za-z0-9_-]/', '_', $group->name).'_'.date('Ymd');

        $service = new TableExportService;

        return $format === 'csv'
            ? $service->toCsv($columns, $members, $filename)
            : $service->toXlsx($columns, $members, $filename, ['sheetTitle' => $group->name, 'freezeHeader' => true]);
    }

    /** @param array<string,mixed> $v */
    private function backToTab(array $v, string $flash, string $message): RedirectResponse
    {
        $params = array_filter([
            'tab' => $v['tab'] ?? null,
            'section' => $v['section'] ?? null,
        ], fn ($x) => $x !== null && $x !== '');

        return redirect()->route('admin.permissions', $params)->with($flash, $message);
    }
}
