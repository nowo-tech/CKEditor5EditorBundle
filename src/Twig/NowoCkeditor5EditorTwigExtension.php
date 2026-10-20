<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Twig;

use Nowo\Ckeditor5EditorBundle\DependencyInjection\NowoCkeditor5EditorExtension;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use function is_array;
use function is_bool;
use function is_file;
use function is_string;

use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

/**
 * Twig helpers for loading the compiled CKEditor 5 widget script from Resources/public after assets:install.
 *
 * - `nowo_ckeditor5_editor_asset_path()` / `nowo_ckeditor5_editor_asset_package()`: low-level helpers for `asset()`.
 * - `nowo_ckeditor5_editor_asset_url()`: public URL with a content-hash `v=` query (safe for long-lived caching).
 * - `nowo_ckeditor5_editor_script()`: renders the eager build `<script defer>` or the lazy loader that injects
 *   the build only when an editor becomes visible.
 *
 * Stateless (worker-safe): hashes are computed from the shipped files on each call (xxh3, sub-millisecond).
 *
 * @author Héctor Franco Aceituno <hectorfranco@nowo.tech>
 */
final class NowoCkeditor5EditorTwigExtension extends AbstractExtension
{
    /** Symfony asset package registered by {@see NowoCkeditor5EditorExtension::prepend()}. */
    public const ASSET_PACKAGE = 'nowo_ckeditor5_editor';

    /**
     * Public base path of the asset package.
     *
     * @deprecated since 1.10, use {@see NowoCkeditor5EditorExtension::ASSET_BASE_PATH}; URLs always go through the asset package
     */
    public const PUBLIC_BASE_PATH = NowoCkeditor5EditorExtension::ASSET_BASE_PATH . '/';

    /** Full editor build (~1.2 MB). */
    public const BUILD_SCRIPT = 'ckeditor5-editor.js';

    /** Lazy loader (a few KB) that injects {@see self::BUILD_SCRIPT} when an editor root becomes visible. */
    public const LOADER_SCRIPT = 'ckeditor5-editor-loader.js';

    /** Editor CSS as a file (no runtime-injected `<style>`: strict CSP `style-src-elem` stays nonce-only). */
    public const BUILD_STYLESHEET = 'ckeditor5-editor.css';

    private const DEFAULT_SCRIPT = self::BUILD_SCRIPT;

    private const SAFE_FILENAME_PATTERN = '#^[a-zA-Z0-9._/-]+$#';

    private const VERSION_LENGTH = 12;

    private readonly string $publicDir;

    private readonly Packages $packages;

    /**
     * @param Packages|null $packages Symfony asset packages (null when framework assets are disabled)
     * @param bool $lazyLoad Default for the `lazy` option of nowo_ckeditor5_editor_script() (config `lazy_load`)
     * @param string|null $publicDir Directory holding the built assets (defaults to Resources/public)
     */
    public function __construct(
        ?Packages $packages = null,
        private readonly bool $lazyLoad = false,
        ?string $publicDir = null,
    ) {
        // Without framework assets, register the same named package the extension prepends (REQ-ASSETS-004).
        $this->packages = $packages ?? new Packages(null, [
            self::ASSET_PACKAGE => new PathPackage(NowoCkeditor5EditorExtension::ASSET_BASE_PATH, new EmptyVersionStrategy()),
        ]);
        $this->publicDir = rtrim($publicDir ?? __DIR__ . '/../Resources/public', '/');
    }

    /**
     * @return list<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('nowo_ckeditor5_editor_asset_path', $this->assetPath(...), ['is_safe' => ['html']]),
            new TwigFunction('nowo_ckeditor5_editor_asset_package', static fn (): string => self::ASSET_PACKAGE),
            new TwigFunction('nowo_ckeditor5_editor_asset_url', $this->assetUrl(...)),
            new TwigFunction('nowo_ckeditor5_editor_script', $this->script(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * Builds a safe asset path inside the bundle public directory.
     *
     * @param string $filename Relative path (e.g. ckeditor5-editor.js)
     *
     * @return string Relative path for use with asset(..., nowo_ckeditor5_editor_asset_package())
     */
    public function assetPath(string $filename): string
    {
        $filename = ltrim($filename, '/');
        if ($filename === '' || str_contains($filename, '..') || preg_match(self::SAFE_FILENAME_PATTERN, $filename) !== 1) {
            return self::DEFAULT_SCRIPT;
        }

        return $filename;
    }

    /**
     * Public URL of a bundle asset with a content-hash `v` query parameter (cache busting).
     *
     * @param string $filename Relative path (e.g. ckeditor5-editor.js)
     *
     * @return string URL such as /bundles/nowockeditor5editor/ckeditor5-editor.js?v=0123456789ab
     */
    public function assetUrl(string $filename = self::BUILD_SCRIPT): string
    {
        $path = $this->assetPath($filename);
        $url  = $this->packages->getUrl($path, self::ASSET_PACKAGE);

        $version = $this->assetVersion($path);
        if ($version === null) {
            return $url;
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . 'v=' . $version;
    }

    /**
     * Renders the `<script defer>` tag that boots the editors (plus the editor stylesheet).
     *
     * Eager (default): `<link rel="stylesheet">` for {@see self::BUILD_STYLESHEET} + the full build. Lazy: the
     * small loader, which injects the stylesheet (URL in `data-ckeditor5-css`) and the full build (URL in
     * `data-ckeditor5-src`) once an editor root is visible; the loader copies its nonce onto both.
     *
     * Accepts `true`/`false` (lazy) or an options hash: `{lazy: bool, nonce: string|null}`.
     *
     * @param array{lazy?: bool|null, nonce?: string|null}|bool|null $options
     * @param string|null $nonce CSP nonce (also accepted inside $options)
     *
     * @return string HTML
     */
    public function script(bool|array|null $options = null, ?string $nonce = null): string
    {
        $lazy = $this->lazyLoad;
        if (is_bool($options)) {
            $lazy = $options;
        } elseif (is_array($options)) {
            if (isset($options['lazy']) && is_bool($options['lazy'])) {
                $lazy = $options['lazy'];
            }
            if ($nonce === null && isset($options['nonce']) && is_string($options['nonce'])) {
                $nonce = $options['nonce'];
            }
        }

        $nonceAttr = $nonce !== null && $nonce !== '' ? ' nonce="' . $this->escape($nonce) . '"' : '';
        $buildUrl  = $this->escape($this->assetUrl(self::BUILD_SCRIPT));
        $cssUrl    = is_file($this->publicDir . '/' . self::BUILD_STYLESHEET)
            ? $this->escape($this->assetUrl(self::BUILD_STYLESHEET))
            : null;

        if (!$lazy) {
            $link = $cssUrl !== null ? '<link rel="stylesheet" href="' . $cssUrl . '"' . $nonceAttr . '>' : '';

            return $link . '<script src="' . $buildUrl . '" defer' . $nonceAttr . '></script>';
        }

        $cssAttr = $cssUrl !== null ? ' data-ckeditor5-css="' . $cssUrl . '"' : '';

        return '<script src="' . $this->escape($this->assetUrl(self::LOADER_SCRIPT)) . '" defer data-ckeditor5-src="' . $buildUrl . '"' . $cssAttr . $nonceAttr . '></script>';
    }

    private function assetVersion(string $path): ?string
    {
        $file = $this->publicDir . '/' . $path;
        if (!is_file($file)) {
            return null;
        }

        $hash = hash_file('xxh3', $file);

        return $hash === false ? null : substr($hash, 0, self::VERSION_LENGTH);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
