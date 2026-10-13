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

        self::assertMatchesRegularExpression('#^<script src="/bundles/nowockeditor5editor/ckeditor5-editor\.js\?v=[0-9a-f]{12}" defer></script>$#', $eager);
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
    }

    public function testCsrfTokenManagerExists(): void
    {
        self::bootKernel();
        self::assertTrue(self::getContainer()->has('security.csrf.token_manager'));
    }
}
