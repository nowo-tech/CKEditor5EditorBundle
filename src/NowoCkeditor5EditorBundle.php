<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle;

use Nowo\Ckeditor5EditorBundle\DependencyInjection\Compiler\TwigPathsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Rich text form field powered by CKEditor 5 (classic editor, GPL OSS plugins via custom Vite build).
 *
 * Similar workflow to FriendsofSymfony `FOSCKEditorBundle` (YAML profiles + form theme),
 * but targets CKEditor 5 with Symfony form themes and named YAML profiles.
 *
 * @author Héctor Franco Aceituno <hectorfranco@nowo.tech>
 */
final class NowoCkeditor5EditorBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TwigPathsPass());
    }
}
