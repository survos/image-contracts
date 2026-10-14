<?php

declare(strict_types=1);

namespace Survos\ImageContracts;

/**
 * Turns a source image URL into a resized/served one, by preset name.
 *
 * Implemented by survos/imgproxy-bundle (ImgproxyUrlBuilder) and survos/imagor-bundle
 * (ImagorUrlBuilder), so a consumer can type against this and run with either backend — or with
 * neither, by taking it as a nullable dependency and falling back to the raw source URL.
 *
 * Deliberately small: only what both backends answer the same way. imgproxy's /info, classify()
 * and detectObjects() (imgproxy Pro), imagor's crop() and cachedSourceUrl(), and each backend's
 * raw-options build() stay on the concrete builders. Perceptual metadata (thumbhash, blurhash,
 * average colour) is {@see ImageMetaProviderInterface}, a separate opt-in.
 *
 * Preset names are the shared vocabulary in survos/data-contracts' MediaPreset plus whatever the
 * backend's own config adds; {@see getPresets()} says which this instance knows.
 */
interface ImageUrlBuilderInterface
{
    /**
     * URL of $source rendered through the named preset.
     *
     * @param string      $source http(s) URL, or an object-storage URL ({@see supports()})
     * @param string|null $format output format override (e.g. 'png'); a backend that cannot honour
     *                            it throws rather than silently returning the preset's own format
     *
     * @throws \InvalidArgumentException unknown preset, unsupported source, or unsupported $format
     * @throws \RuntimeException         the backend is not configured ({@see isConfigured()})
     */
    public function preset(string $source, string $preset = 'thumb', ?string $format = null): string;

    /**
     * Whether this backend can fetch $source at all (scheme, and for object storage, bucket).
     * A false here means preset() would throw or emit a URL that is certain to fail.
     */
    public function supports(string $source): bool;

    /** Whether enough is configured (host, and secret where the backend requires one) to build URLs. */
    public function isConfigured(): bool;

    public function hasPreset(string $preset): bool;

    /**
     * The presets this builder knows, keyed by name. Values are backend-specific (imgproxy:
     * informational size hints; imagor: the options string) — rely on the keys only.
     *
     * @return array<string, mixed>
     */
    public function getPresets(): array;

    /**
     * Inverse of preset(): the original source URL behind a URL this backend built, or $url
     * unchanged when it isn't one (so any URL can be passed through safely). Needed by callers
     * that hand the image to something that fetches the SOURCE itself (an AI model, an /info
     * call); giving them an already-processed URL double-processes it.
     */
    public function sourceUrl(string $url): string;
}
