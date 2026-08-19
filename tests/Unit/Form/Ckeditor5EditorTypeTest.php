<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Unit\Form;

use Nowo\Ckeditor5EditorBundle\Form\Ckeditor5EditorType;
use Nowo\Ckeditor5EditorBundle\Form\DataTransformer\Ckeditor5HtmlSanitizeTransformer;
use Nowo\Ckeditor5EditorBundle\Security\AllowlistCkeditor5HtmlSanitizer;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @covers \Nowo\Ckeditor5EditorBundle\Form\Ckeditor5EditorType
 */
final class Ckeditor5EditorTypeTest extends TestCase
{
    /**
     * @return array<string, array{toolbar: bool, min_height: string, form_theme: string, debug: bool, preset: string, theme?: string}>
     */
    private function sampleConfigs(bool $toolbar = true, string $minHeight = '240px', bool $debug = false, ?string $profileTheme = null): array
    {
        $profile = [
            'toolbar'    => $toolbar,
            'min_height' => $minHeight,
            'form_theme' => 'form_div_layout.html.twig',
            'debug'      => $debug,
            'preset'     => 'standard',
        ];
        if ($profileTheme !== null) {
            $profile['theme'] = $profileTheme;
        }

        return ['default' => $profile];
    }

    /**
     * @return array<string, array{toolbar: bool, min_height: string, form_theme: string, debug: bool, preset: string, theme?: string}>
     */
    private function sampleConfigsTwoProfiles(): array
    {
        return [
            'default' => [
                'toolbar'    => true,
                'min_height' => '240px',
                'form_theme' => 'form_div_layout.html.twig',
                'debug'      => false,
                'preset'     => 'standard',
                'theme'      => 'light',
            ],
            'full' => [
                'toolbar'    => false,
                'min_height' => '480px',
                'form_theme' => 'form_div_layout.html.twig',
                'debug'      => true,
                'preset'     => 'simple',
                'theme'      => 'auto',
            ],
        ];
    }

    private function createCsrfTokenManager(): CsrfTokenManagerInterface
    {
        $m = $this->createMock(CsrfTokenManagerInterface::class);
        $m->method('getToken')->willReturn(new CsrfToken(Ckeditor5EditorType::CSRF_UPLOAD_TOKEN_ID, 'test-csrf'));

        return $m;
    }

    private function createType(bool $toolbar = true, string $minHeight = '240px', bool $debug = false): Ckeditor5EditorType
    {
        return new Ckeditor5EditorType($this->sampleConfigs($toolbar, $minHeight, $debug), 'default', $this->createCsrfTokenManager());
    }

    public function testDefaultOptions(): void
    {
        $type     = $this->createType();
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([]);

        self::assertTrue($options['toolbar']);
        self::assertSame('240px', $options['min_height']);
        self::assertSame([], $options['editor_config']);
        self::assertSame('ckeditor5_placeholder', $options['placeholder']);
        self::assertSame('NowoCkeditor5EditorBundle', $options['translation_domain']);
        self::assertFalse($options['required']);
    }

    public function testResolveWithToolbarFalse(): void
    {
        $type     = $this->createType();
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve(['toolbar' => false]);

        self::assertFalse($options['toolbar']);
    }

    public function testGetParent(): void
    {
        $type = $this->createType();
        self::assertSame(TextareaType::class, $type->getParent());
    }

    public function testGetBlockPrefix(): void
    {
        $type = $this->createType();
        self::assertSame('ckeditor5_editor', $type->getBlockPrefix());
    }

    public function testBuildViewSetsVars(): void
    {
        $type = $this->createType(true, '300px', true);
        $view = new FormView();
        $form = $this->createStub(FormInterface::class);

        $type->buildView($view, $form, [
            'toolbar'            => false,
            'min_height'         => '300px',
            'placeholder'        => 'custom_ph',
            'attr'               => [],
            'translation_domain' => 'messages',
            'required'           => false,
            'empty_data'         => '',
            'config'             => null,
            'editor_config'      => [],
            'height'             => null,
            'theme'              => 'light',
        ]);

        self::assertFalse($view->vars['ckeditor5_toolbar']);
        self::assertSame('300px', $view->vars['ckeditor5_min_height']);
        self::assertSame('custom_ph', $view->vars['ckeditor5_placeholder_key']);
        self::assertTrue($view->vars['ckeditor5_debug']);
    }

    public function testConstructorDefaultsPassedToOptions(): void
    {
        $type     = new Ckeditor5EditorType($this->sampleConfigs(false, '120px', false), 'default', $this->createCsrfTokenManager());
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([]);

        self::assertFalse($options['toolbar']);
        self::assertSame('120px', $options['min_height']);
    }

    public function testResolveUsesProfileThemeFromYaml(): void
    {
        $type     = new Ckeditor5EditorType($this->sampleConfigs(true, '240px', false, 'dark'), 'default', $this->createCsrfTokenManager());
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([]);

        self::assertSame('dark', $options['theme']);
    }

    public function testResolveThemeOptionOverridesWithNormalization(): void
    {
        $type     = $this->createType();
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve(['theme' => 'purple']);

        self::assertSame('light', $options['theme']);
    }

    public function testResolveWithNamedConfigProfile(): void
    {
        $type     = new Ckeditor5EditorType($this->sampleConfigsTwoProfiles(), 'default', $this->createCsrfTokenManager());
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve(['config' => 'full']);

        self::assertSame('full', $options['config']);
        self::assertFalse($options['toolbar']);
        self::assertSame('480px', $options['min_height']);
        self::assertSame('auto', $options['theme']);
    }

    public function testMinHeightOptionOverridesProfile(): void
    {
        $type     = new Ckeditor5EditorType($this->sampleConfigsTwoProfiles(), 'default', $this->createCsrfTokenManager());
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([
            'config'     => 'full',
            'min_height' => 'min(50vh, 640px)',
        ]);

        self::assertSame('min(50vh, 640px)', $options['min_height']);
    }

    public function testHeightOptionAliasWhenMinHeightOmitted(): void
    {
        $type     = new Ckeditor5EditorType($this->sampleConfigsTwoProfiles(), 'default', $this->createCsrfTokenManager());
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([
            'config' => 'full',
            'height' => '  420px ',
        ]);

        self::assertSame('420px', $options['min_height']);
    }

    public function testMinHeightWinsOverHeightWhenBothSet(): void
    {
        $type     = new Ckeditor5EditorType($this->sampleConfigsTwoProfiles(), 'default', $this->createCsrfTokenManager());
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([
            'config'     => 'full',
            'height'     => '100px',
            'min_height' => '200px',
        ]);

        self::assertSame('200px', $options['min_height']);
    }

    public function testEditorConfigMergesOverYamlDefaults(): void
    {
        $type = new Ckeditor5EditorType([
            'default' => [
                'toolbar'    => true,
                'min_height' => '200px',
                'form_theme' => 'form_div_layout.html.twig',
                'debug'      => false,
                'preset'     => 'minimal',
                'theme'      => 'light',
            ],
        ], 'default', $this->createCsrfTokenManager());
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([
            'editor_config' => [
                'preset'     => 'standard',
                'min_height' => '400px',
                'debug'      => true,
            ],
        ]);

        self::assertSame('400px', $options['min_height']);
        self::assertTrue($options['toolbar']);

        $view = new FormView();
        $form = $this->createStub(FormInterface::class);
        $type->buildView($view, $form, $options);

        self::assertSame('standard', $view->vars['ckeditor5_preset']);
        self::assertTrue($view->vars['ckeditor5_debug']);
    }

    public function testEditorConfigUnknownKeyThrows(): void
    {
        $type     = $this->createType();
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('Unknown key');

        $resolver->resolve(['editor_config' => ['not_a_valid_key' => true]]);
    }

    public function testFieldMinHeightStillOverridesEditorConfig(): void
    {
        $type = new Ckeditor5EditorType([
            'default' => [
                'toolbar'    => true,
                'min_height' => '200px',
                'form_theme' => 'form_div_layout.html.twig',
                'debug'      => false,
                'preset'     => 'simple',
                'theme'      => 'light',
            ],
        ], 'default', $this->createCsrfTokenManager());
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve([
            'editor_config' => ['min_height' => '400px'],
            'min_height'    => '120px',
        ]);

        self::assertSame('120px', $options['min_height']);
    }

    public function testResolveUnknownConfigProfileThrows(): void
    {
        $type     = $this->createType();
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('Unknown CKEditor5 config profile');

        $resolver->resolve(['config' => 'missing']);
    }

    public function testResolveFailsWhenDefaultProfileKeyMissing(): void
    {
        $type = new Ckeditor5EditorType([
            'other' => [
                'toolbar'    => true,
                'min_height' => '10px',
                'form_theme' => 'form_div_layout.html.twig',
                'debug'      => false,
                'preset'     => 'minimal',
            ],
        ], 'default', $this->createCsrfTokenManager());

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('Unknown CKEditor5 config profile');

        $resolver->resolve([]);
    }

    public function testBuildViewSetsPresetFromProfile(): void
    {
        $type = new Ckeditor5EditorType([
            'default' => [
                'toolbar'    => true,
                'min_height' => '240px',
                'form_theme' => 'form_div_layout.html.twig',
                'debug'      => false,
                'preset'     => 'minimal',
            ],
        ], 'default', $this->createCsrfTokenManager());

        $view = new FormView();
        $form = $this->createStub(FormInterface::class);

        $type->buildView($view, $form, [
            'toolbar'            => true,
            'min_height'         => '240px',
            'placeholder'        => false,
            'attr'               => [],
            'translation_domain' => 'messages',
            'required'           => false,
            'empty_data'         => '',
            'config'             => null,
            'editor_config'      => [],
            'height'             => null,
            'theme'              => 'dark',
        ]);

        self::assertSame('minimal', $view->vars['ckeditor5_preset']);
        self::assertSame('dark', $view->vars['ckeditor5_theme']);
        self::assertFalse($view->vars['ckeditor5_placeholder_key']);
    }

    public function testBuildViewWithUploadUrlSetsCsrf(): void
    {
        $type = new Ckeditor5EditorType([
            'default' => [
                'toolbar'    => true,
                'min_height' => '240px',
                'form_theme' => 'form_div_layout.html.twig',
                'debug'      => false,
                'preset'     => 'minimal',
                'upload_url' => '/api/upload',
            ],
        ], 'default', $this->createCsrfTokenManager());

        $view = new FormView();
        $form = $this->createStub(FormInterface::class);

        $type->buildView($view, $form, [
            'toolbar'            => true,
            'min_height'         => '240px',
            'placeholder'        => 'ckeditor5_placeholder',
            'attr'               => [],
            'translation_domain' => 'messages',
            'required'           => false,
            'empty_data'         => '',
            'config'             => null,
            'editor_config'      => [],
            'height'             => null,
            'theme'              => 'light',
        ]);

        self::assertSame('/api/upload', $view->vars['ckeditor5_upload_url']);
        self::assertSame('test-csrf', $view->vars['ckeditor5_upload_csrf']);
    }

    public function testNormalizeThemePrivate(): void
    {
        $type = $this->createType();
        $m    = new ReflectionMethod(Ckeditor5EditorType::class, 'normalizeTheme');

        self::assertSame('light', $m->invoke($type, 'invalid-theme'));
        self::assertSame('auto', $m->invoke($type, ' AuTo '));
    }

    public function testBuildFormAddsSanitizerTransformer(): void
    {
        $type    = new Ckeditor5EditorType(
            $this->sampleConfigs(),
            'default',
            $this->createCsrfTokenManager(),
            new AllowlistCkeditor5HtmlSanitizer(),
        );
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())
            ->method('addModelTransformer')
            ->with(self::isInstanceOf(Ckeditor5HtmlSanitizeTransformer::class));

        $type->buildForm($builder, []);
    }

    public function testBuildFormSkipsTransformerWithoutSanitizer(): void
    {
        $type    = $this->createType();
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::never())->method('addModelTransformer');

        $type->buildForm($builder, []);
    }
}
