<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Kernel;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Same app as {@see TestKernel} but with `framework.csrf_protection: false` (host apps without CSRF).
 */
final class NoCsrfTestKernel extends Kernel
{
    use MicroKernelTrait {
        configureContainer as private configureContainerFromProject;
    }

    public function getProjectDir(): string
    {
        return __DIR__ . '/../Fixtures/app';
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment . '_nocsrf';
    }

    private function configureContainer(ContainerConfigurator $container): void
    {
        $this->configureContainerFromProject($container);
        $container->extension('framework', ['csrf_protection' => false]);
    }
}
