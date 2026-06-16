<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\ObDocumentAcl;
use App\Services\DocumentService;
use App\Services\PhotoService;
use App\Services\SectionScopeService;
use App\Services\TableExportService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Section document library — browse folders and documents, download files.
 * All query/business logic lives in {@see DocumentService}; this controller
 * stays thin (CONVENTIONS §3). Upload/edit/folder management is permission 47.
 */
class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentService $documents,
        private readonly SectionScopeService $sectionScope,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $sectionId = $this->resolveSectionId($request);
        $folderId = (int) $request->integer('folder', 0);
        $typeCode = $request->string('type', 'ALL')->toString() ?: 'ALL';

        $folders = $this->documents->folders($sectionId);
        $documents = $this->documents->documents($user, $sectionId, $folderId, $typeCode);

        // Explorer listing: the current folder's sub-folders first (only on the
        // first page), then the paginated documents — folders and files together.
        $rows = collect();
        if ($documents->currentPage() === 1) {
            $rows = $this->documents->subFolders($folders, $folderId)->map(function ($f) use ($user, $sectionId) {
                $fid = (int) $f->DF_ID;

                return (object) [
                    'is_folder' => true,
                    'DF_ID' => $fid,
                    'D_NAME' => $f->DF_NAME,
                    'can_write' => $this->documents->authorize($user, ObDocumentAcl::TYPE_FOLDER, $fid, ObDocumentAcl::RIGHT_WRITE, $sectionId),
                    'can_delete' => $this->documents->authorize($user, ObDocumentAcl::TYPE_FOLDER, $fid, ObDocumentAcl::RIGHT_DELETE, $sectionId),
                    'can_share' => $this->documents->authorize($user, ObDocumentAcl::TYPE_FOLDER, $fid, ObDocumentAcl::RIGHT_SHARE, $sectionId),
                ];
            });
        }
        $rows = $rows->concat($documents->items());
        // May the user add to / manage the current folder? ACL write on the
        // folder, else the legacy library-manage permission (47).
        $canManage = $this->documents->authorize($user, ObDocumentAcl::TYPE_FOLDER, $folderId, ObDocumentAcl::RIGHT_WRITE, $sectionId);
        $canConfig = $user->hasPermission((int) config('documents.feature_manage'));

        return view('document.index', [
            'folders' => $folders,
            'tree' => $this->documents->folderTree($folders),
            'openFolders' => $this->documents->openFolderIds($folders, $folderId),
            'breadcrumb' => $this->documents->breadcrumb($folders, $folderId),
            'rows' => $rows,
            'documents' => $documents,
            'folderId' => $folderId,
            'typeCode' => $typeCode,
            'types' => $this->documents->types(),
            'sectionId' => $sectionId,
            'columns' => $this->columns($sectionId),
            'canManage' => $canManage,
            'canConfig' => $canConfig,
            'canEditAny' => $canManage || $rows->contains(fn ($r) => (bool) ($r->can_write ?? false)),
        ]);
    }

    /** Upload one or more files to the current section/folder (permission 47). */
    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $v = $request->validated();
        $sectionId = (int) $v['section_id'];
        $folderId = (int) ($v['folder_id'] ?? 0);
        abort_unless($this->sectionScope->allows($sectionId), 403);
        abort_unless($this->documents->authorize($request->user(), ObDocumentAcl::TYPE_FOLDER, $folderId, ObDocumentAcl::RIGHT_WRITE, $sectionId), 403);

        foreach ($request->file('userfile') as $file) {
            $this->documents->storeUpload($sectionId, $folderId, $file, $v['type'], (int) $request->user()->P_ID);
        }

        return $this->backToFolder($sectionId, $folderId, 'success', 'Document(s) ajouté(s).');
    }

    /** Edit a document: change its type/security, optionally move it (permission 47). */
    public function update(Request $request, Document $document): RedirectResponse
    {
        abort_unless($this->isLibraryDocument($document), 404);
        $sectionId = (int) $document->S_ID;
        abort_unless($this->sectionScope->allows($sectionId), 403);
        abort_unless($this->documents->authorize($request->user(), ObDocumentAcl::TYPE_DOCUMENT, (int) $document->D_ID, ObDocumentAcl::RIGHT_WRITE, $sectionId), 403);

        $v = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'string', 'exists:type_document,TD_CODE'],
            'folder_id' => ['nullable', 'integer'],
        ]);
        $folderId = (int) ($v['folder_id'] ?? 0);

        $this->documents->updateDocument($document, $v['name'], $v['type'], $folderId);

        return $this->backToFolder($sectionId, $folderId, 'success', 'Document mis à jour.');
    }

    /** Delete a document and its file (permission 47). */
    public function destroy(Document $document): RedirectResponse
    {
        abort_unless($this->isLibraryDocument($document), 404);
        $sectionId = (int) $document->S_ID;
        $folderId = (int) $document->DF_ID;
        abort_unless($this->sectionScope->allows($sectionId), 403);
        abort_unless($this->documents->authorize(request()->user(), ObDocumentAcl::TYPE_DOCUMENT, (int) $document->D_ID, ObDocumentAcl::RIGHT_DELETE, $sectionId), 403);

        $this->documents->deleteDocument($document);

        return $this->backToFolder($sectionId, $folderId, 'success', 'Document supprimé.');
    }

    /** Export the current folder's documents as XLSX or CSV (visible columns). */
    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['xlsx', 'csv'], true), 404);

        $sectionId = $this->resolveSectionId($request);
        $folderId = (int) $request->integer('folder', 0);
        $typeCode = $request->string('type', 'ALL')->toString() ?: 'ALL';

        $rows = $this->documents->documentsForExport($sectionId, $folderId, $typeCode);

        $service = new TableExportService;
        $columns = $service->resolveColumns($this->columns($sectionId), $request, [
            ['Nom', fn ($d) => $d->D_NAME],
        ]);
        $filename = 'Documents_'.date('Ymd');

        return $format === 'csv'
            ? $service->toCsv($columns, $rows, $filename)
            : $service->toXlsx($columns, $rows, $filename, ['sheetTitle' => 'Documents']);
    }

    /** Active section: the navbar-chosen one when allowed, else the home section. */
    private function resolveSectionId(Request $request): int
    {
        $requested = $this->sectionScope->sectionFilter($request);
        if ($requested !== null && $requested >= 0 && $this->sectionScope->canChoose($requested)) {
            return $requested;
        }

        return (int) ($this->sectionScope->defaultSectionId() ?? $request->user()->P_SECTION);
    }

    /** Create a folder in the current section/folder (permission 47). */
    public function folderStore(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'section_id' => ['required', 'integer'],
            'parent_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:50'],
        ]);

        $sectionId = (int) $v['section_id'];
        $parentId = (int) ($v['parent_id'] ?? 0);
        abort_unless($this->sectionScope->allows($sectionId), 403);
        abort_unless($this->documents->authorize($request->user(), ObDocumentAcl::TYPE_FOLDER, $parentId, ObDocumentAcl::RIGHT_WRITE, $sectionId), 403);

        $name = $this->documents->sanitizeFolderName($v['name']);
        if ($name === '') {
            return $this->backToFolder($sectionId, $parentId, 'error', 'Nom de dossier invalide.');
        }
        if ($this->documents->folderNameExists($sectionId, $parentId, $name)) {
            return $this->backToFolder($sectionId, $parentId, 'error', 'Un dossier porte déjà ce nom ici.');
        }

        $this->documents->createFolder($sectionId, $parentId, $name, (int) $request->user()->P_ID);

        return $this->backToFolder($sectionId, $parentId, 'success', "Dossier « {$name} » créé.");
    }

    /** Rename a folder (permission 47). */
    public function folderUpdate(Request $request, DocumentFolder $folder): RedirectResponse
    {
        $v = $request->validate(['name' => ['required', 'string', 'max:50']]);
        $sectionId = (int) $folder->S_ID;
        $parentId = (int) $folder->DF_PARENT;
        abort_unless($this->sectionScope->allows($sectionId), 403);
        abort_unless($this->documents->authorize($request->user(), ObDocumentAcl::TYPE_FOLDER, (int) $folder->DF_ID, ObDocumentAcl::RIGHT_WRITE, $sectionId), 403);

        $name = $this->documents->sanitizeFolderName($v['name']);
        if ($name === '') {
            return $this->backToFolder($sectionId, $parentId, 'error', 'Nom de dossier invalide.');
        }
        if ($this->documents->folderNameExists($sectionId, $parentId, $name, (int) $folder->DF_ID)) {
            return $this->backToFolder($sectionId, $parentId, 'error', 'Un dossier porte déjà ce nom ici.');
        }

        $this->documents->renameFolder($folder, $name);

        return $this->backToFolder($sectionId, $parentId, 'success', 'Dossier renommé.');
    }

    /** Delete an empty folder (permission 47). */
    public function folderDestroy(DocumentFolder $folder): RedirectResponse
    {
        $sectionId = (int) $folder->S_ID;
        $parentId = (int) $folder->DF_PARENT;
        abort_unless($this->sectionScope->allows($sectionId), 403);
        abort_unless($this->documents->authorize(request()->user(), ObDocumentAcl::TYPE_FOLDER, (int) $folder->DF_ID, ObDocumentAcl::RIGHT_DELETE, $sectionId), 403);

        if (! $this->documents->folderIsEmpty($folder)) {
            return $this->backToFolder($sectionId, (int) $folder->DF_ID, 'error',
                'Dossier non vide : videz-le avant de le supprimer.');
        }

        $folder->delete();

        return $this->backToFolder($sectionId, $parentId, 'success', 'Dossier supprimé.');
    }

    private function backToFolder(int $sectionId, int $folderId, string $flash, string $message): RedirectResponse
    {
        return redirect()
            ->route('document.index', array_filter(['folder' => $folderId ?: null, 'section' => $sectionId]))
            ->with($flash, $message);
    }

    /** Stream a library document — permission, type/doc security and section checked. */
    public function download(Request $request, Document $document): BinaryFileResponse
    {
        $user = $request->user();
        $sectionId = (int) $document->S_ID;

        // Only library documents are served here (entity files use their own paths).
        abort_unless($this->isLibraryDocument($document), 404);
        abort_unless($this->sectionScope->allows($sectionId), 403);
        abort_unless($this->documents->canDownload($user, $document, $sectionId), 403);

        $path = $this->documents->existingFilePath($sectionId, (int) $document->DF_ID, $document->D_NAME);
        abort_unless($path !== null, 404);

        // PDFs open inline; everything else downloads as an attachment.
        $inline = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf';

        return response()->file($path, [
            'Content-Availabilitysition' => ($inline ? 'inline' : 'attachment')
                .'; filename="'.basename($document->D_NAME).'"',
        ]);
    }

    /** A document not attached to any entity (event, person, vehicle…). */
    private function isLibraryDocument(Document $document): bool
    {
        return (int) $document->E_CODE === 0 && (int) $document->P_ID === 0
            && (int) $document->V_ID === 0 && (int) $document->M_ID === 0
            && (int) $document->NF_ID === 0 && (int) $document->VI_ID === 0
            && (int) $document->EL_ID === 0;
    }

    /**
     * Column definitions for the explorer table — rows are sub-folders
     * (is_folder = true) and documents together. Reused by the export.
     */
    private function columns(int $sectionId): array
    {
        $isFolder = fn ($d) => (bool) ($d->is_folder ?? false);
        $folderUrl = fn ($d) => route('document.index', ['folder' => $d->DF_ID, 'section' => $sectionId]);

        return [
            ['key' => 'icon', 'label' => '', 'type' => 'html', 'alwaysVisible' => true, 'mobile' => true,
                'cardShow' => true, 'thWidth' => '34px', 'exportable' => false,
                'value' => fn ($d) => $isFolder($d)
                    ? '<i class="fas fa-folder" style="color:var(--color-folder)"></i>'
                    : '<i class="fas '.$this->documents->fileIcon($d->D_NAME).'"></i>'],
            ['key' => 'name', 'label' => 'Nom', 'type' => 'html', 'alwaysVisible' => true, 'mobile' => true, 'cardShow' => true,
                'value' => fn ($d) => $isFolder($d)
                    ? '<a href="'.e($folderUrl($d)).'" class="fw-semibold text-decoration-none">'.e($d->D_NAME).'</a>'
                    : ($d->can_view
                        ? '<a href="'.e(route('document.download', $d->D_ID)).'" class="text-decoration-none">'.e($d->D_NAME).'</a>'
                        : e($d->D_NAME)),
                'exportable' => true, 'exportValue' => fn ($d) => $d->D_NAME],
            ['key' => 'type', 'label' => 'Type', 'type' => 'text', 'mobile' => false,
                'value' => fn ($d) => $isFolder($d) ? 'Dossier' : ($d->TD_LIBELLE ?? $d->TD_CODE ?? '—'),
                'exportable' => true, 'exportValue' => fn ($d) => $isFolder($d) ? 'Dossier' : ($d->TD_LIBELLE ?? '')],
            ['key' => 'created_by', 'label' => 'Ajouté par', 'type' => 'text', 'mobile' => false,
                'value' => fn ($d) => $isFolder($d) ? '—' : ($d->created_by_name ?: '—'),
                'exportable' => true, 'exportValue' => fn ($d) => $isFolder($d) ? '' : ($d->created_by_name ?? '')],
            ['key' => 'date', 'label' => 'Date', 'type' => 'date', 'mobile' => true,
                'value' => fn ($d) => $isFolder($d) ? null : $d->D_CREATED_DATE,
                'exportable' => true, 'exportValue' => fn ($d) => (! $isFolder($d) && $d->D_CREATED_DATE) ? Carbon::parse($d->D_CREATED_DATE)->format('d/m/Y') : ''],
            ['key' => 'actions', 'label' => '', 'type' => 'html', 'alwaysVisible' => true, 'exportable' => false,
                'value' => fn ($d) => $this->rowActions($d, $sectionId)],
        ];
    }

    /** The action buttons for one explorer row (folder or document). */
    private function rowActions(object $d, int $sectionId): string
    {
        if ($d->is_folder ?? false) {
            $fid = (int) $d->DF_ID;
            $out = '<a href="'.e(route('document.index', ['folder' => $fid, 'section' => $sectionId])).'" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Ouvrir"><i class="fas fa-arrow-right fa-xs"></i></a>';
            if ($d->can_write ?? false) {
                $out .= ' <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Renommer" data-folder-edit data-id="'.$fid.'" data-name="'.e($d->D_NAME).'"><i class="fas fa-pen fa-xs"></i></button>';
            }
            if ($d->can_share ?? false) {
                $out .= ' '.$this->shareLink(ObDocumentAcl::TYPE_FOLDER, $fid);
            }
            if ($d->can_delete ?? false) {
                $out .= ' '.$this->deleteForm(route('document.folder.destroy', $fid), 'Supprimer ce dossier ? Il doit être vide.');
            }

            return $out;
        }

        $ext = strtolower(pathinfo($d->D_NAME, PATHINFO_EXTENSION));
        $isImage = in_array($ext, PhotoService::IMAGE_EXTENSIONS, true);
        $dlUrl = e(route('document.download', $d->D_ID));

        if ($d->can_view ?? false) {
            $out = '<a href="'.$dlUrl.'" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Télécharger"><i class="fas fa-download fa-xs"></i></a>';
            if ($isImage) {
                $out .= ' <button type="button" class="btn btn-sm btn-outline-primary py-0 px-1" title="Aperçu"'
                    .' data-lb-src="'.$dlUrl.'" data-lb-title="'.e($d->D_NAME).'">'
                    .'<i class="fas fa-eye fa-xs"></i></button>';
            }
        } else {
            $out = '<span class="text-muted" title="Accès restreint"><i class="fas fa-lock fa-xs"></i></span>';
        }
        if ($d->can_write ?? false) {
            $out .= ' <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Renommer / modifier"'
                .' data-doc-edit data-id="'.$d->D_ID.'" data-name="'.e($d->D_NAME).'" data-type="'.e($d->TD_CODE).'" data-folder="'.(int) $d->DF_ID.'">'
                .'<i class="fas fa-pen fa-xs"></i></button>';
        }
        if ($d->can_share ?? false) {
            $out .= ' '.$this->shareLink(ObDocumentAcl::TYPE_DOCUMENT, (int) $d->D_ID);
        }
        if ($d->can_delete ?? false) {
            $out .= ' '.$this->deleteForm(route('document.destroy', $d->D_ID), 'Supprimer définitivement ce document ?');
        }

        return $out;
    }

    /** Share link that opens the ACL page in a popup window. */
    private function shareLink(string $type, int $id): string
    {
        return '<a href="'.e(route('document.acl', [$type, $id]).'?window=1').'" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Partager" data-acl-window><i class="fas fa-user-lock fa-xs"></i></a>';
    }

    /** Inline DELETE form (confirmed client-side via data-confirm). */
    private function deleteForm(string $action, string $confirm): string
    {
        return '<form method="POST" action="'.e($action).'" class="d-inline" data-confirm="'.e($confirm).'">'
            .csrf_field().method_field('DELETE')
            .'<button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Supprimer"><i class="fas fa-trash fa-xs"></i></button></form>';
    }
}
