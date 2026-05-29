<?php

# project: OpenBrigade

# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonnelController extends Controller
{
    public function index(Request $request): View
    {
        $position = (string) $request->string('position', 'actif');
        $search = trim((string) $request->string('q'));
        $category = (string) $request->string('category', 'ALL');
        $sectionId = (int) $request->integer('section', 0);
            $order       = (string) $request->string('order', 'P_NOM');
            $subsections = (bool) $request->integer('subsections', 1);

        $allowedOrder = [
            'P_NOM', 'P_PRENOM', 'P_CODE', 'P_STATUT', 'P_GRADE',
            'P_DATE_ENGAGEMENT', 'P_FIN', 'P_PROFESSION', 'P_BIRTHDATE',
        ];
        if (! in_array($order, $allowedOrder, true)) {
            $order = 'P_NOM';
        }

        $query = Personnel::query()
            ->with(['section'])
            ->select([
                'P_ID', 'P_PHOTO', 'P_CODE', 'P_PRENOM', 'P_NOM', 'P_STATUT', 'P_GRADE',
                'P_PROFESSION', 'P_SECTION', 'P_EMAIL', 'P_PHONE', 'P_BIRTHDATE',
                'P_DATE_ENGAGEMENT', 'P_OLD_MEMBER', 'GP_ID', 'P_FIN',
            ]);

        if ($position === 'actif') {
            $query->where('P_OLD_MEMBER', 0)->where('GP_ID', '<>', -1);
        } elseif ($position === 'archive') {
            $query->where('P_OLD_MEMBER', '>', 0);
        } elseif ($position === 'bloqued') {
            $query->where('GP_ID', -1)->where('P_OLD_MEMBER', 0);
        }

        if ($category !== '' && $category !== 'ALL') {
            if ($category === 'INT') {
                $query->where('P_STATUT', '<>', 'EXT');
            } else {
                $query->where('P_STATUT', $category);
            }
        }

        if ($sectionId > 0) {
            $query->where('P_SECTION', $sectionId);
        }

        if ($search !== '') {
            $query->where(function ($inner) use ($search): void {
                $inner->where('P_NOM', 'like', "%{$search}%")
                    ->orWhere('P_PRENOM', 'like', "%{$search}%")
                    ->orWhere('P_CODE', 'like', "%{$search}%")
                    ->orWhere('P_EMAIL', 'like', "%{$search}%");
            });
        }

        if (in_array($order, ['P_FIN', 'P_DATE_ENGAGEMENT'], true)) {
            $query->orderByDesc($order);
        } else {
            $query->orderBy($order);
        }

        $items = $query->paginate(50)->withQueryString();

        $sections = Section::query()->orderBy('S_CODE')->get(['S_ID', 'S_CODE', 'S_DESCRIPTION']);

            // Determine if the selected section has sub-sections
            $hasSubsections = false;
            if ($sectionId > 0) {
                $hasSubsections = Section::where('S_PARENT', $sectionId)->exists();
            }

        $categories = Personnel::query()
            ->select('P_STATUT')
            ->whereNotNull('P_STATUT')
            ->distinct()
            ->orderBy('P_STATUT')
            ->pluck('P_STATUT');

        return view('personnel.index', [
            'items' => $items,
            'position' => $position,
            'search' => $search,
            'category' => $category,
            'sectionId' => $sectionId,
            'order' => $order,
            'subsections'    => $subsections,
            'hasSubsections' => $hasSubsections,
            'sections' => $sections,
            'categories' => $categories,
        ]);
    }

    /**
     * Photo directory (trombinoscope) — grid view of personnel with photos.
     */
    public function trombinoscope(Request $request): View
    {
        $search    = trim((string) $request->string('q'));
        $sectionId = (int) $request->integer('section', 0);
        $user      = auth()->user();
        $mySection = (int) $user->P_SECTION;

        $query = Personnel::query()
            ->with(['section'])
            ->where('P_OLD_MEMBER', 0)
            ->where('GP_ID', '<>', -1)
            ->whereNull('P_FIN')
            ->where('P_STATUT', '<>', 'EXT')
            ->select([
                'P_ID', 'P_PHOTO', 'P_CODE', 'P_PRENOM', 'P_NOM',
                'P_GRADE', 'P_SECTION', 'P_PHONE', 'P_EMAIL', 'P_CIVILITE',
            ]);

        $targetSection = $sectionId > 0 ? $sectionId : $mySection;
        $query->where('P_SECTION', $targetSection);

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('P_NOM', 'like', "%{$search}%")
                  ->orWhere('P_PRENOM', 'like', "%{$search}%");
            });
        }

        $items    = $query->orderBy('P_NOM')->orderBy('P_PRENOM')->paginate(48)->withQueryString();
        $sections = Section::query()->orderBy('S_CODE')->get(['S_ID', 'S_CODE', 'S_DESCRIPTION']);

        return view('personnel.trombinoscope', compact('items', 'search', 'sectionId', 'sections'));
    }

    /**
     * Qualifications list for the section (expiry tracking).
     */
    public function qualifications(Request $request): View
    {
        $user      = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        $filter = (string) $request->string('filter', 'all'); // all|expiring|expired

        $today   = now()->toDateString();
        $warn30  = now()->addDays(30)->toDateString();

        $query = DB::table('qualification as q')
            ->join('pompier as p', 'q.P_ID', '=', 'p.P_ID')
            ->join('poste as ps', 'q.PS_ID', '=', 'ps.PS_ID')
            ->where('p.P_SECTION', $sectionId)
            ->where('p.P_OLD_MEMBER', 0)
            ->select(
                'p.P_ID', 'p.P_NOM', 'p.P_PRENOM',
                'ps.PS_ID', 'ps.TYPE as PS_TYPE',
                'q.Q_VAL', 'q.Q_EXPIRATION',
                DB::raw("CASE
                    WHEN q.Q_EXPIRATION IS NOT NULL AND q.Q_EXPIRATION < '{$today}' THEN 'expired'
                    WHEN q.Q_EXPIRATION IS NOT NULL AND q.Q_EXPIRATION <= '{$warn30}' THEN 'expiring'
                    ELSE 'ok'
                END as status")
            )
            ->orderBy('p.P_NOM')
            ->orderBy('p.P_PRENOM')
            ->orderBy('ps.TYPE');

        if ($filter === 'expiring') {
            $query->whereRaw("q.Q_EXPIRATION IS NOT NULL AND q.Q_EXPIRATION > '{$today}' AND q.Q_EXPIRATION <= '{$warn30}'");
        } elseif ($filter === 'expired') {
            $query->whereRaw("q.Q_EXPIRATION IS NOT NULL AND q.Q_EXPIRATION < '{$today}'");
        }

        $items = $query->paginate(50)->withQueryString();

        return view('personnel.qualifications', compact('items', 'filter'));
    }

    public function photo(Personnel $personnel)
    {
        $filename = trim((string) $personnel->P_PHOTO);

        if ($filename !== '') {
            $filename = basename($filename);
            $paths = [
                base_path('archive/legacy_app/images/user-specific/trombi/'.$filename),
                public_path('images/user-specific/trombi/'.$filename),
            ];

            foreach ($paths as $path) {
                if (File::exists($path)) {
                    return response()->file($path);
                }
            }
        }

        $defaultPath = base_path('archive/legacy_app/images/user-specific/DEFAULT.png');
        if (File::exists($defaultPath)) {
            return response()->file($defaultPath);
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><rect width="64" height="64" fill="#e2e8f0"/><circle cx="32" cy="24" r="12" fill="#94a3b8"/><rect x="14" y="40" width="36" height="18" rx="9" fill="#94a3b8"/></svg>';

        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }

    public function show(Personnel $personnel): View
    {
        $personnel->load(['section', 'groupe']);

        return view('personnel.show', [
            'personnel' => $personnel,
        ]);
    }

    public function edit(Personnel $personnel): View
    {
        $sections = Section::query()
            ->orderBy('S_CODE')
            ->get(['S_ID', 'S_CODE', 'S_DESCRIPTION']);

        return view('personnel.edit', [
            'personnel' => $personnel,
            'sections' => $sections,
        ]);
    }

    public function update(Request $request, Personnel $personnel)
    {
        $validated = $request->validate([
            'P_CODE' => [
                'required',
                'string',
                'max:20',
                Rule::unique('pompier', 'P_CODE')->ignore($personnel->P_ID, 'P_ID'),
            ],
            'P_PRENOM' => 'required|string|max:25',
            'P_NOM' => 'required|string|max:30',
            'P_EMAIL' => 'nullable|email|max:60',
            'P_PHONE' => 'nullable|string|max:20',
            'P_PHONE2' => 'nullable|string|max:20',
            'P_STATUT' => 'required|string|max:5',
            'P_GRADE' => 'required|string|max:6',
            'P_PROFESSION' => 'required|string|max:6',
            'P_SECTION' => 'nullable|integer|exists:section,S_ID',
            'P_BIRTHDATE' => 'nullable|date',
            'P_DATE_ENGAGEMENT' => 'nullable|date',
            'P_FIN' => 'nullable|date',
            'P_ADDRESS' => 'nullable|string|max:150',
            'P_ZIP_CODE' => 'nullable|string|max:6',
            'P_CITY' => 'nullable|string|max:30',
        ]);

        $validated['P_HIDE'] = $request->boolean('P_HIDE');
        $validated['P_NOSPAM'] = $request->boolean('P_NOSPAM');

        $personnel->update($validated);

        return redirect()
            ->route('personnel.show', $personnel)
            ->with('success', 'Fiche personnel mise a jour.');
    }
}
