<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Integration;

use Nowo\Ckeditor5EditorBundle\Form\Ckeditor5EditorType;
use Nowo\Ckeditor5EditorBundle\Tests\Kernel\TestKernel;
use Nowo\Ckeditor5EditorBundle\Twig\NowoCkeditor5EditorTwigExtension;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

/**
 * Integration tests: kernel boots with the bundle and core services are wired.
 */
#[RunTestsInSeparateProcesses]
final class BundleIntegrationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function testKernelBoots(): void
    {
        self::bootKernel();
        self::assertTrue(self::getContainer()->has('kernel'));
    }

    public function testFormTypeAndTwigExtensionAreRegistered(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        self::assertTrue($container->has(Ckeditor5EditorType::class));
        self::assertInstanceOf(Ckeditor5EditorType::class, $container->get(Ckeditor5EditorType::class));

        $twig = $container->get('twig');
        self::assertInstanceOf(Environment::class, $twig);
        self::assertTrue($twig->hasExtension(NowoCkeditor5EditorTwigExtension::class));
    }

    public function testCsrfTokenManagerExists(): void
    {
        self::bootKernel();
        self::assertTrue(self::getContainer()->has('security.csrf.token_manager'));
    }
}
