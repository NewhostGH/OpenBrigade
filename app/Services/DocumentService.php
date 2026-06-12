<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\ObDocumentAcl;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
    public function __construct(private readonly DocumentAclService $acl) {}

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

    /**
     * Nested folder tree for the sidebar, built from an already-loaded flat set.
     * Each node is ['folder' => object, 'children' => node[]].
     *
     * @return array<int,array{folder:object,children:array<mixed>}>
     */
    public function folderTree(Collection $folders, int $parentId = 0): array
    {
        return $folders
            ->filter(fn ($f) => (int) ($f->DF_PARENT ?? 0) === $parentId)
            ->map(fn ($f) => [
                'folder' => $f,
                'children' => $this->folderTree($folders, (int) $f->DF_ID),
            ])
            ->values()
            ->all();
    }

    /**
     * Ids on the path to the open folder (the folder itself + its ancestors), so
     * the sidebar tree can render those branches expanded.
     *
     * @return int[]
     */
    public function openFolderIds(Collection $folders, int $folderId): array
    {
        return array_map(fn ($c) => $c['id'], $this->breadcrumb($folders, $folderId));
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
     * Base library-listing query (joins, library filter, type filter, select)
     * for a section + folder. Shared by the paginated view and the export.
     */
    private function listingQuery(int $sectionId, int $folderId, string $typeCode): Builder
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

        return $query->select(
            'd.D_ID', 'd.D_NAME', 'd.TD_CODE', 'd.DS_ID', 'd.DF_ID', 'd.D_CREATED_BY', 'd.D_CREATED_DATE',
            'td.TD_LIBELLE', 'td.TD_SECURITY',
            'ds.DS_LIBELLE', 'ds.F_ID as DS_FID',
            DB::raw("TRIM(CONCAT(COALESCE(p.P_PRENOM,''), ' ', COALESCE(p.P_NOM,''))) as created_by_name")
        );
    }

    /**
     * Paginated library listing for a section + folder, filtered by type.
     * Each row carries a computed `can_view` flag (the download gate) so the
     * view never re-implements the security rules.
     */
    public function documents(User $user, int $sectionId, int $folderId, string $typeCode): LengthAwarePaginator
    {
        $page = $this->listingQuery($sectionId, $folderId, $typeCode)
            ->orderByDesc('d.D_CREATED_DATE')
            ->paginate(30)
            ->withQueryString();

        // Warm the ACL cache for this page (folder chain + each document) once.
        $resources = $this->acl->inheritanceChain(ObDocumentAcl::TYPE_FOLDER, $folderId);
        foreach ($page->getCollection() as $row) {
            $resources[] = [ObDocumentAcl::TYPE_DOCUMENT, (int) $row->D_ID];
        }
        $this->acl->preload($resources);

        $manage = $user->hasPermissionInSection((int) config('documents.feature_manage'), $sectionId);

        $page->getCollection()->transform(function ($row) use ($user, $sectionId, $manage) {
            $id = (int) $row->D_ID;
            $legacyView = $this->canView(
                $user, $sectionId,
                (int) ($row->TD_SECURITY ?? 0), (int) ($row->DS_FID ?? 0), (int) $row->D_CREATED_BY,
            );

            // ACL decides when an ACE governs the item, else the legacy security.
            $row->can_view = $this->acl->can($user, ObDocumentAcl::TYPE_DOCUMENT, $id, ObDocumentAcl::RIGHT_DOWNLOAD) ?? $legacyView;
            $row->can_write = $this->acl->can($user, ObDocumentAcl::TYPE_DOCUMENT, $id, ObDocumentAcl::RIGHT_WRITE) ?? $manage;
            $row->can_delete = $this->acl->can($user, ObDocumentAcl::TYPE_DOCUMENT, $id, ObDocumentAcl::RIGHT_DELETE) ?? $manage;
            $row->can_share = $this->acl->can($user, ObDocumentAcl::TYPE_DOCUMENT, $id, ObDocumentAcl::RIGHT_SHARE) ?? $manage;

            return $row;
        });

        return $page;
    }

    /**
     * Authorise an action right on a folder/document: the ACL decides when an
     * ACE governs the item, otherwise the legacy library-manage permission (47)
     * in the item's section. Used by every write action.
     */
    public function authorize(User $user, string $resourceType, int $resourceId, int $right, int $sectionId): bool
    {
        return $this->acl->can($user, $resourceType, $resourceId, $right)
            ?? $user->hasPermission((int) config('documents.feature_manage'));
    }

    /** May the user download this document? ACL DOWNLOAD, else the legacy gate. */
    public function canDownload(User $user, Document $document, int $sectionId): bool
    {
        $acl = $this->acl->can($user, ObDocumentAcl::TYPE_DOCUMENT, (int) $document->D_ID, ObDocumentAcl::RIGHT_DOWNLOAD);
        if ($acl !== null) {
            return $acl;
        }

        $document->loadMissing('type', 'security');

        return $this->canView(
            $user, $sectionId,
            (int) ($document->type->TD_SECURITY ?? 0),
            (int) ($document->security->F_ID ?? 0),
            (int) $document->D_CREATED_BY,
        );
    }

    /** All documents in a folder (unpaginated) for export. */
    public function documentsForExport(int $sectionId, int $folderId, string $typeCode): Collection
    {
        return $this->listingQuery($sectionId, $folderId, $typeCode)
            ->orderBy('d.D_NAME')
            ->get();
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

    /**
     * Create a folder. A child folder inherits its parent's document type
     * (the legacy app forces this). Returns the new row.
     */
    public function createFolder(int $sectionId, int $parentId, string $name, int $userId): DocumentFolder
    {
        $typeCode = null;
        if ($parentId > 0) {
            $typeCode = DB::table('document_folder')->where('DF_ID', $parentId)->value('TD_CODE');
        }

        return DocumentFolder::create([
            'S_ID' => $sectionId,
            'DF_PARENT' => $parentId,
            'DF_NAME' => $this->sanitizeFolderName($name),
            'TD_CODE' => $typeCode,
            'DF_CREATED_BY' => $userId,
            'DF_CREATED_DATE' => now(),
        ]);
    }

    public function renameFolder(DocumentFolder $folder, string $name): void
    {
        $folder->update(['DF_NAME' => $this->sanitizeFolderName($name)]);
    }

    /** A folder is deletable only when it holds no sub-folder and no document. */
    public function folderIsEmpty(DocumentFolder $folder): bool
    {
        return ! DB::table('document_folder')->where('DF_PARENT', $folder->DF_ID)->exists()
            && ! DB::table('document')->where('DF_ID', $folder->DF_ID)->exists();
    }

    /** Does a sibling folder already use this name? (unique S_ID, DF_PARENT, DF_NAME) */
    public function folderNameExists(int $sectionId, int $parentId, string $name, ?int $exceptId = null): bool
    {
        return DocumentFolder::query()
            ->where('S_ID', $sectionId)
            ->where('DF_PARENT', $parentId)
            ->where('DF_NAME', $this->sanitizeFolderName($name))
            ->when($exceptId !== null, fn ($q) => $q->where('DF_ID', '!=', $exceptId))
            ->exists();
    }

    /** Strip path/quote characters the legacy app also rejected in folder names. */
    public function sanitizeFolderName(string $name): string
    {
        return trim(str_replace(['\\', '/', '"', "'"], '', $name));
    }

    /** FontAwesome classes (icon + colour) for a file, by extension. */
    public function fileIcon(string $name): string
    {
        return match (strtolower(pathinfo($name, PATHINFO_EXTENSION))) {
            'pdf' => 'fa-file-pdf text-danger',
            'jpg', 'jpeg', 'png', 'gif' => 'fa-file-image text-info',
            'doc', 'docx', 'odt' => 'fa-file-word text-primary',
            'xls', 'xlsx' => 'fa-file-excel text-success',
            'ppt', 'pptx', 'pps' => 'fa-file-powerpoint text-warning',
            'zip' => 'fa-file-zipper text-secondary',
            'mp3' => 'fa-file-audio text-body-secondary',
            default => 'fa-file text-muted',
        };
    }

    /**
     * Canonical on-disk directory for a section/folder's files —
     * storage/app/private/documents/{S_ID}/{DF_ID} (section 0 = root, and the
     * folder segment is omitted for files directly in a section's root).
     */
    public function fileDir(int $sectionId, int $folderId): string
    {
        $root = storage_path('app/private/'.config('documents.storage_subpath'));

        return $folderId > 0 ? $root.'/'.$sectionId.'/'.$folderId : $root.'/'.$sectionId;
    }

    /** Canonical on-disk path of a library document's file. */
    public function filePath(int $sectionId, int $folderId, string $name): string
    {
        return $this->fileDir($sectionId, $folderId).'/'.basename($name);
    }

    /** Old on-disk path under the legacy app, kept as a read-only fallback. */
    public function legacyFilePath(int $sectionId, int $folderId, string $name): string
    {
        $root = base_path(config('legacy_bridge.legacy_root').'/'.config('documents.legacy_subpath'));
        $dir = $folderId > 0 ? $root.'/'.$sectionId.'/'.$folderId : $root.'/'.$sectionId;

        return $dir.'/'.basename($name);
    }

    /**
     * Where the file actually lives: the canonical path if present, else the
     * legacy fallback, else null (missing). Used by every read/move.
     */
    public function existingFilePath(int $sectionId, int $folderId, string $name): ?string
    {
        $canonical = $this->filePath($sectionId, $folderId, $name);
        if (File::exists($canonical)) {
            return $canonical;
        }

        $legacy = $this->legacyFilePath($sectionId, $folderId, $name);

        return File::exists($legacy) ? $legacy : null;
    }

    /** Sanitise an uploaded filename like the legacy upload helper. */
    public function sanitizeFileName(string $name): string
    {
        $name = basename(str_replace('\\', '', $name));

        return str_replace([' ', '°', '#', "'", '&', '+', '(', ')'], ['_', '', '', '', '', '', '', ''], $name);
    }

    /** All document types (including syndicate ones) for the management screen. */
    public function manageableTypes(): Collection
    {
        return DB::table('type_document')->orderBy('TD_LIBELLE')->get(['TD_CODE', 'TD_LIBELLE', 'TD_SECURITY', 'TD_SYNDICATE']);
    }

    /** Features (F_ID → label) for the type-security select; F_ID 0 means public. */
    public function features(): Collection
    {
        return DB::table('fonctionnalite')->orderBy('F_LIBELLE')->get(['F_ID', 'F_LIBELLE']);
    }

    /** Is a document type still referenced by a document or folder? */
    public function typeInUse(string $code): bool
    {
        return DB::table('document')->where('TD_CODE', $code)->exists()
            || DB::table('document_folder')->where('TD_CODE', $code)->exists();
    }

    /**
     * Store one uploaded file on disk and record the document row. Per-document
     * visibility is governed by the ACL, so DS_ID is fixed to 1 (public/legacy
     * default) — restrict access with the "Partager" ACL instead.
     */
    public function storeUpload(int $sectionId, int $folderId, UploadedFile $file, string $typeCode, int $userId): void
    {
        $name = $this->sanitizeFileName($file->getClientOriginalName());
        $dir = $this->fileDir($sectionId, $folderId);
        File::ensureDirectoryExists($dir);
        $file->move($dir, $name);

        Document::create([
            'S_ID' => $sectionId,
            'D_NAME' => $name,
            'TD_CODE' => $typeCode,
            'DS_ID' => 1,
            'D_CREATED_BY' => $userId,
            'D_CREATED_DATE' => now(),
            'DF_ID' => $folderId,
        ]);
    }

    /**
     * Edit a document: rename it, change its type and/or move it to another
     * folder — a single on-disk move covers both the rename and the relocation.
     */
    public function updateDocument(Document $document, string $name, string $typeCode, int $newFolderId): void
    {
        $name = $this->sanitizeFileName($name) ?: (string) $document->D_NAME;
        $sectionId = (int) $document->S_ID;
        $oldFolderId = (int) $document->DF_ID;
        $oldName = (string) $document->D_NAME;

        if ($name !== $oldName || $newFolderId !== $oldFolderId) {
            $src = $this->existingFilePath($sectionId, $oldFolderId, $oldName);
            if ($src !== null) {
                $destDir = $this->fileDir($sectionId, $newFolderId);
                File::ensureDirectoryExists($destDir);
                File::move($src, $destDir.'/'.$name);
            }
        }

        $document->update(['D_NAME' => $name, 'TD_CODE' => $typeCode, 'DF_ID' => $newFolderId]);
    }

    /** Delete a document's file (canonical and/or legacy) and its row. */
    public function deleteDocument(Document $document): void
    {
        foreach ([
            $this->filePath((int) $document->S_ID, (int) $document->DF_ID, $document->D_NAME),
            $this->legacyFilePath((int) $document->S_ID, (int) $document->DF_ID, $document->D_NAME),
        ] as $path) {
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        $document->delete();
    }
}
