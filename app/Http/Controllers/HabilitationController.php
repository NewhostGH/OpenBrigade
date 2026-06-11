<?php

namespace App\Http\Controllers;

use App\Models\ObGroup;
use App\Services\FeatureService;
use App\Services\PermissionResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Section-scoped habilitation administration: three tabs —
 *   1. Plafonds par section  — per-section deny-list (ob_section_permission)
 *   2. Groupes d'accès        — global groups × feature grants (ob_group_permission)
 *   3. Rôles organisationnels — roles × feature grants, section-filtered
 */
class HabilitationController extends Controller
{
    public function __construct(private readonly PermissionResolver $resolver) {}

    public function index(Request $request): View
    {
        // Without multi-site there is one section: per-section ceilings are
        // hidden and the screen opens on the groups matrix instead.
        $defaultTab = app(FeatureService::class)->isEnabled('multi_site') ? 'ceiling' : 'groups';
        $tab = in_array($request->string('tab')->toString(), ['ceiling', 'groups', 'roles'], true)
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

        // "group_id|feature_id" => true
        $grants = DB::table('ob_group_permission')->get()
            ->mapWithKeys(fn ($r) => ["{$r->group_id}|{$r->feature_id}" => true]);

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

        return view('admin.habilitations.index', [
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
            'obsolete' => array_map('intval', config('habilitations.obsolete_features', [])),
        ]);
    }

    /** Toggle a group/role -> feature grant (ob_group_permission). */
    public function toggleGrant(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'group_id' => ['required', 'integer'],
            'feature_id' => ['required', 'integer'],
            'grant' => ['required', 'boolean'],
            'tab' => ['nullable', 'string'],
            'section' => ['nullable', 'integer'],
        ]);

        $group = ObGroup::find((int) $v['group_id']);
        abort_if($group === null, 404);

        if ($group->isProtected()) {
            return $this->backToTab($v, 'error', 'Ce groupe système est en lecture seule.');
        }

        if ($v['grant']) {
            DB::table('ob_group_permission')->insertOrIgnore([
                'group_id' => (int) $v['group_id'],
                'feature_id' => (int) $v['feature_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('ob_group_permission')
                ->where('group_id', (int) $v['group_id'])
                ->where('feature_id', (int) $v['feature_id'])
                ->delete();
        }

        return $this->backToTab($v, 'success', 'Habilitation mise à jour.');
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
            return redirect()->route('admin.habilitations', ['tab' => 'ceiling', 'section' => $v['section_id']])
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

        return redirect()->route('admin.habilitations', ['tab' => 'ceiling', 'section' => $v['section_id']])
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

        return redirect()->route('admin.habilitations', ['tab' => $tab])
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
            return redirect()->route('admin.habilitations')->with('error', 'Groupe système protégé.');
        }

        $group->update($v);
        $tab = $group->kind === ObGroup::KIND_ROLE ? 'roles' : 'groups';

        return redirect()->route('admin.habilitations', ['tab' => $tab])->with('success', 'Mis à jour.');
    }

    public function groupDestroy(int $gpId): RedirectResponse
    {
        $group = ObGroup::find($gpId);
        abort_if($group === null, 404);
        if ($group->isProtected()) {
            return redirect()->route('admin.habilitations')->with('error', 'Groupe système protégé.');
        }

        $inUse = DB::table('ob_personnel_group')->where('group_id', $gpId)->exists()
            || DB::table('ob_user_assignment')->where('group_id', $gpId)->exists();
        if ($inUse) {
            return redirect()->route('admin.habilitations')
                ->with('error', 'Affecté à du personnel : suppression impossible.');
        }

        DB::table('ob_group_permission')->where('group_id', $gpId)->delete();
        $tab = $group->kind === ObGroup::KIND_ROLE ? 'roles' : 'groups';
        $group->delete();

        return redirect()->route('admin.habilitations', ['tab' => $tab])->with('success', 'Supprimé.');
    }

    /** @param array<string,mixed> $v */
    private function backToTab(array $v, string $flash, string $message): RedirectResponse
    {
        $params = array_filter([
            'tab' => $v['tab'] ?? null,
            'section' => $v['section'] ?? null,
        ], fn ($x) => $x !== null && $x !== '');

        return redirect()->route('admin.habilitations', $params)->with($flash, $message);
    }
}
