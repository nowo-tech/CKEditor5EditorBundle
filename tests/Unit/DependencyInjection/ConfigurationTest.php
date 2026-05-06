<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Unit\DependencyInjection;

use Nowo\Ckeditor5EditorBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

/**
 * @covers \Nowo\Ckeditor5EditorBundle\DependencyInjection\Configuration
 */
final class ConfigurationTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $processor = new Processor();
        $config    = $processor->processConfiguration(new Configuration(), [[]]);

        self::assertSame('default', $config['default_config']);
        $profile = $config['configs']['default'];
        self::assertTrue($profile['toolbar']);
        self::assertSame('240px', $profile['min_height']);
        self::assertSame('form_div_layout.html.twig', $profile['form_theme']);
        self::assertFalse($profile['debug']);
        self::assertSame('standard', $profile['preset']);
        self::assertSame('light', $profile['theme']);
    }

    public function testCustomConfigurationFlatNormalization(): void
    {
        $processor = new Processor();
        $config    = $processor->processConfiguration(new Configuration(), [[
            'toolbar'    => false,
            'min_height' => '320px',
            'form_theme' => 'bootstrap_5_layout.html.twig',
            'debug'      => true,
            'preset'     => 'simple',
        ]]);

        $profile = $config['configs']['default'];
        self::assertFalse($profile['toolbar']);
        self::assertSame('320px', $profile['min_height']);
        self::assertSame('bootstrap_5_layout.html.twig', $profile['form_theme']);
        self::assertTrue($profile['debug']);
        self::assertSame('simple', $profile['preset']);
    }

    public function testExplicitConfigs(): void
    {
        $processor = new Processor();
        $config    = $processor->processConfiguration(new Configuration(), [[
            'default_config' => 'full',
            'configs'        => [
                'full' => [
                    'toolbar'    => true,
                    'min_height' => '400px',
                    'form_theme' => 'bootstrap_5_layout.html.twig',
                    'debug'      => true,
                    'preset'     => 'minimal',
                    'theme'      => 'dark',
                ],
            ],
        ]]);

        self::assertSame('full', $config['default_config']);
        self::assertSame('minimal', $config['configs']['full']['preset']);
        self::assertSame('dark', $config['configs']['full']['theme']);
    }

    public function testVariablesPresetIsAccepted(): void
    {
        $processor = new Processor();
        $config    = $processor->processConfiguration(new Configuration(), [[
            'configs' => [
                'default' => [
                    'preset' => 'variables',
                ],
            ],
        ]]);

        self::assertSame('variables', $config['configs']['default']['preset']);
    }

    public function testInvalidPresetThrows(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), [[
            'configs' => [
                'default' => [
                    'preset' => 'enterprise-premium-only',
                ],
            ],
        ]]);
    }

    public function testInvalidThemeThrows(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), [[
            'configs' => [
                'default' => [
                    'theme' => 'sepia',
                ],
            ],
        ]]);
    }

    public function testDefaultConfigMustReferenceExistingProfile(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('must exist in nowo_ckeditor5_editor.configs');

        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), [[
            'default_config' => 'missing_profile',
            'configs'        => [
                'default' => [],
            ],
        ]]);
    }

    public function testMergedScalarChunkNormalizesThroughEarlyReturn(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), [true]);
    }
}
