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
            ->with('success', "Cotisations enregistrées pour {$num} personne(s). Total : ".number_format($total, 2, ',', ' ').' €');
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

    // ── Prélèvements (Tab 2) ─────────────────────────────────────────────────

    /**
     * List active members who pay by direct debit (TP_ID = 1).
     * Shows pending (no cotisation yet) vs already-saved counts + batch save form.
     */
    public function directDebits(Request $request)
    {
        $year = (int) $request->integer('year', now()->year);
        $periodeCode = (string) $request->string('periode', 'A');
        $sectionId = (int) $request->integer('section', 0);
        $subsections = (bool) $request->integer('subsections', 1);

        $periodes = DB::table('periode')->orderBy('P_ORDER')->get();
        $periode = $periodes->firstWhere('P_CODE', $periodeCode);

        // Base: TP_ID=1 (direct debit), active, non-EXT, non-admin
        $base = DB::table('pompier as p')
            ->join('section as s', 'p.P_SECTION', '=', 's.S_ID')
            ->leftJoin('personnel_cotisation as pc', function ($join) use ($year, $periodeCode) {
                $join->on('pc.P_ID', '=', 'p.P_ID')
                    ->where('pc.ANNEE', $year)
                    ->where('pc.PERIODE_CODE', $periodeCode)
                    ->where('pc.REMBOURSEMENT', 0);
            })
            ->where('p.TP_ID', 1)
            ->where('p.P_NOM', '!=', 'admin')
            ->where('p.P_STATUT', '!=', 'EXT')
            ->where('p.P_OLD_MEMBER', 0)
            ->where('p.SUSPENDU', 0);

        app(SectionScopeService::class)->apply($base, 'p.P_SECTION', $sectionId, $subsections);

        if ($periode) {
            $this->applyPeriodFilter($base, $periode, $year);
        }

        $pending = (clone $base)
            ->whereNull('pc.PC_DATE')
            ->select(['p.P_ID', 'p.P_NOM', 'p.P_PRENOM', 'p.MONTANT_REGUL', 's.S_CODE'])
            ->orderBy('p.P_NOM')
            ->get();

        $paid = (clone $base)
            ->whereNotNull('pc.PC_DATE')
            ->select(['p.P_ID', 'p.P_NOM', 'p.P_PRENOM', 'pc.MONTANT', 'pc.PC_DATE', 's.S_CODE'])
            ->orderBy('p.P_NOM')
            ->get();

        $totalPending = $pending->sum(fn ($r) => (float) ($r->MONTANT_REGUL ?? 0));
        $totalPaid = $paid->sum(fn ($r) => (float) ($r->MONTANT ?? 0));

        return view('dues.direct-debits', [
            'pending' => $pending,
            'paid' => $paid,
            'totalPending' => $totalPending,
            'totalPaid' => $totalPaid,
            'year' => $year,
            'currentYear' => now()->year,
            'periodeCode' => $periodeCode,
            'sectionId' => $sectionId,
            'subsections' => $subsections,
            'periodes' => $periodes,
        ]);
    }

    /**
     * Batch-save direct-debit cotisations for all pending members.
     */
    public function saveDirectDebits(Request $request)
    {
        $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'periode' => ['required', 'string', 'max:10'],
            'date_prelev' => ['required', 'date'],
        ]);

        $year = (int) $request->input('year');
        $periodeCode = (string) $request->input('periode');
        $datePrelev = Carbon::parse($request->input('date_prelev'))->toDateString();
        $pids = array_filter(array_map('intval', (array) $request->input('pids', [])));

        $num = 0;
        $total = 0.0;

        foreach ($pids as $pid) {
            if ($pid <= 0) {
                continue;
            }

            $montant = (float) $request->input("montant.{$pid}", 0);

            // Skip if already paid for this period
            $exists = DB::table('personnel_cotisation')
                ->where('P_ID', $pid)
                ->where('ANNEE', $year)
                ->where('PERIODE_CODE', $periodeCode)
                ->where('REMBOURSEMENT', 0)
                ->whereNotNull('PC_DATE')
                ->exists();

            if (! $exists) {
                DB::table('personnel_cotisation')->insert([
                    'P_ID' => $pid,
                    'ANNEE' => $year,
                    'PERIODE_CODE' => $periodeCode,
                    'PC_DATE' => $datePrelev,
                    'MONTANT' => $montant,
                    'TP_ID' => 1,
                    'COMMENTAIRE' => '',
                    'REMBOURSEMENT' => 0,
                ]);
                $num++;
                $total += $montant;
            }
        }

        $filters = $request->only(['year', 'periode', 'section', 'subsections']);

        return redirect()->route('dues.direct-debits', $filters)
            ->with('success', "Prélèvements enregistrés pour {$num} personne(s). Total : ".number_format($total, 2, ',', ' ').' €');
    }

    // ── Virements (Tab 3) ─────────────────────────────────────────────────────

    /**
     * List reimbursement / bank-transfer entries (REMBOURSEMENT=1, TP_ID=2).
     * Filterable by section, date range, and include-old toggle.
     */
    public function transfers(Request $request)
    {
        $sectionId = (int) $request->integer('section', 0);
        $subsections = (bool) $request->integer('subsections', 1);
        $includeOld = (bool) $request->integer('include_old', 0);
        $dateFrom = trim((string) $request->string('date_from', ''));
        $dateTo = trim((string) $request->string('date_to', ''));
        $order = (string) $request->string('order', 'PC_DATE');

        $allowedOrders = ['P_NOM', 'P_DATE_ENGAGEMENT', 'P_FIN', 'MONTANT', 'PC_DATE'];
        if (! in_array($order, $allowedOrders, true)) {
            $order = 'PC_DATE';
        }

        $query = DB::table('personnel_cotisation as pc')
            ->join('pompier as p', 'p.P_ID', '=', 'pc.P_ID')
            ->join('section as s', 'p.P_SECTION', '=', 's.S_ID')
            ->where('pc.REMBOURSEMENT', 1)
            ->where('pc.TP_ID', 2)
            ->select([
                'p.P_ID', 'p.P_NOM', 'p.P_PRENOM',
                's.S_CODE', 's.S_ID as S_SID',
                'p.P_DATE_ENGAGEMENT', 'p.P_FIN',
                'pc.PC_ID', 'pc.PC_DATE', 'pc.MONTANT', 'pc.COMMENTAIRE',
            ]);

        app(SectionScopeService::class)->apply($query, 'p.P_SECTION', $sectionId, $subsections);

        if (! $includeOld) {
            $query->where('p.P_OLD_MEMBER', 0)->where('p.SUSPENDU', 0);
        }

        if ($dateFrom !== '') {
            try {
                $query->where('pc.PC_DATE', '>=', Carbon::parse($dateFrom)->toDateString());
            } catch (\Exception) {
            }
        }
        if ($dateTo !== '') {
            try {
                $query->where('pc.PC_DATE', '<=', Carbon::parse($dateTo)->toDateString());
            } catch (\Exception) {
            }
        }

        $descOrders = ['P_DATE_ENGAGEMENT', 'P_FIN', 'PC_DATE', 'MONTANT'];
        if (in_array($order, $descOrders, true)) {
            $alias = in_array($order, ['P_DATE_ENGAGEMENT', 'P_FIN']) ? 'p' : 'pc';
            $query->orderByDesc("{$alias}.{$order}");
        } else {
            $query->orderBy("p.{$order}");
        }

        $items = $query->paginate(50)->withQueryString();

        return view('dues.transfers', [
            'items' => $items,
            'sectionId' => $sectionId,
            'subsections' => $subsections,
            'includeOld' => $includeOld,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'order' => $order,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function parseFilters(Request $request): array
    {
        $year = (int) $request->integer('year', now()->year);
        $periodeCode = (string) $request->string('periode', 'A');
        $sectionId = (int) $request->integer('section', 0);
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
        int $sectionId, bool $subsections, mixed $tpId,
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
