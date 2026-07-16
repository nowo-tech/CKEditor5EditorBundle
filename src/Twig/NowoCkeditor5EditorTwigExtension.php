<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig helpers for loading the compiled CKEditor 5 widget script from Resources/public after assets:install.
 *
 * @author Héctor Franco Aceituno <hectorfranco@nowo.tech>
 */
final class NowoCkeditor5EditorTwigExtension extends AbstractExtension
{
    /** Symfony asset package registered by {@see NowoCkeditor5EditorExtension::prepend()}. */
    public const ASSET_PACKAGE = 'nowo_ckeditor5_editor';

    private const DEFAULT_SCRIPT = 'ckeditor5-editor.js';

    private const SAFE_FILENAME_PATTERN = '#^[a-zA-Z0-9._/-]+$#';

    /**
     * @return list<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('nowo_ckeditor5_editor_asset_path', $this->assetPath(...), ['is_safe' => ['html']]),
            new TwigFunction('nowo_ckeditor5_editor_asset_package', static fn (): string => self::ASSET_PACKAGE),
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
}
