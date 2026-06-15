<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Services\TableExportService;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q'));
        $filtSect = (int) $request->integer('section', 0);

        $items = $this->buildFilteredQuery($request)->paginate(50)->withQueryString();
        $sections = Section::query()->orderBy('S_CODE')->get(['S_ID', 'S_CODE', 'S_DESCRIPTION']);

        return view('equipment.index', compact('items', 'search', 'filtSect', 'sections')
            + ['columns' => $this->equipmentColumns()]);
    }

    /**
     * Section-scoped, searched equipment query shared by the list and exports.
     */
    private function buildFilteredQuery(Request $request): Builder
    {
        $sectionId = (int) auth()->user()->P_SECTION;
        $search = trim((string) $request->string('q'));
        $filtSect = (int) $request->integer('section', 0);
        $target = $filtSect > 0 ? $filtSect : $sectionId;

        $query = DB::table('materiel as m')
            ->leftJoin('type_materiel as tm', 'm.TM_ID', '=', 'tm.TM_ID')
            ->leftJoin('section as s', 'm.S_ID', '=', 's.S_ID')
            ->where('m.S_ID', $target)
            ->select(
                'm.MA_ID', 'm.MA_MODELE', 'm.MA_NUMERO_SERIE',
                'm.MA_LIEU_STOCKAGE', 'm.MA_REV_DATE', 'm.MA_NB',
                'tm.TM_LIBELLE', 'tm.TM_USAGE'
            )
            ->orderBy('tm.TM_LIBELLE')
            ->orderBy('m.MA_MODELE');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('m.MA_MODELE', 'like', "%{$search}%")
                    ->orWhere('m.MA_NUMERO_SERIE', 'like', "%{$search}%")
                    ->orWhere('tm.TM_LIBELLE', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function exportXls(Request $request)
    {
        return $this->export($request, 'xlsx');
    }

    public function exportCsv(Request $request)
    {
        return $this->export($request, 'csv');
    }

    private function export(Request $request, string $format)
    {
        $service = new TableExportService;
        // 'type' / 'modele' are alwaysVisible, so resolveColumns skips them.
        $columns = $service->resolveColumns($this->equipmentColumns(), $request, [
            ['Type',   fn ($m) => $m->TM_LIBELLE ?? ''],
            ['Modèle', fn ($m) => $m->MA_MODELE ?? ''],
        ]);

        $items = $this->buildFilteredQuery($request)->get();
        $filename = 'Materiel_'.date('Ymd');

        return $format === 'csv'
            ? $service->toCsv($columns, $items, $filename)
            : $service->toXlsx($columns, $items, $filename, ['sheetTitle' => 'Matériel', 'freezeHeader' => true]);
    }

    private function equipmentColumns(): array
    {
        return [
            ['key' => 'type', 'label' => 'Type', 'type' => 'text', 'value' => fn ($m) => $m->TM_LIBELLE ?? '—', 'alwaysVisible' => true, 'mobile' => true],
            ['key' => 'modele', 'label' => 'Modèle', 'type' => 'text', 'value' => fn ($m) => $m->MA_MODELE ?: '—', 'alwaysVisible' => true, 'mobile' => true],
            ['key' => 'serie', 'label' => 'N° série', 'type' => 'text', 'value' => fn ($m) => $m->MA_NUMERO_SERIE ?: '—', 'mobile' => false, 'exportable' => true, 'exportValue' => fn ($m) => $m->MA_NUMERO_SERIE ?? ''],
            ['key' => 'lieu', 'label' => 'Lieu', 'type' => 'text', 'value' => fn ($m) => $m->MA_LIEU_STOCKAGE ?: '—', 'mobile' => false, 'exportable' => true, 'exportValue' => fn ($m) => $m->MA_LIEU_STOCKAGE ?? ''],
            ['key' => 'revision', 'label' => 'Révision', 'type' => 'html', 'value' => fn ($m) => $m->MA_REV_DATE ? ((Carbon::parse($m->MA_REV_DATE)->lte(now()->addDays(30))) ? '<i class="fas fa-exclamation-triangle text-warning me-1" title="Révision prochaine"></i>' : '').e(Carbon::parse($m->MA_REV_DATE)->format('d/m/Y')) : '—', 'mobile' => false, 'exportable' => true, 'exportValue' => fn ($m) => $m->MA_REV_DATE ? Carbon::parse($m->MA_REV_DATE)->format('d/m/Y') : ''],
            ['key' => 'qte', 'label' => 'Qté', 'type' => 'text', 'value' => fn ($m) => $m->MA_NB ?? 1, 'mobile' => false, 'exportable' => true, 'exportValue' => fn ($m) => $m->MA_NB ?? 1],
        ];
    }
}
