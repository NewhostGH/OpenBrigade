<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentService;
use App\Services\SectionScopeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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

        // Active section: the navbar-chosen one when allowed, else the home section.
        $requested = (int) $request->integer('section');
        $sectionId = ($requested > 0 && $this->sectionScope->canChoose($requested))
            ? $requested
            : (int) ($this->sectionScope->defaultSectionId() ?? $user->P_SECTION);

        $folderId = (int) $request->integer('folder', 0);
        $typeCode = $request->string('type', 'ALL')->toString() ?: 'ALL';

        $folders = $this->documents->folders($sectionId);

        return view('document.index', [
            'folders' => $folders,
            'rootFolders' => $this->documents->rootFolders($folders),
            'subFolders' => $this->documents->subFolders($folders, $folderId),
            'breadcrumb' => $this->documents->breadcrumb($folders, $folderId),
            'documents' => $this->documents->documents($user, $sectionId, $folderId, $typeCode),
            'folderId' => $folderId,
            'typeCode' => $typeCode,
            'types' => $this->documents->types(),
            'sectionId' => $sectionId,
            'columns' => $this->columns(),
            'canManage' => $user->hasPermission((int) config('documents.feature_manage')),
        ]);
    }

    /** Stream a library document — permission, type/doc security and section checked. */
    public function download(Request $request, Document $document): BinaryFileResponse
    {
        $user = $request->user();
        $sectionId = (int) $document->S_ID;

        // Only library documents are served here (entity files use their own paths).
        abort_unless($this->isLibraryDocument($document), 404);
        abort_unless($this->sectionScope->allows($sectionId), 403);

        $document->loadMissing('type', 'security');
        $canView = $this->documents->canView(
            $user,
            $sectionId,
            (int) ($document->type->TD_SECURITY ?? 0),
            (int) ($document->security->F_ID ?? 0),
            (int) $document->D_CREATED_BY,
        );
        abort_unless($canView, 403);

        $path = $this->documents->filePath($sectionId, (int) $document->DF_ID, $document->D_NAME);
        abort_unless(File::exists($path), 404);

        // PDFs open inline; everything else downloads as an attachment.
        $inline = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf';

        return response()->file($path, [
            'Content-Disposition' => ($inline ? 'inline' : 'attachment')
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

    /** Column definitions, reused by the list view and (later) the export. */
    private function columns(): array
    {
        return [
            ['key' => 'name', 'label' => 'Nom', 'type' => 'html', 'alwaysVisible' => true, 'mobile' => true,
                'value' => fn ($d) => '<i class="fas fa-file fa-xs me-2 text-muted"></i>'.e($d->D_NAME),
                'exportable' => true, 'exportValue' => fn ($d) => $d->D_NAME],
            ['key' => 'type', 'label' => 'Type', 'type' => 'text', 'mobile' => false,
                'value' => fn ($d) => $d->TD_LIBELLE ?? $d->TD_CODE ?? '—',
                'exportable' => true, 'exportValue' => fn ($d) => $d->TD_LIBELLE ?? ''],
            ['key' => 'created_by', 'label' => 'Ajouté par', 'type' => 'text', 'mobile' => false,
                'value' => fn ($d) => $d->created_by_name ?: '—',
                'exportable' => true, 'exportValue' => fn ($d) => $d->created_by_name ?? ''],
            ['key' => 'date', 'label' => 'Date', 'type' => 'date', 'mobile' => true,
                'value' => fn ($d) => $d->D_CREATED_DATE,
                'exportable' => true, 'exportValue' => fn ($d) => $d->D_CREATED_DATE ? Carbon::parse($d->D_CREATED_DATE)->format('d/m/Y') : ''],
            ['key' => 'actions', 'label' => '', 'type' => 'html', 'alwaysVisible' => true, 'exportable' => false,
                'value' => fn ($d) => $d->can_view
                    ? '<a href="'.e(route('document.download', $d->D_ID)).'" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Télécharger"><i class="fas fa-download fa-xs"></i></a>'
                    : '<span class="text-muted" title="Accès restreint"><i class="fas fa-lock fa-xs"></i></span>'],
        ];
    }
}
