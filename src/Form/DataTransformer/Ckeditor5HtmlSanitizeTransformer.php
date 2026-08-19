<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Form\DataTransformer;

use Nowo\Ckeditor5EditorBundle\Security\Ckeditor5HtmlSanitizerInterface;
use Symfony\Component\Form\DataTransformerInterface;

use function is_string;

/**
 * Model transformer that sanitizes HTML on submit when a sanitizer is configured.
 *
 * @implements DataTransformerInterface<string|null, string|null>
 */
final class Ckeditor5HtmlSanitizeTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly Ckeditor5HtmlSanitizerInterface $sanitizer,
    ) {
    }

    public function transform(mixed $value): mixed
    {
        return $value;
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return $this->sanitizer->sanitize($value);
    }
}
