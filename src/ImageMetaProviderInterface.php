<?php

declare(strict_types=1);

namespace Survos\ImageContracts;

/**
 * Perceptual metadata for a source image: dimensions, format, and the cheap placeholders
 * (thumbhash, blurhash, average colour) a page paints before the real image arrives.
 *
 * Separate from {@see ImageUrlBuilderInterface} because it costs an HTTP round trip and a
 * backend feature: imgproxy answers it from its Pro /info endpoint, imagor from meta/. Anything
 * beyond this common subset (palettes, perceptual hashes, classification, object detection) stays
 * on the backend's own DTO.
 */
interface ImageMetaProviderInterface
{
    /**
     * @throws \RuntimeException no HTTP client, not configured, or the backend request failed
     */
    public function imageMeta(string $source): ImageMeta;
}
