<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Integration;

use Nowo\Ckeditor5EditorBundle\Form\Ckeditor5EditorType;
use Nowo\Ckeditor5EditorBundle\Tests\Kernel\TestKernel;
use Nowo\Ckeditor5EditorBundle\Twig\NowoCkeditor5EditorTwigExtension;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Twig\Environment;

use const ENT_HTML5;
use const ENT_QUOTES;
use const JSON_THROW_ON_ERROR;

/**
 * Integration tests: kernel boots with the bundle and core services are wired.
 */
#[RunTestsInSeparateProcesses]
final class BundleIntegrationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function testKernelBoots(): void
    {
        self::bootKernel();
        self::assertTrue(self::getContainer()->has('kernel'));
    }

    public function testFormTypeAndTwigExtensionAreRegistered(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        self::assertTrue($container->has(Ckeditor5EditorType::class));
        self::assertInstanceOf(Ckeditor5EditorType::class, $container->get(Ckeditor5EditorType::class));

        $twig = $container->get('twig');
        self::assertInstanceOf(Environment::class, $twig);
        self::assertTrue($twig->hasExtension(NowoCkeditor5EditorTwigExtension::class));
    }

    public function testScriptFunctionRendersThroughAssetPackage(): void
    {
        self::bootKernel();
        $twig = self::getContainer()->get('twig');
        self::assertInstanceOf(Environment::class, $twig);

        $eager = $twig->createTemplate('{{ nowo_ckeditor5_editor_script() }}')->render();
        $lazy  = $twig->createTemplate('{{ nowo_ckeditor5_editor_script({lazy: true}) }}')->render();

        self::assertMatchesRegularExpression('#^<link rel="stylesheet" href="/bundles/nowockeditor5editor/ckeditor5-editor\.css\?v=[0-9a-f]{12}"><script src="/bundles/nowockeditor5editor/ckeditor5-editor\.js\?v=[0-9a-f]{12}" defer></script>$#', $eager);
        self::assertStringContainsString('/bundles/nowockeditor5editor/ckeditor5-editor-loader.js?v=', $lazy);
        self::assertStringContainsString('data-ckeditor5-src="/bundles/nowockeditor5editor/ckeditor5-editor.js?v=', $lazy);
    }

    public function testWidgetMarkupHasNoInlineStyleElement(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $factory   = $container->get('test.nowo_ckeditor5_editor.form_factory');
        self::assertInstanceOf(FormFactoryInterface::class, $factory);
        $twig = $container->get('twig');
        self::assertInstanceOf(Environment::class, $twig);

        $view = $factory->createNamed('body', Ckeditor5EditorType::class)->createView();
        $html = $twig->createTemplate('{{ form_widget(form) }}')->render(['form' => $view]);

        self::assertStringContainsString('data-ckeditor5-root="1"', $html);
        // Widget CSS ships in the built asset (injected once per page), never as a per-instance inline <style>.
        self::assertStringNotContainsString('<style', $html);
        // Default chrome (utilities): today's markup.
        self::assertStringContainsString('<div class="ckeditor5-editor-chrome border rounded bg-white shadow-sm">', $html);
        self::assertStringContainsString('<div class="ckeditor5-editor-mount p-1" data-ckeditor5-mount="1">', $html);

        $tokens = $twig->createTemplate('{{ form_widget(form) }}')->render(['form' => $factory->createNamed('body', Ckeditor5EditorType::class, null, ['chrome' => 'tokens'])->createView()]);
        self::assertStringContainsString('<div class="ckeditor5-editor-chrome ckeditor5-editor-chrome--tokens">', $tokens);
        self::assertStringContainsString('<div class="ckeditor5-editor-mount" data-ckeditor5-mount="1">', $tokens);
        self::assertStringNotContainsString('bg-white', $tokens);
    }

    public function testWidgetExposesLanguageAndHeadingLabels(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $factory   = $container->get('test.nowo_ckeditor5_editor.form_factory');
        self::assertInstanceOf(FormFactoryInterface::class, $factory);
        $twig = $container->get('twig');
        self::assertInstanceOf(Environment::class, $twig);

        $html = $twig->createTemplate('{{ form_widget(form) }}')->render(['form' => $factory->createNamed('body', Ckeditor5EditorType::class)->createView()]);

        // No request in this context: the UI language falls back to English.
        self::assertStringContainsString('data-ckeditor5-language-value="en"', $html);
        self::assertSame(1, preg_match('/data-ckeditor5-labels-value="([^"]*)"/', $html, $m));
        $labels = json_decode(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($labels);
        self::assertSame(['paragraph', 'heading1', 'heading2', 'heading3', 'heading', 'chooseHeading', 'showMoreItems'], array_keys($labels));
        foreach ($labels as $label) {
            self::assertIsString($label);
            self::assertNotSame('', $label);
        }
    }

    public function testCsrfTokenManagerExists(): void
    {
        self::bootKernel();
        self::assertTrue(self::getContainer()->has('security.csrf.token_manager'));
    }
}
