<?php

namespace App\Http\Controllers;

use App\Services\TableExportService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q'));
        $type = (string) $request->string('type', 'ALL');

        $items = $this->buildFilteredQuery($request)->paginate(50)->withQueryString();

        $types = DB::table('type_company')->orderBy('TC_LIBELLE')->get(['TC_CODE', 'TC_LIBELLE']);

        return view('company.index', compact('items', 'search', 'type', 'types')
            + ['columns' => $this->companyColumns()]);
    }

    /**
     * Section-scoped, searched/typed company query shared by the list and the
     * exports. Pagination is applied by the caller.
     */
    private function buildFilteredQuery(Request $request): Builder
    {
        $sectionId = (int) auth()->user()->P_SECTION;
        $search = trim((string) $request->string('q'));
        $type = (string) $request->string('type', 'ALL');

        $query = DB::table('company as c')
            ->leftJoin('type_company as tc', 'c.TC_CODE', '=', 'tc.TC_CODE')
            ->where('c.S_ID', $sectionId)
            ->select(
                'c.C_ID', 'c.C_NAME', 'c.C_EMAIL', 'c.C_PHONE',
                'c.C_CITY', 'c.C_ZIP_CODE', 'c.TC_CODE',
                'tc.TC_LIBELLE'
            )
            ->orderBy('c.C_NAME');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('c.C_NAME', 'like', "%{$search}%")
                    ->orWhere('c.C_CONTACT_NAME', 'like', "%{$search}%")
                    ->orWhere('c.C_EMAIL', 'like', "%{$search}%");
            });
        }

        if ($type !== 'ALL') {
            $query->where('c.TC_CODE', $type);
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
        // 'nom' is alwaysVisible, so resolveColumns skips it — prepend it.
        $columns = $service->resolveColumns($this->companyColumns(), $request, [
            ['Nom', fn ($c) => $c->C_NAME ?? ''],
        ]);

        $items = $this->buildFilteredQuery($request)->get();
        $filename = 'Clients_'.date('Ymd');

        return $format === 'csv'
            ? $service->toCsv($columns, $items, $filename)
            : $service->toXlsx($columns, $items, $filename, ['sheetTitle' => 'Clients', 'freezeHeader' => true]);
    }

    private function companyColumns(): array
    {
        return [
            ['key' => 'nom', 'label' => 'Nom', 'type' => 'text', 'value' => fn ($c) => $c->C_NAME, 'alwaysVisible' => true, 'sortField' => 'C_NAME', 'mobile' => true],
            ['key' => 'type', 'label' => 'Type', 'type' => 'text', 'value' => fn ($c) => $c->TC_LIBELLE ?? $c->TC_CODE ?? '—', 'mobile' => false, 'exportable' => true, 'exportValue' => fn ($c) => $c->TC_LIBELLE ?? $c->TC_CODE ?? ''],
            ['key' => 'ville', 'label' => 'Ville', 'type' => 'text', 'value' => fn ($c) => $c->C_CITY ? $c->C_CITY.($c->C_ZIP_CODE ? ' ('.$c->C_ZIP_CODE.')' : '') : '—', 'mobile' => false, 'exportable' => true, 'exportValue' => fn ($c) => $c->C_CITY ?? ''],
            ['key' => 'telephone', 'label' => 'Téléphone', 'type' => 'html', 'value' => fn ($c) => $c->C_PHONE ? '<a href="tel:'.e($c->C_PHONE).'" class="text-decoration-none">'.e($c->C_PHONE).'</a>' : '—', 'mobile' => false, 'exportable' => true, 'exportValue' => fn ($c) => $c->C_PHONE ?? ''],
            ['key' => 'email', 'label' => 'E-mail', 'type' => 'html', 'value' => fn ($c) => $c->C_EMAIL ? '<a href="mailto:'.e($c->C_EMAIL).'" class="text-decoration-none">'.e($c->C_EMAIL).'</a>' : '—', 'mobile' => false, 'exportable' => true, 'exportValue' => fn ($c) => $c->C_EMAIL ?? ''],
        ];
    }
}
