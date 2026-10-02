<?php

namespace App\Services\Api;

use App\Exceptions\ApiException;
use App\Support\Audit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * POST /api/v1/events/import: port of the legacy api/import/event.php.
 *
 * `event_code` = 0 creates an activity, any other value rewrites that
 * activity. Sessions are replaced wholesale; each listed person is
 * (re)registered on every session with the given function (TP_ID).
 * Validation order and errnum codes follow the legacy script.
 */
class EventImportService
{
    private const DATETIME = 'Y-m-d H:i';

    /**
     * @param  array<string,mixed>  $data
     * @return array{id:int,created:bool,message:string}
     */
    public function import(array $data): array
    {
        $in = new ApiInput($data);

        $code = $in->int('event_code', 1050);
        $event = $this->validated($in);
        $sessions = $this->sessions($in);
        $people = $this->people($in);

        $type = $event['TE_CODE'];
        $in->failIf(! DB::table('type_evenement')->where('TE_CODE', $type)->exists(), 1450, "Wrong event_type {$type}");

        if ($code === 0) {
            $this->assertNoDuplicate($event, $sessions[0]);
        } elseif (! DB::table('evenement')->where('E_CODE', $code)->exists()) {
            throw new ApiException(1470, "Unknown event code: {$code}", 404);
        }

        $event['E_HEURE_RDV'] = $sessions[0]['start']->format('H:i');

        return DB::transaction(function () use ($code, $event, $sessions, $people) {
            if ($code === 0) {
                $code = (int) DB::table('evenement')->lockForUpdate()->max('E_CODE') + 1;
                DB::table('evenement')->insert($event + [
                    'E_CODE' => $code,
                    'E_NB' => 0,
                    'C_ID' => 0,
                    'E_VISIBLE_OUTSIDE' => 0,
                    'E_EXTERIEUR' => 0,
                    'E_CREATED_BY' => null,
                    'E_CREATE_DATE' => now(),
                ]);
                $result = ['id' => $code, 'created' => true, 'message' => "Insert successful, new event created, number {$code}"];
                Audit::activity('api.event_created', ['event_id' => $code]);
            } else {
                DB::table('evenement')->where('E_CODE', $code)->update($event);
                DB::table('evenement_horaire')->where('E_CODE', $code)->delete();
                DB::table('evenement_participation')->where('E_CODE', $code)->where('EH_ID', '>', count($sessions))->delete();
                $result = ['id' => $code, 'created' => false, 'message' => "Update successful, event number {$code}"];
                Audit::activity('api.event_updated', ['event_id' => $code]);
            }

            foreach ($sessions as $i => $s) {
                DB::table('evenement_horaire')->insert([
                    'E_CODE' => $code,
                    'EH_ID' => $i + 1,
                    'EH_DATE_DEBUT' => $s['start']->toDateString(),
                    'EH_DATE_FIN' => $s['end']->toDateString(),
                    'EH_DEBUT' => $s['start']->format('H:i:s'),
                    'EH_FIN' => $s['end']->format('H:i:s'),
                    'EH_DUREE' => round($s['start']->diffInMinutes($s['end']) / 60, 2),
                    'EH_DESCRIPTION' => '',
                ]);
            }

            foreach ($people as $personId => $function) {
                DB::table('evenement_participation')->where('E_CODE', $code)->where('P_ID', $personId)->delete();
                foreach ($sessions as $i => $s) {
                    DB::table('evenement_participation')->insert([
                        'E_CODE' => $code,
                        'EH_ID' => $i + 1,
                        'P_ID' => $personId,
                        'EP_DUREE' => round($s['start']->diffInMinutes($s['end']) / 60, 2),
                        'TP_ID' => $function,
                        'EP_DATE' => now(),
                        'EP_BY' => null,
                    ]);
                }
            }

            return $result;
        });
    }

    /** @return array<string,mixed> evenement columns */
    private function validated(ApiInput $in): array
    {
        $name = $in->string('event_name', 1060);
        $in->failIf($name === '' || mb_strlen($name) > 60, 1061, 'Invalid event_name (maximum 60 characters)');
        $type = $in->string('event_type', 1070);
        $location = $in->string('location', 1080);
        $in->failIf(mb_strlen($location) > 50, 1081, 'Invalid location (maximum 50 characters)');
        $address = $in->string('address', 1090);
        $in->failIf(mb_strlen($address) > 255, 1091, 'Invalid address (maximum 255 characters)');
        $section = $in->int('section', 1100);
        $in->failIf(! DB::table('section')->where('S_ID', $section)->exists(), 1101, "Invalid section {$section}, it does not exist");

        $comment = $in->string('comment');
        $tarif = $in->has('tarif') ? (float) $in->raw('tarif') : null;

        $url = $in->string('url');
        $in->failIf($url !== '' && (mb_strlen($url) > 500 || ! filter_var($url, FILTER_VALIDATE_URL)), 1110, "URL invalid: {$url}");

        $competence = $in->int('competence');
        $in->failIf($competence > 0 && ! DB::table('poste')->where('PS_ID', $competence)->where('PS_FORMATION', 1)->exists(), 1120, 'Wrong value for competence');

        $contact = $in->string('contact_entreprise');
        $in->failIf(mb_strlen($contact) > 50, 1125, 'Invalid contact_entreprise (maximum 50 characters)');
        $contactTel = $in->phone('contact_tel', 1130);
        $telephone = $in->phone('telephone', 1140);
        $in->failIf(mb_strlen($telephone) > 15, 1140, "telephone invalid: {$telephone}");

        $formation = $in->string('type_formation');
        $in->failIf($formation !== '' && ! DB::table('type_formation')->where('TF_CODE', $formation)->exists(), 1150, "Wrong value for type_formation, try 'I' or 'R'");

        return [
            'TE_CODE' => $type,
            'S_ID' => $section,
            'E_LIBELLE' => $name,
            'E_LIEU' => $location,
            'E_ADDRESS' => $address,
            'E_COMMENT' => $comment,
            'E_TARIF' => $tarif,
            'E_URL' => $url,
            'PS_ID' => $competence > 0 ? $competence : null,
            'E_CONTACT_LOCAL' => $contact,
            'E_CONTACT_TEL' => $contactTel,
            'E_TEL' => $telephone,
            'TF_CODE' => $formation !== '' ? $formation : null,
            'E_NB_STAGIAIRES' => max(0, min(127, $in->int('stagiaires'))),
        ];
    }

    /**
     * Sessions sorted by session_id, which must run 1..n without gaps and in
     * chronological order.
     *
     * @return non-empty-list<array{start:Carbon,end:Carbon}>
     */
    private function sessions(ApiInput $in): array
    {
        $list = $in->raw('event_sessions');
        $in->failIf(! is_array($list) || $list === [], 1210, 'Missing event_sessions');

        $byId = [];
        foreach ($list as $s) {
            $byId[(int) (is_array($s) ? ($s['session_id'] ?? 0) : 0)] = is_array($s) ? $s : [];
        }
        ksort($byId);
        $in->failIf(! isset($byId[1]), 1200, 'No_session 1');
        $in->failIf(array_keys($byId) !== range(1, count($byId)), 1200, 'Session ids must run 1..n without gaps');

        $out = [];
        $previous = null;
        foreach ($byId as $k => $s) {
            $start = is_scalar($s['start'] ?? null) ? (string) $s['start'] : '';
            $end = is_scalar($s['end'] ?? null) ? (string) $s['end'] : '';
            ApiInput::assertDate($start, self::DATETIME, 1400, "start date {$k}");
            ApiInput::assertDate($end, self::DATETIME, 1410, "end date {$k}");

            $startAt = Carbon::createFromFormat(self::DATETIME, $start);
            $endAt = Carbon::createFromFormat(self::DATETIME, $end);
            $in->failIf($endAt->lt($startAt), 1420, "dates of session {$k} are invalid end date must be after start date");
            $in->failIf($previous !== null && $startAt->lt($previous), 1430, 'Inconsistent dates, sessions must be in chronological order');

            $previous = $startAt;
            $out[] = ['start' => $startAt, 'end' => $endAt];
        }

        return $out;
    }

    /** @return array<int,int> P_ID => TP_ID (last entry wins, as in the legacy script) */
    private function people(ApiInput $in): array
    {
        if (! $in->has('people')) {
            return [];
        }

        $list = $in->raw('people');
        $in->failIf(! is_array($list), 1300, 'Wrong values for people');

        $out = [];
        foreach ($list as $p) {
            $in->failIf(! is_array($p) || ! isset($p['user_id']), 1300, 'Wrong values for people');
            $in->failIf(! isset($p['function_id']), 1310, 'Wrong values for people');
            $user = (int) $p['user_id'];
            $in->failIf($user <= 0 || ! DB::table('pompier')->where('P_ID', $user)->exists(), 1320, 'Wrong values for people, user '.(is_scalar($p['user_id']) ? $p['user_id'] : ''));
            $out[$user] = (int) $p['function_id'];
        }

        return $out;
    }

    /**
     * Refuse a second activity of the same type and section whose first
     * session starts at the same date and time.
     *
     * @param  array<string,mixed>  $event
     * @param  array{start:Carbon,end:Carbon}  $first
     */
    private function assertNoDuplicate(array $event, array $first): void
    {
        $twin = DB::table('evenement as e')
            ->join('evenement_horaire as eh', 'eh.E_CODE', '=', 'e.E_CODE')
            ->where('e.TE_CODE', $event['TE_CODE'])
            ->where('e.S_ID', $event['S_ID'])
            ->where('eh.EH_ID', 1)
            ->where('eh.EH_DATE_DEBUT', $first['start']->toDateString())
            ->where('eh.EH_DEBUT', $first['start']->format('H:i:s'))
            ->value('e.E_CODE');

        if ($twin) {
            throw new ApiException(1460, "You are trying to insert a duplicate see this event: {$twin}", 409);
        }
    }
}
