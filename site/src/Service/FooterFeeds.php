<?php

namespace App\Service;

use SimpleXMLElement;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * The latest items of the RSS feeds of the partner institutes, shown in the footer. The feeds are fetched
 * concurrently and kept for an hour; an unreachable feed is simply left empty (and retried after a few minutes).
 *
 * @phpstan-type FeedItem array{title: string, link: string}
 * @phpstan-type Feed array{title: string, url: string, articles: list<FeedItem>}
 */
class FooterFeeds
{
    private const ITEMS_PER_FEED = 5;
    private const TTL = 3600;
    private const RETRY_TTL = 300;
    private const TIMEOUT = 3;

    /**
     * @param array<string, array{title: string, url: string}> $feeds the institutes by the URLs of their feeds
     */
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
        #[Autowire('%app.footer_feeds%')]
        private readonly array $feeds,
    ) {
    }

    /**
     * @return list<Feed>
     */
    public function load(): array
    {
        return $this->cache->get('footer_feeds', function (ItemInterface $item): array {
            $feeds = $this->fetch();
            $complete = !in_array([], array_column($feeds, 'articles'), true);
            $item->expiresAfter($complete ? self::TTL : self::RETRY_TTL);

            return $feeds;
        });
    }

    /**
     * @return list<Feed>
     */
    private function fetch(): array
    {
        $responses = [];
        foreach (array_keys($this->feeds) as $url) {
            $responses[$url] = $this->httpClient->request('GET', $url, [
                'timeout' => self::TIMEOUT,
                'max_duration' => self::TIMEOUT + 1,
            ]);
        }

        $feeds = [];
        foreach ($this->feeds as $url => $feed) {
            $feeds[] = $feed + ['articles' => $this->readItems($responses[$url])];
        }

        return $feeds;
    }

    /**
     * @return list<FeedItem>
     */
    private function readItems(ResponseInterface $response): array
    {
        try {
            $content = $response->getContent();
        } catch (ExceptionInterface) {
            return [];
        }

        $xml = $this->parse($content);
        $articles = [];
        foreach ($xml?->channel->item ?? [] as $entry) {
            $articles[] = ['title' => trim((string) $entry->title), 'link' => trim((string) $entry->link)];
            if (count($articles) === self::ITEMS_PER_FEED) {
                break;
            }
        }

        return $articles;
    }

    private function parse(string $content): ?SimpleXMLElement
    {
        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content, SimpleXMLElement::class, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $xml === false ? null : $xml;
    }
}
