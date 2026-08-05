<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Unit\Security;

use Nowo\Ckeditor5EditorBundle\Security\AllowlistCkeditor5HtmlSanitizer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nowo\Ckeditor5EditorBundle\Security\AllowlistCkeditor5HtmlSanitizer
 */
final class AllowlistCkeditor5HtmlSanitizerTest extends TestCase
{
    public function testStripsScriptTags(): void
    {
        $sanitizer = new AllowlistCkeditor5HtmlSanitizer();
        $result    = $sanitizer->sanitize('<p>Hi</p><script>alert(1)</script>');

        self::assertStringNotContainsString('script', $result);
        self::assertStringContainsString('<p>Hi</p>', $result);
    }
}
