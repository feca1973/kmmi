<?php

namespace App\Tests\Imagine;

use App\Imagine\ZoomCropFilterLoader;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ZoomCropFilterLoaderTest extends TestCase
{
    /** The red corner of the test image, as a share of its width and of its height. */
    private const RED_WIDTH = 0.04;
    private const RED_HEIGHT = 0.12;

    /**
     * @return iterable<string, array{int, int, int, int}> the size of the source, the size of the result
     */
    public static function sizes(): iterable
    {
        yield 'slider, a photo' => [3000, 2000, 1920, 600];
        yield 'slider, a panorama' => [4000, 1000, 1920, 600];
        yield 'slider, a small image' => [400, 300, 1920, 600];
        yield 'slider, the same proportions' => [3840, 1200, 1920, 600];
        yield 'gallery sidebar' => [3000, 2000, 300, 225];
        yield 'gallery list, a landscape' => [3000, 2000, 450, 450];
        yield 'gallery list, a portrait' => [1000, 3000, 450, 450];
    }

    #[DataProvider('sizes')]
    public function testTheImageCoversTheAreaAndKeepsItsTopLeftCorner(
        int $sourceWidth,
        int $sourceHeight,
        int $width,
        int $height,
    ): void {
        $result = (new ZoomCropFilterLoader())->load(
            $this->imageWithRedCorner($sourceWidth, $sourceHeight),
            ['size' => [$width, $height]],
        );

        self::assertSame([$width, $height], [$result->getSize()->getWidth(), $result->getSize()->getHeight()]);

        // The red corner has the height of the source, scaled until the image covers the area.
        $scale = max($width / $sourceWidth, $height / $sourceHeight);
        $redRows = (int) floor(self::RED_HEIGHT * $sourceHeight * $scale);
        self::assertTrue($this->isRed($result, 2, 2), 'The top left corner is the red corner of the source.');
        self::assertTrue($this->isRed($result, 2, $redRows - 6), 'The red corner is as high as the scale says.');
        self::assertFalse($this->isRed($result, 2, $redRows + 6), 'Under the red corner the source is blue.');
        self::assertFalse($this->isRed($result, $width - 3, $height - 3), 'The bottom right corner is blue.');
    }

    public function testTheSizeIsRequired(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ZoomCropFilterLoader())->load($this->imageWithRedCorner(100, 100));
    }

    private function imageWithRedCorner(int $width, int $height): ImageInterface
    {
        $palette = new RGB();
        $image = (new Imagine())->create(new Box($width, $height), $palette->color('#0000ff'));
        $image->draw()->rectangle(
            new Point(0, 0),
            new Point((int) ($width * self::RED_WIDTH), (int) ($height * self::RED_HEIGHT)),
            $palette->color('#ff0000'),
            true,
        );

        return $image;
    }

    private function isRed(ImageInterface $image, int $column, int $row): bool
    {
        $color = $image->getColorAt(new Point($column, $row));

        return $color->getValue(ColorInterface::COLOR_RED) - $color->getValue(ColorInterface::COLOR_BLUE) > 100;
    }
}
