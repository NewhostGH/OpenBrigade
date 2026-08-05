<?php

namespace App\Services;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Selects the events exposed on the public RSS feed and shapes them into the
 * plain item structure consumed by {@see RssFeedService}.
 *
 * This is the single source of truth for "which events appear on the public
 * feed": only events explicitly flagged visible outside the organisation, not
 * cancelled, and scheduled within the recent/upcoming window. Ports the
 * selection rules of the legacy `rss.php` script.
 */
class EventFeedService implements ServiceInterface
{
    /**
     * Events whose most recent schedule started at most this many days ago are
     * still advertised, alongside every future one (legacy `rss.php` window).
     */
    private const RECENT_DAYS = 5;

    public function __construct(private readonly SectionScopeService $sections) {}

    /**
     * The publicly visible events, earliest scheduled first.
     *
     * @param  int|null  $sectionId  Restrict to this section and its descendants (0/null = all).
     * @param  list<string>  $typeCodes  Restrict to these event types (TE_CODE); empty = all.
     * @return Collection<int, Event>
     */
    public function visibleEvents(?int $sectionId = null, array $typeCodes = []): Collection
    {
        $cutoff = Carbon::now()->subDays(self::RECENT_DAYS)->startOfDay();

        $query = Event::query()
            ->where('E_VISIBLE_OUTSIDE', 1)
            ->where('E_CANCELED', 0)
            ->whereHas('horaires', fn ($q) => $q->where('EH_DATE_DEBUT', '>=', $cutoff))
            ->with(['section', 'type', 'horaires' => fn ($q) => $q->orderBy('EH_DATE_DEBUT')])
            ->withMin('horaires as first_start', 'EH_DATE_DEBUT')
            ->orderBy('first_start')
            ->limit((int) config('brigade.max_list_rows', 500));

        if ($sectionId !== null && $sectionId > 0) {
            $query->whereIn('S_ID', $this->sections->descendantIds($sectionId));
        }

        if ($typeCodes !== []) {
            $query->whereIn('TE_CODE', $typeCodes);
        }

        return $query->get();
    }

    /**
     * Map events to the RSS item structure ({@see RssFeedService}).
     *
     * @param  Collection<int, Event>  $events
     * @return list<array{title: string, link: string, guid: string, pubDate: string|null, description: string}>
     */
    public function toFeedItems(Collection $events): array
    {
        return $events->map(function (Event $event): array {
            $url = route('event.show', $event->E_CODE);
            $typeLabel = $event->type?->TE_LIBELLE;
            $title = $typeLabel
                ? $typeLabel.' — '.$event->E_LIBELLE
                : (string) $event->E_LIBELLE;

            return [
                'title' => $title,
                'link' => $url,
                'guid' => $url,
                'pubDate' => $event->E_CREATE_DATE?->toRssString(),
                'description' => $this->describe($event),
            ];
        })->all();
    }

    /**
     * Compose the French free-text description shown for a feed item.
     */
    private function describe(Event $event): string
    {
        $lines = [];

        if ($event->section) {
            $lines[] = 'Organisé par : '.trim($event->section->S_CODE.' — '.$event->section->S_DESCRIPTION, ' —');
        }

        $start = $event->horaires->first()?->EH_DATE_DEBUT;
        if ($start !== null) {
            $lines[] = 'Date : '.$start->format('d/m/Y');
        }

        if (! empty($event->E_LIEU)) {
            $lines[] = 'Lieu : '.$event->E_LIEU;
        }

        if (! empty($event->E_ADDRESS)) {
            $lines[] = 'Adresse : '.$event->E_ADDRESS;
        }

        if (! empty($event->E_COMMENT2)) {
            $lines[] = trim((string) $event->E_COMMENT2);
        }

        return implode("\n", $lines);
    }
}
