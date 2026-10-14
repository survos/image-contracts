<?php

declare(strict_types=1);

namespace Survos\ImageContracts\Test;

use PHPUnit\Framework\TestCase;
use Survos\ImageContracts\ImageUrlBuilderInterface;

/**
 * Contract-conformance tests every ImageUrlBuilderInterface implementation must pass. A backend's
 * test extends this and supplies a configured builder plus one source it can and cannot fetch.
 *
 * Shipped in src/ (like symfony/http-client-contracts' Test\HttpClientTestCase) so each bundle
 * runs the same checks without a dev dependency on the others; it is only autoloaded by tests.
 */
abstract class ImageUrlBuilderTestCase extends TestCase
{
    abstract protected function createBuilder(): ImageUrlBuilderInterface;

    abstract protected function createUnconfiguredBuilder(): ImageUrlBuilderInterface;

    protected function supportedSource(): string
    {
        return 'https://example.com/images/a b.jpg?v=1&w=2';
    }

    abstract protected function unsupportedSource(): string;

    public function testItIsConfigured(): void
    {
        self::assertTrue($this->createBuilder()->isConfigured());
        self::assertFalse($this->createUnconfiguredBuilder()->isConfigured());
    }

    public function testSharedPresetNamesAreKnown(): void
    {
        $builder = $this->createBuilder();
        foreach (['tiny', 'thumb', 'observe', 'display', 'archive'] as $name) {
            self::assertTrue($builder->hasPreset($name), $name);
            self::assertArrayHasKey($name, $builder->getPresets());
        }
        self::assertFalse($builder->hasPreset('no-such-preset'));
    }

    public function testPresetBuildsADistinctUrlPerPreset(): void
    {
        $builder = $this->createBuilder();
        $source = $this->supportedSource();
        $thumb = $builder->preset($source);

        self::assertSame($thumb, $builder->preset($source, 'thumb'), "'thumb' is the default preset");
        self::assertNotSame($source, $thumb);
        self::assertNotSame($thumb, $builder->preset($source, 'tiny'));
        self::assertStringStartsWith('http', $thumb);
    }

    public function testUnknownPresetThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->createBuilder()->preset($this->supportedSource(), 'no-such-preset');
    }

    public function testUnconfiguredBuilderThrowsInsteadOfEmittingABrokenUrl(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->createUnconfiguredBuilder()->preset($this->supportedSource());
    }

    public function testSupports(): void
    {
        $builder = $this->createBuilder();
        self::assertTrue($builder->supports($this->supportedSource()));
        self::assertTrue($builder->supports('http://example.com/a.png'));
        self::assertFalse($builder->supports($this->unsupportedSource()));
        self::assertFalse($builder->supports('data:image/png;base64,AAAA'));
    }

    public function testSourceUrlInvertsPreset(): void
    {
        $builder = $this->createBuilder();
        $source = $this->supportedSource();

        self::assertSame($source, $builder->sourceUrl($builder->preset($source, 'display')));
        self::assertSame($source, $builder->sourceUrl($source), 'a non-backend URL passes through');
        self::assertSame('not a url', $builder->sourceUrl('not a url'));
    }
}
