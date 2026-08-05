<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Unit\Security;

use Nowo\Ckeditor5EditorBundle\Security\IdentityCkeditor5HtmlSanitizer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nowo\Ckeditor5EditorBundle\Security\IdentityCkeditor5HtmlSanitizer
 */
final class IdentityCkeditor5HtmlSanitizerTest extends TestCase
{
    public function testReturnsHtmlUnchanged(): void
    {
        $html      = '<p onclick="x">Hi</p><script>alert(1)</script>';
        $sanitizer = new IdentityCkeditor5HtmlSanitizer();

        self::assertSame($html, $sanitizer->sanitize($html));
    }
}
