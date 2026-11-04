<?php

namespace App\Tests\Imagine;

use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\Binary;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The filter sets of config/packages/liip_imagine.yaml that cut the image to a size, with the real configuration: the
 * live site cuts them from the top left corner of the image (ZoomCropFilterLoader).
 */
final class ImageFilterSetsTest extends KernelTestCase
{
    /**
     * @return iterable<string, array{string, int, int}> the filter set, the size of the result
     */
    public static function croppedSets(): iterable
    {
        yield 'slider' => ['slider', 1920, 600];
        yield 'gallery sidebar' => ['gallery300', 300, 225];
        yield 'gallery list' => ['gallery450', 450, 450];
    }

    #[DataProvider('croppedSets')]
    public function testTheResultIsTheTopLeftPartOfTheImage(string $filterSet, int $width, int $height): void
    {
        $manager = self::getContainer()->get('liip_imagine.filter.manager');
        self::assertInstanceOf(FilterManager::class, $manager);

        $result = $manager->applyFilter(new Binary($this->photoWithRedCorner(), 'image/png', 'png'), $filterSet);

        $image = imagecreatefromstring($result->getContent());
        self::assertNotFalse($image);
        self::assertSame([$width, $height], [imagesx($image), imagesy($image)]);
        self::assertTrue($this->isRed($image, 2, 2), 'The top left corner is the red corner of the photo.');
        self::assertFalse($this->isRed($image, $width - 3, $height - 3), 'The bottom right corner is blue.');
    }

    /**
     * A 3000 x 2000 photo, blue, with a red corner of 120 x 240 pixels (its center is blue, so a crop around the
     * center does not show the red corner).
     */
    private function photoWithRedCorner(): string
    {
        $photo = imagecreatetruecolor(3000, 2000);
        imagefill($photo, 0, 0, (int) imagecolorallocate($photo, 0, 0, 255));
        imagefilledrectangle($photo, 0, 0, 119, 239, (int) imagecolorallocate($photo, 255, 0, 0));

        ob_start();
        imagepng($photo);

        return (string) ob_get_clean();
    }

    private function isRed(\GdImage $image, int $column, int $row): bool
    {
        $color = imagecolorat($image, $column, $row);

        return (($color >> 16) & 255) - ($color & 255) > 100;
    }
}
