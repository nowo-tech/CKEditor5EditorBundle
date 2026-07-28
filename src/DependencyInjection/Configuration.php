<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\DependencyInjection;

use Nowo\Ckeditor5EditorBundle\EditorPreset;
use Nowo\Ckeditor5EditorBundle\EditorTheme;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

use function array_key_exists;
use function is_array;
use function sprintf;

/**
 * Named profiles (toolbar, min_height, form_theme, debug, preset, theme) plus default profile key.
 *
 * Legacy YAML keys `default_config` / `configs` are accepted and mapped to `default_profile` / `profiles`.
 * Flat options under the root are normalized into `profiles.default`.
 *
 * @author Héctor Franco Aceituno <hectorfranco@nowo.tech>
 */
final class Configuration implements ConfigurationInterface
{
    public const ALIAS = 'nowo_ckeditor5_editor';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::ALIAS);
        $root        = $treeBuilder->getRootNode();

        $root
            ->beforeNormalization()
            ->always(static function ($v) {
                if (!is_array($v)) {
                    return $v;
                }

                if (!isset($v['profiles']) && isset($v['configs'])) {
                    $v['profiles'] = $v['configs'];
                    unset($v['configs']);
                }
                if (!isset($v['default_profile']) && isset($v['default_config'])) {
                    $v['default_profile'] = $v['default_config'];
                    unset($v['default_config']);
                }

                if (isset($v['profiles'])) {
                    return $v;
                }

                $defaultProfile = $v['default_profile'] ?? 'default';
                $profile        = [];
                foreach (['toolbar', 'min_height', 'form_theme', 'debug', 'preset', 'theme', 'upload_url'] as $key) {
                    if (array_key_exists($key, $v)) {
                        $profile[$key] = $v[$key];
                    }
                }

                return [
                    'default_profile' => $defaultProfile,
                    'profiles'        => [
                        'default' => $profile,
                    ],
                ];
            })
            ->end()
            ->children()
                ->scalarNode('default_profile')
                    ->defaultValue('default')
                    ->info('Profile name used when the form field omits the "config" option (form option key remains "config" for BC).')
                ->end()
                ->arrayNode('profiles')
                    ->info('Named profiles; each field may reference one via the "config" form option.')
                    ->useAttributeAsKey('name')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->booleanNode('toolbar')
                                ->info('When true, CKEditor shows its toolbar (preset still controls which buttons are available).')
                                ->defaultTrue()
                            ->end()
                            ->scalarNode('min_height')
                                ->info('Default CSS min-height for the editable region wrapper (e.g. 240px, 12rem).')
                                ->defaultValue('240px')
                            ->end()
                            ->scalarNode('form_theme')
                                ->info('Base Symfony form layout (must match twig.form_themes in your app).')
                                ->defaultValue('form_div_layout.html.twig')
                            ->end()
                            ->booleanNode('debug')
                                ->info('When true, the browser console receives detailed logs from the bundle script.')
                                ->defaultFalse()
                            ->end()
                            ->scalarNode('preset')
                                ->info('Editor feature preset: standard, simple, minimal, emoji, typography, variables (plugins + toolbar). Use upload_url with preset standard for server-side image uploads.')
                                ->defaultValue(EditorPreset::Standard->value)
                                ->validate()
                                    ->ifNotInArray(EditorPreset::values())
                                    ->thenInvalid('Invalid nowo_ckeditor5_editor preset %s.')
                                ->end()
                            ->end()
                            ->scalarNode('theme')
                                ->info('Chrome palette for the demo/widget wrapper: light, dark, or auto (EditorTheme).')
                                ->defaultValue(EditorTheme::Light->value)
                                ->validate()
                                    ->ifNotInArray(EditorTheme::values())
                                    ->thenInvalid('Invalid nowo_ckeditor5_editor theme %s.')
                                ->end()
                            ->end()
                            ->scalarNode('upload_url')
                                ->info('POST endpoint URL for image uploads (CKEditor SimpleUploadAdapter). Empty disables uploads.')
                                ->defaultNull()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->validate()
            ->always(static function (array $v): array {
                if (!isset($v['profiles'][$v['default_profile']])) {
                    throw new InvalidConfigurationException(sprintf('nowo_ckeditor5_editor.default_profile ("%s") must exist in nowo_ckeditor5_editor.profiles (keys: %s).', $v['default_profile'], implode(', ', array_keys($v['profiles']))));
                }

                return $v;
            })
            ->end();

        return $treeBuilder;
    }
}
