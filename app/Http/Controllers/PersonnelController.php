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

use App\Models\Cotisation;
use App\Models\Personnel;
use App\Models\Poste;
use App\Models\Qualification;
use App\Models\Section;
use App\Models\TypePaiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonnelController extends Controller
{
    public function index(Request $request): View
    {
        $position    = (string) $request->string('position', 'actif');
        $search      = trim((string) $request->string('q'));
        $category    = (string) $request->string('category', 'INT');
        $sectionId   = (int) $request->integer('section', 0);
        $order       = (string) $request->string('order', 'P_NOM');
        $subsections = (bool) $request->integer('subsections', 1);
        $perPage     = (int) $request->integer('perPage', 100);
        if (! in_array($perPage, [12, 24, 48, 100, 500], true)) {
            $perPage = 100;
        }

        $allowedOrder = [
            'P_NOM', 'P_PRENOM', 'P_CODE', 'P_STATUT', 'P_GRADE',
            'P_DATE_ENGAGEMENT', 'P_FIN', 'P_BIRTHDATE',
        ];
        if (! in_array($order, $allowedOrder, true)) {
            $order = 'P_NOM';
        }

        // Load all sections once for hierarchy building + subsection filtering
        $allSections = Section::query()
            ->orderBy('S_CODE')
            ->get(['S_ID', 'S_CODE', 'S_DESCRIPTION', 'S_PARENT']);

        $query = Personnel::query()
            ->with(['section'])
            ->select([
                'P_ID', 'P_PHOTO', 'P_CODE', 'P_PRENOM', 'P_NOM', 'P_STATUT', 'P_GRADE',
                'P_SECTION', 'P_EMAIL', 'P_PHONE', 'P_PHONE2', 'P_CIVILITE',
                'P_BIRTHDATE', 'P_DATE_ENGAGEMENT', 'P_OLD_MEMBER', 'GP_ID', 'P_FIN',
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
            if ($subsections) {
                $descendants = $this->getDescendantSectionIds($allSections, $sectionId);
                $query->whereIn('P_SECTION', array_merge([$sectionId], $descendants));
            } else {
                $query->where('P_SECTION', $sectionId);
            }
        }

        if ($search !== '') {
            $query->where(function ($inner) use ($search): void {
                $inner->where('P_NOM', 'like', "%{$search}%")
                    ->orWhere('P_PRENOM', 'like', "%{$search}%")
                    ->orWhere('P_CODE', 'like', "%{$search}%")
                    ->orWhere('P_EMAIL', 'like', "%{$search}%")
                    ->orWhere('P_PHONE', 'like', "%{$search}%")
                    ->orWhere('P_PHONE2', 'like', "%{$search}%")
                    ->orWhere('P_GRADE', 'like', "%{$search}%");
            });
        }

        if (in_array($order, ['P_FIN', 'P_DATE_ENGAGEMENT'], true)) {
            $query->orderByDesc($order);
        } else {
            $query->orderBy($order);
        }

        $items = $query->paginate($perPage)->withQueryString();

        // Build hierarchical section options for the filter select
        $sectionOptions = $this->buildSectionTree($allSections);

        return view('personnel.index', [
            'items'          => $items,
            'position'       => $position,
            'search'         => $search,
            'category'       => $category,
            'sectionId'      => $sectionId,
            'order'          => $order,
            'subsections'    => $subsections,
            'perPage'        => $perPage,
            'sectionOptions' => $sectionOptions,
        ]);
    }

    /**
     * Serve grade badge images from the legacy assets directory.
     */
    public function gradeImage(string $grade)
    {
        $grade = preg_replace('/[^A-Z0-9]/', '', strtoupper($grade));

        $path = base_path("archive/legacy_app/images/grades_sp/{$grade}.png");
        if (! File::exists($path)) {
            $path = base_path('archive/legacy_app/images/grades_sp/NR.png');
        }
        if (File::exists($path)) {
            return response()->file($path, ['Cache-Control' => 'public, max-age=86400']);
        }

        // 1×1 transparent PNG fallback
        return response(
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='),
            200,
            ['Content-Type' => 'image/png']
        );
    }

    /**
     * Build a depth-annotated flat list of sections in DFS (tree) order.
     * Sections with S_PARENT = 0 are considered top-level (depth 0).
     */
    private function buildSectionTree(Collection $sections, int $parentId = 0, int $depth = 0): array
    {
        $result = [];
        foreach ($sections as $section) {
            if ((int) ($section->S_PARENT ?? 0) === $parentId) {
                $result[] = [
                    'S_ID'          => (int) $section->S_ID,
                    'S_CODE'        => $section->S_CODE,
                    'S_DESCRIPTION' => $section->S_DESCRIPTION,
                    'depth'         => $depth,
                ];
                array_push($result, ...$this->buildSectionTree($sections, (int) $section->S_ID, $depth + 1));
            }
        }
        return $result;
    }

    /**
     * Return all descendant section IDs of a given section via BFS.
     */
    private function getDescendantSectionIds(Collection $allSections, int $parentId): array
    {
        $ids   = [];
        $queue = [$parentId];
        while (! empty($queue)) {
            $current  = array_shift($queue);
            $children = $allSections
                ->filter(fn ($s) => (int) ($s->S_PARENT ?? 0) === $current)
                ->pluck('S_ID');
            foreach ($children as $childId) {
                $ids[]   = (int) $childId;
                $queue[] = (int) $childId;
            }
        }
        return $ids;
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
        $personnel->load(['section', 'groupe', 'qualifications.poste', 'cotisations.typePaiement']);

        $postes = Poste::query()
            ->orderBy('TYPE')
            ->get(['PS_ID', 'TYPE', 'DESCRIPTION', 'PS_EXPIRABLE']);

        $typesPaiement = TypePaiement::query()
            ->orderBy('TP_DESCRIPTION')
            ->get(['TP_ID', 'TP_DESCRIPTION']);

        $gps = DB::table('gps')->where('P_ID', $personnel->P_ID)->first();

        return view('personnel.show', [
            'personnel'     => $personnel,
            'postes'        => $postes,
            'typesPaiement' => $typesPaiement,
            'gps'           => $gps,
        ]);
    }

    // ── Qualifications (competences) CRUD ────────────────────────────────────

    public function storeQualification(Request $request, Personnel $personnel)
    {
        $validated = $request->validate([
            'PS_ID'        => ['required', 'integer', 'exists:poste,PS_ID'],
            'Q_VAL'        => ['nullable', 'string', 'max:100'],
            'Q_EXPIRATION' => ['nullable', 'date'],
        ]);

        $existing = Qualification::where('P_ID', $personnel->P_ID)
            ->where('PS_ID', $validated['PS_ID'])
            ->exists();

        if ($existing) {
            return redirect()->route('personnel.show', $personnel)
                ->with('error', 'Cette compétence est déjà enregistrée pour ce membre.');
        }

        Qualification::create([
            'P_ID'          => $personnel->P_ID,
            'PS_ID'         => $validated['PS_ID'],
            'Q_VAL'         => $validated['Q_VAL'] ?: null,
            'Q_EXPIRATION'  => $validated['Q_EXPIRATION'] ?: null,
            'Q_UPDATED_BY'  => auth()->id(),
            'Q_UPDATE_DATE' => now(),
        ]);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Compétence ajoutée.');
    }

    public function updateQualification(Request $request, Personnel $personnel, int $psId)
    {
        $validated = $request->validate([
            'Q_VAL'        => ['nullable', 'string', 'max:100'],
            'Q_EXPIRATION' => ['nullable', 'date'],
        ]);

        Qualification::where('P_ID', $personnel->P_ID)
            ->where('PS_ID', $psId)
            ->update([
                'Q_VAL'         => $validated['Q_VAL'] ?: null,
                'Q_EXPIRATION'  => $validated['Q_EXPIRATION'] ?: null,
                'Q_UPDATED_BY'  => auth()->id(),
                'Q_UPDATE_DATE' => now(),
            ]);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Compétence mise à jour.');
    }

    public function destroyQualification(Personnel $personnel, int $psId)
    {
        Qualification::where('P_ID', $personnel->P_ID)
            ->where('PS_ID', $psId)
            ->delete();

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Compétence supprimée.');
    }

    // ── Cotisations CRUD ─────────────────────────────────────────────────────

    public function storeCotisation(Request $request, Personnel $personnel)
    {
        $validated = $request->validate([
            'ANNEE'        => ['required', 'integer', 'min:1990', 'max:2100'],
            'PERIODE_CODE' => ['nullable', 'string', 'max:20'],
            'PC_DATE'      => ['required', 'date'],
            'MONTANT'      => ['required', 'numeric', 'min:0'],
            'TP_ID'        => ['nullable', 'integer', 'exists:type_paiement,TP_ID'],
            'REMBOURSEMENT'=> ['boolean'],
            'COMMENTAIRE'  => ['nullable', 'string', 'max:255'],
        ]);

        $validated['P_ID']          = $personnel->P_ID;
        $validated['REMBOURSEMENT'] = $request->boolean('REMBOURSEMENT') ? 1 : 0;

        Cotisation::create($validated);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Cotisation enregistrée.');
    }

    public function updateCotisation(Request $request, Personnel $personnel, int $pcId)
    {
        $validated = $request->validate([
            'ANNEE'        => ['required', 'integer', 'min:1990', 'max:2100'],
            'PERIODE_CODE' => ['nullable', 'string', 'max:20'],
            'PC_DATE'      => ['required', 'date'],
            'MONTANT'      => ['required', 'numeric', 'min:0'],
            'TP_ID'        => ['nullable', 'integer', 'exists:type_paiement,TP_ID'],
            'REMBOURSEMENT'=> ['boolean'],
            'COMMENTAIRE'  => ['nullable', 'string', 'max:255'],
        ]);

        $validated['REMBOURSEMENT'] = $request->boolean('REMBOURSEMENT') ? 1 : 0;

        Cotisation::where('PC_ID', $pcId)
            ->where('P_ID', $personnel->P_ID)
            ->update($validated);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Cotisation mise à jour.');
    }

    public function destroyCotisation(Personnel $personnel, int $pcId)
    {
        Cotisation::where('PC_ID', $pcId)
            ->where('P_ID', $personnel->P_ID)
            ->delete();

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Cotisation supprimée.');
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
            // Identity
            'P_CIVILITE'        => 'nullable|integer',
            'P_CODE'            => [
                'required', 'string', 'max:20',
                Rule::unique('pompier', 'P_CODE')->ignore($personnel->P_ID, 'P_ID'),
            ],
            'P_PRENOM'          => 'required|string|max:25',
            'P_PRENOM2'         => 'nullable|string|max:25',
            'P_NOM'             => 'required|string|max:30',
            'P_NOM_NAISSANCE'   => 'nullable|string|max:30',
            'P_SEXE'            => 'nullable|in:M,F',
            'P_GRADE'           => 'nullable|string|max:6',
            'P_PROFESSION'      => 'nullable|string|max:6',
            'P_STATUT'          => 'required|string|max:5',
            'P_SECTION'         => 'nullable|integer|exists:section,S_ID',
            'P_DATE_ENGAGEMENT' => 'nullable|date',
            'P_FIN'             => 'nullable|date',
            // Contact
            'P_EMAIL'           => 'nullable|email|max:60',
            'P_PHONE'           => 'nullable|string|max:20',
            'P_PHONE2'          => 'nullable|string|max:20',
            'P_ADDRESS'         => 'nullable|string|max:150',
            'P_ZIP_CODE'        => 'nullable|string|max:6',
            'P_CITY'            => 'nullable|string|max:30',
            'P_PAYS'            => 'nullable|string|max:50',
            // Emergency contact
            'P_RELATION_PRENOM' => 'nullable|string|max:50',
            'P_RELATION_NOM'    => 'nullable|string|max:50',
            'P_RELATION_PHONE'  => 'nullable|string|max:20',
            'P_RELATION_MAIL'   => 'nullable|email|max:100',
            // Personal info
            'P_BIRTHDATE'       => 'nullable|date',
            'P_BIRTHPLACE'      => 'nullable|string|max:50',
            'P_BIRTH_DEP'       => 'nullable|string|max:3',
            // Licence
            'P_LICENCE'         => 'nullable|string|max:30',
            'P_LICENCE_DATE'    => 'nullable|date',
            'P_LICENCE_EXPIRY'  => 'nullable|date',
            // Notes
            'OBSERVATION'       => 'nullable|string',
            // Photo
            'photo_upload'      => 'nullable|image|max:4096',
        ]);

        // Boolean flags (unchecked checkboxes are not submitted)
        $validated['P_HIDE']    = $request->boolean('P_HIDE');
        $validated['P_NOSPAM']  = $request->boolean('P_NOSPAM');
        $validated['NPAI']      = $request->boolean('NPAI');
        $validated['SUSPENDU']  = $request->boolean('SUSPENDU');

        // Handle photo upload
        if ($request->hasFile('photo_upload') && $request->file('photo_upload')->isValid()) {
            $file       = $request->file('photo_upload');
            $extension  = $file->getClientOriginalExtension() ?: 'jpg';
            $filename   = $personnel->P_ID . '_' . time() . '.' . $extension;
            $destDir    = public_path('images/user-specific/trombi');

            if (! is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            $file->move($destDir, $filename);
            $validated['P_PHOTO'] = $filename;
        }

        // Remove the virtual field before saving
        unset($validated['photo_upload']);

        $personnel->update($validated);

        return redirect()
            ->route('personnel.show', $personnel)
            ->with('success', 'Fiche personnel mise à jour.');
    }
}
