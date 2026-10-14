<?php

declare(strict_types=1);

namespace Survos\ImageContracts;

/**
 * The metadata every image backend can answer, normalised. {@see ImageMetaProviderInterface}.
 */
final readonly class ImageMeta
{
    /**
     * @param string|null $format       lowercase, as the backend reports it (jpeg, png, webp, …)
     * @param string|null $thumbhash    base64, the form every ThumbHash decoder takes
     * @param string|null $averageColor "#rrggbb"
     */
    public function __construct(
        public ?int $width = null,
        public ?int $height = null,
        public ?string $format = null,
        public ?string $thumbhash = null,
        public ?string $blurhash = null,
        public ?string $averageColor = null,
    ) {
    }

    public function aspectRatio(): ?float
    {
        return $this->width && $this->height ? $this->width / $this->height : null;
    }

    /** @return array<string, int|string> null fields omitted */
    public function toArray(): array
    {
        return array_filter(get_object_vars($this), static fn (mixed $v): bool => null !== $v);
    }
}
