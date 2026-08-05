<?php

use App\Services\RssFeedService;

// ── Helpers ──────────────────────────────────────────────────────────────────

function sampleChannel(array $overrides = []): array
{
    return array_merge([
        'title' => 'CIS Dupont',
        'link' => 'https://example.test',
        'description' => 'Agenda public',
        'language' => 'fr',
    ], $overrides);
}

function sampleItem(array $overrides = []): array
{
    return array_merge([
        'title' => 'Formation — PSC1',
        'link' => 'https://example.test/events/42',
        'guid' => 'https://example.test/events/42',
        'pubDate' => 'Tue, 04 Aug 2026 10:00:00 +0000',
        'description' => "Organisé par : DUP — Dupont\nLieu : Paris",
    ], $overrides);
}

// ── Tests ────────────────────────────────────────────────────────────────────

it('builds a well-formed RSS 2.0 document', function () {
    $xml = (new RssFeedService)->build(sampleChannel(), [sampleItem()]);

    $doc = simplexml_load_string($xml);

    expect($doc)->not->toBeFalse()
        ->and((string) $doc['version'])->toBe('2.0')
        ->and((string) $doc->channel->title)->toBe('CIS Dupont')
        ->and((string) $doc->channel->link)->toBe('https://example.test')
        ->and((string) $doc->channel->language)->toBe('fr')
        ->and($doc->channel->item)->toHaveCount(1)
        ->and((string) $doc->channel->item[0]->title)->toBe('Formation — PSC1');
});

it('marks the guid as a non-permalink', function () {
    $xml = (new RssFeedService)->build(sampleChannel(), [sampleItem()]);
    $doc = simplexml_load_string($xml);

    expect((string) $doc->channel->item[0]->guid['isPermaLink'])->toBe('false');
});

it('escapes special characters in element text', function () {
    $xml = (new RssFeedService)->build(
        sampleChannel(['title' => 'A & B <Co>']),
        [sampleItem(['title' => 'Repas & débrief <urgent>'])],
    );

    // Raw markup must be entity-encoded, never leak as tags…
    expect($xml)->not->toContain('<Co>')
        ->and($xml)->toContain('A &amp; B');

    // …and it must still parse back to the exact original strings.
    $doc = simplexml_load_string($xml);
    expect((string) $doc->channel->title)->toBe('A & B <Co>')
        ->and((string) $doc->channel->item[0]->title)->toBe('Repas & débrief <urgent>');
});

it('preserves description layout via CDATA', function () {
    $desc = "Ligne 1 & suite\nLigne 2 <b>";
    $xml = (new RssFeedService)->build(sampleChannel(), [sampleItem(['description' => $desc])]);

    expect($xml)->toContain('<![CDATA[');

    $doc = simplexml_load_string($xml);
    expect((string) $doc->channel->item[0]->description)->toBe($desc);
});

it('omits pubDate when none is provided', function () {
    $xml = (new RssFeedService)->build(sampleChannel(), [sampleItem(['pubDate' => null])]);
    $doc = simplexml_load_string($xml);

    expect(isset($doc->channel->item[0]->pubDate))->toBeFalse();
});

it('defaults the channel language to fr', function () {
    $channel = sampleChannel();
    unset($channel['language']);

    $xml = (new RssFeedService)->build($channel, []);
    $doc = simplexml_load_string($xml);

    expect((string) $doc->channel->language)->toBe('fr');
});

it('returns a response with the RSS content type', function () {
    $response = (new RssFeedService)->toResponse(sampleChannel(), [sampleItem()]);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('Content-Type'))->toBe('application/rss+xml; charset=UTF-8')
        ->and($response->getContent())->toContain('<rss version="2.0">');
});
