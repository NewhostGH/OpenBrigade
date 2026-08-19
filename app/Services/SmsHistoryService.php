<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * SMS history — native successor to legacy `histo_sms.php` ("Historique SMS").
 *
 * Read-only listing of sent SMS from the legacy `smslog` table, joined to the
 * recipient ({@see User} → `pompier`) and to both the recipient's
 * section and the sending SMS account's section. Rows are section-scoped on the
 * recipient's section through {@see SectionScopeService}, so a user only ever
 * sees SMS sent to members of sections they may access.
 */
class SmsHistoryService implements ServiceInterface
{
    public function __construct(
        private readonly SectionScopeService $scope,
    ) {}

    /**
     * Paginated SMS history for the given (inclusive) date range, optionally
     * narrowed to a section. Ordered most-recent first.
     *
     * @return LengthAwarePaginator<int,object>
     */
    public function history(?int $sectionId, string $from, string $to, int $perPage = 50): LengthAwarePaginator
    {
        $query = DB::table('smslog as s')
            ->join('pompier as p', 'p.P_ID', '=', 's.P_ID')
            ->leftJoin('section as se', 'se.S_ID', '=', 'p.P_SECTION')
            ->leftJoin('section as sm', 'sm.S_ID', '=', 's.S_ID')
            ->whereDate('s.S_DATE', '>=', $from)
            ->whereDate('s.S_DATE', '<=', $to)
            ->select(
                's.P_ID',
                'p.P_NOM',
                'p.P_PRENOM',
                's.S_DATE',
                's.S_NB',
                's.S_TEXTE',
                'se.S_CODE as recipient_section',
                'sm.S_CODE as account_code',
                's.S_PROVIDER',
            )
            ->orderByDesc('s.S_DATE');

        $this->scope->apply($query, 'p.P_SECTION', $sectionId);

        return $query->paginate($perPage)->withQueryString();
    }
}
