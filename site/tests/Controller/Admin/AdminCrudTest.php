<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\ArticleCrudController;
use App\Controller\Admin\ArticleTypeCrudController;
use App\Controller\Admin\BookCategoryCrudController;
use App\Controller\Admin\CitiesCrudController;
use App\Controller\Admin\DocCrudController;
use App\Controller\Admin\EventCrudController;
use App\Controller\Admin\EventPlacesCrudController;
use App\Controller\Admin\GalleryCrudController;
use App\Controller\Admin\LegacyCrudController;
use App\Controller\Admin\MediaCrudController;
use App\Controller\Admin\MonumentCrudController;
use App\Controller\Admin\MonumentSubmitterCrudController;
use App\Controller\Admin\MonumentTypeCrudController;
use App\Controller\Admin\OrganizationCrudController;
use App\Controller\Admin\RegionsCrudController;
use App\Controller\Admin\SliderCrudController;
use App\Entity\Article\Article;
use App\Entity\Article\ArticleType;
use App\Entity\Region\Cities;
use App\Entity\Region\Regions;
use App\Tests\Support\AdminClientTrait;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClassConstant;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The lists and forms of the admin follow the legacy admin: same columns and field order, page size and default sort.
 * The expected values are taken from the legacy easy_admin.yaml.
 */
/**
 * The test names every CRUD controller of the admin.
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
final class AdminCrudTest extends WebTestCase
{
    use AdminClientTrait;
    use ResetDatabase;

    /**
     * @return iterable<string, array{string, class-string, list<string>, list<string>}>
     *     path, controller, list columns, form field labels
     */
    public static function crudProvider(): iterable
    {
        yield 'regions' => ['/admin/regions', RegionsCrudController::class, ['Név'], ['Név']];
        yield 'cities' => [
            '/admin/cities',
            CitiesCrudController::class,
            ['Cím', 'Régió', 'Aktív'],
            ['Régiók', 'Név', 'Líd', 'Szöveg', 'Főkép', 'Aktív'],
        ];
        yield 'articles' => [
            '/admin/article',
            ArticleCrudController::class,
            ['Cím', 'Rovat', 'Létrehozva', 'Aktív'],
            [
                'Név', 'Bevezető', 'Szöveg', 'Hírekhez: cikk szerzője', 'Hírekhez: cikk forrása',
                'Könyvekhez: könyv szerzője', 'Kategória', 'Kapcsolódó település', 'Publikáció dátuma', 'Aktív',
                'Művészet típus', 'Művész születésnapja', 'Oktatási intézmény típus', 'Kiadvány típus',
                'Kiadás éve', 'Galéria', 'Főkép', 'Csatolmányok',
            ],
        ];
        yield 'events' => [
            '/admin/event',
            EventCrudController::class,
            ['Cím', 'Típus', 'Település', '-tól', '-ig', 'Aktív'],
            [
                'Név', 'Bevezető', 'Szöveg', 'Helyszín neve', 'Helyszín címe', 'Kapcsolattartó személy(ek)',
                'Típus', 'Kapcsolódó település', 'Kapcsolódó hely', 'Szervezetek (több is kiválasztható)',
                'Dátum (-tól)', 'Dátum (-ig)', 'Országkód', 'Aktív', 'Telefonszám', 'E-mail', 'Fax', 'Webcím',
                'A hely webcíme', 'Esemény képe, plakátja',
            ],
        ];
        yield 'organizations' => [
            '/admin/organization',
            OrganizationCrudController::class,
            ['Cím', 'Típus', 'Település', 'Ország', 'Frissítve', 'Aktív'],
            [
                'Név', 'Szervezet bemutatása', 'Szervezet célja', 'Működési területe', 'Szervezet szolgáltatásai',
                'Vezető rövid leírása', 'Helyszín címe', 'Vezető neve', 'Vezető tisztsége', 'Kapcsolattartó neve',
                'Típus', 'Kapcsolódó település', 'Országkód', 'Aktív', 'Szervezet e-mail címe',
                'Szervezet honlapja', 'Szervezet alapításának éve',
                'Otthoni telefonszám (+országhívó utána egyben az összes szám)',
                'Munkahelyi telefonszám (+országhívó)', 'Munkahelyi mobilszám (+országhívó)',
                'Fax elérhetőség (+országhívó)', 'Vezető e-mail címe',
                'Kapcsolattartó munkahelyi telefonszáma: (+országhívó)',
                'Kapcsolattartó munkahelyi mobilszám (+országhívó)',
                'Kapcsolattartó fax elérhetőség (+országhívó)', 'Kapcsolattartó e-mail címe', 'Galéria',
                'Csatolmányok',
            ],
        ];
        yield 'monuments' => [
            '/admin/monument',
            MonumentCrudController::class,
            ['Cím', 'Típus', 'Besorolás', 'Létrehozva', 'Aktív'],
            [
                'Az emlékhely pontos neve, egyéb neve (ha van)', 'Az emlékhely típusa',
                'Létező vagy eltűnt emlék(hely)', 'Az emlékhely besorolása', 'A település neve magyarul',
                'Utca, házszám, településrész (magyarul és ukránul)', 'GPS (szélességi fok)',
                'GPS (hosszúsági fok)', 'Készítés időpontja', 'Készíttető neve', 'Készítő neve',
                'Készítés céljának ismert oka', 'Felirata (ha van)', 'Mettől meddig volt használatban',
                'Gondozását felügyeli', 'Állapota', 'Kapcsolódó rendezvények, ünnepségek', 'Rövid ismertető',
                'Forrásinformációk', 'Fotókat készítette', 'Galéria', 'Főkép', 'Összállító neve', 'Aktív',
            ],
        ];

        yield from self::lookupCrudProvider();
    }

    /**
     * @return iterable<string, array{string, class-string, list<string>, list<string>}>
     */
    private static function lookupCrudProvider(): iterable
    {
        yield 'article types' => ['/admin/article-type', ArticleTypeCrudController::class, ['Név'], ['Név']];
        yield 'event places' => [
            '/admin/event-places',
            EventPlacesCrudController::class,
            ['Név', 'Cím'],
            ['Név', 'Cím', 'Webcím (URL)'],
        ];
        yield 'monument types' => ['/admin/monument-type', MonumentTypeCrudController::class, ['Cím'], ['Megnevezés']];
        yield 'monument submitters' => [
            '/admin/monument-submitter',
            MonumentSubmitterCrudController::class,
            ['Megnevezés'],
            ['Megnevezés'],
        ];
        yield 'galleries' => [
            '/admin/gallery',
            GalleryCrudController::class,
            ['Név', 'Aktív', 'Kép', 'Képek'],
            ['Név', 'Aktív', 'Bortítókép', 'Képek'],
        ];
        yield 'media' => [
            '/admin/media',
            MediaCrudController::class,
            ['Cím', 'Aktív'],
            ['Cím', 'Típus', 'Aktív', 'Csatolmány'],
        ];
        yield 'slider' => [
            '/admin/slider',
            SliderCrudController::class,
            ['Megnevezés', 'Sorrend', 'Kép', 'Frissítve', 'Aktív'],
            ['Cím', 'Alcím', 'URL', 'Sorrend (egy számmal jelezve)', 'Aktív', 'Kép'],
        ];
        yield 'documents' => [
            '/admin/doc',
            DocCrudController::class,
            ['Cím', 'Létrehozva', 'Módosítva'],
            ['Név', 'Líd', 'Szöveg', 'Aktív', 'Főkép', 'Csatolmány'],
        ];
        yield 'book categories' => [
            '/admin/book-category',
            BookCategoryCrudController::class,
            ['Cím', 'Szülő kategória', 'Aktív'],
            ['Megnevezés', 'Szülő típus', 'Aktív'],
        ];
    }

    /**
     * @param class-string $controller
     * @param list<string> $columns
     * @param list<string> $formLabels
     */
    #[DataProvider('crudProvider')]
    public function testListColumnsAndFormFieldsFollowTheLegacyAdmin(
        string $path,
        string $controller,
        array $columns,
        array $formLabels
    ): void {
        // The list columns, in order (an empty list renders no table header, so the field setup is read).
        $listed = [];
        foreach ((new $controller())->configureFields(Crud::PAGE_INDEX) as $field) {
            $dto = $field->getAsDto();
            if ($dto->isDisplayedOn(Crud::PAGE_INDEX)) {
                $listed[] = $dto->getLabel();
            }
        }
        self::assertSame($columns, $listed, 'List columns of ' . $path);

        $client = $this->adminClient();
        $client->request('GET', $path);
        self::assertResponseIsSuccessful();

        $crawler = $client->request('GET', $path . '/new');
        self::assertResponseIsSuccessful();
        $labels = $crawler->filter('form[name] label, form[name] legend.col-form-label')
            ->each(fn ($label) => trim($label->text()));
        self::assertSame($formLabels, $labels, 'Form fields of ' . $path);
    }

    /**
     * @return iterable<string, array{string, int, array<string, string>}>
     */
    public static function listSettingsProvider(): iterable
    {
        yield 'articles' => ['Article', 15, ['insertDate' => 'DESC']];
        yield 'events' => ['Event', 15, ['dateFrom' => 'DESC']];
        yield 'organizations' => ['Organization', 15, ['updateDate' => 'DESC']];
        yield 'monuments' => ['Monument', 15, []];
        yield 'regions' => ['Regions', 20, ['title' => 'ASC']];
        yield 'cities' => ['Cities', 50, ['regions.title' => 'ASC']];
        yield 'slider' => ['Slider', 15, ['sorting' => 'ASC']];
        yield 'media' => ['Media', 50, []];
        yield 'monument types' => ['MonumentType', 50, []];
        yield 'monument submitters' => ['MonumentSubmitter', 50, []];
        yield 'book categories' => ['BookCategory', 50, []];
        yield 'galleries' => ['Gallery', 15, []];
    }

    /**
     * @param array<string, string> $sort
     */
    #[DataProvider('listSettingsProvider')]
    public function testPageSizeAndDefaultSortFollowTheLegacyAdmin(string $name, int $pageSize, array $sort): void
    {
        $class = 'App\\Controller\\Admin\\' . $name . 'CrudController';

        self::assertSame($pageSize, (new ReflectionClassConstant($class, 'PAGE_SIZE'))->getValue());
        self::assertSame($sort, (new ReflectionClassConstant($class, 'DEFAULT_SORT'))->getValue());
    }

    public function testCityCanBeCreatedAndEditedWithItsTranslation(): void
    {
        $client = $this->adminClient();
        $entityManager = $this->entityManager();
        $region = (new Regions())->setTitle('Teszt régió');
        $entityManager->persist($region);
        $entityManager->flush();

        $crawler = $client->request('GET', '/admin/cities/new');
        $form = $crawler->selectButton('Létrehozás')->form([
            'Cities[regions]' => (string) $region->getId(),
            'Cities[translation][title]' => 'Próbafalva',
            'Cities[translation][lead]' => 'Bevezető',
        ]);
        $client->submit($form);
        self::assertResponseRedirects();

        $entityManager->clear();
        $city = $entityManager->getRepository(Cities::class)->findOneBy([]) ?? self::fail('City was not saved.');
        self::assertSame('Próbafalva', $city->getDefaultTitle());
        self::assertSame('hu', $city->getTranslation()->getLocale());
        self::assertSame('Bevezető', $city->getTranslation()->getLead());
        self::assertSame('probafalva', $city->getTranslation()->getSlug());

        $crawler = $client->request('GET', '/admin/cities/' . $city->getId() . '/edit');
        $client->submit($crawler->selectButton('Mentés')->form(['Cities[translation][title]' => 'Újfalu']));
        self::assertResponseRedirects();

        $listPage = $client->request('GET', '/admin/cities');
        $headers = $listPage->filter('table thead th')->each(fn ($header) => trim($header->text()));
        self::assertSame(
            ['Cím', 'Régió', 'Aktív'],
            array_values(array_filter($headers, fn ($text) => $text !== '' && $text !== 'Műveletek'))
        );

        $entityManager->clear();
        self::assertCount(1, $entityManager->getRepository(Cities::class)->findAll());
        $city = $entityManager->getRepository(Cities::class)->findOneBy([]) ?? self::fail('City is gone.');
        self::assertSame('Újfalu', $city->getDefaultTitle());
        self::assertCount(1, $city->getTranslations(), 'Editing does not add a second translation.');
    }

    public function testArticleSaveWithDatePickerValueAndTypeFilter(): void
    {
        $client = $this->adminClient();
        $entityManager = $this->entityManager();
        $type = new ArticleType();
        $type->getTranslation()->setName('Hírek');
        $otherType = new ArticleType();
        $otherType->getTranslation()->setName('Egyebek');
        $entityManager->persist($type);
        $entityManager->persist($otherType);
        $entityManager->flush();

        $crawler = $client->request('GET', '/admin/article/new');
        self::assertCount(1, $crawler->filter('input.datetimepicker'), 'The date fields use the date picker.');
        $form = $crawler->selectButton('Létrehozás')->form([
            'Article[translation][title]' => 'Első cikk',
            'Article[articleType]' => (string) $type->getId(),
            'Article[pubDate]' => '2026-05-04 10:30',
        ]);
        $client->submit($form);
        self::assertResponseRedirects();

        $entityManager->clear();
        $article = $entityManager->getRepository(Article::class)->findOneBy([]) ?? self::fail('Article not saved.');
        self::assertSame('2026-05-04 10:30', $article->getPubDate()->format('Y-m-d H:i'));
        self::assertSame('Első cikk', $article->getDefaultTitle());
        self::assertNotNull($article->getInsertDate());

        $listedTitles = fn (ArticleType $articleType) => $client->request('GET', '/admin/article', [
            'filters' => ['articleType' => ['comparison' => '=', 'value' => (string) $articleType->getId()]],
        ])->filter('table')->text();
        self::assertStringContainsString('Első cikk', $listedTitles($type));
        self::assertStringNotContainsString('Első cikk', $listedTitles($otherType));
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
