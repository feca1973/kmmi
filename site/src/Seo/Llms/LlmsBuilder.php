<?php

namespace App\Seo\Llms;

use App\Seo\PublicUrlBuilder;
use App\Service\UniqueContent;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * The files for the language models: /llms.txt (what the site is and where its content is) and /llms-full.txt (the
 * permanent content in one Markdown file). The articles are not in the full file (there are thousands): every
 * article, event, monument ... is available as `{url}.md`.
 */
class LlmsBuilder
{
    private const LATEST_NEWS = 10;
    private const FULL_TTL = 3600;

    public function __construct(
        private readonly PublicUrlBuilder $urls,
        private readonly UniqueContent $uniqueContent,
        private readonly MarkdownRenderer $renderer,
        private readonly ContentPages $content,
        private readonly CacheInterface $cache,
    ) {
    }

    public function index(): string
    {
        $lines = [
            '# Kárpátaljai Magyar Művelődési Intézet (KMMI)',
            '',
            '> A kárpátaljai magyar kultúra honlapja: hírek, rendezvények, emlékhelyek, szervezetek, települések, '
                . 'galériák, arcképcsarnok, könyvtár. ' . $this->uniqueContent->get('page-title'),
            '',
            'Nyelv: magyar. Az oldalak szövege szabadon olvasható; a cikkek, rendezvények, emlékhelyek és a többi '
                . 'tartalmi oldal Markdown formában is elérhető, ha az URL végére `.md` kerül.',
            '',
            '## Fő oldalak',
            '',
            ...$this->links([
                ['Hírek', '/cikkek/hirek', 'A legfrissebb hírek, beszámolók'],
                ['Rendezvények', '/rendezvenyek', 'Kárpátaljai magyar rendezvények'],
                ['Emlékhelyek', '/emlekhelyek', 'Emlékművek, szobrok, emléktáblák'],
                ['Szervezetek', '/szervezetek', 'Kárpátaljai magyar szervezetek, intézmények'],
                ['Települések', '/regiok/telepulesek', 'Kárpátaljai települések'],
                ['Képtár', '/kepek', 'Fotógalériák'],
                ['Arcképcsarnok', '/arckepcsarnok/muveszek', 'Művészek, írók, költők, népművészek'],
                ['Könyvtár', '/konyvtar/konyvek', 'Könyvek, folyóiratok és más kiadványok'],
                ['Oktatási intézmények', '/oktatasi-intezmenyek/altalanos-iskolak', 'Kárpátaljai oktatási intézmények'],
                ['Egyházak', '/egyhazak', 'Kárpátaljai egyházak'],
                ['Népművészet', '/nepmuveszet', 'Kárpátaljai népművészet'],
                ['Rólunk', '/informacio/rolunk', 'A Kárpátaljai Magyar Művelődési Intézetről'],
            ]),
            '',
            '## Legfrissebb hírek',
            '',
            ...$this->links(array_map(
                static fn (MarkdownPage $page): array => [$page->title, $page->path . '.md', ''],
                $this->content->latestNews(self::LATEST_NEWS)
            )),
            '',
            '## Optional',
            '',
            ...$this->links([
                ['Teljes tartalom', '/llms-full.txt', 'Az állandó tartalmak egyetlen fájlban'],
                ['Sitemap', '/sitemap.xml', 'Az összes oldal listája'],
            ]),
        ];

        return implode("\n", $lines) . "\n";
    }

    public function full(): string
    {
        return $this->cache->get('llms_full', function (ItemInterface $item): string {
            $item->expiresAfter(self::FULL_TTL);

            $parts = [
                '# Kárpátaljai Magyar Művelődési Intézet (KMMI) – teljes tartalom',
                '> Az állandó tartalmak egy fájlban. A cikkek külön, `{url}.md` címen érhetők el (lásd '
                    . $this->urls->absoluteUrl('/llms.txt') . ').',
            ];
            foreach ($this->content->categories() as $title => $pages) {
                if ($pages !== []) {
                    $parts = [...$parts, '## ' . $title, ...$this->renderPages($pages)];
                }
            }

            return implode("\n\n", $parts) . "\n";
        });
    }

    /**
     * @param list<MarkdownPage> $pages
     *
     * @return list<string>
     */
    private function renderPages(array $pages): array
    {
        return array_map(fn (MarkdownPage $page): string => trim($this->renderer->render($page, 3)), $pages);
    }

    /**
     * @param list<array{0: string, 1: string, 2: string}> $items title, path, description
     *
     * @return list<string>
     */
    private function links(array $items): array
    {
        return array_map(
            fn (array $item): string => '- [' . $item[0] . '](' . $this->urls->absoluteUrl($item[1]) . ')'
                . ($item[2] === '' ? '' : ': ' . $item[2]),
            $items
        );
    }
}
