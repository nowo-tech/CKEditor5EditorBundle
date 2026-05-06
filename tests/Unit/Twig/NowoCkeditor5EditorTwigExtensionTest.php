<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Unit\Twig;

use Nowo\Ckeditor5EditorBundle\Twig\NowoCkeditor5EditorTwigExtension;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nowo\Ckeditor5EditorBundle\Twig\NowoCkeditor5EditorTwigExtension
 */
final class NowoCkeditor5EditorTwigExtensionTest extends TestCase
{
    public function testGetFunctionsReturnsAssetPathFunction(): void
    {
        $ext = new NowoCkeditor5EditorTwigExtension();
        $fns = $ext->getFunctions();

        self::assertCount(1, $fns);
        self::assertSame('nowo_ckeditor5_editor_asset_path', $fns[0]->getName());
    }

    public function testAssetPathReturnsPathWithAssetDir(): void
    {
        $ext = new NowoCkeditor5EditorTwigExtension();

        self::assertSame('bundles/nowockeditor5editor/ckeditor5-editor.js', $ext->assetPath('ckeditor5-editor.js'));
        self::assertSame('bundles/nowockeditor5editor/ckeditor5-editor.js', $ext->assetPath('/ckeditor5-editor.js'));
    }

    public function testAssetPathRejectsPathTraversal(): void
    {
        $ext     = new NowoCkeditor5EditorTwigExtension();
        $default = 'bundles/' . NowoCkeditor5EditorTwigExtension::ASSET_DIR . '/ckeditor5-editor.js';
        self::assertSame($default, $ext->assetPath('../other/file.js'));
    }

    public function testAssetPathRejectsInvalidCharacters(): void
    {
        $ext     = new NowoCkeditor5EditorTwigExtension();
        $default = 'bundles/' . NowoCkeditor5EditorTwigExtension::ASSET_DIR . '/ckeditor5-editor.js';
        self::assertSame($default, $ext->assetPath('bad<script>.js'));
        self::assertSame($default, $ext->assetPath(''));
    }

    public function testAssetPathAllowsSubpath(): void
    {
        $ext = new NowoCkeditor5EditorTwigExtension();
        self::assertSame('bundles/nowockeditor5editor/css/theme.css', $ext->assetPath('css/theme.css'));
    }
}
