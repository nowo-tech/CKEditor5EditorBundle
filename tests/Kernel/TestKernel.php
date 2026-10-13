<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Kernel;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Minimal kernel for integration tests (config under tests/Fixtures/app).
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function getProjectDir(): string
    {
        return __DIR__ . '/../Fixtures/app';
    }
}
