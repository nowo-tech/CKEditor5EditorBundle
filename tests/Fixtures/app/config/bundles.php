<?php

declare(strict_types=1);
use Nowo\Ckeditor5EditorBundle\NowoCkeditor5EditorBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;

return [
    FrameworkBundle::class           => ['all' => true],
    TwigBundle::class                => ['all' => true],
    NowoCkeditor5EditorBundle::class => ['all' => true],
];
