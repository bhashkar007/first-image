<?php
/**
 * First Image plugin for Craft CMS 5.x
 *
 * @link      https://uxi360.com
 */

namespace uxi360\firstimage\twigextensions;

use Craft;
use craft\elements\Asset;
use craft\helpers\Db;
use Throwable;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use uxi360\firstimage\helpers\ImageFinder;

/**
 * @author    UXI360 Team
 * @since     3.0.0
 */
class FirstImageTwigExtension extends AbstractExtension
{
    /**
     * Matches a whole asset reference tag, e.g. `{asset:123:url||https://example.com/a.jpg}`.
     */
    private const ASSET_REF_PATTERN = '~^\{asset:(\d+)(?:@(\d+))?(?::[^|}]*)?(?:\|\|.*)?\}$~s';

    /**
     * @var array<string, string|null> Results for this request
     */
    private array $cache = [];

    /**
     * @inheritdoc
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('getFirstImage', [$this, 'getFirstImage']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getFirstImage', [$this, 'getFirstImage']),
        ];
    }

    /**
     * Returns the URL of the first image found in the given content.
     *
     * @param mixed $content   A Redactor / CKEditor field value, or any HTML string
     * @param mixed $transform A named transform handle, a transform definition array,
     *                         or an ImageTransform model - same as `asset.getUrl()`
     */
    public function getFirstImage(mixed $content = null, mixed $transform = null): ?string
    {
        $html = $this->contentToHtml($content);

        if ($html === '') {
            return null;
        }

        if ($transform === '' || $transform === []) {
            $transform = null;
        }

        // Model instances aren't cached; handles and arrays are.
        if (is_object($transform)) {
            return $this->resolve($html, $transform);
        }

        $key = md5($html) . ':' . md5((string)json_encode($transform));

        if (!array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $this->resolve($html, $transform);
        }

        return $this->cache[$key];
    }

    /**
     * Returns the HTML to search in.
     *
     * Redactor and CKEditor field values keep their unparsed content, in which
     * images are stored as asset reference tags. That is preferred, because it
     * tells us exactly which asset an image is.
     */
    private function contentToHtml(mixed $content): string
    {
        if ($content === null) {
            return '';
        }

        if (is_object($content) && method_exists($content, 'getRawContent')) {
            return (string)$content->getRawContent();
        }

        if (is_scalar($content) || $content instanceof \Stringable) {
            return (string)$content;
        }

        return '';
    }

    private function resolve(string $html, mixed $transform): ?string
    {
        $src = ImageFinder::firstSrc($html);

        if ($src === null) {
            return null;
        }

        $url = $src;
        $asset = null;

        if (preg_match(self::ASSET_REF_PATTERN, $src, $match)) {
            // What the field itself would have rendered for this image.
            $url = $this->parseRef($src);

            if ($transform !== null) {
                $siteId = !empty($match[2]) ? (int)$match[2] : null;
                $asset = Craft::$app->getAssets()->getAssetById((int)$match[1], $siteId);
            }
        } elseif ($transform !== null) {
            $asset = $this->findAssetByUrl($src);
        }

        if ($transform === null || $asset === null) {
            return $url;
        }

        try {
            return $asset->getUrl($transform) ?? $url;
        } catch (Throwable $e) {
            // e.g. an unknown transform handle - don't break the page over a thumbnail.
            Craft::warning(
                "Couldn't apply the transform to asset {$asset->id}: {$e->getMessage()}",
                __METHOD__
            );

            return $url;
        }
    }

    /**
     * Resolves a reference tag to a URL, or null if it can't be resolved.
     */
    private function parseRef(string $ref): ?string
    {
        try {
            $url = trim(Craft::$app->getElements()->parseRefs($ref));
        } catch (Throwable) {
            return null;
        }

        if ($url === '' || str_starts_with($url, '{')) {
            return null;
        }

        return $url;
    }

    /**
     * Finds the asset behind a plain image URL, by matching it against the volumes' base URLs.
     */
    private function findAssetByUrl(string $url): ?Asset
    {
        $url = preg_replace('~[?#].*$~s', '', $url) ?? $url;

        if ($url === '' || str_starts_with($url, 'data:')) {
            return null;
        }

        foreach (Craft::$app->getVolumes()->getAllVolumes() as $volume) {
            try {
                $rootUrl = $volume->getRootUrl();
            } catch (Throwable) {
                continue;
            }

            if (!$rootUrl) {
                continue;
            }

            $rootUrl = rtrim($rootUrl, '/') . '/';

            // Absolute, protocol-relative and root-relative forms of the volume URL.
            $prefixes = array_unique(array_filter([
                $rootUrl,
                preg_replace('~^https?:~i', '', $rootUrl),
                parse_url($rootUrl, PHP_URL_PATH) ?: null,
            ]));

            foreach ($prefixes as $prefix) {
                if ($prefix === '/' || !str_starts_with($url, $prefix)) {
                    continue;
                }

                $path = rawurldecode(substr($url, strlen($prefix)));
                $filename = basename($path);
                $folderPath = dirname($path);
                $folderPath = ($folderPath === '.' || $folderPath === '') ? '' : $folderPath . '/';

                if ($filename === '') {
                    continue;
                }

                $assets = Craft::$app->getAssets();
                $folder = $folderPath === ''
                    ? $assets->getRootFolderByVolumeId($volume->id)
                    : $assets->findFolder(['volumeId' => $volume->id, 'path' => $folderPath]);

                if ($folder === null) {
                    continue;
                }

                $asset = Asset::find()
                    ->folderId($folder->id)
                    ->filename(Db::escapeParam($filename))
                    ->one();

                if ($asset !== null) {
                    return $asset;
                }
            }
        }

        return null;
    }
}
