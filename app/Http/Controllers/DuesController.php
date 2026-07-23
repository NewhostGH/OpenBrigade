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

use App\Models\PaymentType;
use App\Services\FeatureService;
use App\Services\SectionScopeService;
use App\Services\TableExportService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DuesController extends Controller
{
    private const ALLOWED_ORDERS = [
        'P_NOM', 'P_STATUT', 'P_SECTION', 'TP_DESCRIPTION',
        'PC_DATE', 'PC_ID', 'P_DATE_ENGAGEMENT', 'P_FIN',
    ];

    private const DESC_ORDERS = ['P_DATE_ENGAGEMENT', 'P_FIN', 'PC_DATE', 'PC_ID'];

    public function index(Request $request)
    {
        [$year, $periodeCode, $sectionId, $subsections, $tpId, $paid, $includeOld, $order]
            = $this->parseFilters($request);

        $periodes = DB::table('periode')->orderBy('P_ORDER')->get();
        $typesPaiement = PaymentType::orderBy('TP_DESCRIPTION')->get(['TP_ID', 'TP_DESCRIPTION']);
        $periode = $periodes->firstWhere('P_CODE', $periodeCode);

        $items = $this->buildQuery($year, $periodeCode, $periode, $sectionId, $subsections, $tpId, $paid, $includeOld, $order)->get();

        return view('dues.index', [
            'items' => $items,
            'year' => $year,
            'currentYear' => now()->year,
            'periodeCode' => $periodeCode,
            'sectionId' => $sectionId,
            'subsections' => $subsections,
            'tpId' => $tpId,
            'paid' => $paid,
            'includeOld' => $includeOld,
            'order' => $order,
            'periodes' => $periodes,
            'typesPaiement' => $typesPaiement,
        ]);
    }

    public function batchSave(Request $request)
    {
        $year = (int) $request->integer('year', now()->year);
        $periodeCode = (string) $request->string('periode', 'A');
        $tpId = (int) $request->integer('type_paiement', 0);
        $people = array_filter(array_map('intval', (array) $request->input('people', [])));
        $today = now()->toDateString();

        $num = 0;
        $total = 0.0;

        foreach ($people as $pid) {
            if ($pid <= 0) {
                continue;
            }

            $paidFlag = $request->boolean("payments.{$pid}");
            $montant = (float) $request->input("montant.{$pid}", 0);
            $datePaid = trim((string) $request->input("date_paid.{$pid}", ''));
            $comment = substr((string) $request->input("commentaire.{$pid}", ''), 0, 100);

            DB::table('personnel_cotisation')
                ->where('P_ID', $pid)
                ->where('ANNEE', $year)
                ->where('PERIODE_CODE', $periodeCode)
                ->where('REMBOURSEMENT', 0)
                ->delete();

            if ($paidFlag) {
                if ($datePaid === '') {
                    $datePaid = $today;
                } else {
                    try {
                        $datePaid = Carbon::createFromFormat('d/m/Y', $datePaid)->toDateString();
                    } catch (\Exception) {
                        try {
                            $datePaid = Carbon::parse($datePaid)->toDateString();
                        } catch (\Exception) {
                            $datePaid = $today;
                        }
                    }
                }

                DB::table('personnel_cotisation')->insert([
                    'P_ID' => $pid,
                    'ANNEE' => $year,
                    'PERIODE_CODE' => $periodeCode,
                    'PC_DATE' => $datePaid,
                    'MONTANT' => $montant,
                    'TP_ID' => $tpId ?: 0,
                    'COMMENTAIRE' => $comment,
                    'REMBOURSEMENT' => 0,
                ]);

                $num++;
                $total += $montant;
            }
        }

        $filters = $request->only(['year', 'periode', 'section', 'subsections', 'type_paiement', 'paid', 'include_old']);

        return redirect()->route('dues.index', $filters)
            ->with('success', "Cotisations enregistrées pour {$num} personne(s). Total : ".Money::format($total));
    }

    public function export(Request $request)
    {
        [$year, $periodeCode, $sectionId, $subsections, $tpId, $paid, $includeOld, $order]
            = $this->parseFilters($request);

        $periodes = DB::table('periode')->orderBy('P_ORDER')->get();
        $periode = $periodes->firstWhere('P_CODE', $periodeCode);

        $rows = $this->buildQuery($year, $periodeCode, $periode, $sectionId, $subsections, $tpId, $paid, $includeOld, $order)->get();

        $columns = [
            ['Nom Prénom',  fn ($r) => strtoupper($r->P_NOM).' '.ucfirst(strtolower($r->P_PRENOM))],
            ['Statut',      fn ($r) => $r->P_STATUT],
            // Only meaningful with several sites.
            ...(app(FeatureService::class)->isEnabled('multi_site')
                ? [['Section', fn ($r) => $r->S_CODE]] : []),
            ['Entrée',      fn ($r) => $r->P_DATE_ENGAGEMENT ? Carbon::parse($r->P_DATE_ENGAGEMENT)->format('d/m/Y') : ''],
            ['Sortie',      fn ($r) => $r->P_FIN ? Carbon::parse($r->P_FIN)->format('d/m/Y') : ''],
            ['Payé',        fn ($r) => $r->PC_DATE ? 'Oui' : 'Non'],
            ['Montant',     fn ($r) => $r->MONTANT ?? ''],
            ['Date payé',   fn ($r) => $r->PC_DATE ? Carbon::parse($r->PC_DATE)->format('d/m/Y') : ''],
            ['Commentaire', fn ($r) => $r->COMMENTAIRE ?? ''],
        ];

        return (new TableExportService)->toXlsx(
            $columns,
            $rows,
            "Cotisations_{$year}_{$periodeCode}",
            ['sheetTitle' => 'Cotisations', 'headerRgb' => 'FFCC33', 'freezeHeader' => true, 'zoomScale' => 85, 'repeatHeader' => true]
        );
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function parseFilters(Request $request): array
    {
        $year = (int) $request->integer('year', now()->year);
        $periodeCode = (string) $request->string('periode', 'A');
        $sectionId = app(SectionScopeService::class)->sectionFilter($request);
        $subsections = (bool) $request->integer('subsections', 1);
        $tpId = (string) $request->string('type_paiement', 'ALL');
        $paid = (string) $request->string('paid', '2');
        $includeOld = (bool) $request->integer('include_old', 0);
        $order = (string) $request->string('order', 'P_NOM');

        if (! in_array($order, self::ALLOWED_ORDERS, true)) {
            $order = 'P_NOM';
        }

        return [$year, $periodeCode, $sectionId, $subsections, $tpId, $paid, $includeOld, $order];
    }

    private function buildQuery(
        int $year, string $periodeCode, ?object $periode,
        ?int $sectionId, bool $subsections, mixed $tpId,
        string $paid, bool $includeOld, string $order
    ) {
        $query = DB::table('pompier as p')
            ->leftJoin('personnel_cotisation as pc', function ($join) use ($year, $periodeCode) {
                $join->on('pc.P_ID', '=', 'p.P_ID')
                    ->where('pc.ANNEE', $year)
                    ->where('pc.PERIODE_CODE', $periodeCode)
                    ->where('pc.REMBOURSEMENT', 0);
            })
            ->join('section as s', 'p.P_SECTION', '=', 's.S_ID')
            ->join('type_paiement as tp', 'p.TP_ID', '=', 'tp.TP_ID')
            ->select([
                'p.P_ID', 'p.P_NOM', 'p.P_PRENOM', 'p.P_STATUT',
                'p.P_DATE_ENGAGEMENT', 'p.P_FIN', 'p.TP_ID',
                's.S_CODE',
                'tp.TP_DESCRIPTION',
                'pc.PC_ID', 'pc.MONTANT', 'pc.PC_DATE', 'pc.COMMENTAIRE',
            ])
            ->where('p.P_NOM', '!=', 'admin')
            ->where('p.P_STATUT', '!=', 'EXT');

        app(SectionScopeService::class)->apply($query, 'p.P_SECTION', $sectionId, $subsections);

        if ($tpId !== 'ALL' && is_numeric($tpId)) {
            $query->where('p.TP_ID', (int) $tpId);
        }

        if ($periode) {
            $this->applyPeriodFilter($query, $periode, $year);
        }

        if ($paid === '1') {
            $query->whereNotNull('pc.PC_DATE');
        } elseif ($paid === '0') {
            $query->whereNull('pc.PC_DATE');
        }

        if (! $includeOld) {
            $query->where('p.P_OLD_MEMBER', 0)->where('p.SUSPENDU', 0);
        }

        $tableMap = [
            'TP_DESCRIPTION' => 'tp',
            'PC_DATE' => 'pc',
            'PC_ID' => 'pc',
            'P_DATE_ENGAGEMENT' => 'p',
            'P_FIN' => 'p',
            'P_STATUT' => 'p',
            'P_SECTION' => 's',
            'P_NOM' => 'p',
        ];
        $alias = $tableMap[$order] ?? 'p';

        if (in_array($order, self::DESC_ORDERS, true)) {
            $query->orderByDesc("{$alias}.{$order}");
        } else {
            $orderCol = $order === 'P_SECTION' ? 's.S_CODE' : "{$alias}.{$order}";
            $query->orderBy($orderCol);
        }

        return $query;
    }

    private function applyPeriodFilter($query, object $periode, int $year): void
    {
        $pCode = $periode->P_CODE;
        $pDate = $periode->P_DATE;

        if ($pDate && is_numeric($pDate)) {
            $month = str_pad((string) $pDate, 2, '0', STR_PAD_LEFT);
            $start = "{$year}-{$month}-01";
            $nextStart = Carbon::createFromDate($year, (int) $month, 1)->addMonth()->toDateString();
            $query->where(fn ($q) => $q->where('p.P_DATE_ENGAGEMENT', '<', $nextStart)->orWhereNull('p.P_DATE_ENGAGEMENT'))
                ->where(fn ($q) => $q->where('p.P_FIN', '>', $start)->orWhereNull('p.P_FIN'));

            return;
        }

        match ($pCode) {
            'T1' => $query->where(fn ($q) => $q->where('p.P_DATE_ENGAGEMENT', '<', "{$year}-04-01")->orWhereNull('p.P_DATE_ENGAGEMENT'))
                ->where(fn ($q) => $q->where('p.P_FIN', '>', "{$year}-01-01")->orWhereNull('p.P_FIN')),
            'T2' => $query->where(fn ($q) => $q->where('p.P_DATE_ENGAGEMENT', '<', "{$year}-07-01")->orWhereNull('p.P_DATE_ENGAGEMENT'))
                ->where(fn ($q) => $q->where('p.P_FIN', '>', "{$year}-04-01")->orWhereNull('p.P_FIN')),
            'T3' => $query->where(fn ($q) => $q->where('p.P_DATE_ENGAGEMENT', '<', "{$year}-10-01")->orWhereNull('p.P_DATE_ENGAGEMENT'))
                ->where(fn ($q) => $q->where('p.P_FIN', '>', "{$year}-07-01")->orWhereNull('p.P_FIN')),
            'T4' => $query->where(fn ($q) => $q->where('p.P_DATE_ENGAGEMENT', '<=', "{$year}-12-31")->orWhereNull('p.P_DATE_ENGAGEMENT'))
                ->where(fn ($q) => $q->where('p.P_FIN', '>', "{$year}-10-01")->orWhereNull('p.P_FIN')),
            'S1' => $query->where(fn ($q) => $q->where('p.P_DATE_ENGAGEMENT', '<', "{$year}-07-01")->orWhereNull('p.P_DATE_ENGAGEMENT'))
                ->where(fn ($q) => $q->where('p.P_DATE_ENGAGEMENT', '>', "{$year}-01-01")->orWhereNull('p.P_DATE_ENGAGEMENT')),
            'S2' => $query->where(fn ($q) => $q->where('p.P_DATE_ENGAGEMENT', '<=', "{$year}-12-31")->orWhereNull('p.P_DATE_ENGAGEMENT'))
                ->where(fn ($q) => $q->where('p.P_DATE_ENGAGEMENT', '>', "{$year}-07-01")->orWhereNull('p.P_DATE_ENGAGEMENT')),
            'A' => $query->where(fn ($q) => $q->where('p.P_DATE_ENGAGEMENT', '<=', "{$year}-12-31")->orWhereNull('p.P_DATE_ENGAGEMENT'))
                ->where(fn ($q) => $q->where('p.P_FIN', '>=', "{$year}-01-01")->orWhereNull('p.P_FIN')),
            default => null,
        };
    }
}
