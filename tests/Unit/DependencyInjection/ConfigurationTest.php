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

        self::assertSame('default', $config['default_profile']);
        $profile = $config['profiles']['default'];
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

        $profile = $config['profiles']['default'];
        self::assertFalse($profile['toolbar']);
        self::assertSame('320px', $profile['min_height']);
        self::assertSame('bootstrap_5_layout.html.twig', $profile['form_theme']);
        self::assertTrue($profile['debug']);
        self::assertSame('simple', $profile['preset']);
    }

    public function testExplicitProfiles(): void
    {
        $processor = new Processor();
        $config    = $processor->processConfiguration(new Configuration(), [[
            'default_profile' => 'full',
            'profiles'        => [
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

        self::assertSame('full', $config['default_profile']);
        self::assertSame('minimal', $config['profiles']['full']['preset']);
        self::assertSame('dark', $config['profiles']['full']['theme']);
    }

    public function testLegacyConfigsKeysAreMapped(): void
    {
        $processor = new Processor();
        $config    = $processor->processConfiguration(new Configuration(), [[
            'default_config' => 'full',
            'configs'        => [
                'full' => [
                    'preset' => 'minimal',
                    'theme'  => 'dark',
                ],
            ],
        ]]);

        self::assertSame('full', $config['default_profile']);
        self::assertSame('minimal', $config['profiles']['full']['preset']);
        self::assertArrayNotHasKey('default_config', $config);
        self::assertArrayNotHasKey('configs', $config);
    }

    public function testVariablesPresetIsAccepted(): void
    {
        $processor = new Processor();
        $config    = $processor->processConfiguration(new Configuration(), [[
            'profiles' => [
                'default' => [
                    'preset' => 'variables',
                ],
            ],
        ]]);

        self::assertSame('variables', $config['profiles']['default']['preset']);
    }

    public function testInvalidPresetThrows(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), [[
            'profiles' => [
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
            'profiles' => [
                'default' => [
                    'theme' => 'sepia',
                ],
            ],
        ]]);
    }

    public function testDefaultProfileMustReferenceExistingProfile(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('must exist in nowo_ckeditor5_editor.profiles');

        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), [[
            'default_profile' => 'missing_profile',
            'profiles'        => [
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

    public function testHtmlSanitizerDefaultsToNull(): void
    {
        $processor = new Processor();
        $config    = $processor->processConfiguration(new Configuration(), [[]]);

        self::assertArrayHasKey('html_sanitizer', $config);
        self::assertNull($config['html_sanitizer']);
    }

    public function testFlatConfigurationPreservesHtmlSanitizer(): void
    {
        $processor = new Processor();
        $config    = $processor->processConfiguration(new Configuration(), [[
            'html_sanitizer' => 'allowlist',
        ]]);

        self::assertSame('allowlist', $config['html_sanitizer']);
    }
}
