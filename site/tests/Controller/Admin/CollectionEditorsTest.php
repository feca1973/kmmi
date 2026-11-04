<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Article\Article;
use App\Entity\Article\ArticleType;
use App\Entity\Gallery\Gallery;
use App\Entity\Gallery\GalleryImage;
use App\Entity\Unique\UniqueGroup;
use App\Entity\Unique\UniqueItem;
use App\Tests\Support\AdminClientTrait;
use App\Enum\UniqueType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The editors of the gallery images, the attachments and the unique items save what the legacy editors saved.
 */
final class CollectionEditorsTest extends WebTestCase
{
    use AdminClientTrait;
    use ResetDatabase;

    public function testGalleryImagesCanBeEditedSortedAddedAndRemoved(): void
    {
        $client = $this->adminClient();
        $entityManager = $this->entityManager();
        $gallery = (new Gallery())->setName('Nyári tábor');
        foreach (['/uploads/images/a.jpg', '/uploads/images/b.jpg', '/uploads/images/c.jpg'] as $index => $path) {
            $image = (new GalleryImage())->setPath($path)->setName('kép ' . $index)->setPosition($index + 1);
            $gallery->addGalleryImage($image);
        }
        $entityManager->persist($gallery);
        $entityManager->flush();
        $galleryId = $gallery->getId();

        $values = $this->formValues($client, '/admin/gallery/' . $galleryId . '/edit', 'Mentés');
        $images = &$values['Gallery']['galleryImages'];
        $images[0]['position'] = '2';   // the first image moves behind the second
        $images[1]['position'] = '1';
        $images[0]['name'] = 'első, átnevezve';
        unset($images[2]);              // the third image is removed
        $images[7] = ['path' => '/uploads/images/d.jpg', 'name' => 'új kép', 'position' => '3'];
        unset($images);
        $this->save($client, '/admin/gallery/' . $galleryId . '/edit', $values);

        $entityManager->clear();
        $saved = $entityManager->getRepository(Gallery::class)->find($galleryId) ?? self::fail('Gallery is gone.');
        $listed = array_map(
            fn (GalleryImage $image) => [$image->getPath(), $image->getName(), $image->getPosition()],
            $saved->getGalleryImages()->toArray()
        );
        self::assertSame([
            ['/uploads/images/b.jpg', 'kép 1', 1],
            ['/uploads/images/a.jpg', 'első, átnevezve', 2],
            ['/uploads/images/d.jpg', 'új kép', 3],
        ], $listed);
        $count = $entityManager->getRepository(GalleryImage::class)->count([]);
        self::assertSame(3, $count, 'The removed image is deleted.');
    }

    public function testAnAttachmentCanBeAddedToAnArticle(): void
    {
        $client = $this->adminClient();
        $entityManager = $this->entityManager();
        $type = new ArticleType();
        $type->getTranslation()->setName('Hírek');
        $article = (new Article())->setPubDate(new DateTime('2026-01-01 10:00'))->setArticleType($type);
        $article->getTranslation()->setTitle('Cikk csatolmánnyal');
        $entityManager->persist($type);
        $entityManager->persist($article);
        $entityManager->flush();
        $articleId = $article->getId();

        $values = $this->formValues($client, '/admin/article/' . $articleId . '/edit', 'Mentés');
        $values['Article']['attachments'][0] = ['path' => '/uploads/attachments/dok.pdf', 'name' => 'Jegyzőkönyv'];
        $this->save($client, '/admin/article/' . $articleId . '/edit', $values);

        $entityManager->clear();
        $saved = $entityManager->getRepository(Article::class)->find($articleId) ?? self::fail('Article is gone.');
        self::assertCount(1, $saved->getAttachments());
        $attachment = $saved->getAttachments()->first();
        self::assertSame('/uploads/attachments/dok.pdf', $attachment->getPath());
        self::assertSame('Jegyzőkönyv', $attachment->getName());
        self::assertSame($saved, $attachment->getArticle(), 'The attachment belongs to the article.');
    }

    public function testUniqueItemsAreEditedByTheirType(): void
    {
        $client = $this->adminClient();
        $entityManager = $this->entityManager();
        $group = (new UniqueGroup())->setTitle('Elérhetőségek');
        $short = (new UniqueItem())->setTitle('Email')->setType(UniqueType::String)->setContent('a@example.test');
        $long = (new UniqueItem())->setTitle('Cím')->setType(UniqueType::Text)->setContent('Beregszász');
        $group->addUniqueItem($short);
        $group->addUniqueItem($long);
        $entityManager->persist($group);
        $entityManager->flush();
        $groupId = $group->getId();

        $crawler = $client->request('GET', '/admin/unique-group/' . $groupId . '/edit');
        self::assertCount(1, $crawler->filter('input[name$="[content]"]'), 'A short text is a text input.');
        self::assertCount(1, $crawler->filter('textarea[name$="[content]"]'), 'A long text is a text area.');
        self::assertCount(0, $crawler->filter('.field-collection-add-button'), 'No item can be added.');
        self::assertCount(0, $crawler->filter('.field-collection-delete-button'), 'No item can be deleted.');

        $values = $this->formValues($client, '/admin/unique-group/' . $groupId . '/edit', 'Mentés');
        $items = &$values['UniqueGroup']['uniqueItems'];
        $items[0]['content'] = 'b@example.test';
        $items[1]['content'] = 'Beregszász, Európa tér 1.';
        unset($items);
        $this->save($client, '/admin/unique-group/' . $groupId . '/edit', $values);

        $entityManager->clear();
        $contents = array_map(
            fn (UniqueItem $item) => $item->getContent(),
            $entityManager->getRepository(UniqueGroup::class)->find($groupId)->getUniqueItems()->toArray()
        );
        self::assertSame(['b@example.test', 'Beregszász, Európa tér 1.'], array_values($contents));

        $client->request('GET', '/admin/unique-group/new');
        self::assertResponseStatusCodeSame(403, 'Groups are not created in the admin.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formValues(KernelBrowser $client, string $path, string $button): array
    {
        $crawler = $client->request('GET', $path);
        self::assertResponseIsSuccessful();

        return $crawler->selectButton($button)->form()->getPhpValues();
    }

    /**
     * @param array<string, mixed> $values
     */
    private function save(KernelBrowser $client, string $path, array $values): void
    {
        $client->request('POST', $path, $values);
        self::assertResponseRedirects();
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
