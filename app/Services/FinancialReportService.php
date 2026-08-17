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

namespace App\Services;

use App\Models\PaymentType;
use Illuminate\Support\Facades\DB;

/**
 * Financial report — "Cotisations par section".
 *
 * Native successor to legacy `report_cotisations.php`. Aggregates membership
 * fees (personnel_cotisation) collected over a date range, broken down by
 * section and profession, with one column per payment type, the rejected
 * payments (rejet) and a net total.
 *
 * The legacy screen grouped by "department" using the now-removed
 * `section_flat` cache; this native version groups by the member's own
 * section and honours `SectionScopeService` (multi_site isolation + navbar
 * scope) instead, which is the current data-isolation authority (CONVENTIONS
 * §10).
 *
 * All SQL-facing methods are thin; the pivot/total assembly lives in the pure
 * {@see self::assemble()} so it is unit-testable without a database.
 */
class FinancialReportService implements ServiceInterface
{
    /** Placeholder shown for members with no profession set. */
    public const NO_PROFESSION = '—';

    public function __construct(
        private readonly SectionScopeService $scope,
    ) {}

    /**
     * Build the full report for the given scope and date range.
     *
     * @param  ?int  $sectionId    navbar/filter section (null = no narrowing)
     * @param  bool  $subsections  include descendant sections in the scope
     * @param  string  $from       inclusive start date (Y-m-d)
     * @param  string  $to         inclusive end date (Y-m-d)
     * @return array{paymentTypes: array<int,string>, sections: array<int,array<string,mixed>>, totals: array<string,mixed>}
     */
    public function report(?int $sectionId, bool $subsections, string $from, string $to): array
    {
        $paymentTypes = PaymentType::orderBy('TP_DESCRIPTION')
            ->pluck('TP_DESCRIPTION', 'TP_ID')
            ->map(fn ($label) => (string) $label)
            ->toArray();

        $cotis = $this->cotisations($sectionId, $subsections, $from, $to);
        $rejets = $this->rejets($sectionId, $subsections, $from, $to);
        $effectifs = $this->effectifs($sectionId, $subsections, $from, $to);

        $sectionIds = array_values(array_unique(array_merge(
            array_keys($cotis),
            array_keys($rejets),
            array_keys($effectifs),
        )));

        $sections = $this->sectionLabels($sectionIds);

        return $this->assemble($sections, $effectifs, $cotis, $rejets, $paymentTypes);
    }

    /**
     * Pure pivot + totals assembly. Kept database-free so the aggregation
     * rules are unit-testable in isolation.
     *
     * @param  array<int,array{S_ID:int,label:string}>  $sections  ordered
     * @param  array<int,int>  $effectifs  active headcount per section id
     * @param  array<int,array<string,array<int,float>>>  $cotis  [sid][prof][tpId] => amount
     * @param  array<int,array<string,float>>  $rejets  [sid][prof] => amount
     * @param  array<int,string>  $paymentTypes  [tpId => label], column order
     * @return array{paymentTypes: array<int,string>, sections: array<int,array<string,mixed>>, totals: array<string,mixed>}
     */
    public function assemble(array $sections, array $effectifs, array $cotis, array $rejets, array $paymentTypes): array
    {
        $tpIds = array_keys($paymentTypes);
        $zeroRow = array_fill_keys($tpIds, 0.0);

        $grandAmounts = $zeroRow;
        $grandRejets = 0.0;
        $grandTotal = 0.0;
        $grandEffectifs = 0;

        $outSections = [];

        foreach ($sections as $section) {
            $sid = $section['S_ID'];
            $professions = array_values(array_unique(array_merge(
                array_keys($cotis[$sid] ?? []),
                array_keys($rejets[$sid] ?? []),
            )));
            sort($professions);

            $lines = [];
            $subAmounts = $zeroRow;
            $subRejets = 0.0;
            $subTotal = 0.0;

            foreach ($professions as $prof) {
                $amounts = $zeroRow;
                foreach ($tpIds as $tpId) {
                    $amounts[$tpId] = round((float) ($cotis[$sid][$prof][$tpId] ?? 0), 2);
                }
                $rej = round((float) ($rejets[$sid][$prof] ?? 0), 2);
                $lineTotal = round(array_sum($amounts) - $rej, 2);

                $lines[] = [
                    'profession' => $prof === '' ? self::NO_PROFESSION : $prof,
                    'amounts' => $amounts,
                    'rejets' => $rej,
                    'total' => $lineTotal,
                ];

                foreach ($tpIds as $tpId) {
                    $subAmounts[$tpId] = round($subAmounts[$tpId] + $amounts[$tpId], 2);
                }
                $subRejets = round($subRejets + $rej, 2);
                $subTotal = round($subTotal + $lineTotal, 2);
            }

            $effectif = (int) ($effectifs[$sid] ?? 0);

            $outSections[] = [
                'S_ID' => $sid,
                'label' => $section['label'],
                'effectifs' => $effectif,
                'lines' => $lines,
                'subtotal' => [
                    'amounts' => $subAmounts,
                    'rejets' => $subRejets,
                    'total' => $subTotal,
                ],
            ];

            foreach ($tpIds as $tpId) {
                $grandAmounts[$tpId] = round($grandAmounts[$tpId] + $subAmounts[$tpId], 2);
            }
            $grandRejets = round($grandRejets + $subRejets, 2);
            $grandTotal = round($grandTotal + $subTotal, 2);
            $grandEffectifs += $effectif;
        }

        return [
            'paymentTypes' => $paymentTypes,
            'sections' => $outSections,
            'totals' => [
                'effectifs' => $grandEffectifs,
                'amounts' => $grandAmounts,
                'rejets' => $grandRejets,
                'total' => $grandTotal,
            ],
        ];
    }

    // ── Database-facing aggregates ─────────────────────────────────────────────

    /**
     * Sum of collected fees, grouped by section, profession and payment type.
     *
     * @return array<int,array<string,array<int,float>>>  [sid][prof][tpId] => amount
     */
    private function cotisations(?int $sectionId, bool $subsections, string $from, string $to): array
    {
        $query = DB::table('personnel_cotisation as pc')
            ->join('pompier as p', 'p.P_ID', '=', 'pc.P_ID')
            ->where('pc.REMBOURSEMENT', 0)
            ->whereBetween('pc.PC_DATE', [$from, $to])
            ->where('p.P_NOM', '<>', 'admin')
            ->groupBy('p.P_SECTION', 'p.P_PROFESSION', 'pc.TP_ID')
            ->select(
                'p.P_SECTION as sid',
                'p.P_PROFESSION as prof',
                'pc.TP_ID as tp',
                DB::raw('SUM(pc.MONTANT) as total'),
            );

        $this->scope->apply($query, 'p.P_SECTION', $sectionId, $subsections);

        $out = [];
        foreach ($query->get() as $row) {
            $out[(int) $row->sid][(string) ($row->prof ?? '')][(int) $row->tp] = (float) $row->total;
        }

        return $out;
    }

    /**
     * Sum of rejected/unregularised payments, grouped by section and profession.
     *
     * Matches the legacy filter: a rejection counts when it is the final
     * rejection (REGUL_ID = 3) or has not yet been regularised (REGULARISE = 0).
     *
     * @return array<int,array<string,float>>  [sid][prof] => amount
     */
    private function rejets(?int $sectionId, bool $subsections, string $from, string $to): array
    {
        $query = DB::table('rejet as r')
            ->join('pompier as p', 'p.P_ID', '=', 'r.P_ID')
            ->where(fn ($w) => $w->where('r.REGUL_ID', 3)->orWhere('r.REGULARISE', 0))
            ->whereBetween('r.DATE_REJET', [$from, $to])
            ->where('p.P_NOM', '<>', 'admin')
            ->groupBy('p.P_SECTION', 'p.P_PROFESSION')
            ->select(
                'p.P_SECTION as sid',
                'p.P_PROFESSION as prof',
                DB::raw('SUM(r.MONTANT_REJET) as total'),
            );

        $this->scope->apply($query, 'p.P_SECTION', $sectionId, $subsections);

        $out = [];
        foreach ($query->get() as $row) {
            $out[(int) $row->sid][(string) ($row->prof ?? '')] = (float) $row->total;
        }

        return $out;
    }

    /**
     * Active headcount per section over the period: members present at any
     * point in the range (engaged on/before the end, not left before the
     * start), excluding radiated/old members and the legacy `admin` account.
     *
     * @return array<int,int>  [sid => count]
     */
    private function effectifs(?int $sectionId, bool $subsections, string $from, string $to): array
    {
        $query = DB::table('pompier as p')
            ->where('p.P_OLD_MEMBER', 0)
            ->where('p.P_NOM', '<>', 'admin')
            ->where(fn ($w) => $w->whereNull('p.P_DATE_ENGAGEMENT')->orWhere('p.P_DATE_ENGAGEMENT', '<=', $to))
            ->where(fn ($w) => $w->whereNull('p.P_FIN')->orWhere('p.P_FIN', '>=', $from))
            ->groupBy('p.P_SECTION')
            ->select('p.P_SECTION as sid', DB::raw('COUNT(*) as nb'));

        $this->scope->apply($query, 'p.P_SECTION', $sectionId, $subsections);

        $out = [];
        foreach ($query->get() as $row) {
            $out[(int) $row->sid] = (int) $row->nb;
        }

        return $out;
    }

    /**
     * Labels for the involved sections, in org-chart order (S_ORDER, S_CODE).
     *
     * @param  int[]  $sectionIds
     * @return array<int,array{S_ID:int,label:string}>
     */
    private function sectionLabels(array $sectionIds): array
    {
        if ($sectionIds === []) {
            return [];
        }

        return DB::table('section')
            ->whereIn('S_ID', $sectionIds)
            ->orderBy('S_ORDER')
            ->orderBy('S_CODE')
            ->get(['S_ID', 'S_CODE', 'S_DESCRIPTION'])
            ->map(fn ($s) => [
                'S_ID' => (int) $s->S_ID,
                'label' => trim(($s->S_CODE ? $s->S_CODE.' — ' : '').($s->S_DESCRIPTION ?? '')) ?: 'Section '.$s->S_ID,
            ])
            ->all();
    }
}
