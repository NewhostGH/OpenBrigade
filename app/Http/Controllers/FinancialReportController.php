<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

namespace App\Http\Controllers;

use App\Services\FinancialReportService;
use App\Services\SectionScopeService;
use App\Services\TableExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Financial report — native successor to legacy `report_cotisations.php`
 * ("Cotisations par section"). Read-only aggregation gated by permission 53
 * (financial data) and section-scoped through {@see SectionScopeService}.
 */
class FinancialReportController extends Controller
{
    public function __construct(
        private readonly FinancialReportService $service,
        private readonly SectionScopeService $scope,
    ) {}

    public function index(Request $request): View
    {
        [$sectionId, $subsections, $from, $to] = $this->parseFilters($request);

        $report = $this->service->report($sectionId, $subsections, $from, $to);

        return view('statistics.financial-report', array_merge($report, [
            'from' => $from,
            'to' => $to,
            'sectionId' => $sectionId,
            'subsections' => $subsections,
        ]));
    }

    public function exportXls(Request $request): StreamedResponse
    {
        return $this->export($request, 'xls');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        return $this->export($request, 'csv');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function export(Request $request, string $format): StreamedResponse
    {
        [$sectionId, $subsections, $from, $to] = $this->parseFilters($request);

        $report = $this->service->report($sectionId, $subsections, $from, $to);

        $columns = $this->exportColumns($report['paymentTypes']);
        $rows = $this->exportRows($report);
        $filename = 'Cotisations_'.str_replace('-', '', $from).'_'.str_replace('-', '', $to);

        $service = new TableExportService;

        return $format === 'csv'
            ? $service->toCsv($columns, $rows, $filename)
            : $service->toXlsx($columns, $rows, $filename, [
                'sheetTitle' => 'Cotisations',
                'headerRgb' => 'FFCC33',
                'freezeHeader' => true,
                'repeatHeader' => true,
                'zoomScale' => 85,
            ]);
    }

    /**
     * Flat export column set: Section, Profession, one per payment type,
     * Rejets, Total. Reuses the report's own payment-type ordering (rule 6).
     *
     * @param  array<int,string>  $paymentTypes
     * @return array<int,array{0:string,1:callable}>
     */
    private function exportColumns(array $paymentTypes): array
    {
        $columns = [
            ['Section', fn (array $r) => $r['section']],
            ['Effectif', fn (array $r) => $r['effectifs']],
            ['Profession', fn (array $r) => $r['profession']],
        ];

        foreach ($paymentTypes as $tpId => $label) {
            $columns[] = [$label, fn (array $r) => $r['amounts'][$tpId] ?? 0];
        }

        $columns[] = ['Rejets', fn (array $r) => $r['rejets']];
        $columns[] = ['Total', fn (array $r) => $r['total']];

        return $columns;
    }

    /**
     * Flatten the report into export rows: each section's profession lines,
     * a per-section subtotal, then a grand-total row.
     *
     * @param  array{paymentTypes: array<int,string>, sections: array<int,array<string,mixed>>, totals: array<string,mixed>}  $report
     * @return array<int,array<string,mixed>>
     */
    private function exportRows(array $report): array
    {
        $rows = [];

        foreach ($report['sections'] as $section) {
            foreach ($section['lines'] as $line) {
                $rows[] = [
                    'section' => $section['label'],
                    'effectifs' => $section['effectifs'],
                    'profession' => $line['profession'],
                    'amounts' => $line['amounts'],
                    'rejets' => $line['rejets'],
                    'total' => $line['total'],
                ];
            }

            $rows[] = [
                'section' => $section['label'],
                'effectifs' => $section['effectifs'],
                'profession' => 'Sous-total',
                'amounts' => $section['subtotal']['amounts'],
                'rejets' => $section['subtotal']['rejets'],
                'total' => $section['subtotal']['total'],
            ];
        }

        $rows[] = [
            'section' => 'TOTAL',
            'effectifs' => $report['totals']['effectifs'],
            'profession' => '',
            'amounts' => $report['totals']['amounts'],
            'rejets' => $report['totals']['rejets'],
            'total' => $report['totals']['total'],
        ];

        return $rows;
    }

    /**
     * Resolve the filters: section scope + inclusive date range. The range
     * defaults to the current calendar year; malformed dates fall back to it.
     *
     * @return array{0:?int,1:bool,2:string,3:string}
     */
    private function parseFilters(Request $request): array
    {
        $sectionId = $this->scope->sectionFilter($request);
        $subsections = (bool) $request->integer('subsections', 1);

        $from = $this->parseDate($request->input('from'), Carbon::create(now()->year, 1, 1));
        $to = $this->parseDate($request->input('to'), Carbon::create(now()->year, 12, 31));

        // Guard against an inverted range so the query never silently returns nothing.
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$sectionId, $subsections, $from, $to];
    }

    private function parseDate(mixed $value, Carbon $default): string
    {
        $value = is_string($value) ? trim($value) : '';
        if ($value === '') {
            return $default->toDateString();
        }

        foreach (['Y-m-d', 'd/m/Y'] as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $value)->toDateString();
            } catch (\Exception) {
                // try the next accepted format
            }
        }

        return $default->toDateString();
    }
}
