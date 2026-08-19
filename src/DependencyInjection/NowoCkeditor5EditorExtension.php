<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\DependencyInjection;

use Nowo\Ckeditor5EditorBundle\EditorTheme;
use Nowo\Ckeditor5EditorBundle\Form\Ckeditor5EditorType;
use Nowo\Ckeditor5EditorBundle\Security\AllowlistCkeditor5HtmlSanitizer;
use Nowo\Ckeditor5EditorBundle\Security\Ckeditor5HtmlSanitizerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Loads services and prepends the bundle form theme(s) to Twig.
 *
 * @author Héctor Franco Aceituno <hectorfranco@nowo.tech>
 */
final class NowoCkeditor5EditorExtension extends Extension implements PrependExtensionInterface
{
    /** @var array<string, string> */
    private const FORM_THEME_MAP = [
        'form_div_layout.html.twig'               => '@NowoCkeditor5EditorBundle/Form/ckeditor5_editor_theme.html.twig',
        'form_table_layout.html.twig'             => '@NowoCkeditor5EditorBundle/Form/ckeditor5_editor_theme_table.html.twig',
        'bootstrap_5_layout.html.twig'            => '@NowoCkeditor5EditorBundle/Form/ckeditor5_editor_theme_bootstrap5.html.twig',
        'bootstrap_5_horizontal_layout.html.twig' => '@NowoCkeditor5EditorBundle/Form/ckeditor5_editor_theme_bootstrap5_horizontal.html.twig',
        'bootstrap_4_layout.html.twig'            => '@NowoCkeditor5EditorBundle/Form/ckeditor5_editor_theme_bootstrap4.html.twig',
        'bootstrap_4_horizontal_layout.html.twig' => '@NowoCkeditor5EditorBundle/Form/ckeditor5_editor_theme_bootstrap4_horizontal.html.twig',
        'bootstrap_3_layout.html.twig'            => '@NowoCkeditor5EditorBundle/Form/ckeditor5_editor_theme_bootstrap3.html.twig',
        'bootstrap_3_horizontal_layout.html.twig' => '@NowoCkeditor5EditorBundle/Form/ckeditor5_editor_theme_bootstrap3_horizontal.html.twig',
        'foundation_5_layout.html.twig'           => '@NowoCkeditor5EditorBundle/Form/ckeditor5_editor_theme_foundation5.html.twig',
        'foundation_6_layout.html.twig'           => '@NowoCkeditor5EditorBundle/Form/ckeditor5_editor_theme_foundation6.html.twig',
        'tailwind_2_layout.html.twig'             => '@NowoCkeditor5EditorBundle/Form/ckeditor5_editor_theme_tailwind2.html.twig',
    ];

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $config         = $this->processConfiguration(new Configuration(), $configs);
        $defaultName    = $config['default_profile'];
        $defaultProfile = $config['profiles'][$defaultName];

        $container->setParameter(Configuration::ALIAS . '.default_profile', $defaultName);
        $container->setParameter(Configuration::ALIAS . '.profiles', $config['profiles']);
        // Legacy parameter names (same values) for BC.
        $container->setParameter(Configuration::ALIAS . '.default_config', $defaultName);
        $container->setParameter(Configuration::ALIAS . '.configs', $config['profiles']);

        $container->setParameter(Configuration::ALIAS . '.toolbar', $defaultProfile['toolbar']);
        $container->setParameter(Configuration::ALIAS . '.min_height', $defaultProfile['min_height']);
        $container->setParameter(Configuration::ALIAS . '.form_theme', $defaultProfile['form_theme']);
        $container->setParameter(Configuration::ALIAS . '.debug', $defaultProfile['debug']);
        $container->setParameter(Configuration::ALIAS . '.preset', $defaultProfile['preset']);
        $container->setParameter(Configuration::ALIAS . '.theme', $defaultProfile['theme'] ?? EditorTheme::Light->value);

        $this->configureHtmlSanitizer($container, $config['html_sanitizer'] ?? null);
    }

    /**
     * Opt-in HTML sanitizer for Ckeditor5EditorType (default: off for BC).
     */
    private function configureHtmlSanitizer(ContainerBuilder $container, mixed $htmlSanitizer): void
    {
        $typeDefinition = $container->getDefinition(Ckeditor5EditorType::class);

        if ($htmlSanitizer === null || $htmlSanitizer === '') {
            $typeDefinition->setArgument('$htmlSanitizer', null);

            return;
        }

        if ($htmlSanitizer === 'allowlist') {
            if (!$container->hasDefinition(AllowlistCkeditor5HtmlSanitizer::class)
                && !$container->hasAlias(AllowlistCkeditor5HtmlSanitizer::class)
            ) {
                $container->register(AllowlistCkeditor5HtmlSanitizer::class, AllowlistCkeditor5HtmlSanitizer::class);
            }
            $container->setAlias(Ckeditor5HtmlSanitizerInterface::class, AllowlistCkeditor5HtmlSanitizer::class);
        } else {
            $container->setAlias(Ckeditor5HtmlSanitizerInterface::class, (string) $htmlSanitizer);
        }

        $typeDefinition->setArgument('$htmlSanitizer', new Reference(Ckeditor5HtmlSanitizerInterface::class));
    }

    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('framework')) {
            $container->prependExtensionConfig('framework', [
                'assets' => [
                    'packages' => [
                        Configuration::ALIAS => [
                            'base_path' => '/bundles/nowockeditor5editor',
                        ],
                    ],
                ],
            ]);
        }

        if (!$container->hasExtension('twig')) {
            return;
        }

        $configs = $container->getExtensionConfig(Configuration::ALIAS);
        /** @var array{default_profile: string, profiles: array<string, array<string, mixed>>} $config */
        $config = $this->processConfiguration(new Configuration(), $configs);

        $themePaths = $this->orderedFormThemePaths($config);
        $container->prependExtensionConfig('twig', [
            'form_themes' => $themePaths,
        ]);
    }

    /**
     * @param array{default_profile: string, profiles: array<string, array<string, mixed>>} $processedConfig
     *
     * @return list<string>
     */
    private function orderedFormThemePaths(array $processedConfig): array
    {
        $profiles     = $processedConfig['profiles'];
        $defaultName  = $processedConfig['default_profile'];
        $defaultTheme = $profiles[$defaultName]['form_theme'] ?? 'form_div_layout.html.twig';
        $defaultPath  = self::FORM_THEME_MAP[$defaultTheme] ?? self::FORM_THEME_MAP['form_div_layout.html.twig'];

        $unique = [];
        foreach ($profiles as $profile) {
            $ft         = $profile['form_theme'] ?? 'form_div_layout.html.twig';
            $p          = self::FORM_THEME_MAP[$ft] ?? self::FORM_THEME_MAP['form_div_layout.html.twig'];
            $unique[$p] = true;
        }

        $others = array_keys($unique);
        sort($others);
        $rest = array_values(array_filter($others, static fn (string $p): bool => $p !== $defaultPath));

        return array_merge([$defaultPath], $rest);
    }

    public function getAlias(): string
    {
        return Configuration::ALIAS;
    }
}
