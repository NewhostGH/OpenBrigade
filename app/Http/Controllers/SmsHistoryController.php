<?php

namespace App\Http\Controllers;

use App\Services\SectionScopeService;
use App\Services\SmsHistoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SMS history — native successor to legacy `histo_sms.php` ("Historique SMS").
 * Read-only listing gated by permission 23 (send SMS) and section-scoped
 * through {@see SectionScopeService}.
 */
class SmsHistoryController extends Controller
{
    public function __construct(
        private readonly SmsHistoryService $service,
        private readonly SectionScopeService $scope,
    ) {}

    public function index(Request $request): View
    {
        $sectionId = $this->scope->sectionFilter($request);
        [$from, $to] = $this->parseRange($request);
        $perPage = min(max($request->integer('perPage', 50), 10), 200);

        $items = $this->service->history($sectionId, $from, $to, $perPage);

        return view('communication.sms-history', [
            'items' => $items,
            'columns' => $this->columns(),
            'from' => $from,
            'to' => $to,
            'sectionId' => $sectionId,
        ]);
    }

    /**
     * Display columns for the SMS history table (ob-table schema).
     *
     * @return array<int,array<string,mixed>>
     */
    private function columns(): array
    {
        return [
            ['key' => 'name', 'label' => __('communication.sms_col_name'), 'type' => 'html', 'alwaysVisible' => true, 'mobile' => true,
                'value' => fn ($r) => '<a href="'.route('personnel.show', $r->P_ID).'" class="text-decoration-none fw-semibold">'
                    .e(strtoupper($r->P_NOM ?? '').' '.ucfirst(mb_strtolower($r->P_PRENOM ?? ''))).'</a>'],
            ['key' => 'section', 'label' => __('communication.sms_col_section'), 'type' => 'text', 'mobile' => false,
                'value' => fn ($r) => $r->recipient_section ?: '—'],
            ['key' => 'date', 'label' => __('communication.sms_col_date'), 'type' => 'text', 'mobile' => true,
                'value' => fn ($r) => $r->S_DATE ? Carbon::parse($r->S_DATE)->format('d/m/Y H:i') : '—'],
            ['key' => 'nb', 'label' => __('communication.sms_col_nb'), 'type' => 'text', 'mobile' => false,
                'value' => fn ($r) => (int) $r->S_NB],
            ['key' => 'account', 'label' => __('communication.sms_col_account'), 'type' => 'text', 'mobile' => false,
                'value' => fn ($r) => trim(($r->account_code ?? '').' — '.($r->S_PROVIDER ?? ''), ' —') ?: '—'],
            ['key' => 'text', 'label' => __('communication.sms_col_text'), 'type' => 'text', 'mobile' => false,
                'value' => fn ($r) => $r->S_TEXTE ?? ''],
        ];
    }

    /**
     * Resolve the inclusive date range, defaulting to the current calendar
     * year. Malformed dates fall back to that default; an inverted range is
     * swapped so the query never silently returns nothing.
     *
     * @return array{0:string,1:string}
     */
    private function parseRange(Request $request): array
    {
        $from = $this->parseDate($request->input('from'), Carbon::create(now()->year, 1, 1));
        $to = $this->parseDate($request->input('to'), Carbon::create(now()->year, 12, 31));

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
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
