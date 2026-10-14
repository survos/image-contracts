# survos/image-contracts

One contract for "give me this image at preset X", so `survos/imgproxy-bundle` and
`survos/imagor-bundle` are interchangeable and consumers never name a concrete builder.

No framework, no bundle dependency: a bundle that only *optionally* renders through an image
backend requires this package and types its dependency as `?ImageUrlBuilderInterface`.

## Interfaces

| Interface | What | Implemented by |
|---|---|---|
| `ImageUrlBuilderInterface` | `preset($source, $preset = 'thumb', ?$format)`, `supports($source)`, `isConfigured()`, `hasPreset()`, `getPresets()`, `sourceUrl($url)` | `ImgproxyUrlBuilder`, `ImagorUrlBuilder` |
| `ImageMetaProviderInterface` | `imageMeta($source): ImageMeta` — width, height, format, thumbhash, blurhash, average colour | both (imgproxy needs Pro's `/info`) |

Deliberately left off: imgproxy's `info()`/`classify()`/`detectObjects()` (Pro), imagor's
`crop()`/`cachedSourceUrl()`, and each backend's raw-options `build()`/`buildUrl()`. Use the
concrete builder when you need those.

Preset names are the shared `MediaPreset` vocabulary (`tiny`, `thumb`, `observe`, `display`,
`archive`) plus whatever the backend's config adds.

## Usage

```php
use Survos\ImageContracts\ImageUrlBuilderInterface;

final class Thumbnails
{
    public function __construct(private readonly ?ImageUrlBuilderInterface $images = null) {}

    public function thumb(string $source): string
    {
        return null !== $this->images && $this->images->supports($source)
            ? $this->images->preset($source, 'thumb')
            : $source; // no backend installed: the source URL still renders
    }
}
```

Each bundle aliases both interfaces to its builder. With **both** bundles installed, the later
one in `bundles.php` wins; pin the choice in the app:

```yaml
# config/services.yaml
services:
    Survos\ImageContracts\ImageUrlBuilderInterface: '@Survos\ImagorBundle\Service\ImagorUrlBuilder'
```

The backend-specific names stay as aliases: `ImgproxyUrlBuilder::resizePreset()` and the
`|imgproxy` / `|imagor` Twig filters.

## Testing an implementation

`Survos\ImageContracts\Test\ImageUrlBuilderTestCase` is the conformance suite; a backend's test
extends it and supplies a configured builder, an unconfigured one, and a source it can't fetch.
