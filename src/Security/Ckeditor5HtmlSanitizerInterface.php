<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Security;

/**
 * Sanitizes rich-text HTML from CKEditor 5. Apps should inject an implementation
 * in their persist listeners; the bundle does not persist HTML itself.
 */
interface Ckeditor5HtmlSanitizerInterface
{
    /** Returns a safe version of the given editor HTML. */
    public function sanitize(string $html): string;
}
