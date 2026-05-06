<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\DependencyInjection;

use Nowo\Ckeditor5EditorBundle\EditorPreset;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

use function array_key_exists;
use function is_array;
use function sprintf;

/**
 * Named profiles (toolbar, min_height, form_theme, debug, preset, theme) plus default profile key.
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
                if (isset($v['configs'])) {
                    return $v;
                }

                $defaultConfig = $v['default_config'] ?? 'default';
                $profile       = [];
                foreach (['toolbar', 'min_height', 'form_theme', 'debug', 'preset', 'theme', 'upload_url'] as $key) {
                    if (array_key_exists($key, $v)) {
                        $profile[$key] = $v[$key];
                    }
                }

                return [
                    'default_config' => $defaultConfig,
                    'configs'        => [
                        'default' => $profile,
                    ],
                ];
            })
            ->end()
            ->children()
                ->scalarNode('default_config')
                    ->defaultValue('default')
                    ->info('Profile name used when the form field omits the "config" option.')
                ->end()
                ->arrayNode('configs')
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
                                ->info('Chrome palette for the demo/widget wrapper: light, dark, or auto.')
                                ->defaultValue('light')
                                ->validate()
                                    ->ifNotInArray(['light', 'dark', 'auto'])
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
                if (!isset($v['configs'][$v['default_config']])) {
                    throw new InvalidConfigurationException(sprintf('nowo_ckeditor5_editor.default_config ("%s") must exist in nowo_ckeditor5_editor.configs (keys: %s).', $v['default_config'], implode(', ', array_keys($v['configs']))));
                }

                return $v;
            })
            ->end();

        return $treeBuilder;
    }
}
