<?php

namespace App\Http\Controllers;

use App\Models\ObDocumentAcl;
use App\Models\ObGroup;
use App\Services\DocumentAclService;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Per-object ACL management ("Partager") for a library file or folder. Reachable
 * by anyone holding the SHARE right on the item (or the legacy manage permission
 * 47). The resolution itself lives in {@see DocumentAclService}.
 */
class DocumentAclController extends Controller
{
    public function __construct(
        private readonly DocumentService $documents,
        private readonly DocumentAclService $acl,
    ) {}

    public function show(Request $request, string $type, int $id): View
    {
        [$name, $sectionId] = $this->resolveResource($type, $id);
        $this->ensureShare($request, $type, $id, $sectionId);

        $groups = ObGroup::query()->where('kind', ObGroup::KIND_GROUP)->orderBy('ordering')->orderBy('id')->get(['id', 'name']);
        $roles = ObGroup::query()->where('kind', ObGroup::KIND_ROLE)->orderBy('ordering')->orderBy('id')->get(['id', 'name']);
        $people = DB::table('pompier')->where('P_SECTION', $sectionId)->whereNull('P_FIN')
            ->orderBy('P_NOM')->orderBy('P_PRENOM')->get(['P_ID', 'P_NOM', 'P_PRENOM']);

        // Label maps for rendering the current ACEs.
        $groupNames = $groups->pluck('name', 'id')->all() + $roles->pluck('name', 'id')->all();
        $peopleNames = $people->mapWithKeys(fn ($p) => [(int) $p->P_ID => $p->P_NOM.' '.$p->P_PRENOM])->all();

        return view('document.acl', [
            'layout' => $request->boolean('window') ? 'layout.popup' : 'layout.app',
            'isWindow' => $request->boolean('window'),
            'type' => $type,
            'id' => $id,
            'name' => $name,
            'sectionId' => $sectionId,
            'aces' => $this->acl->ownAces($type, $id),
            'groups' => $groups,
            'roles' => $roles,
            'people' => $people,
            'groupNames' => $groupNames,
            'peopleNames' => $peopleNames,
            'rightLabels' => ObDocumentAcl::rightLabels(),
        ]);
    }

    public function store(Request $request, string $type, int $id): RedirectResponse
    {
        [, $sectionId] = $this->resolveResource($type, $id);
        $this->ensureShare($request, $type, $id, $sectionId);

        $v = $request->validate([
            'principal_type' => ['required', 'in:user,group,role,everyone'],
            'group_id' => ['nullable', 'integer'],
            'role_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'effect' => ['required', 'in:allow,deny'],
            'rights' => ['required', 'array', 'min:1'],
            'rights.*' => ['integer'],
        ]);

        $principalId = match ($v['principal_type']) {
            'group' => (int) ($v['group_id'] ?? 0),
            'role' => (int) ($v['role_id'] ?? 0),
            'user' => (int) ($v['user_id'] ?? 0),
            default => 0,
        };
        if ($v['principal_type'] !== 'everyone' && $principalId <= 0) {
            return back()->with('error', 'Sélectionnez un bénéficiaire.');
        }

        $rights = array_sum(array_map('intval', $v['rights']));
        $this->acl->setAce($type, $id, $v['principal_type'], $principalId, $v['effect'], $rights, (int) $request->user()->P_ID);

        return redirect()->route('document.acl', $this->backParams($request, $type, $id))->with('success', 'Autorisation enregistrée.');
    }

    public function destroy(Request $request, int $ace): RedirectResponse
    {
        $row = DB::table('ob_document_acl')->where('id', $ace)->first(['resource_type', 'resource_id']);
        abort_if($row === null, 404);

        [, $sectionId] = $this->resolveResource($row->resource_type, (int) $row->resource_id);
        $this->ensureShare($request, $row->resource_type, (int) $row->resource_id, $sectionId);

        $this->acl->removeAce($ace);

        return redirect()->route('document.acl', $this->backParams($request, $row->resource_type, (int) $row->resource_id))
            ->with('success', 'Autorisation supprimée.');
    }

    /** Route params back to the ACL page, preserving the popup-window flag. */
    private function backParams(Request $request, string $type, int $id): array
    {
        return $request->boolean('window')
            ? ['type' => $type, 'id' => $id, 'window' => 1]
            : ['type' => $type, 'id' => $id];
    }

    /** @return array{0:string,1:int} [display name, section id] */
    private function resolveResource(string $type, int $id): array
    {
        abort_unless(in_array($type, [ObDocumentAcl::TYPE_FOLDER, ObDocumentAcl::TYPE_DOCUMENT], true), 404);

        $row = $type === ObDocumentAcl::TYPE_FOLDER
            ? DB::table('document_folder')->where('DF_ID', $id)->first(['DF_NAME as name', 'S_ID'])
            : DB::table('document')->where('D_ID', $id)->first(['D_NAME as name', 'S_ID']);
        abort_if($row === null, 404);

        return [(string) $row->name, (int) $row->S_ID];
    }

    private function ensureShare(Request $request, string $type, int $id, int $sectionId): void
    {
        abort_unless(
            $this->documents->authorize($request->user(), $type, $id, ObDocumentAcl::RIGHT_SHARE, $sectionId),
            403,
        );
    }
}
