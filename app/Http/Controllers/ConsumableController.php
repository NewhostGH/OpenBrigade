<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Services\TableExportService;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConsumableController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q'));
        $filtSect = (int) $request->integer('section', 0);
        $alert = (bool) $request->boolean('alert', false);

        $items = $this->buildFilteredQuery($request)->paginate(50)->withQueryString();
        $sections = Section::query()->orderBy('S_CODE')->get(['S_ID', 'S_CODE', 'S_DESCRIPTION']);

        return view('consumable.index', compact('items', 'search', 'filtSect', 'alert', 'sections')
            + ['columns' => $this->consommableColumns()]);
    }

    /**
     * Section-scoped, searched/alert-filtered consumable query shared by the
     * list and the exports.
     */
    private function buildFilteredQuery(Request $request): Builder
    {
        $sectionId = (int) auth()->user()->P_SECTION;
        $search = trim((string) $request->string('q'));
        $filtSect = (int) $request->integer('section', 0);
        $alert = (bool) $request->boolean('alert', false);
        $target = $filtSect > 0 ? $filtSect : $sectionId;
        $today = now()->toDateString();

        $query = DB::table('consommable as c')
            ->leftJoin('type_consommable as tc', 'c.TC_ID', '=', 'tc.TC_ID')
            ->where('c.S_ID', $target)
            ->select(
                'c.C_ID', 'c.C_DESCRIPTION', 'c.C_NOMBRE',
                'c.C_MINIMUM', 'c.C_DATE_PEREMPTION', 'c.C_LIEU_STOCKAGE',
                'tc.TC_LIBELLE',
                DB::raw("CASE
                    WHEN c.C_DATE_PEREMPTION IS NOT NULL AND c.C_DATE_PEREMPTION < '{$today}' THEN 'expired'
                    WHEN c.C_DATE_PEREMPTION IS NOT NULL AND c.C_DATE_PEREMPTION <= DATE_ADD('{$today}', INTERVAL 90 DAY) THEN 'expiring'
                    WHEN c.C_MINIMUM > 0 AND c.C_NOMBRE < c.C_MINIMUM THEN 'low'
                    ELSE 'ok'
                END as alert_level")
            )
            ->orderBy('tc.TC_LIBELLE')
            ->orderBy('c.C_DESCRIPTION');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('c.C_DESCRIPTION', 'like', "%{$search}%")
                    ->orWhere('tc.TC_LIBELLE', 'like', "%{$search}%");
            });
        }

        if ($alert) {
            $query->where(function ($q) use ($today): void {
                $q->whereRaw("(c.C_DATE_PEREMPTION IS NOT NULL AND c.C_DATE_PEREMPTION <= DATE_ADD('{$today}', INTERVAL 90 DAY))")
                    ->orWhereRaw('(c.C_MINIMUM > 0 AND c.C_NOMBRE < c.C_MINIMUM)');
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
        // 'type' / 'description' are alwaysVisible, so resolveColumns skips them.
        $columns = $service->resolveColumns($this->consommableColumns(), $request, [
            ['Type',        fn ($c) => $c->TC_LIBELLE ?? ''],
            ['Description', fn ($c) => $c->C_DESCRIPTION ?? ''],
        ]);

        $items = $this->buildFilteredQuery($request)->get();
        $filename = 'Consommables_'.date('Ymd');

        return $format === 'csv'
            ? $service->toCsv($columns, $items, $filename)
            : $service->toXlsx($columns, $items, $filename, ['sheetTitle' => 'Consommables', 'freezeHeader' => true]);
    }

    private function consommableColumns(): array
    {
        return [
            ['key' => 'type', 'label' => 'Type', 'type' => 'text', 'value' => fn ($c) => $c->TC_LIBELLE ?? '—', 'alwaysVisible' => true, 'mobile' => true],
            ['key' => 'description', 'label' => 'Description', 'type' => 'text', 'value' => fn ($c) => $c->C_DESCRIPTION ?: '—', 'alwaysVisible' => true, 'mobile' => true],
            ['key' => 'qte_min', 'label' => 'Qté / Min', 'type' => 'html', 'value' => fn ($c) => (($c->C_MINIMUM > 0 && $c->C_NOMBRE < $c->C_MINIMUM) ? '<span class="text-danger fw-semibold">'.$c->C_NOMBRE.'</span>' : $c->C_NOMBRE).($c->C_MINIMUM > 0 ? ' <span class="text-muted">/ '.$c->C_MINIMUM.'</span>' : ''), 'mobile' => false, 'exportable' => true, 'exportValue' => fn ($c) => $c->C_NOMBRE],
            ['key' => 'lieu', 'label' => 'Lieu', 'type' => 'text', 'value' => fn ($c) => $c->C_LIEU_STOCKAGE ?: '—', 'mobile' => false, 'exportable' => true, 'exportValue' => fn ($c) => $c->C_LIEU_STOCKAGE ?? ''],
            ['key' => 'peremption', 'label' => 'Péremption', 'type' => 'html', 'value' => fn ($c) => $c->C_DATE_PEREMPTION ? (($c->alert_level === 'expired' || $c->alert_level === 'expiring') ? '<i class="fas fa-exclamation-triangle text-warning me-1" title="Attention"></i>' : '').e(Carbon::parse($c->C_DATE_PEREMPTION)->format('d/m/Y')) : '—', 'mobile' => false, 'exportable' => true, 'exportValue' => fn ($c) => $c->C_DATE_PEREMPTION ? Carbon::parse($c->C_DATE_PEREMPTION)->format('d/m/Y') : ''],
            ['key' => 'statut', 'label' => 'Statut', 'type' => 'badge', 'value' => fn ($c) => $c->alert_level ?? 'ok', 'badgeMap' => ['expired' => ['Périmé', 'ob-badge-bloqued'], 'expiring' => ['Expire bientôt', 'ob-badge-ben'], 'low' => ['Stock bas', 'ob-badge-pres'], 'ok' => ['OK', 'ob-badge-actif']], 'exportable' => true, 'exportValue' => fn ($c) => $c->alert_level ?? 'ok', 'mobile' => true],
        ];
    }
}
