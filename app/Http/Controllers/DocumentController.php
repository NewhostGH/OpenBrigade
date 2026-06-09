<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DocumentController extends Controller
{
    /**
     * Document library — shows folder tree and documents for a given folder.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $sectionId = (int) $user->P_SECTION;
        $folderId = (int) $request->integer('folder', 0);
        $typeCode = (string) $request->string('type', 'ALL');

        // All folders for the user's section (used to build breadcrumb + tree)
        $allFolders = DB::table('document_folder')
            ->where('S_ID', $sectionId)
            ->orderBy('DF_NAME')
            ->get(['DF_ID', 'DF_PARENT', 'DF_NAME', 'TD_CODE']);

        // Current folder's breadcrumb
        $breadcrumb = $this->buildBreadcrumb($allFolders, $folderId);

        // Sub-folders of current folder
        $subFolders = $allFolders->where('DF_PARENT', $folderId === 0 ? null : $folderId)
            ->values();
        if ($folderId === 0) {
            // Root: folders with no parent or parent = 0
            $subFolders = $allFolders->filter(fn ($f) => ! $f->DF_PARENT || $f->DF_PARENT === 0)
                ->values();
        }

        // Documents in current folder
        $docQuery = DB::table('document as d')
            ->leftJoin('type_document as td', 'd.TD_CODE', '=', 'td.TD_CODE')
            ->leftJoin('pompier as p', 'd.D_CREATED_BY', '=', 'p.P_ID')
            ->where('d.S_ID', $sectionId);

        if ($folderId > 0) {
            $docQuery->where('d.DF_ID', $folderId);
        } else {
            $docQuery->whereNull('d.DF_ID')->orWhere('d.DF_ID', 0);
        }

        if ($typeCode !== 'ALL') {
            $docQuery->where('d.TD_CODE', $typeCode);
        }

        $documents = $docQuery
            ->orderByDesc('d.D_CREATED_DATE')
            ->select(
                'd.D_ID', 'd.D_NAME', 'd.TD_CODE', 'd.D_CREATED_DATE',
                'td.TD_LIBELLE',
                DB::raw("CONCAT(p.P_PRENOM, ' ', p.P_NOM) as created_by_name")
            )
            ->paginate(30)
            ->withQueryString();

        // Document types for filter
        $types = DB::table('type_document')
            ->orderBy('TD_LIBELLE')
            ->get(['TD_CODE', 'TD_LIBELLE']);

        return view('document.index', compact(
            'allFolders', 'subFolders', 'breadcrumb',
            'documents', 'folderId', 'typeCode', 'types'
        ));
    }

    private function buildBreadcrumb($allFolders, int $folderId): array
    {
        if ($folderId === 0) {
            return [];
        }

        $crumbs = [];
        $current = $folderId;
        $visited = [];

        while ($current > 0 && ! in_array($current, $visited, true)) {
            $folder = $allFolders->firstWhere('DF_ID', $current);
            if (! $folder) {
                break;
            }
            $crumbs[] = ['id' => $folder->DF_ID, 'name' => $folder->DF_NAME];
            $visited[] = $current;
            $current = (int) ($folder->DF_PARENT ?? 0);
        }

        return array_reverse($crumbs);
    }
}
