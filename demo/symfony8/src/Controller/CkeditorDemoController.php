<?php

declare(strict_types=1);

namespace App\Controller;

use Nowo\Ckeditor5EditorBundle\Form\Ckeditor5EditorType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

use function is_string;

/**
 * Demo Symfony 8: CKEditor 5 classic widget (OSS GPL build from the bundle).
 */
class CkeditorDemoController extends AbstractController
{
    private const LOCALE_REQ = ['_locale' => 'en|es'];

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/', name: 'app_root', methods: ['GET'])]
    public function root(): RedirectResponse
    {
        return $this->redirectToRoute('app_home', ['_locale' => 'en']);
    }

    #[Route(path: '/{_locale}/', name: 'app_home', requirements: self::LOCALE_REQ, defaults: ['_locale' => 'en'], methods: ['GET'])]
    public function home(Request $request): Response
    {
        $request->setLocale((string) $request->attributes->get('_locale', 'en'));

        return $this->render('ckeditor_demo/home.html.twig');
    }

    #[Route(path: '/{_locale}/demo', name: 'app_demo', requirements: self::LOCALE_REQ, defaults: ['_locale' => 'en'], methods: ['GET', 'POST'])]
    public function demo(Request $request): Response
    {
        $request->setLocale((string) $request->attributes->get('_locale', 'en'));
        $field = 'content';
        $html  = $request->query->get('html', '<p>Hello CKEditor 5</p>');
        $data  = [$field => is_string($html) ? $html : '<p>Hello CKEditor 5</p>'];

        $form = $this->createFormBuilder($data, ['translation_domain' => 'messages'])
            ->add($field, Ckeditor5EditorType::class, [
                'label'              => 'demo.body_label',
                'translation_domain' => 'messages',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                'success',
                $this->translator->trans('demo.saved', [], 'messages'),
            );
        }

        return $this->render('ckeditor_demo/show.html.twig', [
            'form'        => $form,
            'saved_value' => (string) ($form->get($field)->getData() ?? ''),
        ]);
    }

    #[Route(path: '/{_locale}/demo/configs', name: 'app_demo_configs', requirements: self::LOCALE_REQ, defaults: ['_locale' => 'en'], methods: ['GET', 'POST'])]
    public function demoConfigs(Request $request): Response
    {
        $request->setLocale((string) $request->attributes->get('_locale', 'en'));

        $data = [
            'content_standard' => '<p>' . $this->translator->trans('demo.sample_standard', [], 'messages') . '</p>',
            'content_simple'   => '<p>' . $this->translator->trans('demo.sample_simple', [], 'messages') . '</p>',
            'content_minimal'  => '<p>' . $this->translator->trans('demo.sample_minimal', [], 'messages') . '</p>',
        ];

        $form = $this->createFormBuilder($data, ['translation_domain' => 'messages'])
            ->add('content_standard', Ckeditor5EditorType::class, [
                'config'             => 'full',
                'label'              => 'demo.profile_standard_label',
                'help'               => 'demo.profile_standard_help',
                'translation_domain' => 'messages',
            ])
            ->add('content_simple', Ckeditor5EditorType::class, [
                'config'             => 'simple',
                'label'              => 'demo.profile_simple_label',
                'help'               => 'demo.profile_simple_help',
                'translation_domain' => 'messages',
            ])
            ->add('content_minimal', Ckeditor5EditorType::class, [
                'config'             => 'minimal',
                'label'              => 'demo.profile_minimal_label',
                'help'               => 'demo.profile_minimal_help',
                'translation_domain' => 'messages',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                'success',
                $this->translator->trans('demo.saved_configs', [], 'messages'),
            );
        }

        return $this->render('ckeditor_demo/configs.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{_locale}/demo/inline-config', name: 'app_demo_inline_config', requirements: self::LOCALE_REQ, defaults: ['_locale' => 'en'], methods: ['GET', 'POST'])]
    public function demoInlineConfig(Request $request): Response
    {
        $request->setLocale((string) $request->attributes->get('_locale', 'en'));

        $field = 'content';
        $data  = [
            $field => '<p>' . $this->translator->trans('demo.inline_config_sample_html', [], 'messages') . '</p>',
        ];

        $form = $this->createFormBuilder($data, ['translation_domain' => 'messages'])
            ->add($field, Ckeditor5EditorType::class, [
                'config'        => 'minimal',
                'editor_config' => [
                    'preset'     => 'standard',
                    'min_height' => 'min(32vh, 480px)',
                ],
                'label'              => 'demo.inline_config_field_label',
                'help'               => 'demo.inline_config_field_help',
                'translation_domain' => 'messages',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                'success',
                $this->translator->trans('demo.saved', [], 'messages'),
            );
        }

        return $this->render('ckeditor_demo/inline_config.html.twig', [
            'form'        => $form,
            'saved_value' => (string) ($form->get($field)->getData() ?? ''),
        ]);
    }

    #[Route(path: '/{_locale}/demo/variants', name: 'app_demo_variants', requirements: self::LOCALE_REQ, defaults: ['_locale' => 'en'], methods: ['GET', 'POST'])]
    public function demoVariants(Request $request): Response
    {
        $request->setLocale((string) $request->attributes->get('_locale', 'en'));

        $data = [
            'content_compact'   => '<p>' . $this->translator->trans('demo.sample_compact', [], 'messages') . '</p>',
            'content_folio'     => '<p>' . $this->translator->trans('demo.sample_folio', [], 'messages') . '</p>',
            'content_folio_env' => '<p>' . $this->translator->trans('demo.sample_folio_env', [], 'messages') . '</p>',
            'content_dark'      => '<p>' . $this->translator->trans('demo.sample_dark', [], 'messages') . '</p>',
            'content_auto'      => '<p>' . $this->translator->trans('demo.sample_auto', [], 'messages') . '</p>',
            'content_reading'   => '<p>' . $this->translator->trans('demo.sample_reading', [], 'messages') . '</p>',
        ];

        $form = $this->createFormBuilder($data, ['translation_domain' => 'messages'])
            ->add('content_compact', Ckeditor5EditorType::class, [
                'config'             => 'compact',
                'label'              => 'demo.variant_compact_label',
                'help'               => 'demo.variant_compact_help',
                'translation_domain' => 'messages',
            ])
            ->add('content_folio', Ckeditor5EditorType::class, [
                'config'             => 'folio',
                'label'              => 'demo.variant_folio_label',
                'help'               => 'demo.variant_folio_help',
                'translation_domain' => 'messages',
            ])
            ->add('content_folio_env', Ckeditor5EditorType::class, [
                'config'             => 'folio_env',
                'label'              => 'demo.variant_folio_env_label',
                'help'               => 'demo.variant_folio_env_help',
                'translation_domain' => 'messages',
            ])
            ->add('content_dark', Ckeditor5EditorType::class, [
                'config'             => 'dark_note',
                'label'              => 'demo.variant_dark_label',
                'help'               => 'demo.variant_dark_help',
                'translation_domain' => 'messages',
            ])
            ->add('content_auto', Ckeditor5EditorType::class, [
                'config'             => 'auto_panel',
                'label'              => 'demo.variant_auto_label',
                'help'               => 'demo.variant_auto_help',
                'translation_domain' => 'messages',
            ])
            ->add('content_reading', Ckeditor5EditorType::class, [
                'config'             => 'reading_mode',
                'label'              => 'demo.variant_reading_label',
                'help'               => 'demo.variant_reading_help',
                'translation_domain' => 'messages',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                'success',
                $this->translator->trans('demo.saved_variants', [], 'messages'),
            );
        }

        return $this->render('ckeditor_demo/variants.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{_locale}/demo/emoji', name: 'app_demo_emoji', requirements: self::LOCALE_REQ, defaults: ['_locale' => 'en'], methods: ['GET', 'POST'])]
    public function demoEmoji(Request $request): Response
    {
        $request->setLocale((string) $request->attributes->get('_locale', 'en'));

        $field = 'content';
        $data  = [
            $field => '<p>' . $this->translator->trans('demo.emoji_sample_html', [], 'messages') . '</p>',
        ];

        $form = $this->createFormBuilder($data, ['translation_domain' => 'messages'])
            ->add($field, Ckeditor5EditorType::class, [
                'config'             => 'with_emoji',
                'label'              => 'demo.emoji_field_label',
                'help'               => 'demo.emoji_field_help',
                'translation_domain' => 'messages',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                'success',
                $this->translator->trans('demo.saved', [], 'messages'),
            );
        }

        return $this->render('ckeditor_demo/emoji.html.twig', [
            'form'        => $form,
            'saved_value' => (string) ($form->get($field)->getData() ?? ''),
        ]);
    }

    #[Route(path: '/{_locale}/demo/typography', name: 'app_demo_typography', requirements: self::LOCALE_REQ, defaults: ['_locale' => 'en'], methods: ['GET', 'POST'])]
    public function demoTypography(Request $request): Response
    {
        $request->setLocale((string) $request->attributes->get('_locale', 'en'));

        $field = 'content';
        $data  = [
            $field => '<p>' . $this->translator->trans('demo.typography_sample_html', [], 'messages') . '</p>',
        ];

        $form = $this->createFormBuilder($data, ['translation_domain' => 'messages'])
            ->add($field, Ckeditor5EditorType::class, [
                'config'             => 'typography_styled',
                'label'              => 'demo.typography_field_label',
                'help'               => 'demo.typography_field_help',
                'translation_domain' => 'messages',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                'success',
                $this->translator->trans('demo.saved', [], 'messages'),
            );
        }

        return $this->render('ckeditor_demo/typography.html.twig', [
            'form'        => $form,
            'saved_value' => (string) ($form->get($field)->getData() ?? ''),
        ]);
    }

    #[Route(path: '/{_locale}/demo/variables', name: 'app_demo_variables', requirements: self::LOCALE_REQ, defaults: ['_locale' => 'en'], methods: ['GET', 'POST'])]
    public function demoVariables(Request $request): Response
    {
        $request->setLocale((string) $request->attributes->get('_locale', 'en'));

        $field = 'content';
        $data  = [
            $field => '<p>' . $this->translator->trans('demo.variables_sample_html', [], 'messages') . '</p>',
        ];

        $form = $this->createFormBuilder($data, ['translation_domain' => 'messages'])
            ->add($field, Ckeditor5EditorType::class, [
                'config'             => 'with_variables',
                'label'              => 'demo.variables_field_label',
                'help'               => 'demo.variables_field_help',
                'translation_domain' => 'messages',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                'success',
                $this->translator->trans('demo.saved', [], 'messages'),
            );
        }

        return $this->render('ckeditor_demo/variables.html.twig', [
            'form'        => $form,
            'saved_value' => (string) ($form->get($field)->getData() ?? ''),
        ]);
    }

    #[Route(path: '/{_locale}/demo/upload-images', name: 'app_demo_upload_images', requirements: self::LOCALE_REQ, defaults: ['_locale' => 'en'], methods: ['GET', 'POST'])]
    public function demoUploadImages(Request $request): Response
    {
        $request->setLocale((string) $request->attributes->get('_locale', 'en'));

        $field = 'content';
        $data  = [
            $field => '<p>' . $this->translator->trans('demo.upload_images_sample_html', [], 'messages') . '</p>',
        ];

        $form = $this->createFormBuilder($data, ['translation_domain' => 'messages'])
            ->add($field, Ckeditor5EditorType::class, [
                'config'             => 'with_image_upload',
                'label'              => 'demo.upload_images_field_label',
                'help'               => 'demo.upload_images_field_help',
                'translation_domain' => 'messages',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                'success',
                $this->translator->trans('demo.saved', [], 'messages'),
            );
        }

        return $this->render('ckeditor_demo/upload_images.html.twig', [
            'form'        => $form,
            'saved_value' => (string) ($form->get($field)->getData() ?? ''),
        ]);
    }
}
