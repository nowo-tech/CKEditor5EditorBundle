<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Form;

use Nowo\Ckeditor5EditorBundle\EditorPreset;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

use function array_replace;
use function in_array;
use function is_array;
use function is_string;
use function sprintf;

/**
 * Rich-text field: HTML in a hidden textarea, replaced by CKEditor 5 classic (see frontend bundle script).
 *
 * Options:
 * - `min_height` (string): CSS min-height for the editable area (e.g. `320px`, `min(40vh, 480px)`). Overrides the named YAML profile value when set on the field.
 * - `height` (string|null): Alias for `min_height`. If only `height` is passed, it sets min-height. When both are passed, `min_height` wins.
 * - `editor_config` (array): Optional keys (`toolbar`, `min_height`, `form_theme`, `debug`, `preset`, `theme`, `upload_url`) merged **over** the named YAML profile (`config`). Field-level `toolbar`, `min_height`, `height`, `theme` still win over merged values when set explicitly.
 *
 * @extends AbstractType<string>
 *
 * @author Héctor Franco Aceituno <hectorfranco@nowo.tech>
 */
final class Ckeditor5EditorType extends AbstractType
{
    /** CSRF token id for POST image uploads (SimpleUploadAdapter header X-CSRF-TOKEN). */
    public const CSRF_UPLOAD_TOKEN_ID = 'ckeditor_upload';

    /**
     * @param array<string, array{toolbar: bool, min_height: string, form_theme: string, debug: bool, preset: string, theme?: string, upload_url?: string|null}> $configs
     */
    public function __construct(
        private readonly array $configs,
        private readonly string $defaultConfigName,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $profile                                 = $this->mergeYamlProfileWithEditorConfig($options);
        $view->vars['ckeditor5_toolbar']         = $options['toolbar'];
        $view->vars['ckeditor5_min_height']      = $options['min_height'];
        $view->vars['ckeditor5_placeholder_key'] = $options['placeholder'];
        $view->vars['ckeditor5_debug']           = $profile['debug'];
        $view->vars['ckeditor5_preset']          = EditorPreset::fromString((string) $profile['preset'])->value;
        $view->vars['ckeditor5_theme']           = $options['theme'];

        $uploadRaw                           = $profile['upload_url'] ?? null;
        $uploadUrl                           = is_string($uploadRaw) ? trim($uploadRaw) : '';
        $view->vars['ckeditor5_upload_url']  = $uploadUrl !== '' ? $uploadUrl : null;
        $view->vars['ckeditor5_upload_csrf'] = $uploadUrl !== ''
            ? $this->csrfTokenManager->getToken(self::CSRF_UPLOAD_TOKEN_ID)->getValue()
            : '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'config'             => null,
            'editor_config'      => [],
            'placeholder'        => 'ckeditor5_placeholder',
            'attr'               => ['rows' => 10, 'cols' => 80],
            'translation_domain' => 'NowoCkeditor5EditorBundle',
            'required'           => false,
            'empty_data'         => '',
            'height'             => null,
        ]);

        $resolver->setDefault('toolbar', fn (Options $options): bool => $this->mergeYamlProfileWithEditorConfigFromOptions($options)['toolbar']);
        $resolver->setDefault('min_height', function (Options $options): string {
            $height = $options['height'];
            if (is_string($height) && trim($height) !== '') {
                return trim($height);
            }

            return $this->mergeYamlProfileWithEditorConfigFromOptions($options)['min_height'];
        });
        $resolver->setDefault('theme', fn (Options $options): string => $this->normalizeTheme((string) ($this->mergeYamlProfileWithEditorConfigFromOptions($options)['theme'] ?? 'light')));

        $resolver->setAllowedTypes('config', ['null', 'string']);
        $resolver->setAllowedTypes('editor_config', ['array']);
        $resolver->setAllowedTypes('toolbar', ['bool']);
        $resolver->setAllowedTypes('min_height', ['string']);
        $resolver->setAllowedTypes('height', ['null', 'string']);
        $resolver->setAllowedTypes('placeholder', ['null', 'string', 'bool']);
        $resolver->setAllowedTypes('theme', ['string']);

        $resolver->setNormalizer('theme', fn(Options $options, string $value): string => $this->normalizeTheme($value));

        $resolver->setNormalizer('min_height', function (Options $options, string $value): string {
            $t = trim($value);

            return $t !== '' ? $t : $this->mergeYamlProfileWithEditorConfigFromOptions($options)['min_height'];
        });

        $resolver->setNormalizer('editor_config', static function (Options $options, array $value): array {
            $allowed = ['toolbar', 'min_height', 'form_theme', 'debug', 'preset', 'theme', 'upload_url'];
            foreach (array_keys($value) as $key) {
                if (!in_array($key, $allowed, true)) {
                    throw new InvalidOptionsException(sprintf('Unknown key "%s" in editor_config. Allowed: %s.', $key, implode(', ', $allowed)));
                }
            }

            return $value;
        });

        $resolver->setNormalizer('config', function (Options $options, mixed $value): ?string {
            if ($value === null || $value === '') {
                return null;
            }
            if (!isset($this->configs[$value])) {
                throw new InvalidOptionsException(sprintf('Unknown CKEditor5 config profile "%s". Available profiles: %s.', $value, implode(', ', array_keys($this->configs))));
            }

            return $value;
        });
    }

    private function normalizeTheme(string $value): string
    {
        $t = strtolower(trim($value));

        return in_array($t, ['light', 'dark', 'auto'], true) ? $t : 'light';
    }

    /**
     * YAML profile + `editor_config` overrides (same keys as {@see Configuration} profiles).
     *
     * @param array<string, mixed> $options
     *
     * @return array{toolbar: bool, min_height: string, form_theme: string, debug: bool, preset: string, theme?: string, upload_url?: string|null}
     */
    private function mergeYamlProfileWithEditorConfig(array $options): array
    {
        $name = $options['config'] ?? $this->defaultConfigName;
        if (!isset($this->configs[$name])) {
            throw new InvalidOptionsException(sprintf('Unknown CKEditor5 config profile "%s". Available profiles: %s.', $name, implode(', ', array_keys($this->configs))));
        }

        $base = $this->configs[$name];
        $over = $options['editor_config'] ?? [];
        if (!is_array($over) || $over === []) {
            return $base;
        }

        /** @var array{toolbar: bool, min_height: string, form_theme: string, debug: bool, preset: string, theme?: string, upload_url?: string|null} $merged */
        $merged = array_replace($base, $over);

        $merged['preset'] = EditorPreset::fromString((string) $merged['preset'])->value;

        return $merged;
    }

    /**
     * @param Options<array<string, mixed>> $options
     *
     * @return array{toolbar: bool, min_height: string, form_theme: string, debug: bool, preset: string, theme?: string, upload_url?: string|null}
     */
    private function mergeYamlProfileWithEditorConfigFromOptions(Options $options): array
    {
        return $this->mergeYamlProfileWithEditorConfig([
            'config'        => $options['config'],
            'editor_config' => $options['editor_config'] ?? [],
        ]);
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'ckeditor5_editor';
    }
}
