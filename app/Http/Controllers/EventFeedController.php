<?php

namespace App\Http\Controllers;

use App\Services\AppIdentityService;
use App\Services\EventFeedService;
use App\Services\GeneralSettingService;
use App\Services\RssFeedService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Public RSS 2.0 feed of upcoming events (legacy `rss.php`).
 *
 * Exposes only events flagged visible outside the organisation, so it is
 * intentionally unauthenticated — no member data is ever surfaced.
 */
class EventFeedController extends Controller
{
    public function __invoke(
        Request $request,
        EventFeedService $feed,
        RssFeedService $rss,
        AppIdentityService $identity,
        GeneralSettingService $settings,
    ): Response {
        $validated = $request->validate([
            'section' => ['nullable', 'integer', 'min:1'],
            'types' => ['nullable', 'string', 'max:255'],
        ]);

        $sectionId = isset($validated['section']) ? (int) $validated['section'] : null;
        $typeCodes = $this->parseTypes($validated['types'] ?? null);

        $events = $feed->visibleEvents($sectionId, $typeCodes);

        $link = $settings->siteUrl() ?: (string) config('app.url');

        $channel = [
            'title' => $identity->shortName(),
            'link' => $link,
            'description' => $identity->shortName().' — Agenda public',
            'language' => 'fr',
        ];

        return $rss->toResponse($channel, $feed->toFeedItems($events));
    }

    /**
     * Split the comma-separated `types` filter into clean TE_CODE tokens.
     *
     * @return list<string>
     */
    private function parseTypes(?string $types): array
    {
        if ($types === null || $types === '') {
            return [];
        }

        return collect(explode(',', $types))
            ->map(fn (string $code) => trim($code))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
