<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\RedirectResponse;
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
        $user = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        $sections = DB::table('section')
            ->where('S_INACTIVE', 0)
            ->orderBy('S_ORDER')
            ->orderBy('S_CODE')
            ->get(['S_ID', 'S_PARENT', 'S_CODE', 'S_DESCRIPTION', 'S_PHONE', 'S_EMAIL']);

        $memberCounts = $this->memberCounts();

        $tree = $this->buildTree($sections, 0, $memberCounts);

        return view('organisation.index', compact('tree', 'sectionId'));
    }

    // ── Sections list + CRUD (replaces departement.php) ──────────────────────

    public function sections(): View
    {
        $sections = DB::table('section as s')
            ->leftJoin('section as p', 'p.S_ID', '=', 's.S_PARENT')
            ->orderBy('s.S_ORDER')
            ->orderBy('s.S_CODE')
            ->get([
                's.S_ID', 's.S_PARENT', 's.S_CODE', 's.S_DESCRIPTION', 's.S_CITY',
                's.S_PHONE', 's.S_EMAIL', 's.S_ORDER', 's.S_INACTIVE',
                'p.S_DESCRIPTION as parent_name', 'p.S_CODE as parent_code',
            ])
            ->each(fn ($s) => $s->S_DESCRIPTION = $s->S_DESCRIPTION ?: 'Section '.$s->S_ID);

        $counts = $this->memberCounts();

        return view('organisation.sections', compact('sections', 'counts'));
    }

    public function createSection(): View
    {
        return view('organisation.section-form', [
            'section' => null,
            'parents' => $this->parentOptions(null),
        ]);
    }

    public function storeSection(Request $request): RedirectResponse
    {
        $data = $this->validateSection($request);

        $data['S_ID'] = (int) (DB::table('section')->max('S_ID') ?? 0) + 1;
        $data['S_INACTIVE'] = $request->boolean('S_INACTIVE');

        Section::create($data);

        return redirect()->route('organisation.sections')
            ->with('success', 'Section créée.');
    }

    public function editSection(Section $section): View
    {
        return view('organisation.section-form', [
            'section' => $section,
            'parents' => $this->parentOptions((int) $section->S_ID),
        ]);
    }

    public function updateSection(Request $request, Section $section): RedirectResponse
    {
        $data = $this->validateSection($request);
        $data['S_INACTIVE'] = $request->boolean('S_INACTIVE');

        $section->update($data);

        return redirect()->route('organisation.sections')
            ->with('success', 'Section mise à jour.');
    }

    public function destroySection(Section $section): RedirectResponse
    {
        $hasChildren = DB::table('section')->where('S_PARENT', $section->S_ID)->exists();
        if ($hasChildren) {
            return redirect()->route('organisation.sections')
                ->with('error', 'Cette section a des sous-sections — déplacez-les d’abord.');
        }

        $hasMembers = DB::table('pompier')->where('P_SECTION', $section->S_ID)->exists();
        if ($hasMembers) {
            return redirect()->route('organisation.sections')
                ->with('error', 'Cette section contient des membres — réaffectez-les d’abord.');
        }

        $section->delete();

        return redirect()->route('organisation.sections')
            ->with('success', 'Section supprimée.');
    }

    // ── Cartographie (replaces jvectormap.php) — sections on a Leaflet map ────

    public function cartographie(): View
    {
        // Place each section at the centroid of its geolocated members.
        $rows = DB::table('gps as g')
            ->join('pompier as p', 'g.P_ID', '=', 'p.P_ID')
            ->leftJoin('section as s', 'p.P_SECTION', '=', 's.S_ID')
            ->where('p.P_OLD_MEMBER', 0)
            ->where('p.GP_ID', '<>', -1)
            ->whereNotNull('g.LAT')->whereNotNull('g.LNG')
            ->where('g.LAT', '<>', 0)->where('g.LNG', '<>', 0)
            ->groupBy('p.P_SECTION', 's.S_CODE', 's.S_DESCRIPTION')
            ->select(
                'p.P_SECTION',
                's.S_CODE',
                's.S_DESCRIPTION',
                DB::raw('AVG(g.LAT) as lat'),
                DB::raw('AVG(g.LNG) as lng'),
                DB::raw('COUNT(*) as cnt')
            )
            ->get();

        $markers = $rows->map(function ($r) {
            $label = ($r->S_CODE ? $r->S_CODE.' — ' : '').($r->S_DESCRIPTION ?: 'Section '.$r->P_SECTION);

            return [
                'name' => $label,
                'grade' => $r->cnt.' membre'.($r->cnt > 1 ? 's' : ''),
                'section' => '',
                'phone' => '',
                'address' => $r->cnt.' membre'.($r->cnt > 1 ? 's' : '').' géolocalisé'.($r->cnt > 1 ? 's' : ''),
                'lat' => (float) $r->lat,
                'lng' => (float) $r->lng,
                'photo_url' => '',
                'profile_url' => route('geolocalisation.index', ['section' => $r->P_SECTION]),
            ];
        })->values()->toArray();

        return view('organisation.cartographie', [
            'markers' => $markers,
            'count' => count($markers),
        ]);
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function memberCounts()
    {
        return DB::table('pompier')
            ->where('P_OLD_MEMBER', 0)
            ->where('GP_ID', '<>', -1)
            ->whereNull('P_FIN')
            ->groupBy('P_SECTION')
            ->select('P_SECTION', DB::raw('COUNT(*) as cnt'))
            ->pluck('cnt', 'P_SECTION');
    }

    /** Sections eligible as a parent, excluding the section itself. */
    private function parentOptions(?int $excludeId)
    {
        return DB::table('section')
            ->when($excludeId, fn ($q) => $q->where('S_ID', '<>', $excludeId))
            ->orderBy('S_CODE')
            ->get(['S_ID', 'S_CODE', 'S_DESCRIPTION'])
            ->each(fn ($s) => $s->S_DESCRIPTION = $s->S_DESCRIPTION ?: 'Section '.$s->S_ID);
    }

    /** @return array<string,mixed> */
    private function validateSection(Request $request): array
    {
        return $request->validate([
            'S_CODE' => ['required', 'string', 'max:25'],
            'S_DESCRIPTION' => ['nullable', 'string', 'max:80'],
            'S_PARENT' => ['nullable', 'integer'],
            'S_PHONE' => ['nullable', 'string', 'max:20'],
            'S_FAX' => ['nullable', 'string', 'max:20'],
            'S_EMAIL' => ['nullable', 'email', 'max:60'],
            'S_URL' => ['nullable', 'string', 'max:60'],
            'S_ADDRESS' => ['nullable', 'string', 'max:150'],
            'S_ADDRESS_COMPLEMENT' => ['nullable', 'string', 'max:150'],
            'S_ZIP_CODE' => ['nullable', 'string', 'max:6'],
            'S_CITY' => ['nullable', 'string', 'max:30'],
            'S_ORDER' => ['nullable', 'integer', 'min:0', 'max:255'],
        ]);
    }

    private function buildTree($sections, int $parentId, $memberCounts): array
    {
        return $sections
            ->filter(function ($s) use ($parentId) {
                $p = (int) ($s->S_PARENT ?? 0);

                return $parentId === 0 ? ($p === 0) : $p === $parentId;
            })
            ->map(fn ($s) => [
                'section' => $s,
                'count' => (int) ($memberCounts[$s->S_ID] ?? 0),
                'children' => $this->buildTree($sections, (int) $s->S_ID, $memberCounts),
            ])
            ->values()
            ->all();
    }
}
