<?php

namespace App\Tests\Support;

use App\Entity\Article\ActorType;
use App\Entity\Article\Article;
use App\Entity\Article\ArticleTranslation;
use App\Entity\Article\ArticleType;
use App\Entity\Article\ArticleTypeTranslation;
use App\Entity\Article\BookCategory;
use App\Entity\Article\EducationType;
use App\Entity\Attachment;
use App\Entity\Doc\Doc;
use App\Entity\Doc\DocTranslation;
use App\Entity\Event\EventPlaces;
use App\Entity\Event\Events;
use App\Entity\Event\EventsTranslation;
use App\Entity\Event\EventType;
use App\Entity\Gallery\Gallery;
use App\Entity\Gallery\GalleryImage;
use App\Entity\Media;
use App\Entity\Menu\Menu;
use App\Entity\Menu\MenuItem;
use App\Entity\Monuments\Monument;
use App\Entity\Monuments\MonumentExisting;
use App\Entity\Monuments\MonumentLocalization;
use App\Entity\Monuments\MonumentSubmitter;
use App\Entity\Monuments\MonumentType;
use App\Entity\Organization\Organization;
use App\Entity\Organization\OrganizationTranslation;
use App\Entity\Organization\OrganizationType;
use App\Entity\Region\Cities;
use App\Entity\Region\CitiesTranslation;
use App\Entity\Region\Regions;
use App\Entity\Slider\Slider;
use App\Entity\Slider\SliderTranslation;
use App\Entity\Unique\UniqueGroup;
use App\Entity\Unique\UniqueItem;
use App\Enum\MediaType;
use App\Enum\UniqueType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use ReflectionProperty;

/**
 * A small public site: one item of everything the public pages show. The site code knows some rows by their ids
 * (the menu 2, the article types 6 news, 10 folk art and 12 churches), so those are saved with their live ids.
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
final class PublicSiteFixtures
{
    private const NEWS_TYPE_ID = 6;
    private const FOLK_TYPE_ID = 10;
    private const CHURCHES_TYPE_ID = 12;
    private const MAIN_MENU_ID = 2;

    /**
     * @var array<string, string>
     */
    private array $urls = [];

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Saves the site and returns the paths of its pages by their names.
     *
     * @return array<string, string>
     */
    public function create(): array
    {
        $this->createSiteTexts();
        $this->createMenu();
        $gallery = $this->createGallery();
        $this->createSlider();
        $this->createArticles($gallery);
        $city = $this->createCity();
        $this->createEvent($city);
        $this->createMonument($gallery);
        $this->createOrganization($city, $gallery);
        $this->createDocuments();
        $this->createLibrary();
        $this->createDataPages($city, $gallery);
        $this->createMedia();
        $this->entityManager->flush();

        $this->urls += [
            'homepage' => '/',
            'gallery' => '/galeria/' . $gallery->getSlug(),
            'gallery list' => '/kepek',
            'news list' => '/cikkek/hirek',
            'churches' => '/egyhazak',
            'folk art' => '/nepmuveszet',
            'events' => '/rendezvenyek',
            'monuments' => '/emlekhelyek',
            'cities' => '/regiok/telepulesek',
            'organizations' => '/szervezetek',
            'about us' => '/informacio/rolunk',
            'department' => '/informacio/kozgyujtemenyi-reszleg',
            'partners' => '/informacio/partnereink',
            'books' => '/konyvtar/konyvek',
            'periodicals' => '/konyvtar/folyoiratok/egyutt',
            'book' => '/konyvtar/konyvek/reszletes/egy-konyv',
            'periodical issue' => '/konyvtar/egyutt/reszletes/egy-szam',
            'artists' => '/arckepcsarnok/muveszek',
            'artist' => '/arckepcsarnok/muveszek/egy-muvesz',
            'kindergartens' => '/oktatasi-intezmenyek/ovodak',
            'audio list' => '/hangtar',
            'audio' => '/hangtar/egy-hang',
            'video list' => '/videotar',
            'video' => '/videotar/egy-video',
        ];

        return $this->urls;
    }

    /**
     * One more published news article (call after create(); it is persisted, not flushed).
     */
    public function addNews(string $title, string $content = '', string $published = '-1 day'): Article
    {
        $article = (new Article())
            ->setArticleType($this->entityManager->find(ArticleType::class, self::NEWS_TYPE_ID))
            ->setPubDate(new DateTime($published))
            ->setActive(true);
        $translation = new ArticleTranslation();
        $translation->setTitle($title)->setContent($content);
        $article->addTranslation($translation);
        $this->entityManager->persist($article);

        return $article;
    }

    private function createSiteTexts(): void
    {
        $group = (new UniqueGroup())->setTitle('Oldal');
        $texts = [
            'page-title' => 'KMMI | Kárpátaljai Magyar Művelődési Intézet',
            'cegnev' => 'Kárpátaljai Magyar Művelődési Intézet',
            'telefonszam' => '+380 00 000 00 00',
            'email' => 'iroda@example.test',
            'page-keywords' => 'Kárpátalja, kultúra',
            'logo' => '/uploads/images/palyazatok/KMMI-Logo.png',
            'bga-logo' => '/uploads/images/sliders/Tamogatok-2024.jpg',
            'bga-logo-link' => 'https://example.test/',
        ];
        $this->entityManager->persist($group);
        foreach ($texts as $slug => $content) {
            $this->entityManager->persist(
                (new UniqueItem())
                    ->setTitle($slug)
                    ->setSlug($slug)
                    ->setContent($content)
                    ->setType(UniqueType::String)
                    ->setUniqueGroup($group)
            );
        }
    }

    private function createMenu(): void
    {
        $menu = (new Menu())->setTitle('Főmenu - HU');
        $this->persistWithId($menu, self::MAIN_MENU_ID);
        // The public menu shows the top level; "Adattár" has a tree of sub items (Könyvtár has one too).
        $library = $this->menuItem('Könyvtár', '/konyvtar/konyvek')
            ->addChild($this->menuItem('Folyóiratok', '/konyvtar/folyoiratok/egyutt'));
        $data = $this->menuItem('Adattár', '#')->addChild($library)->addChild($this->menuItem('Képtár', '/kepek'));
        $roots = [$this->menuItem('Hírek', '/cikkek/hirek'), $data, $this->menuItem('Rendezvények', '/rendezvenyek')];
        foreach ($roots as $item) {
            $this->entityManager->persist($item->setMenu($menu));
        }
    }

    private function menuItem(string $title, string $url): MenuItem
    {
        return (new MenuItem())->setTitle($title)->setConnectedType(MenuItem::CUSTOM_URL)->setConnectedValue($url);
    }

    private function createSlider(): void
    {
        $slider = (new Slider())->setImage('/uploads/images/sliders/kolcsey.jpg')->setSorting(1)->setActive(true);
        $translation = new SliderTranslation();
        $translation->setTitle('Kölcsey miniszobor');
        $slider->addTranslation($translation);
        $this->entityManager->persist($slider);
    }

    private function createGallery(): Gallery
    {
        $gallery = (new Gallery())->setName('Próba galéria')->setCoverImage('/uploads/images/gallery/a/1.jpg');
        $gallery->setActive(true);
        foreach ([1, 2] as $position) {
            $gallery->addGalleryImage(
                (new GalleryImage())
                    ->setName('Kép ' . $position)
                    ->setPath('/uploads/images/gallery/a/' . $position . '%20b.jpg')
                    ->setPosition($position)
            );
        }
        $this->entityManager->persist($gallery);

        return $gallery;
    }

    private function createArticles(Gallery $gallery): void
    {
        $types = [
            self::NEWS_TYPE_ID => 'Hírek',
            self::FOLK_TYPE_ID => 'Népművészet',
            self::CHURCHES_TYPE_ID => 'Egyházak',
        ];
        foreach ($types as $id => $name) {
            $type = new ArticleType();
            $typeTranslation = new ArticleTypeTranslation();
            $typeTranslation->setName($name);
            $type->addTranslation($typeTranslation);
            $this->persistWithId($type, $id);

            $article = (new Article())
                ->setArticleType($type)
                ->setPubDate(new DateTime('-2 days'))
                ->setMainImage('/uploads/images/hirek/kep%20egy.jpg')
                ->setActive(true);
            $translation = new ArticleTranslation();
            $translation->setTitle($name . ' &#8211; cikk')
                ->setLead('<p>Bevezető szöveg.</p>')
                ->setContent('<p>A cikk szövege.</p>')
                ->setAuthorName('Szerző')
                ->setAuthorSource('Forrás');
            $article->addTranslation($translation);
            if ($id === self::NEWS_TYPE_ID) {
                $article->setGallery($gallery);
                $article->addAttachment(
                    (new Attachment())->setName('Melléklet')->setPath('uploads/attachments/melleklet.pdf')
                );
            }
            $this->entityManager->persist($article);
            $this->entityManager->flush();
            $this->addArticleUrls($id, $type, $article);
        }
    }

    private function addArticleUrls(int $typeId, ArticleType $type, Article $article): void
    {
        if ($typeId === self::NEWS_TYPE_ID) {
            $this->urls['article'] = '/cikkek/' . $type->getSlug() . '/' . $article->getSlug();
        }
        if ($typeId === self::FOLK_TYPE_ID) {
            $this->urls['folk art article'] = '/nepmuveszet/' . $article->getSlug();
        }
    }

    private function createCity(): Cities
    {
        $region = new Regions();
        $region->setTitle('Beregszászi járás')->setSlug('beregszaszi-jaras');
        $city = (new Cities())->setRegions($region)->setImage('/uploads/images/telepules.jpg');
        $city->setActive(true);
        $translation = new CitiesTranslation();
        $translation->setTitle('Beregszász')->setSlug('beregszasz')->setLead('<p>Város.</p>')
            ->setContent('<p>A város leírása.</p>');
        $city->addTranslation($translation);
        $this->entityManager->persist($region);
        $this->entityManager->persist($city);
        $this->entityManager->flush();
        $this->urls['city'] = '/regiok/telepulesek/beregszaszi-jaras/beregszasz';

        return $city;
    }

    private function createEvent(Cities $city): void
    {
        $type = new EventType();
        $type->setName('Ünnep, ünnepség')->setSlug('unnep-unnepseg');
        $place = (new EventPlaces())->setName('Kultúrház');
        $event = (new Events())
            ->setType($type)
            ->setCity($city)
            ->setPlace($place)
            ->setCountryId('UA')
            ->setDateFrom(new DateTime('2024-03-05 10:00'))
            ->setDateTo(new DateTime('2024-03-07 18:00'))
            ->setImage('/uploads/images/esemeny.jpg');
        $event->setActive(true);
        $translation = new EventsTranslation();
        $translation->setTitle('Magyar kultúra napja')->setSlug('magyar-kultura-napja')
            ->setLead('<p>Rendezvény.</p>')->setContent('<p>A rendezvény leírása.</p>')->setPlaceName('Beregszász');
        $event->addTranslation($translation);
        foreach ([$type, $place, $event] as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->urls['event type'] = '/rendezvenyek/unnep-unnepseg';
        $this->urls['event'] = '/rendezvenyek/unnep-unnepseg/magyar-kultura-napja';
    }

    private function createMonument(Gallery $gallery): void
    {
        $type = new MonumentType();
        $type->setName('Szobor')->setSlug('szobor');
        $existing = new MonumentExisting();
        $existing->setName('Létező')->setSlug('letezo');
        $localization = new MonumentLocalization();
        $localization->setName('Nyilvános')->setSlug('nyilvanos');
        $submitter = new MonumentSubmitter();
        $submitter->setName('Önkormányzat')->setSlug('onkormanyzat');
        $monument = (new Monument())
            ->setType($type)
            ->setExisting($existing)
            ->setLocalization($localization)
            ->setSubmitter($submitter)
            ->setName('Kossuth-szobor')
            ->setSlug('kossuth-szobor')
            ->setPlaceCity('Tecső')
            ->setCreatorName('Szobrász')
            ->setMomumentCondition('Jó')
            ->setLongDescription('<p>Az emlékmű leírása.</p>')
            ->setCompiledBy('Összeállító')
            ->setGallery($gallery)
            ->setImage('/uploads/images/emlekhely.jpg');
        $monument->setActive(true);
        foreach ([$type, $existing, $localization, $submitter, $monument] as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->urls['monument'] = '/emlekhelyek/szobor/kossuth-szobor';
    }

    private function createOrganization(Cities $city, Gallery $gallery): void
    {
        $type = new OrganizationType();
        $type->setName('Egyesület')->setSlug('egyesulet');
        $organization = (new Organization())->setType($type)->setCity($city)->setGallery($gallery)
            ->setInstitutionYear('1990')->setContactUrl('example.test');
        $organization->setActive(true);
        $translation = new OrganizationTranslation();
        $translation->setTitle('Próba egyesület')->setSlug('proba-egyesulet')
            ->setFieldOfActivityId('<p>Működési terület.</p>')->setIntroduction('<p>Bemutatkozás.</p>');
        $organization->addTranslation($translation);
        $this->entityManager->persist($type);
        $this->entityManager->persist($organization);
        $this->urls['organization'] = '/szervezetek/proba-egyesulet';
    }

    private function createDocuments(): void
    {
        // The "About us" page is the document 1, the departments and the contacts are the documents 2-5.
        foreach ([[1, 'Rólunk'], [2, 'Közgyűjteményi részleg'], [6, 'Partnereink']] as [$id, $title]) {
            $doc = new Doc();
            $doc->setActive(true);
            $translation = new DocTranslation();
            $translation->setTitle($title)->setLead('<p>Bevezető.</p>')->setText('<p>Az oldal szövege.</p>');
            $doc->addTranslation($translation);
            $this->persistWithId($doc, $id);
        }
    }

    private function createLibrary(): void
    {
        $books = new BookCategory();
        $books->setName('Könyvek');
        $periodicals = new BookCategory();
        $periodicals->setName('Folyóiratok');
        $together = new BookCategory();
        $together->setName('Együtt');
        $together->setParent($periodicals);
        foreach ([[$books, 'Egy könyv'], [$together, 'Egy szám']] as [$category, $title]) {
            $book = $this->article($title)->setBookCategory($category)->setBookReleaseYear('2019');
            $this->entityManager->persist($category);
            $this->entityManager->persist($book);
        }
        $this->entityManager->persist($periodicals);
    }

    private function createDataPages(Cities $city, Gallery $gallery): void
    {
        $artists = new ActorType();
        $artists->setName('Művészek');
        $artist = $this->article('Egy művész')->setActorType($artists)->setCity($city)->setGallery($gallery);

        $kindergartens = new EducationType();
        $kindergartens->setName('Óvodák');
        $kindergarten = $this->article('Egy óvoda')->setEducationType($kindergartens);

        foreach ([$artists, $artist, $kindergartens, $kindergarten] as $entity) {
            $this->entityManager->persist($entity);
        }
    }

    private function createMedia(): void
    {
        $audio = (new Media())->setType(MediaType::Audio)->setPath('/uploads/attachments/audio/a.mp3')
            ->setLead('<p>Hangfelvétel.</p>');
        $audio->setTitle('Egy hang');
        $audio->setActive(true);
        $video = (new Media())->setType(MediaType::Video)->setPath('/uploads/attachments/video/a.flv')
            ->setLead('<p>Videó.</p>');
        $video->setTitle('Egy videó');
        $video->setActive(true);
        $this->entityManager->persist($audio);
        $this->entityManager->persist($video);
    }

    /**
     * A published, active article without a type (the data pages have their own type).
     */
    private function article(string $title): Article
    {
        $article = (new Article())->setPubDate(new DateTime('-3 days'))->setActive(true);
        $translation = new ArticleTranslation();
        $translation->setTitle($title)->setLead('<p>Bevezető.</p>')->setContent('<p>Szöveg.</p>')
            ->setBookAuthor('Szerző Sz.');
        $article->addTranslation($translation);

        return $article;
    }

    /**
     * Saves the entity with the given id instead of a generated one.
     */
    private function persistWithId(object $entity, int $id): void
    {
        $metadata = $this->entityManager->getClassMetadata($entity::class);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());
        (new ReflectionProperty($entity, 'id'))->setValue($entity, $id);
        $this->entityManager->persist($entity);
    }
}
