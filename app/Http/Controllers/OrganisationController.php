<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrganisationController extends Controller
{
    /**
     * Organisation overview — hierarchy tree + member counts per section.
     */
    public function index(): View
    {
        $user      = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        // Load all sections with their immediate parent chain
        $sections = DB::table('section')
            ->where('S_INACTIVE', 0)
            ->orderBy('S_ORDER')
            ->orderBy('S_CODE')
            ->get(['S_ID', 'S_PARENT', 'S_CODE', 'S_DESCRIPTION', 'S_PHONE', 'S_EMAIL']);

        // Member counts per section
        $memberCounts = DB::table('pompier')
            ->where('P_OLD_MEMBER', 0)
            ->where('GP_ID', '<>', -1)
            ->whereNull('P_FIN')
            ->groupBy('P_SECTION')
            ->select('P_SECTION', DB::raw('COUNT(*) as cnt'))
            ->pluck('cnt', 'P_SECTION');

        // Build tree starting from the user's section and upward
        $tree = $this->buildTree($sections, 0, $memberCounts);

        return view('organisation.index', compact('tree', 'sectionId'));
    }

    private function buildTree($sections, int $parentId, $memberCounts): array
    {
        return $sections
            ->filter(function ($s) use ($parentId) {
                $p = (int) ($s->S_PARENT ?? 0);
                return $parentId === 0
                    ? ($p === 0 || $p === null)
                    : $p === $parentId;
            })
            ->map(function ($s) use ($sections, $memberCounts) {
                return [
                    'section'  => $s,
                    'count'    => (int) ($memberCounts[$s->S_ID] ?? 0),
                    'children' => $this->buildTree($sections, (int) $s->S_ID, $memberCounts),
                ];
            })
            ->values()
            ->all();
    }
}
