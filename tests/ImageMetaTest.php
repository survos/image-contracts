<?php

declare(strict_types=1);

namespace Survos\ImageContracts\Tests;

use PHPUnit\Framework\TestCase;
use Survos\ImageContracts\ImageMeta;

final class ImageMetaTest extends TestCase
{
    public function testToArrayOmitsUnknownFields(): void
    {
        $meta = new ImageMeta(width: 800, height: 600, format: 'jpeg', averageColor: '#c0c0c0');

        self::assertSame(['width' => 800, 'height' => 600, 'format' => 'jpeg', 'averageColor' => '#c0c0c0'], $meta->toArray());
        self::assertEqualsWithDelta(4 / 3, $meta->aspectRatio(), 1e-9);
        self::assertNull((new ImageMeta())->aspectRatio());
    }
}
