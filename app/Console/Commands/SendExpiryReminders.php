<?php

// project: OpenBrigade

namespace App\Console\Commands;

use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * E-mail members whose expirable qualifications / medical aptitudes are about
 * to lapse. Each expirable poste can define its own warning window
 * (poste.DAYS_WARNING); the --days option is the fallback when it is 0.
 *
 * Scheduled daily from routes/console.php. One digest email per member.
 */
class SendExpiryReminders extends Command
{
    protected $signature = 'reminders:expiry {--days=30 : Default warning window when a poste sets none} {--dry-run : List who would be notified without sending}';

    protected $description = 'Notify members of qualifications/aptitudes expiring soon';

    public function handle(NotificationService $notifications): int
    {
        if (! $this->option('dry-run') && ! $notifications->isMailAllowed()) {
            $this->warn('Mail is disabled (mail_allowed = 0) — nothing sent.');

            return self::SUCCESS;
        }

        $defaultDays = max(1, (int) $this->option('days'));
        $today = Carbon::today();
        $horizon = $today->copy()->addDays($defaultDays);

        // Pull every expirable qualification with an expiry date within the
        // widest possible window, then filter per-poste against its own
        // DAYS_WARNING (falling back to the default) in PHP.
        $rows = DB::table('qualification as q')
            ->join('poste as p', 'p.PS_ID', '=', 'q.PS_ID')
            ->join('pompier as m', 'm.P_ID', '=', 'q.P_ID')
            ->where('p.PS_EXPIRABLE', 1)
            ->whereNotNull('q.Q_EXPIRATION')
            ->whereNull('m.P_FIN')
            ->whereNotNull('m.P_EMAIL')
            ->where('m.P_EMAIL', '!=', '')
            ->where('q.Q_EXPIRATION', '>=', $today->toDateString())
            ->where('q.Q_EXPIRATION', '<=', $horizon->toDateString())
            ->get([
                'q.P_ID', 'q.Q_EXPIRATION', 'p.DESCRIPTION', 'p.DAYS_WARNING',
                'm.P_EMAIL', 'm.P_PRENOM', 'm.P_NOM',
            ]);

        // Group the due ones by member.
        $byMember = [];
        foreach ($rows as $row) {
            $window = ((int) $row->DAYS_WARNING) > 0 ? (int) $row->DAYS_WARNING : $defaultDays;
            $expiry = Carbon::parse($row->Q_EXPIRATION);

            if ($expiry->gt($today->copy()->addDays($window))) {
                continue; // outside this poste's own warning window
            }

            $byMember[$row->P_ID]['member'] ??= $row;
            $byMember[$row->P_ID]['items'][] = [
                'label' => $row->DESCRIPTION,
                'date' => $expiry,
            ];
        }

        if ($byMember === []) {
            $this->info('No qualifications expiring in the window — nothing to send.');

            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($byMember as $group) {
            $member = $group['member'];
            $lines = collect($group['items'])
                ->sortBy(fn ($i) => $i['date'])
                ->map(fn ($i) => '  • '.$i['label'].' — expire le '.$i['date']->format('d/m/Y'))
                ->implode("\n");

            $body = 'Bonjour '.ucfirst((string) $member->P_PRENOM).",\n\n"
                ."Les qualifications suivantes arrivent à échéance :\n\n"
                .$lines."\n\n"
                .'Merci de prendre contact avec votre responsable pour les renouveler.';

            if ($this->option('dry-run')) {
                $this->line("[dry-run] {$member->P_EMAIL} — ".count($group['items']).' qualification(s)');

                continue;
            }

            if ($notifications->sendEmail((string) $member->P_EMAIL, 'Qualifications à renouveler — '.config('app.name'), $body)) {
                $sent++;
            }
        }

        $this->info($this->option('dry-run')
            ? count($byMember).' member(s) would be notified.'
            : "Expiry reminders queued for {$sent} member(s).");

        return self::SUCCESS;
    }
}
