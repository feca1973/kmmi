<?php

namespace App\Seo;

use DateTimeImmutable;
use LogicException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Collects the entries of the providers, groups them by section into sitemap files and renders the sitemap index
 * (/sitemap.xml) and the files (/sitemap-{name}.xml).
 */
class SitemapBuilder
{
    /** The limit of the sitemap protocol per file: a bigger section continues in `{section}-2`, `-3`, ... */
    public const MAX_URLS_PER_FILE = 50000;

    /** Section names: lowercase letters and digits (no hyphen: it separates the part number). */
    public const SECTION_PATTERN = '/^[a-z][a-z0-9]*$/';

    private const HOME_PATH = '/';

    /**
     * @param iterable<SitemapProviderInterface> $providers
     */
    public function __construct(
        #[AutowireIterator('app.sitemap_provider')]
        private readonly iterable $providers,
        private readonly PublicUrlBuilder $urls,
    ) {
    }

    /**
     * The non-empty files in the order of their names. A path appears once (the first entry wins).
     *
     * @return list<SitemapFile>
     */
    public function files(): array
    {
        $sections = $this->withHomeFirst($this->entriesBySection());
        ksort($sections);

        $files = [];
        foreach ($sections as $section => $entries) {
            foreach (array_chunk($entries, self::MAX_URLS_PER_FILE) as $index => $chunk) {
                $files[] = new SitemapFile($index === 0 ? (string) $section : $section . '-' . ($index + 1), $chunk);
            }
        }

        return $files;
    }

    /**
     * @param list<SitemapFile> $files
     */
    public function indexXml(array $files): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($files as $file) {
            $lines[] = '  <sitemap>';
            $lines[] = '    <loc>' . $this->escape($this->urls->absoluteUrl($file->path())) . '</loc>';
            $lines = [...$lines, ...$this->lastModLine($file->lastModified())];
            $lines[] = '  </sitemap>';
        }

        $lines[] = '</sitemapindex>';

        return implode("\n", $lines) . "\n";
    }

    public function fileXml(SitemapFile $file): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($file->entries as $entry) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . $this->escape($this->urls->absoluteUrl($entry->path)) . '</loc>';
            $lines = [...$lines, ...$this->lastModLine($entry->lastModified)];
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines) . "\n";
    }

    /**
     * The newest modification of all the files, null when none has one.
     *
     * @param list<SitemapFile> $files
     */
    public function lastModified(array $files): ?int
    {
        $latest = max([0, ...array_map(static fn (SitemapFile $file): int => $file->lastModified() ?? 0, $files)]);

        return $latest > 0 ? $latest : null;
    }

    /**
     * @return array<string, list<SitemapEntry>> section => entries
     */
    private function entriesBySection(): array
    {
        $sections = [];
        $seen = [];
        foreach ($this->providers as $provider) {
            $section = $this->validSection($provider);
            foreach ($provider->entries() as $entry) {
                if (!isset($seen[$entry->path])) {
                    $seen[$entry->path] = true;
                    $sections[$section][] = $entry;
                }
            }
        }

        return $sections;
    }

    private function validSection(SitemapProviderInterface $provider): string
    {
        $section = $provider->section();
        if (preg_match(self::SECTION_PATTERN, $section) !== 1) {
            throw new LogicException(sprintf(
                'The sitemap section "%s" of %s is not valid: lowercase letters and digits, starting with a letter.',
                $section,
                $provider::class,
            ));
        }

        return $section;
    }

    /**
     * The home page shows the latest content: its last modification is the newest of all the other entries. It stays
     * in its own file, as the first entry.
     *
     * @param array<string, list<SitemapEntry>> $sections
     *
     * @return array<string, list<SitemapEntry>>
     */
    private function withHomeFirst(array $sections): array
    {
        $latest = 0;
        $homeSection = null;
        foreach ($sections as $section => $entries) {
            foreach ($entries as $entry) {
                $latest = max($latest, $entry->lastModified ?? 0);
                if ($entry->path === self::HOME_PATH) {
                    $homeSection = $section;
                }
            }
        }
        if ($homeSection === null) {
            return $sections;
        }

        $others = array_values(array_filter(
            $sections[$homeSection],
            static fn (SitemapEntry $entry): bool => $entry->path !== self::HOME_PATH,
        ));
        $sections[$homeSection] = [new SitemapEntry(self::HOME_PATH, $latest > 0 ? $latest : null), ...$others];

        return $sections;
    }

    /**
     * @return list<string>
     */
    private function lastModLine(?int $timestamp): array
    {
        if ($timestamp === null) {
            return [];
        }

        return ['    <lastmod>' . (new DateTimeImmutable('@' . $timestamp))->format('c') . '</lastmod>'];
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
