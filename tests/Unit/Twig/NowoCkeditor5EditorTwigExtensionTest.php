<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Unit\Twig;

use Nowo\Ckeditor5EditorBundle\Twig\NowoCkeditor5EditorTwigExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

use function dirname;

/**
 * @covers \Nowo\Ckeditor5EditorBundle\Twig\NowoCkeditor5EditorTwigExtension
 */
final class NowoCkeditor5EditorTwigExtensionTest extends TestCase
{
    private const PUBLIC_DIR = __DIR__ . '/../../../src/Resources/public';

    public function testGetFunctionsReturnsAssetHelpers(): void
    {
        $ext = new NowoCkeditor5EditorTwigExtension();
        $fns = $ext->getFunctions();

        self::assertCount(4, $fns);
        self::assertSame('nowo_ckeditor5_editor_asset_path', $fns[0]->getName());
        self::assertSame('nowo_ckeditor5_editor_asset_package', $fns[1]->getName());
        self::assertSame('nowo_ckeditor5_editor_asset_url', $fns[2]->getName());
        self::assertSame('nowo_ckeditor5_editor_script', $fns[3]->getName());
    }

    public function testAssetPathReturnsRelativePath(): void
    {
        $ext = new NowoCkeditor5EditorTwigExtension();

        self::assertSame('ckeditor5-editor.js', $ext->assetPath('ckeditor5-editor.js'));
        self::assertSame('ckeditor5-editor.js', $ext->assetPath('/ckeditor5-editor.js'));
    }

    public function testAssetPathRejectsPathTraversal(): void
    {
        $ext = new NowoCkeditor5EditorTwigExtension();
        self::assertSame('ckeditor5-editor.js', $ext->assetPath('../other/file.js'));
    }

    public function testAssetPathRejectsInvalidCharacters(): void
    {
        $ext = new NowoCkeditor5EditorTwigExtension();
        self::assertSame('ckeditor5-editor.js', $ext->assetPath('bad<script>.js'));
        self::assertSame('ckeditor5-editor.js', $ext->assetPath(''));
    }

    public function testAssetPathAllowsSubpath(): void
    {
        $ext = new NowoCkeditor5EditorTwigExtension();
        self::assertSame('css/theme.css', $ext->assetPath('css/theme.css'));
    }

    public function testAssetUrlWithoutPackagesUsesBundleBasePathAndContentHash(): void
    {
        $ext  = new NowoCkeditor5EditorTwigExtension();
        $hash = substr((string) hash_file('xxh3', self::PUBLIC_DIR . '/ckeditor5-editor.js'), 0, 12);

        self::assertSame('/bundles/nowockeditor5editor/ckeditor5-editor.js?v=' . $hash, $ext->assetUrl());
        self::assertMatchesRegularExpression('#^/bundles/nowockeditor5editor/ckeditor5-editor-loader\.js\?v=[0-9a-f]{12}$#', $ext->assetUrl('ckeditor5-editor-loader.js'));
    }

    public function testAssetUrlWithoutFileHasNoVersion(): void
    {
        $ext = new NowoCkeditor5EditorTwigExtension();

        self::assertSame('/bundles/nowockeditor5editor/missing.js', $ext->assetUrl('missing.js'));
    }

    public function testAssetUrlUsesAssetPackageAndAppendsToExistingQuery(): void
    {
        $packages = new Packages(new Package(new EmptyVersionStrategy()), [
            NowoCkeditor5EditorTwigExtension::ASSET_PACKAGE => new PathPackage('/cdn/ck', new StaticVersionStrategy('r1', '%s?app=%s')),
        ]);
        $ext = new NowoCkeditor5EditorTwigExtension($packages, false, self::PUBLIC_DIR . '/');

        self::assertMatchesRegularExpression('#^/cdn/ck/ckeditor5-editor\.js\?app=r1&v=[0-9a-f]{12}$#', $ext->assetUrl('ckeditor5-editor.js'));
    }

    public function testScriptEagerByDefault(): void
    {
        $ext  = new NowoCkeditor5EditorTwigExtension();
        $html = $ext->script();

        self::assertMatchesRegularExpression('#^<script src="/bundles/nowockeditor5editor/ckeditor5-editor\.js\?v=[0-9a-f]{12}" defer></script>$#', $html);
        self::assertSame($html, $ext->script(false));
        self::assertSame($html, $ext->script(['lazy' => false]));
        self::assertSame($html, $ext->script(['nonce' => '']));
    }

    public function testScriptLazyRendersLoaderWithBuildUrl(): void
    {
        $ext  = new NowoCkeditor5EditorTwigExtension();
        $html = $ext->script(true);

        self::assertMatchesRegularExpression('#^<script src="/bundles/nowockeditor5editor/ckeditor5-editor-loader\.js\?v=[0-9a-f]{12}" defer data-ckeditor5-src="/bundles/nowockeditor5editor/ckeditor5-editor\.js\?v=[0-9a-f]{12}"></script>$#', $html);
        self::assertSame($html, $ext->script(['lazy' => true]));
    }

    public function testScriptUsesConfiguredLazyDefaultUnlessOverridden(): void
    {
        $ext = new NowoCkeditor5EditorTwigExtension(null, true);

        self::assertStringContainsString('ckeditor5-editor-loader.js', $ext->script());
        self::assertStringContainsString('ckeditor5-editor-loader.js', $ext->script(['lazy' => null]));
        self::assertStringNotContainsString('loader', $ext->script(['lazy' => false]));
    }

    public function testScriptNonceIsEscaped(): void
    {
        $ext = new NowoCkeditor5EditorTwigExtension();

        self::assertStringEndsWith(' defer nonce="a&quot;b"></script>', $ext->script(null, 'a"b'));
        self::assertStringEndsWith('" nonce="xyz"></script>', $ext->script(['lazy' => true, 'nonce' => 'xyz']));
        self::assertStringEndsWith(' nonce="arg"></script>', $ext->script(['nonce' => 'opt'], 'arg'));
    }

    public function testScriptRendersRawInTwigWithNamedAndHashArguments(): void
    {
        $twig = new Environment(new ArrayLoader([
            'eager' => '{{ nowo_ckeditor5_editor_script() }}',
            'lazy'  => '{{ nowo_ckeditor5_editor_script({lazy: true, nonce: "n1"}) }}',
            'named' => '{{ nowo_ckeditor5_editor_script(true, nonce="n2") }}',
            'url'   => '{{ nowo_ckeditor5_editor_asset_url("ckeditor5-editor-loader.js") }}',
        ]));
        $twig->addExtension(new NowoCkeditor5EditorTwigExtension(null, false, dirname(__DIR__, 3) . '/src/Resources/public'));

        self::assertStringStartsWith('<script src="/bundles/nowockeditor5editor/ckeditor5-editor.js?v=', $twig->render('eager'));
        self::assertStringContainsString('data-ckeditor5-src="/bundles/nowockeditor5editor/ckeditor5-editor.js?v=', $twig->render('lazy'));
        self::assertStringEndsWith(' nonce="n1"></script>', $twig->render('lazy'));
        self::assertStringEndsWith(' nonce="n2"></script>', $twig->render('named'));
        self::assertStringStartsWith('/bundles/nowockeditor5editor/ckeditor5-editor-loader.js?v=', $twig->render('url'));
    }
}
