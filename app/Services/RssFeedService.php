<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use Illuminate\Http\Response;

/**
 * Builds and streams RSS 2.0 feed responses. The format counterpart of
 * {@see ICalExportService}: callers hand over a plain channel/item structure
 * and this service produces a well-formed, correctly escaped XML document —
 * never string-concatenated markup.
 *
 * @phpstan-type FeedChannel array{title: string, link: string, description: string, language?: string}
 * @phpstan-type FeedItem array{title: string, link: string, guid: string, pubDate?: string|null, description?: string}
 */
class RssFeedService implements ServiceInterface
{
    /**
     * Build an RSS 2.0 response from a channel description and its items.
     *
     * @param  FeedChannel  $channel  Feed-level metadata (title, link, description, language).
     * @param  list<FeedItem>  $items  Feed entries in display order.
     */
    public function toResponse(array $channel, array $items): Response
    {
        return response($this->build($channel, $items), 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }

    /**
     * Serialise the channel and items to an RSS 2.0 XML string.
     *
     * @param  FeedChannel  $channel
     * @param  list<FeedItem>  $items
     */
    public function build(array $channel, array $items): string
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $rss = $doc->createElement('rss');
        $rss->setAttribute('version', '2.0');
        $doc->appendChild($rss);

        $channelEl = $doc->createElement('channel');
        $rss->appendChild($channelEl);

        $this->appendText($doc, $channelEl, 'title', $channel['title']);
        $this->appendText($doc, $channelEl, 'link', $channel['link']);
        $this->appendText($doc, $channelEl, 'description', $channel['description']);
        $this->appendText($doc, $channelEl, 'language', $channel['language'] ?? 'fr');

        foreach ($items as $item) {
            $itemEl = $doc->createElement('item');
            $channelEl->appendChild($itemEl);

            $this->appendText($doc, $itemEl, 'title', $item['title']);
            $this->appendText($doc, $itemEl, 'link', $item['link']);

            $guid = $this->appendText($doc, $itemEl, 'guid', $item['guid']);
            $guid->setAttribute('isPermaLink', 'false');

            if (! empty($item['pubDate'])) {
                $this->appendText($doc, $itemEl, 'pubDate', $item['pubDate']);
            }

            // Descriptions carry newlines and free-text punctuation; wrap them
            // in CDATA so the layout survives instead of being entity-encoded.
            $descEl = $doc->createElement('description');
            $descEl->appendChild($doc->createCDATASection($item['description'] ?? ''));
            $itemEl->appendChild($descEl);
        }

        return (string) $doc->saveXML();
    }

    /**
     * Append a text-content child element, letting DOMDocument escape the value.
     */
    private function appendText(DOMDocument $doc, DOMElement $parent, string $name, string $value): DOMElement
    {
        $el = $doc->createElement($name);
        $el->appendChild($doc->createTextNode($value));
        $parent->appendChild($el);

        return $el;
    }
}
