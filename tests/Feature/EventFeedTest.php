<?php

use App\Models\Event;
use App\Models\EventSchedule;
use App\Models\EventType;
use App\Models\Section;
use App\Services\AppIdentityService;
use App\Services\EventFeedService;
use App\Services\GeneralSettingService;
use Illuminate\Database\Eloquent\Collection;

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * A publicly-visible event with its section, type and one schedule attached as
 * relations — no database required.
 */
function feedEvent(array $attrs = []): Event
{
    $event = (new Event)->forceFill(array_merge([
        'E_CODE' => 42,
        'E_LIBELLE' => 'PSC1 session',
        'E_LIEU' => 'Paris',
        'E_ADDRESS' => '1 rue de la Paix',
        'E_COMMENT2' => 'Prévoir une tenue adaptée',
        'E_CREATE_DATE' => '2026-08-01 09:00:00',
        'E_VISIBLE_OUTSIDE' => 1,
        'E_CANCELED' => 0,
    ], $attrs));

    $event->setRelation('type', (new EventType)->forceFill(['TE_LIBELLE' => 'Formation']));
    $event->setRelation('section', (new Section)->forceFill([
        'S_CODE' => 'DUP', 'S_DESCRIPTION' => 'Dupont',
    ]));
    $event->setRelation('horaires', Collection::make([
        (new EventSchedule)->forceFill(['EH_DATE_DEBUT' => '2026-08-10']),
    ]));

    return $event;
}

/**
 * Bind a partial EventFeedService whose selection is stubbed to $events while
 * the real toFeedItems() shaping still runs, plus deterministic identity.
 *
 * @param  Collection<int, Event>  $events
 */
function feedStub(Collection $events): void
{
    $feed = Mockery::mock(EventFeedService::class)->makePartial();
    $feed->shouldReceive('visibleEvents')->andReturn($events);
    app()->instance(EventFeedService::class, $feed);

    $identity = Mockery::mock(AppIdentityService::class)->makePartial();
    $identity->shouldReceive('shortName')->andReturn('CIS Test');
    app()->instance(AppIdentityService::class, $identity);

    $settings = Mockery::mock(GeneralSettingService::class)->makePartial();
    $settings->shouldReceive('siteUrl')->andReturn('https://cis.test');
    app()->instance(GeneralSettingService::class, $settings);
}

// ── Tests ────────────────────────────────────────────────────────────────────

it('serves the events feed publicly without authentication', function () {
    feedStub(Collection::make([feedEvent()]));

    $response = $this->get(route('feed.events'));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toBe('application/rss+xml; charset=UTF-8');
});

it('renders each visible event as an RSS item', function () {
    feedStub(Collection::make([feedEvent()]));

    $xml = $this->get(route('feed.events'))->getContent();
    $doc = simplexml_load_string($xml);

    expect($doc)->not->toBeFalse()
        ->and((string) $doc->channel->title)->toBe('CIS Test')
        ->and((string) $doc->channel->link)->toBe('https://cis.test')
        ->and($doc->channel->item)->toHaveCount(1)
        ->and((string) $doc->channel->item[0]->title)->toBe('Formation — PSC1 session')
        ->and((string) $doc->channel->item[0]->link)->toBe(url('/events/42'))
        ->and((string) $doc->channel->item[0]->description)->toContain('Organisé par : DUP — Dupont')
        ->and((string) $doc->channel->item[0]->description)->toContain('Lieu : Paris');
});

it('returns an empty but valid feed when no events match', function () {
    feedStub(Collection::make([]));

    $xml = $this->get(route('feed.events'))->getContent();
    $doc = simplexml_load_string($xml);

    expect($doc)->not->toBeFalse()
        ->and($doc->channel->item)->toHaveCount(0);
});

it('passes the section and types filters through to the service', function () {
    $feed = Mockery::mock(EventFeedService::class)->makePartial();
    $feed->shouldReceive('visibleEvents')
        ->once()
        ->with(7, ['FOR', 'INT'])
        ->andReturn(Collection::make([]));
    app()->instance(EventFeedService::class, $feed);

    $this->get(route('feed.events', ['section' => 7, 'types' => 'FOR, INT,FOR']))
        ->assertOk();
});

it('rejects a non-numeric section filter', function () {
    feedStub(Collection::make([]));

    $this->get(route('feed.events', ['section' => 'abc']))
        ->assertStatus(302); // validation redirect
});
