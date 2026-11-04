<?php

namespace App\Tests\Controller\Admin;

use App\Tests\Support\AdminClientTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The rich text fields are CKEditor text areas that know the license key and the file manager URLs.
 */
final class RichTextFieldTest extends WebTestCase
{
    use AdminClientTrait;
    use ResetDatabase;

    public function testArticleTextsAreCkeditorFields(): void
    {
        $client = $this->adminClient();

        $crawler = $client->request('GET', '/admin/article/new');

        $editors = $crawler->filter('textarea[data-ckeditor]');
        self::assertCount(2, $editors, 'The lead and the content are rich text fields.');
        $editor = $editors->first();
        self::assertSame('test-license-key', $editor->attr('data-license-key'));
        self::assertSame('/admin/elfinder/images', $editor->attr('data-images-url'));
        self::assertSame('/admin/elfinder/attachments', $editor->attr('data-files-url'));
        self::assertStringStartsWith('/admin/efupload/images', (string) $editor->attr('data-upload-url'));
        self::assertStringContainsString('response_format=ckeditor5', (string) $editor->attr('data-upload-url'));

        $chooser = $crawler->filter('input[data-file-chooser]')->first();
        self::assertSame('/admin/elfinder/images', $chooser->attr('data-file-chooser'));
    }
}
