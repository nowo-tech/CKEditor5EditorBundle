<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Unit\Security;

use Nowo\Ckeditor5EditorBundle\Security\AllowlistCkeditor5HtmlSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nowo\Ckeditor5EditorBundle\Security\AllowlistCkeditor5HtmlSanitizer
 * @covers \Nowo\Ckeditor5EditorBundle\Security\EmbedIframeSrcAttributeSanitizer
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

    public function testKeepsCommonBlockAndInlineMarkup(): void
    {
        $sanitizer = new AllowlistCkeditor5HtmlSanitizer();
        $html      = '<figure><img src="/x.png"><figcaption>Cap</figcaption></figure><h5>H</h5><p>a<sub>2</sub><sup>3</sup><mark>m</mark></p>';
        $result    = $sanitizer->sanitize($html);

        self::assertStringContainsString('<figure>', $result);
        self::assertStringContainsString('<figcaption>', $result);
        self::assertStringContainsString('<h5>', $result);
        self::assertStringContainsString('<sub>', $result);
        self::assertStringContainsString('<sup>', $result);
        self::assertStringContainsString('<mark>', $result);
    }

    public function testStripsUnquotedEventHandler(): void
    {
        $sanitizer = new AllowlistCkeditor5HtmlSanitizer();
        $result    = $sanitizer->sanitize('<img src="x.png" onerror=alert(1)>');

        self::assertStringNotContainsString('onerror', $result);
        self::assertStringNotContainsString('alert', $result);
    }

    public function testStripsEventHandlerGluedToPreviousAttribute(): void
    {
        $sanitizer = new AllowlistCkeditor5HtmlSanitizer();
        $result    = $sanitizer->sanitize('<img src="x.png"onerror=alert(1)>');

        self::assertStringNotContainsString('onerror', $result);
        self::assertStringNotContainsString('alert', $result);
    }

    public function testStripsUnquotedJavascriptHref(): void
    {
        $sanitizer = new AllowlistCkeditor5HtmlSanitizer();
        $result    = $sanitizer->sanitize('<a href=javascript:alert(1)>x</a>');

        self::assertStringNotContainsString('javascript:', $result);
        self::assertStringNotContainsString('alert', $result);
    }

    public function testDropsSrcdocFromAllowedYoutubeIframe(): void
    {
        $sanitizer = new AllowlistCkeditor5HtmlSanitizer();
        $result    = $sanitizer->sanitize('<iframe src="https://www.youtube.com/embed/abc" srcdoc="<script>alert(1)</script>"></iframe>');

        self::assertStringContainsString('youtube.com', $result);
        self::assertStringNotContainsString('srcdoc', $result);
        self::assertStringNotContainsString('script', $result);
        self::assertStringNotContainsString('alert', $result);
    }

    public function testStrictDropsEveryIframe(): void
    {
        $sanitizer = new AllowlistCkeditor5HtmlSanitizer(allowEmbeds: false);
        $result    = $sanitizer->sanitize('<p>v</p><iframe src="https://www.youtube.com/embed/abc"></iframe>');

        self::assertStringNotContainsString('iframe', $result);
        self::assertStringNotContainsString('youtube.com', $result);
        self::assertStringContainsString('<p>v</p>', $result);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function eventHandlerPayloads(): iterable
    {
        yield 'slash before handler, unquoted' => ['<img/onerror=alert(1) src=x>'];
        yield 'slash before handler, double quoted' => ['<img/onerror="alert(1)" src="x">'];
        yield 'slash before handler, single quoted' => ["<img/onerror='alert(1)' src='x'>"];
        yield 'newline separator' => ["<img src=x\nonerror=alert(1)>"];
        yield 'uppercase handler' => ['<IMG SRC=x ONERROR=alert(1)>'];
        yield 'svg onload' => ['<svg/onload=alert(1)>'];
    }

    #[DataProvider('eventHandlerPayloads')]
    public function testEventHandlerBypassPayloadsAreNeutralised(string $payload): void
    {
        foreach ([true, false] as $allowEmbeds) {
            $result = (new AllowlistCkeditor5HtmlSanitizer($allowEmbeds))->sanitize($payload);

            self::assertStringNotContainsStringIgnoringCase('onerror', $result);
            self::assertStringNotContainsStringIgnoringCase('onload', $result);
            self::assertStringNotContainsString('alert', $result);
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function encodedSchemePayloads(): iterable
    {
        yield 'decimal entity, double quoted' => ['<a href="&#106;avascript:alert(1)">x</a>'];
        yield 'hex entity, single quoted' => ["<a href='&#x6A;avascript:alert(1)'>x</a>"];
        yield 'decimal entity, unquoted' => ['<a href=&#106;avascript:alert(1)>x</a>'];
        yield 'entity-encoded colon' => ['<a href="javascript&colon;alert(1)">x</a>'];
        yield 'tab inside scheme' => ["<a href=\"java\tscript:alert(1)\">x</a>"];
        yield 'leading spaces and mixed case' => ['<a href="  JaVaScRiPt:alert(1)">x</a>'];
        yield 'vbscript' => ['<a href="vbscript:msgbox(1)">x</a>'];
        yield 'data html link' => ['<a href="data:text/html,<script>alert(1)</script>">x</a>'];
        yield 'encoded img src' => ['<img src="&#106;avascript:alert(1)">'];
    }

    #[DataProvider('encodedSchemePayloads')]
    public function testEncodedDangerousSchemesAreDropped(string $payload): void
    {
        $result = (new AllowlistCkeditor5HtmlSanitizer())->sanitize($payload);

        self::assertStringNotContainsStringIgnoringCase('href=', $result);
        self::assertStringNotContainsStringIgnoringCase('src=', $result);
        self::assertStringNotContainsString('alert', $result);
        self::assertStringNotContainsString('msgbox', $result);
    }

    public function testKeepsSafeLinksAndImages(): void
    {
        $result = (new AllowlistCkeditor5HtmlSanitizer())->sanitize(
            '<a href="https://example.com/a" target="_blank" rel="noopener">h</a><a href="mailto:me@example.com">m</a><a href="/rel">r</a><img src="https://cdn.example.com/i.png" alt="i" width="10">',
        );

        self::assertStringContainsString('href="https://example.com/a"', $result);
        self::assertStringContainsString('target="_blank"', $result);
        self::assertStringContainsString('href="mailto:', $result);
        self::assertStringContainsString('href="/rel"', $result);
        self::assertStringContainsString('src="https://cdn.example.com/i.png"', $result);
        self::assertStringContainsString('alt="i"', $result);
    }

    public function testDropsDataImageSource(): void
    {
        $result = (new AllowlistCkeditor5HtmlSanitizer())->sanitize('<img src="data:image/svg+xml;base64,PHN2Zz4=">');

        self::assertStringNotContainsString('data:', $result);
    }

    public function testKeepsTextOfUnlistedWrapperButDropsDangerousContainers(): void
    {
        $result = (new AllowlistCkeditor5HtmlSanitizer())->sanitize('<section>kept</section><style>p{}</style><object data="x">o</object>');

        self::assertStringContainsString('kept', $result);
        self::assertStringNotContainsString('section', $result);
        self::assertStringNotContainsString('style', $result);
        self::assertStringNotContainsString('object', $result);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function rejectedIframeSources(): iterable
    {
        yield 'javascript scheme with allowed host' => ['javascript://www.youtube.com/%0Aalert(document.cookie)'];
        yield 'protocol-relative' => ['//www.youtube.com/embed/abc'];
        yield 'plain http' => ['http://www.youtube.com/embed/abc'];
        yield 'relative path' => ['/embed/abc'];
        yield 'look-alike host' => ['https://www.youtube.com.evil.example/embed/abc'];
    }

    #[DataProvider('rejectedIframeSources')]
    public function testRejectsIframeSourcesOutsideHttpsAllowlist(string $src): void
    {
        $result = (new AllowlistCkeditor5HtmlSanitizer())->sanitize('<p>v</p><iframe src="' . $src . '"></iframe>');

        self::assertStringNotContainsString('iframe', $result);
        self::assertStringContainsString('<p>v</p>', $result);
    }

    public function testKeepsAllowedVimeoIframeOnly(): void
    {
        $result = (new AllowlistCkeditor5HtmlSanitizer())->sanitize('<iframe src="https://player.vimeo.com/video/1" onload="alert(1)" width="1"></iframe>');

        self::assertSame('<iframe src="https://player.vimeo.com/video/1"></iframe>', $result);
    }

    public function testSanitizesLongDocumentsWithoutTruncation(): void
    {
        $html   = str_repeat('<p>lorem ipsum dolor sit amet</p>', 2_000);
        $result = (new AllowlistCkeditor5HtmlSanitizer())->sanitize($html);

        self::assertSame($html, $result);
    }
}
