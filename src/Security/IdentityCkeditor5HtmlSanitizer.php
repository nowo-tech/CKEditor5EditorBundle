<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Security;

/**
 * Default no-op sanitizer (returns HTML unchanged). Replace with Allowlist or a custom
 * implementation before persisting user-generated content.
 */
final class IdentityCkeditor5HtmlSanitizer implements Ckeditor5HtmlSanitizerInterface
{
    public function sanitize(string $html): string
    {
        return $html;
    }
}
