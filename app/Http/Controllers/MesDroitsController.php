<?php

namespace App\Http\Controllers;

use App\Services\PermissionResolver;
use App\Services\SectionScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Read-only "Mes droits" screen: the current user previews their effective
 * permissions for a chosen section and role (default "all roles"). Groups are
 * always applied and not selectable.
 */
class MesDroitsController extends Controller
{
    public function __construct(
        private readonly PermissionResolver $resolver,
        private readonly SectionScopeService $sectionScope,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        // Same set and order as the navbar switcher (memberships + descendants).
        $sections = $this->sectionScope->switcherSections();
        $sectionId = (int) $request->integer('section')
            ?: ($this->resolver->activeSectionId($user) ?? (int) ($sections->first()->S_ID ?? 0));

        $roles = $this->resolver->userRoles($user, $sectionId);
        $roleId = $request->integer('role') ?: null;

        // Origin maps (display only): which group/role grants each feature.
        $groupIds = DB::table('ob_personnel_group')
            ->where('person_id', (int) $user->P_ID)
            ->where('group_id', '!=', -1)
            ->pluck('group_id')
            ->map(fn ($v) => (int) $v)
            ->values()
            ->all();

        $origins = []; // feature_id => [labels]
        $this->collectOrigins(
            $origins,
            DB::table('ob_group_permission as gp')
                ->join('ob_group as g', 'g.id', '=', 'gp.group_id')
                ->whereIn('gp.group_id', $groupIds)
                ->get(['gp.feature_id', 'g.name'])
        );

        $roleQuery = DB::table('ob_user_assignment as a')
            ->join('ob_group as g', 'g.id', '=', 'a.group_id')
            ->join('ob_group_permission as gp', 'gp.group_id', '=', 'a.group_id')
            ->where('a.person_id', (int) $user->P_ID)
            ->where('g.kind', 'role');
        if ($roleId !== null) {
            $roleQuery->where('a.group_id', $roleId);
        }
        $this->collectOrigins($origins, $roleQuery->get(['gp.feature_id', 'g.name']));

        $denied = $this->resolver->effectiveDenied($sectionId);

        $features = DB::table('fonctionnalite as f')
            ->leftJoin('type_fonctionnalite as tf', 'tf.TF_ID', '=', 'f.TF_ID')
            ->orderBy('f.TF_ID')
            ->orderBy('f.F_ID')
            ->get(['f.F_ID', 'f.F_LIBELLE', 'f.F_DESCRIPTION', 'f.F_FLAG', 'tf.TF_DESCRIPTION as category']);

        return view('mes-droits.index', [
            'sections' => $sections,
            'sectionId' => $sectionId,
            'roles' => $roles,
            'roleId' => $roleId,
            'featuresByCategory' => $features->groupBy('category'),
            'origins' => $origins,
            'denied' => $denied,
            'obsolete' => array_map('intval', config('habilitations.obsolete_features', [])),
        ]);
    }

    /**
     * @param  array<int,string[]>  $origins
     * @param  Collection<int,object>  $rows
     */
    private function collectOrigins(array &$origins, $rows): void
    {
        foreach ($rows as $r) {
            $fid = (int) $r->feature_id;
            $origins[$fid] ??= [];
            if (! in_array($r->name, $origins[$fid], true)) {
                $origins[$fid][] = $r->name;
            }
        }
    }
}
