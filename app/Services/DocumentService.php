<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Document library business logic — single source of truth for folder trees,
 * the library listing, per-document visibility, and on-disk file paths. Keeps
 * {@see App\Http\Controllers\DocumentController} thin (CONVENTIONS §3).
 *
 * Library documents are those not attached to any entity (event, person,
 * vehicle…) — see {@see Document::scopeLibrary()}.
 */
class DocumentService implements ServiceInterface
{
    /** All folders for a section, ordered for tree + breadcrumb building. */
    public function folders(int $sectionId): Collection
    {
        return DB::table('document_folder')
            ->where('S_ID', $sectionId)
            ->orderBy('DF_NAME')
            ->get(['DF_ID', 'DF_PARENT', 'DF_NAME', 'TD_CODE']);
    }

    /** Root folders (DF_PARENT = 0 or null) from an already-loaded set. */
    public function rootFolders(Collection $folders): Collection
    {
        return $folders->filter(fn ($f) => ! $f->DF_PARENT)->values();
    }

    /** Direct sub-folders of a folder (0 = root) from an already-loaded set. */
    public function subFolders(Collection $folders, int $folderId): Collection
    {
        if ($folderId === 0) {
            return $this->rootFolders($folders);
        }

        return $folders->filter(fn ($f) => (int) $f->DF_PARENT === $folderId)->values();
    }

    /**
     * Breadcrumb (root → current) for a folder, cycle-guarded.
     *
     * @return array<int,array{id:int,name:string}>
     */
    public function breadcrumb(Collection $folders, int $folderId): array
    {
        $crumbs = [];
        $current = $folderId;
        $visited = [];

        while ($current > 0 && ! in_array($current, $visited, true)) {
            $folder = $folders->firstWhere('DF_ID', $current);
            if (! $folder) {
                break;
            }
            $crumbs[] = ['id' => (int) $folder->DF_ID, 'name' => (string) $folder->DF_NAME];
            $visited[] = $current;
            $current = (int) ($folder->DF_PARENT ?? 0);
        }

        return array_reverse($crumbs);
    }

    /** Document types, optionally filtered to the brigade's syndicate flag. */
    public function types(int $syndicate = 0): Collection
    {
        return DB::table('type_document')
            ->where('TD_SYNDICATE', $syndicate)
            ->orderBy('TD_LIBELLE')
            ->get(['TD_CODE', 'TD_LIBELLE', 'TD_SECURITY']);
    }

    /**
     * Paginated library listing for a section + folder, filtered by type.
     * Each row carries a computed `can_view` flag (the download gate) so the
     * view never re-implements the security rules.
     */
    public function documents(User $user, int $sectionId, int $folderId, string $typeCode): LengthAwarePaginator
    {
        $query = DB::table('document as d')
            ->leftJoin('type_document as td', 'd.TD_CODE', '=', 'td.TD_CODE')
            ->leftJoin('document_security as ds', 'd.DS_ID', '=', 'ds.DS_ID')
            ->leftJoin('pompier as p', 'd.D_CREATED_BY', '=', 'p.P_ID')
            ->where('d.S_ID', $sectionId)
            // library documents only — not attached to any entity
            ->where('d.E_CODE', 0)->where('d.P_ID', 0)->where('d.V_ID', 0)
            ->where('d.M_ID', 0)->where('d.NF_ID', 0)->where('d.VI_ID', 0)->where('d.EL_ID', 0)
            ->where('d.DF_ID', $folderId);

        if ($typeCode !== 'ALL') {
            $query->where('d.TD_CODE', $typeCode);
        }

        $page = $query
            ->orderByDesc('d.D_CREATED_DATE')
            ->select(
                'd.D_ID', 'd.D_NAME', 'd.TD_CODE', 'd.DS_ID', 'd.D_CREATED_BY', 'd.D_CREATED_DATE',
                'td.TD_LIBELLE', 'td.TD_SECURITY',
                'ds.DS_LIBELLE', 'ds.F_ID as DS_FID',
                DB::raw("TRIM(CONCAT(COALESCE(p.P_PRENOM,''), ' ', COALESCE(p.P_NOM,''))) as created_by_name")
            )
            ->paginate(30)
            ->withQueryString();

        $page->getCollection()->transform(function ($row) use ($user, $sectionId) {
            $row->can_view = $this->canView(
                $user,
                $sectionId,
                (int) ($row->TD_SECURITY ?? 0),
                (int) ($row->DS_FID ?? 0),
                (int) $row->D_CREATED_BY,
            );

            return $row;
        });

        return $page;
    }

    /**
     * May the user view/download a document, given its type-security (F_ID
     * required for the type), its document-security (F_ID required for the row)
     * and its creator? Mirrors the legacy showfile.php gate for library docs.
     */
    public function canView(User $user, int $sectionId, int $typeSecurity, int $docSecurityFid, int $createdBy): bool
    {
        // The document's type may itself require a feature to be seen at all.
        if ($typeSecurity > 0 && ! $user->hasPermissionInSection($typeSecurity, $sectionId)) {
            return false;
        }

        // Public document, the creator, or a library manager always passes.
        if ($docSecurityFid === 0 || (int) $user->P_ID === $createdBy) {
            return true;
        }

        return $user->hasPermissionInSection($docSecurityFid, $sectionId)
            || $user->hasPermissionInSection((int) config('documents.feature_manage'), $sectionId);
    }

    /** Absolute on-disk path of a library document's file. */
    public function filePath(int $sectionId, int $folderId, string $name): string
    {
        $root = base_path(config('legacy_bridge.legacy_root').'/'.config('documents.files_subpath'));
        $dir = $folderId > 0 ? $root.'/'.$sectionId.'/'.$folderId : $root.'/'.$sectionId;

        return $dir.'/'.basename($name);
    }
}
