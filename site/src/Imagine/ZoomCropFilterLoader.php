<?php

namespace App\Imagine;

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use InvalidArgumentException;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

/**
 * The "zoom_crop" filter (config/packages/liip_imagine.yaml): the legacy zoomCrop(width, height, '#FFFFFF', 0, 0) of
 * Gregwar Image. The image is scaled, up or down, until it covers the whole area, and the part that does not fit is
 * cut off at the right and at the bottom.
 *
 * The position arguments 0, 0 mean the top left corner on the live site: it runs PHP 7.1, where the switch of
 * Gregwar finds `0 == 'left'` true (PHP 8 would center the image). The "thumbnail" filter of Liip Imagine cuts
 * around the center, which showed another part of the slides and of the gallery covers than the live site does.
 */
class ZoomCropFilterLoader implements LoaderInterface
{
    /**
     * @param array<string, mixed> $options "size": the [width, height] of the result
     */
    public function load(ImageInterface $image, array $options = []): ImageInterface
    {
        [$width, $height] = $this->sizeOf($options);

        $current = $image->getSize();
        $ratio = $current->getWidth() / $current->getHeight();

        // An image that is wider than the area is as high as the area, any other is as wide as the area.
        $cover = $ratio > $width / $height
            ? new Box((int) ($height * $ratio), $height)
            : new Box($width, (int) ($width / $ratio));

        $image->resize($cover);
        $image->crop(new Point(0, 0), new Box($width, $height));

        return $image;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array{int, int}
     */
    private function sizeOf(array $options): array
    {
        $size = $options['size'] ?? null;
        if (is_array($size) && count($size) === 2) {
            [$width, $height] = array_values($size);
            if (is_int($width) && is_int($height) && $width > 0 && $height > 0) {
                return [$width, $height];
            }
        }

        throw new InvalidArgumentException('The size option of the zoom_crop filter is [width, height].');
    }
}
