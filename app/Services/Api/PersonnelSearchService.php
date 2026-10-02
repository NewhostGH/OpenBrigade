<?php

namespace App\Services\Api;

use App\Exceptions\ApiException;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * POST /api/v1/personnel/search: port of the legacy api/export/search.php.
 *
 * Criteria are ANDed; each one is a prefix match unless `qstrict` is set.
 * External people (P_STATUT = EXT) are never returned. Each result carries
 * the person's currently valid competences, keyed by PS_ID.
 */
class PersonnelSearchService
{
    /** Request field → pompier column(s). */
    private const CRITERIA = [
        'username' => ['P_CODE'],
        'lastname' => ['P_NOM'],
        'firstname' => ['P_PRENOM'],
        'email' => ['P_EMAIL'],
        'phone' => ['P_PHONE', 'P_PHONE2'],
    ];

    /**
     * @param  array<string,mixed>  $input
     * @return list<array<string,mixed>>
     */
    public function search(array $input): array
    {
        $strict = (int) ($input['qstrict'] ?? 0) === 1;
        $query = DB::table('pompier')
            ->where('P_STATUT', '<>', 'EXT')
            ->select(['P_ID', 'P_CODE', 'P_NOM', 'P_PRENOM', 'P_EMAIL', 'P_BIRTHDATE', 'P_PHONE', 'P_PHONE2', 'P_SEXE', 'P_SECTION'])
            ->orderBy('P_NOM')
            ->orderBy('P_PRENOM');

        $filtered = false;

        if ((int) ($input['id'] ?? 0) > 0) {
            $query->where('P_ID', (int) $input['id']);
            $filtered = true;
        }

        foreach (self::CRITERIA as $field => $columns) {
            $value = trim((string) ($input[$field] ?? ''));
            if ($value === '' || $value === '0') {
                continue;
            }
            $filtered = true;
            $query->where(function (Builder $q) use ($columns, $value, $strict) {
                foreach ($columns as $column) {
                    $strict
                        ? $q->orWhereRaw("LOWER({$column}) = ?", [mb_strtolower($value)])
                        : $q->orWhereRaw("LOWER({$column}) LIKE ? ESCAPE '!'", [$this->prefix($value)]);
                }
            });
        }

        if (! $filtered) {
            throw new ApiException(50, 'No search criteria provided');
        }

        if ((int) ($input['exclude_old'] ?? 0) === 1) {
            $query->where('P_OLD_MEMBER', 0);
        }

        $rows = $query->get();
        $skills = $this->skills($rows->pluck('P_ID')->all());

        return $rows->map(fn ($row) => [
            'id' => (int) $row->P_ID,
            'username' => $row->P_CODE,
            'lastname' => $row->P_NOM,
            'firstname' => $row->P_PRENOM,
            'email' => $row->P_EMAIL,
            'birthdate' => $row->P_BIRTHDATE,
            'phone' => $row->P_PHONE,
            'phone2' => $row->P_PHONE2,
            'sexe' => $row->P_SEXE,
            'section' => $row->P_SECTION === null ? null : (int) $row->P_SECTION,
            'skills' => (object) ($skills[$row->P_ID] ?? []),
        ])->values()->all();
    }

    /**
     * Valid (non-expired) competences per person: [P_ID => [PS_ID => TYPE]].
     *
     * @param  list<int|string>  $ids
     * @return array<int|string,array<int,string>>
     */
    private function skills(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $out = [];
        DB::table('qualification as q')
            ->join('poste as p', 'p.PS_ID', '=', 'q.PS_ID')
            ->whereIn('q.P_ID', $ids)
            ->where(fn (Builder $q) => $q->whereNull('q.Q_EXPIRATION')->orWhere('q.Q_EXPIRATION', '>=', now()->toDateString()))
            ->orderBy('p.PS_ID')
            ->get(['q.P_ID', 'p.PS_ID', 'p.TYPE'])
            ->each(function ($row) use (&$out) {
                $out[$row->P_ID][(int) $row->PS_ID] = (string) $row->TYPE;
            });

        return $out;
    }

    private function prefix(string $value): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($value)).'%';
    }
}
