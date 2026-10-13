<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Integration;

use Nowo\Ckeditor5EditorBundle\Form\Ckeditor5EditorType;
use Nowo\Ckeditor5EditorBundle\Tests\Kernel\NoCsrfTestKernel;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The container must compile when the host app disables CSRF protection.
 */
#[RunTestsInSeparateProcesses]
final class NoCsrfIntegrationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return NoCsrfTestKernel::class;
    }

    public function testFormTypeIsAvailableWithoutCsrfTokenManager(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        self::assertFalse($container->has('security.csrf.token_manager'));
        self::assertInstanceOf(Ckeditor5EditorType::class, $container->get(Ckeditor5EditorType::class));
    }
}
