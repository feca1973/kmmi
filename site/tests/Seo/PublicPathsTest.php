<?php

namespace App\Tests\Seo;

use App\Entity\Article\ArticleType;
use App\Seo\PublicPaths;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouterInterface;

/**
 * The paths of PublicPaths are the ones of the routes of the controllers (the URLs of the live site), with the
 * parameters of the routes in the right places.
 */
final class PublicPathsTest extends KernelTestCase
{
    public function testEveryPathIsTheOneOfItsRoute(): void
    {
        self::bootKernel();
        $router = self::getContainer()->get(RouterInterface::class);
        $paths = new PublicPaths();

        $expected = [
            [$paths->article(6, 'hirek', 'egy-cikk'), 'news_single', ['type' => 'hirek', 'slug' => 'egy-cikk']],
            [
                $paths->article(ArticleType::FOLK_ART_ID, 'nepmuveszet', 'egy-cikk'),
                'folk_show',
                ['slug' => 'egy-cikk'],
            ],
            [
                $paths->libraryItem('egyutt', 'egy-szam'),
                'library_show',
                ['category' => 'egyutt', 'slug' => 'egy-szam'],
            ],
            [$paths->actor('muveszek', 'egy-muvesz'), 'actor_show', ['category' => 'muveszek', 'slug' => 'egy-muvesz']],
            [$paths->event('unnep', 'egy-nap'), 'event_single', ['type' => 'unnep', 'slug' => 'egy-nap']],
            [$paths->monument('szobor', 'egy-szobor'), 'monument_show', ['type' => 'szobor', 'slug' => 'egy-szobor']],
            [$paths->organization('egy-egyesulet'), 'organization_single', ['slug' => 'egy-egyesulet']],
            [
                $paths->city('beregszaszi-jaras', 'beregszasz'),
                'cities_single',
                ['region' => 'beregszaszi-jaras', 'slug' => 'beregszasz'],
            ],
            [$paths->document('rolunk'), 'document_single', ['slug' => 'rolunk']],
            [$paths->gallery('egy-galeria'), 'gallery_single', ['slug' => 'egy-galeria']],
        ];

        foreach ($expected as [$path, $route, $parameters]) {
            $match = $router->match($path);

            self::assertSame($route, $match['_route'], $path);
            foreach ($parameters as $name => $value) {
                self::assertSame($value, $match[$name], $path . ' ' . $name);
            }
        }
    }
}
