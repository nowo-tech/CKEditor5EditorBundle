<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Unit\DependencyInjection;

use Nowo\Ckeditor5EditorBundle\DependencyInjection\Configuration;
use Nowo\Ckeditor5EditorBundle\DependencyInjection\NowoCkeditor5EditorExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @covers \Nowo\Ckeditor5EditorBundle\DependencyInjection\NowoCkeditor5EditorExtension
 */
final class NowoCkeditor5EditorExtensionTest extends TestCase
{
    public function testGetAlias(): void
    {
        $extension = new NowoCkeditor5EditorExtension();
        self::assertSame(Configuration::ALIAS, $extension->getAlias());
    }

    public function testLoadRegistersParameters(): void
    {
        $container = new ContainerBuilder();
        $extension = new NowoCkeditor5EditorExtension();
        $extension->load([[]], $container);

        self::assertTrue($container->hasParameter('nowo_ckeditor5_editor.default_profile'));
        self::assertTrue($container->hasParameter('nowo_ckeditor5_editor.profiles'));
        self::assertTrue($container->hasParameter('nowo_ckeditor5_editor.default_config'));
        self::assertTrue($container->hasParameter('nowo_ckeditor5_editor.configs'));
        self::assertSame(
            $container->getParameter('nowo_ckeditor5_editor.default_profile'),
            $container->getParameter('nowo_ckeditor5_editor.default_config'),
        );
        self::assertSame(
            $container->getParameter('nowo_ckeditor5_editor.profiles'),
            $container->getParameter('nowo_ckeditor5_editor.configs'),
        );
        self::assertTrue($container->hasParameter('nowo_ckeditor5_editor.toolbar'));
        self::assertTrue($container->hasParameter('nowo_ckeditor5_editor.min_height'));
        self::assertTrue($container->hasParameter('nowo_ckeditor5_editor.form_theme'));
        self::assertTrue($container->hasParameter('nowo_ckeditor5_editor.debug'));
        self::assertTrue($container->hasParameter('nowo_ckeditor5_editor.preset'));
    }

    public function testLoadWithCustomConfig(): void
    {
        $container = new ContainerBuilder();
        $extension = new NowoCkeditor5EditorExtension();
        $extension->load([[
            'toolbar'    => false,
            'min_height' => '400px',
            'form_theme' => 'bootstrap_5_layout.html.twig',
            'debug'      => true,
            'preset'     => 'minimal',
        ]], $container);

        self::assertFalse($container->getParameter('nowo_ckeditor5_editor.toolbar'));
        self::assertSame('400px', $container->getParameter('nowo_ckeditor5_editor.min_height'));
        self::assertSame('bootstrap_5_layout.html.twig', $container->getParameter('nowo_ckeditor5_editor.form_theme'));
        self::assertTrue($container->getParameter('nowo_ckeditor5_editor.debug'));
        self::assertSame('minimal', $container->getParameter('nowo_ckeditor5_editor.preset'));
    }

    public function testPrependAddsTwigFormTheme(): void
    {
        $twigExtension = new class extends Extension {
            public function load(array $configs, ContainerBuilder $container): void
            {
            }

            public function getAlias(): string
            {
                return 'twig';
            }
        };
        $container = new ContainerBuilder();
        $container->registerExtension($twigExtension);
        $container->loadFromExtension('twig', ['strict_variables' => false]);
        $container->registerExtension(new NowoCkeditor5EditorExtension());
        $container->loadFromExtension(Configuration::ALIAS, ['form_theme' => 'form_div_layout.html.twig']);

        $extension = new NowoCkeditor5EditorExtension();
        $extension->prepend($container);

        $twigConfig = $container->getExtensionConfig('twig');
        self::assertNotEmpty($twigConfig);
        $config = $twigConfig[0] ?? [];
        self::assertArrayHasKey('form_themes', $config);
    }

    public function testPrependUsesDefaultThemeWhenUnknown(): void
    {
        $twigExtension = new class extends Extension {
            public function load(array $configs, ContainerBuilder $container): void
            {
            }

            public function getAlias(): string
            {
                return 'twig';
            }
        };
        $container = new ContainerBuilder();
        $container->registerExtension($twigExtension);
        $container->loadFromExtension('twig', []);
        $container->registerExtension(new NowoCkeditor5EditorExtension());
        $container->loadFromExtension(Configuration::ALIAS, ['form_theme' => 'unknown_theme.html.twig']);

        $extension = new NowoCkeditor5EditorExtension();
        $extension->prepend($container);

        $twigConfig = $container->getExtensionConfig('twig');
        $config     = $twigConfig[0] ?? [];
        self::assertContains('@NowoCkeditor5EditorBundle/Form/ckeditor5_editor_theme.html.twig', $config['form_themes']);
    }

    public function testPrependSkipsWhenTwigNotLoaded(): void
    {
        $container = new ContainerBuilder();
        $extension = new NowoCkeditor5EditorExtension();
        $extension->prepend($container);
        self::assertFalse($container->hasExtension('twig'));
    }

    public function testPrependConfiguresAssets(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new FrameworkExtension());
        (new NowoCkeditor5EditorExtension())->prepend($container);
        $configs = $container->getExtensionConfig('framework');
        self::assertSame('/bundles/nowockeditor5editor', $configs[0]['assets']['packages']['nowo_ckeditor5_editor']['base_path']);
    }
}
