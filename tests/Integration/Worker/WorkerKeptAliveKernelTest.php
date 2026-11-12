<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Integration\Worker;

use Nowo\Ckeditor5EditorBundle\Form\Ckeditor5EditorType;
use Nowo\Ckeditor5EditorBundle\Tests\Kernel\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

use function gc_collect_cycles;
use function memory_get_usage;
use function sprintf;
use function str_contains;

/**
 * REQ-RUNTIME-002: ONE production-like kernel (debug off), `services_resetter` / `kernel.reset` never called (FrankenPHP worker, kernel reset
 * false). N iterations alternate two users (own session, own locale, own editor value): a host form with the editor is
 * built, submitted and rendered with the shared form factory and Twig. Each render must carry only its own upload CSRF
 * token, locale and value, and memory must stay flat.
 */
final class WorkerKeptAliveKernelTest extends KernelTestCase
{
    private const ITERATIONS = 1200;
    private const WARM_UP    = 100;
    private const MARGIN     = 4 * 1024 * 1024;

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function testAlternatingUsersInOneKernelLeakNothingAndKeepMemoryFlat(): void
    {
        self::bootKernel(['debug' => false]);
        $container = self::getContainer();
        $factory   = $container->get('test.nowo_ckeditor5_editor.form_factory');
        $twig      = $container->get('twig');
        $stack     = $container->get('request_stack');
        $csrf      = $container->get('security.csrf.token_manager');
        self::assertInstanceOf(FormFactoryInterface::class, $factory);
        self::assertInstanceOf(Environment::class, $twig);
        self::assertInstanceOf(RequestStack::class, $stack);
        self::assertInstanceOf(CsrfTokenManagerInterface::class, $csrf);

        $users = [
            'alpha' => ['locale' => 'en', 'text' => '<p>alpha-secret-text</p>', 'mark' => 'alpha-secret-text', 'other' => 'beta-secret-text', 'session' => new Session(new MockArraySessionStorage())],
            'beta'  => ['locale' => 'es', 'text' => '<p>beta-secret-text</p>', 'mark' => 'beta-secret-text', 'other' => 'alpha-secret-text', 'session' => new Session(new MockArraySessionStorage())],
        ];

        $baseline = 0;
        for ($i = 0; $i < self::ITERATIONS; ++$i) {
            if ($i === self::WARM_UP) {
                gc_collect_cycles();
                $baseline = memory_get_usage();
            }

            $who  = $i % 2 === 0 ? 'alpha' : 'beta';
            $user = $users[$who];

            $request = $this->requestWithSession($user['session']);
            $request->setLocale($user['locale']);
            $stack->push($request);

            try {
                $form = $factory->createNamedBuilder('host')
                    ->add('body', Ckeditor5EditorType::class, ['config' => 'upload'])
                    ->getForm();
                $form->submit(['body' => $user['text']]);
                self::assertTrue($form->isSynchronized(), sprintf('iteration %d', $i));
                self::assertSame($user['text'], $form->get('body')->getData(), sprintf('iteration %d', $i));

                $view = $form->createView();
                $html = $twig->createTemplate('{{ form_widget(form) }}')->render(['form' => $view]);
            } finally {
                $stack->pop();
            }

            $token = $view['body']->vars['ckeditor5_upload_csrf'];
            self::assertNotSame('', $token, sprintf('iteration %d', $i));
            self::assertStringContainsString('data-ckeditor5-upload-csrf-value="' . $token . '"', $html, sprintf('iteration %d', $i));
            self::assertStringContainsString('data-ckeditor5-language-value="' . $user['locale'] . '"', $html, sprintf('iteration %d', $i));
            self::assertStringContainsString($user['mark'], $html, sprintf('iteration %d', $i));
            self::assertFalse(str_contains($html, $user['other']), sprintf('iteration %d: other user data in output', $i));

            // The token is valid in the session that rendered it and in no other one (tokens are masked, so compare by validity).
            foreach ($users as $name => $candidate) {
                $stack->push($this->requestWithSession($candidate['session']));

                try {
                    self::assertSame($name === $who, $csrf->isTokenValid(new CsrfToken(Ckeditor5EditorType::CSRF_UPLOAD_TOKEN_ID, $token)), sprintf('iteration %d: token validity in session %s', $i, $name));
                } finally {
                    $stack->pop();
                }
            }
        }

        gc_collect_cycles();
        $growth = memory_get_usage() - $baseline;
        self::assertLessThan(self::MARGIN, $growth, sprintf('memory grew by %d bytes over %d iterations', $growth, self::ITERATIONS - self::WARM_UP));
    }

    private function requestWithSession(Session $session): Request
    {
        $request = Request::create('/');
        $request->setSession($session);

        return $request;
    }
}
