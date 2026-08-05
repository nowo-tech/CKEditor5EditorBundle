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

    public function testStripsInlineEventHandlers(): void
    {
        $sanitizer = new AllowlistCkeditor5HtmlSanitizer();
        $result    = $sanitizer->sanitize('<p onclick="alert(1)">Hi</p>');

        self::assertStringNotContainsString('onclick', $result);
        self::assertStringContainsString('<p>Hi</p>', $result);
    }

    public function testStripsJavascriptHref(): void
    {
        $sanitizer = new AllowlistCkeditor5HtmlSanitizer();
        $result    = $sanitizer->sanitize('<a href="javascript:alert(1)">x</a>');

        self::assertStringNotContainsString('javascript:', $result);
    }

    public function testKeepsAllowedYoutubeIframe(): void
    {
        $sanitizer = new AllowlistCkeditor5HtmlSanitizer();
        $iframe    = '<iframe src="https://www.youtube.com/embed/abc"></iframe>';
        $result    = $sanitizer->sanitize('<p>v</p>' . $iframe);

        self::assertStringContainsString('youtube.com', $result);
        self::assertStringContainsString('<iframe', $result);
    }

    public function testStripsDisallowedIframeHost(): void
    {
        $sanitizer = new AllowlistCkeditor5HtmlSanitizer();
        $result    = $sanitizer->sanitize('<iframe src="https://evil.example/embed"></iframe>');

        self::assertStringNotContainsString('iframe', $result);
        self::assertStringNotContainsString('evil.example', $result);
    }

    public function testStripsIframeWithoutSrc(): void
    {
        $sanitizer = new AllowlistCkeditor5HtmlSanitizer();
        $result    = $sanitizer->sanitize('<iframe></iframe><p>ok</p>');

        self::assertStringNotContainsString('iframe', $result);
        self::assertStringContainsString('<p>ok</p>', $result);
    }
}
