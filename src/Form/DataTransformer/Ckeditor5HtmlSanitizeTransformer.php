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
final readonly class Ckeditor5HtmlSanitizeTransformer implements DataTransformerInterface
{
    /** Wraps the sanitizer applied to submitted editor HTML. */
    public function __construct(
        private Ckeditor5HtmlSanitizerInterface $sanitizer,
    ) {
    }

    /** Returns the model value unchanged (HTML is sanitized on submit only). */
    public function transform(mixed $value): mixed
    {
        return $value;
    }

    /** Sanitizes the submitted HTML string; non-string values pass through. */
    public function reverseTransform(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return $this->sanitizer->sanitize($value);
    }
}
