<?php

declare(strict_types=1);

namespace Nowo\Ckeditor5EditorBundle\Tests\Unit;

use Nowo\Ckeditor5EditorBundle\EditorChrome;
use PHPUnit\Framework\TestCase;

use function dirname;

/**
 * @covers \Nowo\Ckeditor5EditorBundle\EditorChrome
 */
final class EditorChromeTest extends TestCase
{
    public function testValues(): void
    {
        self::assertSame(['utilities', 'tokens'], EditorChrome::values());
    }

    public function testFromString(): void
    {
        self::assertSame(EditorChrome::Utilities, EditorChrome::fromString('utilities'));
        self::assertSame(EditorChrome::Tokens, EditorChrome::fromString(' TOKENS '));
        self::assertSame(EditorChrome::Utilities, EditorChrome::fromString('unknown'));
    }

    public function testTheBuiltStylesheetPaintsTheTokenChrome(): void
    {
        $css = (string) file_get_contents(dirname(__DIR__, 2) . '/src/Resources/public/ckeditor5-editor.css');

        foreach (['.ckeditor5-editor-chrome--tokens', 'var(--nowo-ui-surface,#fff)', 'var(--nowo-ui-surface,#0f172a)', '--ck-color-toolbar-background:var(--ckeditor5-surface-muted)', '.ck-content h1', '.ck-body-wrapper'] as $needle) {
            self::assertStringContainsString($needle, $css, $needle);
        }
        // The default (utilities) dark rules keep their specificity and skip the token chrome.
        self::assertStringContainsString('.ckeditor5-editor-chrome:where(:not(.ckeditor5-editor-chrome--tokens))', $css);
    }
}
