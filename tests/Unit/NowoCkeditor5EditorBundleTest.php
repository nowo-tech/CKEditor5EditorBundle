<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Unit;

use Nowo\Ckeditor5EditorBundle\DependencyInjection\Compiler\TwigPathsPass;
use Nowo\Ckeditor5EditorBundle\NowoCkeditor5EditorBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Nowo\Ckeditor5EditorBundle\NowoCkeditor5EditorBundle
 */
final class NowoCkeditor5EditorBundleTest extends TestCase
{
    public function testBundleCanBeInstantiated(): void
    {
        $bundle = new NowoCkeditor5EditorBundle();
        /* @phpstan-ignore staticMethod.alreadyNarrowedType (ensures bundle type) */
        self::assertInstanceOf(NowoCkeditor5EditorBundle::class, $bundle);
    }

    public function testBuildRegistersTwigPathsPass(): void
    {
        $bundle    = new NowoCkeditor5EditorBundle();
        $container = new ContainerBuilder();
        $bundle->build($container);

        $names = [];
        foreach ($container->getCompilerPassConfig()->getBeforeOptimizationPasses() as $pass) {
            $names[] = $pass::class;
        }

        self::assertContains(TwigPathsPass::class, $names);
    }
}
